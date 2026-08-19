<?php

/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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

use Drupal\civiremote_funding\Api\DTO\FundingCase;
use Drupal\civiremote_funding\Api\Exception\ApiCallFailedException;
use Drupal\civiremote_funding\Api\FundingApi;
use Drupal\civiremote_funding\Form\ChooseFundingCaseForm;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ChooseFundingCaseController extends ControllerBase {

  public function __construct(
    private readonly FundingApi $fundingApi
  ) {}

  /**
   * @return array<int|string, mixed>
   */
  public function form(Request $request): array {
    $copyDataFromId = $request->query->get('copyDataFromId');
    if (NULL === $copyDataFromId) {
      throw new NotFoundHttpException('Query parameter copyDataFromId is missing');
    }

    if (!ctype_digit((string) $copyDataFromId)) {
      throw new BadRequestHttpException('copyDataFromId is not an integer');
    }

    try {
      $copyDataFromId = (int) $copyDataFromId;
      $applicationProcess = $this->fundingApi->getApplicationProcess(
        $copyDataFromId,
        ['funding_case_id.funding_case_type_id']
      );
      if (NULL === $applicationProcess) {
        throw new NotFoundHttpException("Application process with ID $copyDataFromId not found");
      }

      $fundingCaseTypeId = $applicationProcess->get('funding_case_id.funding_case_type_id');
      assert(is_int($fundingCaseTypeId));

      $fundingCaseOptions = $this->getFundingCaseOptions($fundingCaseTypeId);
    }
    catch (ApiCallFailedException $e) {
      $this->messenger()->addError(
        $this->t('Failed to load funding cases: @error', ['@error' => $e->getMessage()])
      );

      return [];
    }

    $fundingCaseOptionsCount = count($fundingCaseOptions);
    if (0 === $fundingCaseOptionsCount) {
      $this->messenger()->addStatus($this->t('No matching funding cases found.'));

      return [];
    }

    return $this->formBuilder()->getForm(
      ChooseFundingCaseForm::class,
      $fundingCaseOptions,
      $copyDataFromId
    );
  }

  /**
   * @return array<int, string>
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  private function getFundingCaseOptions(int $fundingCaseTypeId): array {
    $options = [];
    $fundingCases = $this->fundingApi->getFundingCasesByFundingCaseTypeNotClosed($fundingCaseTypeId, ['id' => 'DESC']);
    foreach ($fundingCases as $fundingCase) {
      if ($this->isNewApplicationPossible($fundingCase)) {
        $options[$fundingCase->getId()] = $fundingCase->getIdentifier();
      }
    }

    return $options;
  }

  /**
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  private function isNewApplicationPossible(FundingCase $fundingCase): bool {
    return [] !== $this->fundingApi->getAllowedApplicationProcessActionsInitialByFundingCase($fundingCase->getId());
  }

}
