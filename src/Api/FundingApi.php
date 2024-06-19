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

namespace Drupal\civiremote_funding\Api;

use Drupal\civiremote_funding\Access\RemoteContactIdProviderInterface;
use Drupal\civiremote_funding\Api\DTO\ApplicationProcessActivity;
use Drupal\civiremote_funding\Api\DTO\ApplicationProcessTemplate;
use Drupal\civiremote_funding\Api\DTO\ClearingProcess;
use Drupal\civiremote_funding\Api\DTO\FundingCase;
use Drupal\civiremote_funding\Api\DTO\FundingCaseInfo;
use Drupal\civiremote_funding\Api\DTO\FundingCaseType;
use Drupal\civiremote_funding\Api\DTO\FundingProgram;
use Drupal\civiremote_funding\Api\DTO\PayoutProcess;
use Drupal\civiremote_funding\Api\DTO\TransferContract;
use Drupal\civiremote_funding\Api\Form\FormSubmitResponse;
use Drupal\civiremote_funding\Api\Form\FormValidationResponse;
use Drupal\civiremote_funding\Api\Form\FundingForm;

class FundingApi {

  private CiviCRMApiClientInterface $apiClient;

  private RemoteContactIdProviderInterface $remoteContactIdProvider;

  public function __construct(
    CiviCRMApiClientInterface $apiClient,
    RemoteContactIdProviderInterface $remoteContactIdProvider
  ) {
    $this->apiClient = $apiClient;
    $this->remoteContactIdProvider = $remoteContactIdProvider;
  }

  public function getFundingCase(int $fundingCaseId): ?FundingCase {
    $result = $this->apiClient->executeV4('RemoteFundingCase', 'get', [
      'remoteContactId' => $this->remoteContactIdProvider->getRemoteContactId(),
      'where' => [['id', '=', $fundingCaseId]],
    ]);

    return FundingCase::oneOrNullFromApiResult($result);
  }

