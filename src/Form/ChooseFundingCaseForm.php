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

namespace Drupal\civiremote_funding\Form;

use Drupal\civiremote_funding\Api\FundingApi;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Psr\Container\ContainerInterface;

final class ChooseFundingCaseForm extends FormBase {

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
    return 'funding_choose_funding_case';
  }

  /**
   * @param array<int, string> $fundingCaseOptions
   */
  public function buildForm(
    array $form,
    FormStateInterface $form_state,
    array $fundingCaseOptions = [],
    int $copyDataFromId = 0
  ): array {
    return [
      'fundingCaseId' => [
        '#type' => 'select',
        '#title' => $this->t('Select Funding Case'),
        '#options' => $fundingCaseOptions,
        '#required' => TRUE,
      ],
      'copyDataFromId' => [
        '#type' => 'value',
        '#value' => $copyDataFromId,
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
    /** @var numeric-string $fundingCaseIdStr */
    $fundingCaseIdStr = $formState->getValue('fundingCaseId');
    $fundingCaseId = (int) $fundingCaseIdStr;
    /** @var int $copyDataFromId */
    $copyDataFromId = $formState->getValue('copyDataFromId');

    $this->redirectToApplicationForm($fundingCaseId, $copyDataFromId, $formState);
  }

  private function redirectToApplicationForm(
    int $fundingCaseId,
    int $copyDataFromId,
    FormStateInterface $formState
  ): void {
    $formState->setRedirect(
      'civiremote_funding.case_application_add',
      ['fundingCaseId' => $fundingCaseId],
      ['query' => ['copyDataFromId' => $copyDataFromId]]
    );
  }

}
