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

namespace Drupal\civiremote_funding\Controller;

use Drupal\civiremote_funding\Api\FundingApi;
use Drupal\civiremote_funding\RemotePage\RemotePageProxy;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class DrawdownSubmitConfirmationDownloadController {

  private FundingApi $fundingApi;

  private RemotePageProxy $remotePageProxy;

  public function __construct(
    FundingApi $fundingApi,
    RemotePageProxy $remotePageProxy
  ) {
    $this->fundingApi = $fundingApi;
    $this->remotePageProxy = $remotePageProxy;
  }

  /**
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   * @throws \Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function download(int $drawdownId): Response {
    $uri = $this->getDownloadUri($drawdownId);

    return $this->remotePageProxy->get($uri);
  }

  /**
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  private function getDownloadUri(int $drawdownId): string {
    $drawdown = $this->fundingApi->getDrawdown($drawdownId);
    if (NULL === $drawdown?->getSubmitConfirmationDocumentUri()) {
      throw new NotFoundHttpException();
    }

    return $drawdown->getSubmitConfirmationDocumentUri();
  }

}
