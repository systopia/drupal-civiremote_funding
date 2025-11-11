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

namespace Drupal\civiremote_funding\Form;

use Assert\Assertion;
use Drupal\civiremote_funding\Api\Exception\ApiCallFailedException;
use Drupal\civiremote_funding\Api\FundingApi;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class NewDrawdownForm extends FormBase {

  protected FundingApi $fundingApi;

  protected LanguageManagerInterface $languageManager;

  protected int $payoutProcessId = -1;

  public function __construct(FundingApi $fundingApi, LanguageManagerInterface $languageManager) {
    $this->fundingApi = $fundingApi;
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritDoc}
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get(FundingApi::class),
      $container->get('language_manager')
    );
  }

  public function getFormId(): string {
    return 'funding_new_drawdown';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(
    array $form,
    FormStateInterface $formState,
    ?int $fundingCaseId = NULL,
    ?int $payoutProcessId = NULL
  ): array {
    Assertion::notNull($fundingCaseId);
    Assertion::notNull($payoutProcessId);
    $this->payoutProcessId = $payoutProcessId;

    try {
      $payoutProcess = $this->fundingApi->getPayoutProcess($this->payoutProcessId);
      if (NULL === $payoutProcess || $payoutProcess->getFundingCaseId() !== $fundingCaseId) {
        throw new NotFoundHttpException();
      }

      $transferContract = $this->fundingApi->getTransferContract($payoutProcess->getFundingCaseId());
      if (NULL === $transferContract) {
        throw new NotFoundHttpException();
      }
    }
    catch (ApiCallFailedException $e) {
      $this->messenger()->addError(
        $this->t('API request failed: @error', ['@error' => $e->getMessage()])
      );

      return [];
    }

    $numberFormatter = new \NumberFormatter($this->languageManager->getCurrentLanguage()->getId(), \NumberFormatter::CURRENCY);
    $amountAvailableFormatted = $numberFormatter->formatCurrency($transferContract->getAmountAvailable(), $transferContract->getCurrency());

    return [
      '#attributes' => ['class' => ['civiremote-funding-form']],
      '#title' => $this->t('Create Drawdown'),
      'transferContract' => [
        '#type' => 'item',
        '#title' => $this->t('Transfer Contract'),
        '#markup' => $transferContract->getIdentifier(),
      ],
      'amount' => [
        '#type' => 'number',
        '#title' => $this->t('Amount in @currency', ['@currency' => $transferContract->getCurrency()]),
        '#required' => TRUE,
        '#field_suffix' => $this->t(
          '(Available: @amountAvailable)',
          ['@amountAvailable' => $amountAvailableFormatted]
        ),
        '#step' => 0.01,
        '#min' => 0,
        '#max' => $transferContract->getAmountAvailable(),
        '#attached' => ['library' => ['json_forms/number_input']],
      ],
      'actions' => [
        'submit' => [
          '#type' => 'submit',
          '#value' => $this->t('Submit'),
        ],
      ],
    ];
  }

  public function submitForm(array &$form, FormStateInterface $formState): void {
    try {
      $this->fundingApi->createDrawdown(
        $this->payoutProcessId,
        // @phpstan-ignore-next-line
        (float) $formState->getValue('amount'),
      );
      $this->messenger()->addMessage($this->t('Drawdown created.'));
      if (NULL === $formState->getRedirect()) {
        $formState->setRedirect('<front>');
      }
    }
    catch (ApiCallFailedException $e) {
      $this->messenger()->addError(
        $this->t('Failed to create drawdown: @error', ['@error' => $e->getMessage()])
      );
      $formState->setRebuild();
    }
  }

}
