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

namespace Drupal\civiremote_funding\Form;

use Drupal\civiremote_funding\Api\Exception\ApiCallFailedException;
use Drupal\civiremote_funding\Api\FundingApi;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Psr\Container\ContainerInterface;

final class ChooseFundingProgramForm extends FormBase {

  protected FundingApi $fundingApi;

  public function __construct(FundingApi $fundingApi) {
    $this->fundingApi = $fundingApi;
  }

  /**
   * {@inheritDoc}
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get(FundingApi::class),
    );
  }

  public function getFormId(): string {
    return 'funding_choose_funding_program';
  }

  /**
   * @param array<int, string> $fundingProgramOptions
   */
  public function buildForm(array $form, FormStateInterface $form_state, array $fundingProgramOptions = []): array {
    return [
      'fundingProgramId' => [
        '#type' => 'select',
        '#title' => $this->t('Select Funding Program'),
        '#options' => $fundingProgramOptions,
        '#required' => TRUE,
      ],
      'actions' => [
        'submit' => [
          '#type' => 'submit',
          '#value' => $this->t('Next'),
        ],
      ],
    ];
  }

  public function submitForm(array &$form, FormStateInterface $formState): void {
    /** @phpstan-var numeric-string $fundingProgramIdStr */
    $fundingProgramIdStr = $formState->getValue('fundingProgramId');
    $fundingProgramId = (int) $fundingProgramIdStr;
    try {
      $fundingCaseTypes = $this->fundingApi->getFundingCaseTypesByFundingProgramId(
        $fundingProgramId
      );
    }
    catch (ApiCallFailedException $e) {
      $this->messenger()->addError(
        $this->t('Failed to load funding case types: @error', ['@error' => $e->getMessage()])
      );

      return;
    }

    if (0 === count($fundingCaseTypes)) {
      $this->messenger()->addError($this->t('No funding case type available in the selected funding program.'));
    }
    else {
      // @todo Support funding programs with multiple funding case types.
      $this->redirectToApplicationForm($fundingProgramId, $fundingCaseTypes[0]->getId(), $formState);
    }
  }

  private function redirectToApplicationForm(
    int $fundingProgramId,
    int $fundingCaseTypeId,
    FormStateInterface $formState
  ): void {
    $formState->setRedirect('civiremote_funding.new_application_form', [
      'fundingProgramId' => $fundingProgramId,
      'fundingCaseTypeId' => $fundingCaseTypeId,
    ]);
  }

}
