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
use Drupal\civiremote_funding\Access\RemoteContactIdProviderInterface;
use Drupal\civiremote_funding\Api\Exception\ApiCallFailedException;
use Drupal\civiremote_funding\Api\FundingApi;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class NewDrawdownForm extends FormBase {

  protected FundingApi $fundingApi;

  protected RemoteContactIdProviderInterface $remoteContactIdProvider;

  protected int $payoutProcessId = -1;

  public function __construct(
    FundingApi $fundingApi,
    RemoteContactIdProviderInterface $remoteContactIdProvider
  ) {
    $this->fundingApi = $fundingApi;
    $this->remoteContactIdProvider = $remoteContactIdProvider;
  }

  /**
   * @inheritDoc
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get(FundingApi::class),
      $container->get(RemoteContactIdProviderInterface::class),
    );
  }

  public function getFormId(): string {
    return 'funding_new_drawdown';
  }

  // phpcs:disable Squiz.PHP.CommentedOutCode.Found
  public function buildForm(array $form, FormStateInterface $formState/*, int $payoutProcessId */): array {
  // phpcs:enable
    Assertion::integer(func_get_arg(2));
    $this->payoutProcessId = func_get_arg(2);

    try {
      $payoutProcess = $this->fundingApi->getPayoutProcess(
        $this->remoteContactIdProvider->getRemoteContactId(),
        $this->payoutProcessId,
      );
      if (NULL === $payoutProcess) {
        throw new NotFoundHttpException();
      }

      $transferContract = $this->fundingApi->getTransferContract(
        $this->remoteContactIdProvider->getRemoteContactId(),
        $payoutProcess->getFundingCaseId(),
      );
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

    return [
      '#title' => $this->t('Create Drawdown'),
      'transferContract' => [
        '#type' => 'item',
        '#title' => $this->t('Transfer contract'),
        '#markup' => $transferContract->getTitle(),
      ],
      'amount' => [
        '#type' => 'number',
        '#title' => $this->t('Amount'),
        '#required' => TRUE,
        '#field_suffix' => $transferContract->getCurrency(),
        '#step' => 0.01,
        '#max' => $transferContract->getAmountAvailable(),
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
        $this->remoteContactIdProvider->getRemoteContactId(),
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