  /**
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function getNewFundingCaseForm(int $fundingProgramId, int $fundingCaseTypeId): FundingForm {
    $result = $this->apiClient->executeV4('RemoteFundingCase', 'getNewForm', [
      'remoteContactId' => $this->remoteContactIdProvider->getRemoteContactId(),
      'fundingProgramId' => $fundingProgramId,
      'fundingCaseTypeId' => $fundingCaseTypeId,
    ]);

    return FundingForm::fromApiResultValue($result['values']);
  }

  /**
   * @phpstan-param array<int|string, mixed> $data
   *   JSON serializable array.
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function validateNewFundingCaseForm(
    int $fundingProgramId,
    int $fundingCaseTypeId,
    array $data
  ): FormValidationResponse {
    $result = $this->apiClient->executeV4('RemoteFundingCase', 'validateNewForm', [
      'remoteContactId' => $this->remoteContactIdProvider->getRemoteContactId(),
      'fundingProgramId' => $fundingProgramId,
      'fundingCaseTypeId' => $fundingCaseTypeId,
      'data' => $data,
    ]);

    return FormValidationResponse::fromApiResultValue($result['values']);
  }

  /**
   * @phpstan-param array<int|string, mixed> $data
   *   JSON serializable array.
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function submitNewFundingCaseForm(
    int $fundingProgramId,
    int $fundingCaseTypeId,
    array $data
  ): FormSubmitResponse {
    $result = $this->apiClient->executeV4('RemoteFundingCase', 'submitNewForm', [
      'remoteContactId' => $this->remoteContactIdProvider->getRemoteContactId(),
      'fundingProgramId' => $fundingProgramId,
      'fundingCaseTypeId' => $fundingCaseTypeId,
      'data' => $data,
    ]);

    return FormSubmitResponse::fromApiResultValue($result['values']);
  }

  /**
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function getFundingCaseInfoByApplicationProcessId(int $applicationProcessId): ?FundingCaseInfo {
    $result = $this->apiClient->executeV4('RemoteFundingCaseInfo', 'get', [
      'remoteContactId' => $this->remoteContactIdProvider->getRemoteContactId(),
      'where' => [['application_process_id', '=', $applicationProcessId]],
    ]);

    return FundingCaseInfo::oneOrNullFromApiResult($result);
  }

  public function getFundingCaseType(int $fundingCaseTypeId): ?FundingCaseType {
    $result = $this->apiClient->executeV4('RemoteFundingCaseType', 'get', [
      'remoteContactId' => $this->remoteContactIdProvider->getRemoteContactId(),
      'where' => [
        ['id', '=', $fundingCaseTypeId],
      ],
    ]);

    return FundingCaseType::oneOrNullFromApiResult($result);
  }

  /**
   * @return array<FundingCaseType>
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function getFundingCaseTypesByFundingProgramId(int $fundingProgramId): array {
    $result = $this->apiClient->executeV4('RemoteFundingCaseType', 'getByFundingProgramId', [
      'remoteContactId' => $this->remoteContactIdProvider->getRemoteContactId(),
      'fundingProgramId' => $fundingProgramId,
    ]);

    return FundingCaseType::allFromArrays($result['values']);
  }

  public function getFundingProgram(int $fundingProgramId): ?FundingProgram {
    $result = $this->apiClient->executeV4('RemoteFundingProgram', 'get', [
      'remoteContactId' => $this->remoteContactIdProvider->getRemoteContactId(),
      'where' => [['id', '=', $fundingProgramId]],
    ]);

    return FundingProgram::oneOrNullFromApiResult($result);
  }

  /**
   * @phpstan-return array<FundingProgram>
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function getFundingPrograms(): array {
    $result = $this->apiClient->executeV4('RemoteFundingProgram', 'get', [
      'remoteContactId' => $this->remoteContactIdProvider->getRemoteContactId(),
    ]);

    return FundingProgram::allFromArrays($result['values']);
  }

  /**
   * @phpstan-return array<ApplicationProcessActivity>
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function getApplicationActivities(int $applicationProcessId): array {
    $result = $this->apiClient->executeV4('RemoteFundingApplicationProcessActivity', 'get', [
      'remoteContactId' => $this->remoteContactIdProvider->getRemoteContactId(),
      'applicationProcessId' => $applicationProcessId,
    ]);

    return ApplicationProcessActivity::allFromArrays($result['values']);
  }

  public function getApplicationTemplateRenderUri(int $applicationProcessId, int $templateId): string {
    $result = $this->apiClient->executeV4('RemoteFundingApplicationProcess', 'getTemplateRenderUri', [
      'remoteContactId' => $this->remoteContactIdProvider->getRemoteContactId(),
      'applicationProcessId' => $applicationProcessId,
      'templateId' => $templateId,
    ]);

    // @phpstan-ignore-next-line
    return $result['values']['renderUri'];
  }

  /**
   * @phpstan-return list<ApplicationProcessTemplate>
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function getApplicationTemplates(int $applicationProcessId): array {
    $result = $this->apiClient->executeV4('RemoteFundingApplicationProcess', 'getTemplates', [
      'remoteContactId' => $this->remoteContactIdProvider->getRemoteContactId(),
      'applicationProcessId' => $applicationProcessId,
    ]);

    return ApplicationProcessTemplate::allFromArrays($result['values']);
  }

  /**
   * @phpstan-return array<string, \Drupal\civiremote_funding\Api\DTO\Option>
   *   Options with option ID as key.
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function getApplicationStatusOptions(int $applicationProcessId): array {
    return FieldOptionsLoader::new($this->apiClient)->getOptions(
      $this->remoteContactIdProvider->getRemoteContactId(),
      'RemoteFundingApplicationProcess',
      'status',
      ['id' => $applicationProcessId],
    );
  }

  public function getAddApplicationForm(int $fundingCaseId, ?int $copyDataFromId = NULL): FundingForm {
    $result = $this->apiClient->executeV4('RemoteFundingApplicationProcess', 'getAddForm', [
      'remoteContactId' => $this->remoteContactIdProvider->getRemoteContactId(),
      'fundingCaseId' => $fundingCaseId,
      'copyDataFromId' => $copyDataFromId,
    ]);

    return FundingForm::fromApiResultValue($result['values']);
  }

  /**
   * @phpstan-param array<int|string, mixed> $data
   *   JSON serializable array.
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function validateAddApplicationForm(int $fundingCaseId, array $data): FormValidationResponse {
    $result = $this->apiClient->executeV4('RemoteFundingApplicationProcess', 'validateAddForm', [
      'remoteContactId' => $this->remoteContactIdProvider->getRemoteContactId(),
      'fundingCaseId' => $fundingCaseId,
      'data' => $data,
    ]);

    return FormValidationResponse::fromApiResultValue($result['values']);
  }

  /**
   * @phpstan-param array<int|string, mixed> $data
   *   JSON serializable array.
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function submitAddApplicationForm(int $fundingCaseId, array $data): FormSubmitResponse {
    $result = $this->apiClient->executeV4('RemoteFundingApplicationProcess', 'submitAddForm', [
      'remoteContactId' => $this->remoteContactIdProvider->getRemoteContactId(),
      'fundingCaseId' => $fundingCaseId,
      'data' => $data,
    ]);

    return FormSubmitResponse::fromApiResultValue($result['values']);
  }

  /**
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function getApplicationForm(int $applicationProcessId): FundingForm {
    $result = $this->apiClient->executeV4('RemoteFundingApplicationProcess', 'getForm', [
      'remoteContactId' => $this->remoteContactIdProvider->getRemoteContactId(),
      'applicationProcessId' => $applicationProcessId,
    ]);

    return FundingForm::fromApiResultValue($result['values']);
  }

  /**
   * @phpstan-param array<int|string, mixed> $data
   *   JSON serializable array.
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function validateApplicationForm(int $applicationProcessId, array $data): FormValidationResponse {
    $result = $this->apiClient->executeV4('RemoteFundingApplicationProcess', 'validateForm', [
      'remoteContactId' => $this->remoteContactIdProvider->getRemoteContactId(),
      'applicationProcessId' => $applicationProcessId,
      'data' => $data,
    ]);

    return FormValidationResponse::fromApiResultValue($result['values']);
  }

  /**
   * @phpstan-param array<int|string, mixed> $data
   *   JSON serializable array.
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function submitApplicationForm(int $applicationProcessId, array $data): FormSubmitResponse {
    $result = $this->apiClient->executeV4('RemoteFundingApplicationProcess', 'submitForm', [
      'remoteContactId' => $this->remoteContactIdProvider->getRemoteContactId(),
      'applicationProcessId' => $applicationProcessId,
      'data' => $data,
    ]);

    return FormSubmitResponse::fromApiResultValue($result['values']);
  }

  /**
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function getNewApplicationForm(int $fundingProgramId, int $fundingCaseTypeId): FundingForm {
    $result = $this->apiClient->executeV4('RemoteFundingCase', 'getNewApplicationForm', [
      'remoteContactId' => $this->remoteContactIdProvider->getRemoteContactId(),
      'fundingProgramId' => $fundingProgramId,
      'fundingCaseTypeId' => $fundingCaseTypeId,
    ]);

    return FundingForm::fromApiResultValue($result['values']);
  }

  /**
   * @phpstan-param array<int|string, mixed> $data
   *   JSON serializable array.
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function validateNewApplicationForm(
    int $fundingProgramId,
    int $fundingCaseTypeId,
    array $data
  ): FormValidationResponse {
    $result = $this->apiClient->executeV4('RemoteFundingCase', 'validateNewApplicationForm', [
      'remoteContactId' => $this->remoteContactIdProvider->getRemoteContactId(),
      'fundingProgramId' => $fundingProgramId,
      'fundingCaseTypeId' => $fundingCaseTypeId,
      'data' => $data,
    ]);

    return FormValidationResponse::fromApiResultValue($result['values']);
  }

  /**
   * @phpstan-param array<int|string, mixed> $data
   *   JSON serializable array.
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function submitNewApplicationForm(
    int $fundingProgramId,
    int $fundingCaseTypeId,
    array $data
  ): FormSubmitResponse {
    $result = $this->apiClient->executeV4('RemoteFundingCase', 'submitNewApplicationForm', [
      'remoteContactId' => $this->remoteContactIdProvider->getRemoteContactId(),
      'fundingProgramId' => $fundingProgramId,
      'fundingCaseTypeId' => $fundingCaseTypeId,
      'data' => $data,
    ]);

    return FormSubmitResponse::fromApiResultValue($result['values']);
  }

  /**
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function getTransferContract(int $fundingCaseId): ?TransferContract {
    $result = $this->apiClient->executeV4('RemoteFundingTransferContract', 'get', [
      'remoteContactId' => $this->remoteContactIdProvider->getRemoteContactId(),
      'where' => [['funding_case_id', '=', $fundingCaseId]],
    ]);

    return TransferContract::oneOrNullFromApiResult($result);
  }

  /**
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function getPayoutProcess(int $payoutProcessId): ?PayoutProcess {
    $result = $this->apiClient->executeV4('RemoteFundingPayoutProcess', 'get', [
      'remoteContactId' => $this->remoteContactIdProvider->getRemoteContactId(),
      'where' => [['id', '=', $payoutProcessId]],
    ]);

    return PayoutProcess::oneOrNullFromApiResult($result);
  }

  /**
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function createDrawdown(int $payoutProcessId, float $amount): void {
    $this->apiClient->executeV4('RemoteFundingDrawdown', 'create', [
      'remoteContactId' => $this->remoteContactIdProvider->getRemoteContactId(),
      'payoutProcessId' => $payoutProcessId,
      'amount' => $amount,
    ]);
  }

  public function getFundingCaseUpdateForm(int $fundingCaseId): FundingForm {
    $result = $this->apiClient->executeV4('RemoteFundingCase', 'getUpdateForm', [
      'remoteContactId' => $this->remoteContactIdProvider->getRemoteContactId(),
      'fundingCaseId' => $fundingCaseId,
    ]);

    return FundingForm::fromApiResultValue($result['values']);
  }

  /**
   * @phpstan-param array<int|string, mixed> $data
   *   JSON serializable array.
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function validateFundingCaseUpdateForm(int $fundingCaseId, array $data): FormValidationResponse {
    $result = $this->apiClient->executeV4('RemoteFundingCase', 'validateUpdateForm', [
      'remoteContactId' => $this->remoteContactIdProvider->getRemoteContactId(),
      'fundingCaseId' => $fundingCaseId,
      'data' => $data,
    ]);

    return FormValidationResponse::fromApiResultValue($result['values']);
  }

  /**
   * @phpstan-param array<int|string, mixed> $data
   *   JSON serializable array.
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function submitFundingCaseUpdateForm(int $fundingCaseId, array $data): FormSubmitResponse {
    $result = $this->apiClient->executeV4('RemoteFundingCase', 'submitUpdateForm', [
      'remoteContactId' => $this->remoteContactIdProvider->getRemoteContactId(),
      'fundingCaseId' => $fundingCaseId,
      'data' => $data,
    ]);

    return FormSubmitResponse::fromApiResultValue($result['values']);
  }

  /**
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function getOrCreateClearingProcess(int $applicationProcessId): ClearingProcess {
    $result = $this->apiClient->executeV4('RemoteFundingClearingProcess', 'getOrCreate', [
      'remoteContactId' => $this->remoteContactIdProvider->getRemoteContactId(),
      'applicationProcessId' => $applicationProcessId,
    ]);

    // @phpstan-ignore-next-line
    return ClearingProcess::fromArray($result['values']);
  }

  /**
   * @phpstan-return array<string, \Drupal\civiremote_funding\Api\DTO\Option>
   *   Options with option ID as key.
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function getClearingStatusOptions(): array {
    return FieldOptionsLoader::new($this->apiClient)->getOptions(
      $this->remoteContactIdProvider->getRemoteContactId(),
      'RemoteFundingClearingProcess',
      'status'
    );
  }

  public function getClearingForm(int $clearingProcessId): FundingForm {
    $result = $this->apiClient->executeV4('RemoteFundingClearingProcess', 'getForm', [
      'remoteContactId' => $this->remoteContactIdProvider->getRemoteContactId(),
      'id' => $clearingProcessId,
    ]);

    return FundingForm::fromApiResultValue($result['values']);
  }

  /**
   * @phpstan-param array<int|string, mixed> $data
   *   JSON serializable array.
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function validateClearingForm(int $clearingProcessId, array $data): FormValidationResponse {
    $result = $this->apiClient->executeV4('RemoteFundingClearingProcess', 'validateForm', [
      'remoteContactId' => $this->remoteContactIdProvider->getRemoteContactId(),
      'id' => $clearingProcessId,
      'data' => $data,
    ]);

    return FormValidationResponse::fromApiResultValue($result['values']);
  }

  /**
   * @phpstan-param array<int|string, mixed> $data
   *   JSON serializable array.
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  public function submitClearingForm(int $clearingProcessId, array $data): FormSubmitResponse {
    $result = $this->apiClient->executeV4('RemoteFundingClearingProcess', 'submitForm', [
      'remoteContactId' => $this->remoteContactIdProvider->getRemoteContactId(),
      'id' => $clearingProcessId,
      'data' => $data,
    ]);

    return FormSubmitResponse::fromApiResultValue($result['values']);
  }

}
