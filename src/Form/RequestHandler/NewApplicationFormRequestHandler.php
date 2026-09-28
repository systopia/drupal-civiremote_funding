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
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\HttpFoundation\Request;

final class NewApplicationFormRequestHandler implements FormRequestHandlerInterface {

  private FundingApi $fundingApi;

  public function __construct(FundingApi $fundingApi) {
    $this->fundingApi = $fundingApi;
  }

  public function getForm(Request $request): FundingForm {
    $routeMatch = RouteMatch::createFromRequest($request);

    return $this->fundingApi->getNewApplicationForm(
      $this->getFundingProgramId($routeMatch),
      $this->getFundingCaseTypeId($routeMatch),
      $this->getCopyDataFromId($request),
    );
  }

  public function validateForm(Request $request, array $data): FormValidationResponse {
    $routeMatch = RouteMatch::createFromRequest($request);

    return $this->fundingApi->validateNewApplicationForm(
      $this->getFundingProgramId($routeMatch),
      $this->getFundingCaseTypeId($routeMatch),
      $data,
    );
  }

  public function submitForm(Request $request, array $data): FormSubmitResponse {
    $routeMatch = RouteMatch::createFromRequest($request);

    return $this->fundingApi->submitNewApplicationForm(
      $this->getFundingProgramId($routeMatch),
      $this->getFundingCaseTypeId($routeMatch),
      $data,
    );
  }

  private function getCopyDataFromId(Request $request): ?int {
    $copyDataFromId = $request->query->get('copyDataFromId');
    if (NULL === $copyDataFromId) {
      return NULL;
    }

    Assertion::integerish($copyDataFromId);

    return (int) $copyDataFromId;
  }

  private function getFundingCaseTypeId(RouteMatchInterface $routeMatch): int {
    $fundingCaseTypeId = $routeMatch->getParameter('fundingCaseTypeId');
    Assertion::integerish($fundingCaseTypeId);

    return (int) $fundingCaseTypeId;
  }

  private function getFundingProgramId(RouteMatchInterface $routeMatch): int {
    $fundingProgramId = $routeMatch->getParameter('fundingProgramId');
    Assertion::integerish($fundingProgramId);

    return (int) $fundingProgramId;
  }

}
