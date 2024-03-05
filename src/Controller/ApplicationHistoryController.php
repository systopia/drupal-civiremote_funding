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
use Drupal\civiremote_funding\Util\DestinationUtil;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ApplicationHistoryController extends ControllerBase {

  private FundingApi $fundingApi;

  public function __construct(FundingApi $fundingApi) {
    $this->fundingApi = $fundingApi;
  }

  /**
   * @return array<string, mixed>
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function content(int $applicationProcessId, Request $request): array {
    $info = $this->fundingApi->getFundingCaseInfoByApplicationProcessId($applicationProcessId);
    if (NULL === $info) {
      throw new NotFoundHttpException();
    }

    return [
      '#type' => 'civiremote_funding_application_history',
      '#title' => $this->t('Application History'),
      '#application_title' => $info->getApplicationProcessTitle(),
      '#back_link_destination' => DestinationUtil::getDestinationWithoutBasePath($request),
      '#activities' => $this->fundingApi->getApplicationActivities($applicationProcessId),
      '#status_options' => $this->fundingApi->getApplicationStatusOptions($applicationProcessId),
      '#clearing_status_options' => $this->fundingApi->getClearingStatusOptions(),
    ];
  }

}
