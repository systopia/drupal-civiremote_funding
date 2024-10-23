<?php

/*
 * Copyright (C) 2024 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
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
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

final class ApplicationTemplateRenderController extends ControllerBase {

  private FundingApi $fundingApi;

  private RemotePageProxy $remotePageProxy;

  public function __construct(
    FundingApi $fundingApi,
    RemotePageProxy $remotePageProxy
  ) {
    $this->fundingApi = $fundingApi;
    $this->remotePageProxy = $remotePageProxy;
  }

  public function render(int $applicationProcessId, int $templateId): Response {
    $uri = $this->getDownloadUri($applicationProcessId, $templateId);

    return $this->remotePageProxy->get($uri, 20);
  }

  private function getDownloadUri(int $applicationProcessId, int $templateId): string {
    return $this->fundingApi->getApplicationTemplateRenderUri($applicationProcessId, $templateId);
  }

}
