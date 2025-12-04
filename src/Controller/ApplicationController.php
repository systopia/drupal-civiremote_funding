<?php

/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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
use Drupal\civiremote_funding\Form\ApplicationForm;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

final class ApplicationController extends ControllerBase {

  private FundingApi $fundingApi;

  public function __construct(FundingApi $fundingApi) {
    $this->fundingApi = $fundingApi;
  }

  /**
   * @return array<int|string, mixed>
   */
  public function form(int $applicationProcessId, Request $request): array {
    $form = $this->formBuilder()->getForm(ApplicationForm::class);

    $container = [
      '#title' => $form['#title'],
      '#type' => 'container',
    ];

    $applicationProcess = $this->fundingApi->getApplicationProcess($applicationProcessId,);
    if (NULL !== $applicationProcess) {
      // Add identifier if not already in the title.
      if (!str_contains($container['#title'], $applicationProcess->getIdentifier())) {
        $container['identifier'] = ['#markup' => '<h2>' . $applicationProcess->getIdentifier() . '</h2>'];
      }

      $history = [
        '#type' => 'civiremote_funding_application_history',
        '#activities' => $this->fundingApi->getApplicationActivities($applicationProcessId),
        '#status_options' => $this->fundingApi->getApplicationStatusOptions($applicationProcessId),
        '#clearing_status_options' => $this->fundingApi->getClearingStatusOptions(),
      ];
    }
    else {
      $history = [];
    }

    $container['tabs'] = [
      '#theme' => 'tabby_tabs',
      '#labels' => [
        $this->t('Application'),
        $this->t('History'),
      ],
      '#content' => [$form, $history],
    ];

    return $container;
  }

  public function title(int $applicationProcessId): ?string {
    $applicationProcess = $this->fundingApi->getApplicationProcess($applicationProcessId);

    return $applicationProcess?->getIdentifier();
  }

}
