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
use Drupal\civiremote_funding\Form\NewApplicationForm;
use Drupal\civiremote_funding\Form\NewFundingCaseForm;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class NewApplicationController extends ControllerBase {

  private FundingApi $fundingApi;

  public function __construct(FundingApi $fundingApi) {
    $this->fundingApi = $fundingApi;
  }

  /**
   * @return array<int|string, mixed>
   */
  public function form(int $fundingCaseTypeId, Request $request): array {
    $combinedApplication = $request->getSession()->get("$fundingCaseTypeId:combinedApplication");

    if (!is_bool($combinedApplication)) {
      $fundingCaseType = $this->fundingApi->getFundingCaseType($fundingCaseTypeId);
      if (NULL === $fundingCaseType) {
        throw new NotFoundHttpException();
      }
      $combinedApplication = $fundingCaseType->getIsCombinedApplication();
      $request->getSession()->set("$fundingCaseTypeId:combinedApplication", $combinedApplication);
    }

    if ($combinedApplication) {
      return $this->formBuilder()->getForm(NewFundingCaseForm::class);
    }

    return $this->formBuilder()->getForm(NewApplicationForm::class);
  }

}
