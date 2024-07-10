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

namespace Drupal\civiremote_funding\File;

use Assert\Assertion;
use Drupal\civiremote_funding\Entity\FundingFileInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileRepositoryInterface;
use Drupal\file\FileStorageInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Mime\MimeTypeGuesserInterface;

class FundingFileDownloader {

  private FileRepositoryInterface $fileRepository;

  private FileStorageInterface $fileStorage;

  private FundingFileHttpClient $fundingFileHttpClient;

  private FundingFileManager $fundingFileManager;

  private LoggerInterface $logger;

  private MimeTypeGuesserInterface $mimeTypeGuesser;

  public function __construct(
    FileRepositoryInterface $fileRepository,
    FileStorageInterface $fileStorage,
    FundingFileHttpClient $fundingFileHttpClient,
    FundingFileManager $fundingFileManager,
    LoggerInterface $logger,
    MimeTypeGuesserInterface $mimeTypeGuesser
  ) {
    $this->fileRepository = $fileRepository;
    $this->fileStorage = $fileStorage;
    $this->fundingFileHttpClient = $fundingFileHttpClient;
    $this->fundingFileManager = $fundingFileManager;
    $this->logger = $logger;
    $this->mimeTypeGuesser = $mimeTypeGuesser;
  }

  /**
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   * @throws \Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException
   */
  public function download(FundingFileInterface $fundingFile): void {
    Assertion::notEmpty($fundingFile->getCiviUri());
    Assertion::notNull($fundingFile->getFile());
    Assertion::notNull($fundingFile->getFile()->getFileUri());

    try {
      $response = $this->fundingFileHttpClient->get($fundingFile);
    }
    catch (GuzzleException $e) {
      $this->logger->error(sprintf('Downloading file from CiviCRM failed: %s', $e->getMessage()), [
        'uri' => $fundingFile->getCiviUri(),
      ]);

      throw new ServiceUnavailableHttpException(NULL, '', $e, $e->getCode());
    }

    if (404 === $response->getStatusCode()) {
      $this->fundingFileManager->delete($fundingFile);

      throw new NotFoundHttpException();
    }

    if (401 === $response->getStatusCode()) {
      $this->logger->error('Authentication at CiviCRM failed', [
        'uri' => $fundingFile->getCiviUri(),
      ]);

      throw new ServiceUnavailableHttpException();
    }

    if (403 === $response->getStatusCode()) {
      throw new AccessDeniedHttpException();
    }

    if (200 === $response->getStatusCode()) {
      // For large files it might make sense to write data in chunks instead of
      // loading the whole file in memory. The Drupal API doesn't support this,
      // though.
      $file = $this->fileRepository->writeData(
        (string) $response->getBody(),
        $fundingFile->getFile()->getFileUri(),
        FileSystemInterface::EXISTS_REPLACE
      );

      Assertion::notNull($file->getFileUri());
      $file->setMimeType($this->mimeTypeGuesser->guessMimeType($file->getFileUri()) ?? 'application/octet-stream');
      $this->fileStorage->save($file);
      $fundingFile->setLastModified($response->getHeaderLine('Last-Modified'));
      $this->fundingFileManager->save($fundingFile);
    }
    elseif (304 !== $response->getStatusCode()) {
      $this->logger->error('Unexpected response while downloading file from CiviCRM', [
        'uri' => $fundingFile->getCiviUri(),
        'statusCode' => $response->getStatusCode(),
        'reasonPhrase' => $response->getReasonPhrase(),
      ]);

      throw new ServiceUnavailableHttpException();
    }

  }

}
