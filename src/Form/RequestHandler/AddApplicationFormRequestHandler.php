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

namespace Drupal\civiremote_funding\Form\RequestHandler;

use Assert\Assertion;
use Drupal\civiremote_funding\Api\Form\FormSubmitResponse;
use Drupal\civiremote_funding\Api\Form\FormValidationResponse;
use Drupal\civiremote_funding\Api\Form\FundingForm;
use Drupal\civiremote_funding\Api\FundingApi;
use Drupal\Core\Routing\RouteMatch;
use Symfony\Component\HttpFoundation\Request;

final class AddApplicationFormRequestHandler implements FormRequestHandlerInterface {

  private FundingApi $fundingApi;

  public function __construct(FundingApi $fundingApi) {
    $this->fundingApi = $fundingApi;
  }

  public function getForm(Request $request): FundingForm {
    return $this->fundingApi->getAddApplicationForm(
      $this->getFundingCaseId($request),
      $request->query->has('copyDataFromId') ? (int) $request->query->get('copyDataFromId') : NULL,
    );
  }

  public function validateForm(Request $request, array $data): FormValidationResponse {
    return $this->fundingApi->validateAddApplicationForm(
      $this->getFundingCaseId($request),
      $data,
    );
  }

  public function submitForm(Request $request, array $data): FormSubmitResponse {
    return $this->fundingApi->submitAddApplicationForm(
      $this->getFundingCaseId($request),
      $data,
    );
  }

  private function getFundingCaseId(Request $request): int {
    $routeMatch = RouteMatch::createFromRequest($request);
    $fundingCaseId = $routeMatch->getParameter('fundingCaseId');
    Assertion::integerish($fundingCaseId);

    return (int) $fundingCaseId;
  }

}
