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

use Drupal\civiremote_funding\Access\RemoteContactIdProviderInterface;
use Drupal\civiremote_funding\Api\FundingApi;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ApplicationHistoryController extends ControllerBase {

  private FundingApi $fundingApi;

  private RemoteContactIdProviderInterface $contactIdProvider;

  public function __construct(FundingApi $fundingApi, RemoteContactIdProviderInterface $contactIdProvider) {
    $this->fundingApi = $fundingApi;
    $this->contactIdProvider = $contactIdProvider;
  }

  /**
   * @return array<string, mixed>
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function content(int $applicationProcessId, Request $request): array {
    $fundingCaseInfo = $this->fundingApi->getFundingCaseInfoByApplicationProcessId(
      $this->contactIdProvider->getRemoteContactId(),
      $applicationProcessId,
    );
    if (NULL === $fundingCaseInfo) {
      throw new NotFoundHttpException();
    }

    $destination = $request->query->get('destination');
    if (!is_string($destination) || !str_starts_with($destination, '/')) {
      $destination = NULL;
    }

    return [
      '#theme' => 'civiremote_funding_application_history',
      '#title' => $this->t('History'),
      '#destination' => $destination,
      '#application_title' => $fundingCaseInfo->getApplicationProcessTitle(),
      '#activities' => $this->fundingApi->getApplicationActivities(
        $this->contactIdProvider->getRemoteContactId(),
        $applicationProcessId
      ),
      '#statusLabels' => $this->fundingApi->getApplicationStatusLabels(
        $this->contactIdProvider->getRemoteContactId(),
        $applicationProcessId
      ),
    ];
  }

}
