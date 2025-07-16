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

final class ApplicationController extends ControllerBase {

  private FundingApi $fundingApi;

  public function __construct(FundingApi $fundingApi) {
    $this->fundingApi = $fundingApi;
  }

  /**
   * @return array<int|string, mixed>
   */
  public function form(int $applicationProcessId): array {
    $form = $this->formBuilder()->getForm(ApplicationForm::class);

    // Add identifier to beginning of the form if not already in the title.
    $applicationProcess = $this->fundingApi->getApplicationProcess($applicationProcessId,);
    if (NULL !== $applicationProcess && !str_contains($form['#title'], $applicationProcess->getIdentifier())) {
      $form = array_merge([
        '_identifier' => [
          '#plain_text' => $applicationProcess->getIdentifier(),
        ],
      ], $form);
    }

    return $form;
  }

  public function title(int $applicationProcessId): ?string {
    $info = $this->fundingApi->getApplicationProcess($applicationProcessId);

    return NULL === $info ? NULL : $info->getIdentifier();
  }

}
