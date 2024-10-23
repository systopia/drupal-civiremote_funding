<?php

/*
 * Copyright (C) 2023 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Drupal\civiremote_funding\RemotePage;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class RemotePageProxy {

  private const RETURNED_HEADERS = [
    'Content-Type',
    'Content-Length',
    'Content-Disposition',
  ];

  private RemotePageClient $client;

  private LoggerInterface $logger;

  public function __construct(RemotePageClient $client, LoggerInterface $logger) {
    $this->client = $client;
    $this->logger = $logger;
  }

  /**
   * @param int|null $timeoutSeconds
   *   Timeout in seconds to use. (NULL means default timeout.)
   *
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   * @throws \Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException
   */
  public function get(string $uri, ?int $timeoutSeconds = NULL): Response {
    try {
      $remoteResponse = $this->client->request('GET', $uri, ['timeout' => $timeoutSeconds]);
    }
    catch (GuzzleException $e) {
      $this->logger->error(sprintf('Loading "%s" from CiviCRM failed: %s', $uri, $e->getMessage()));

      throw new ServiceUnavailableHttpException(NULL, '', $e, $e->getCode());
    }

    if (Response::HTTP_NOT_FOUND === $remoteResponse->getStatusCode()) {
      throw new NotFoundHttpException();
    }

    if (Response::HTTP_UNAUTHORIZED === $remoteResponse->getStatusCode()) {
      $this->logger->error('Authentication at CiviCRM failed', [
        'uri' => $uri,
      ]);

      throw new ServiceUnavailableHttpException();
    }

    if (Response::HTTP_FORBIDDEN === $remoteResponse->getStatusCode()) {
      throw new AccessDeniedHttpException();
    }

    if (Response::HTTP_OK === $remoteResponse->getStatusCode()) {
      return new StreamedResponse(
        function () use ($remoteResponse) {
          $body = $remoteResponse->getBody();
          while (!$body->eof()) {
            echo $body->read(1024);
          }
        },
        Response::HTTP_OK,
        $this->buildResponseHeaders($remoteResponse),
      );
    }

    $this->logger->error(sprintf('Unexpected response while loading "%s" from CiviCRM', $uri), [
      'statusCode' => $remoteResponse->getStatusCode(),
      'reasonPhrase' => $remoteResponse->getReasonPhrase(),
    ]);

    throw new ServiceUnavailableHttpException();
  }

  /**
   * @phpstan-return array<string, array<string>>
   */
  private function buildResponseHeaders(ResponseInterface $remoteResponse): array {
    $headers = [];
    foreach (self::RETURNED_HEADERS as $headerName) {
      if ($remoteResponse->hasHeader($headerName)) {
        $headers[$headerName] = $remoteResponse->getHeader($headerName);
      }
    }

    return $headers;
  }

}
