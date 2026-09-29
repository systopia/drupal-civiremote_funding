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

declare(strict_types = 1);

namespace Drupal\civiremote_funding\Form;

use Assert\Assertion;
use Drupal\civiremote_funding\Api\Exception\ApiCallFailedException;
use Drupal\civiremote_funding\Api\FundingApi;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class NewFundingAmountApprovedChangeRequestForm extends FormBase {

  private const COMMENT_MAX_LENGTH = 1000;

  protected FundingApi $fundingApi;

  protected LanguageManagerInterface $languageManager;

  public function __construct(FundingApi $fundingApi, LanguageManagerInterface $languageManager) {
    $this->fundingApi = $fundingApi;
    $this->languageManager = $languageManager;
  }

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get(FundingApi::class),
      $container->get('language_manager')
    );
  }

  public function getFormId(): string {
    return 'funding_new_funding_amount_approved_change_request';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(
    array $form,
    FormStateInterface $form_state,
    ?int $fundingCaseId = NULL
  ): array {
    Assertion::notNull($fundingCaseId);
    $form_state->set('funding_case_id', $fundingCaseId);

    try {
      $transferContract = $this->fundingApi->getTransferContract($fundingCaseId);
      if (NULL === $transferContract) {
        throw new NotFoundHttpException();
      }
      $fundingCase = $this->fundingApi->getFundingCase($fundingCaseId);
      if (NULL === $fundingCase) {
        throw new NotFoundHttpException();
      }
    }
    catch (ApiCallFailedException $e) {
      $this->messenger()->addError(
        $this->t('API request failed: @error', ['@error' => $e->getMessage()])
      );

      return [];
    }

    $numberFormatter = new \NumberFormatter(
      $this->languageManager->getCurrentLanguage()
        ->getId(), \NumberFormatter::CURRENCY
    );
    $amountApprovedFormatted = $numberFormatter->formatCurrency(
      $fundingCase->getAmountApproved(),
      $transferContract->getCurrency()
    );

    return [
      '#attributes' => ['class' => ['civiremote-funding-form']],
      '#title' => $this->t('Create Higher/Lesser Amount Request'),
      'transferContract' => [
        '#type' => 'item',
        '#title' => $this->t('Transfer Contract'),
        '#markup' => $transferContract->getIdentifier(),
      ],
      'amountApproved' => [
        '#type' => 'item',
        '#title' => $this->t('Current approved amount'),
        '#markup' => $amountApprovedFormatted,
      ],
      'amount_requested' => [
        '#type' => 'number',
        '#title' => $this->t('Requested amount in @currency', ['@currency' => $transferContract->getCurrency()]),
        '#required' => TRUE,
        '#step' => 0.01,
        '#min' => 0,
        '#attached' => ['library' => ['json_forms/number_input']],
      ],
      'comment' => [
        '#type' => 'textarea',
        '#title' => $this->t('Comment'),
        '#required' => FALSE,
        '#maxlength' => self::COMMENT_MAX_LENGTH,
      ],
      'actions' => [
        'submit' => [
          '#type' => 'submit',
          '#value' => $this->t('Submit'),
        ],
      ],
    ];
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $fundingCaseId = $form_state->get('funding_case_id');
    try {
      $this->fundingApi->createFundingAmountApprovedChangeRequest(
        $fundingCaseId,
        // @phpstan-ignore-next-line
        (float) $form_state->getValue('amount_requested'),
        $form_state->getValue('comment'),
      );
      $this->messenger()
        ->addMessage($this->t('Higher/lesser amount request created.'));
      if (NULL === $form_state->getRedirect()) {
        $form_state->setRedirect('<front>');
      }
    }
    catch (ApiCallFailedException $e) {
      $this->messenger()->addError(
        $this->t('Failed to create higher/lesser amount request: @error', ['@error' => $e->getMessage()])
      );
      $form_state->setRebuild();
    }
  }

}
