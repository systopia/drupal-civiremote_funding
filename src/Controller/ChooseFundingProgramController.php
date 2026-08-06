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

use Drupal\civiremote_funding\Api\DTO\FundingProgram;
use Drupal\civiremote_funding\Api\Exception\ApiCallFailedException;
use Drupal\civiremote_funding\Api\FundingApi;
use Drupal\civiremote_funding\Form\ChooseFundingProgramForm;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ChooseFundingProgramController extends ControllerBase {

  private FundingApi $fundingApi;

  private UrlGeneratorInterface $urlGenerator;

  public function __construct(FundingApi $fundingApi, UrlGeneratorInterface $urlGenerator) {
    $this->fundingApi = $fundingApi;
    $this->urlGenerator = $urlGenerator;
  }

  /**
   * @return array<int|string, mixed>|RedirectResponse
   */
  public function form(Request $request) {
    $copyDataFromId = $request->query->get('copyDataFromId');

    try {
      if (NULL === $copyDataFromId) {
        $fundingCaseTypeId = NULL;
      }
      else {
        if ((int) $copyDataFromId != $copyDataFromId) {
          throw new BadRequestHttpException('copyDataFromId is not an integer');
        }

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
      }

      $fundingProgramOptions = $this->getFundingProgramOptions($fundingCaseTypeId);
    }
    catch (ApiCallFailedException $e) {
      $this->messenger()->addError(
        $this->t('Failed to load available funding programs: @error', ['@error' => $e->getMessage()])
      );

      return [];
    }

    $fundingProgramOptionsCount = count($fundingProgramOptions);
    if (0 === $fundingProgramOptionsCount) {
      $this->messenger()->addStatus($this->t('Currently there is no funding program for new applications.'));

      return [];
    }

    if (1 === $fundingProgramOptionsCount) {
      $fundingProgramId = key($fundingProgramOptions);
      try {
        $fundingCaseTypes = $this->fundingApi->getFundingCaseTypesByFundingProgramId(
          $fundingProgramId
        );
      }
      catch (ApiCallFailedException $e) {
        $this->messenger()->addError(
          $this->t('Failed to load funding case types: @error', ['@error' => $e->getMessage()])
        );

        return [];
      }

      if (NULL !== $fundingCaseTypeId) {
        $fundingCaseTypes = array_filter(
          $fundingCaseTypes,
          fn($fundingCaseType) => $fundingCaseType->getId() === $fundingCaseTypeId
        );
      }

      if (0 === count($fundingCaseTypes)) {
        $this->messenger()->addError($this->t('No funding case type available in the selected funding program.'));

        return [];
      }

      // @todo Support funding programs with multiple funding case types.
      $url = $this->urlGenerator->generate(
        'civiremote_funding.new_application_form',
        [
          'fundingProgramId' => $fundingProgramId,
          'fundingCaseTypeId' => $fundingCaseTypes[0]->getId(),
        ],
      );
      if (NULL !== $copyDataFromId) {
        $url .= '?copyDataFromId=' . $copyDataFromId;
      }

      return new RedirectResponse($url);
    }

    return $this->formBuilder()->getForm(
      ChooseFundingProgramForm::class,
      $fundingProgramOptions,
      $fundingCaseTypeId,
      $copyDataFromId
    );
  }

  /**
   * @return array<int, string>
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  private function getFundingProgramOptions(?int $fundingCaseTypeId): array {
    $options = [];
    foreach ($this->fundingApi->getFundingPrograms() as $fundingProgram) {
      if ($this->isNewApplicationPossible($fundingProgram, $fundingCaseTypeId)) {
        $options[$fundingProgram->getId()] = $fundingProgram->getTitle();
      }
    }

    return $options;
  }

  private function isInRequestPeriod(FundingProgram $fundingProgram): bool {
    $today = new \DateTime(date('Y-m-d'));

    return $today >= $fundingProgram->getRequestsStartDate() && $today <= $fundingProgram->getRequestsEndDate();
  }

  private function isNewApplicationPossible(FundingProgram $fundingProgram, ?int $fundingCaseTypeId): bool {
    if (
      !\in_array('application_create', $fundingProgram->getPermissions(), TRUE)
      || !$this->isInRequestPeriod($fundingProgram)
    ) {
      return FALSE;
    }

    if (NULL === $fundingCaseTypeId) {
      return TRUE;
    }

    $fundingCaseTypes = $this->fundingApi->getFundingCaseTypesByFundingProgramId($fundingProgram->getId());
    $fundingCaseTypes = array_filter($fundingCaseTypes, fn ($fundingCaseType) => $fundingCaseType->getId() === $fundingCaseTypeId);

    return [] !== $fundingCaseTypes;
  }

}
