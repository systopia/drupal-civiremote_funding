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
use Drupal\civiremote_funding\Form\ClearingForm;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;

final class ClearingController extends ControllerBase {

  private FundingApi $fundingApi;

  public function __construct(FundingApi $fundingApi) {
    $this->fundingApi = $fundingApi;
  }

  /**
   * @return array<int|string, mixed>
   */
  public function formByApplicationProcessId(Request $request, int $applicationProcessId): array {
    $clearingProcess = $this->fundingApi->getOrCreateClearingProcess($applicationProcessId);
    $request->attributes->set('clearingProcessId', $clearingProcess->getId());

    $info = $this->fundingApi->getFundingCaseInfoByApplicationProcessId(
      $applicationProcessId,
    );

    $form = $this->formBuilder()->getForm(ClearingForm::class);

    // Add identifier to beginning of the form if not already in the title.
    if (NULL !== $info && !str_contains($form['#title'], $info->getApplicationProcesIdentifier())) {
      $form = array_merge([
        '_identifier' => [
          '#plain_text' => $info->getApplicationProcesIdentifier(),
        ],
      ], $form);
    }

    return $form;
  }

  public function title(int $applicationProcessId): ?string {
    // @todo Return appropriate title.
    return NULL;
  }

}
