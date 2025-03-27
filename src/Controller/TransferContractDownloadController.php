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

final class TransferContractDownloadController {

  private FundingApi $fundingApi;

  private RemotePageProxy $remotePageProxy;

  public function __construct(
    FundingApi $fundingApi,
    RemotePageProxy $remotePageProxy
  ) {
    $this->fundingApi = $fundingApi;
    $this->remotePageProxy = $remotePageProxy;
  }

  public function download(int $fundingCaseId): Response {
    $uri = $this->getDownloadUri($fundingCaseId);

    return $this->remotePageProxy->get($uri);
  }

  private function getDownloadUri(int $fundingCaseId): string {
    $fundingCase = $this->fundingApi->getFundingCase($fundingCaseId);
    if (NULL === $fundingCase || NULL === $fundingCase->getTransferContractUri()
      || !in_array('contract_view', $fundingCase->getPermissions(), TRUE)
    ) {
      throw new NotFoundHttpException();
    }

    return $fundingCase->getTransferContractUri();
  }

}
