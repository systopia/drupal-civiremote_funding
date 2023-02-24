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

namespace Drupal\civiremote_funding\Api\DTO;

/**
 * @phpstan-type fundingCaseInfoT array{
 *   funding_case_id: int,
 *   funding_case_permissions: array<string>,
 *   funding_case_status: string,
 *   funding_case_creation_date: string,
 *   funding_case_modification_date: string,
 *   funding_case_type_id: int,
 *   funding_program_id: int,
 *   funding_program_currency: string,
 *   funding_program_title: string,
 *   application_process_id: int,
 *   application_process_title: string,
 *   application_process_short_description: string,
 *   application_process_status: string,
 *   application_process_is_review_calculative: bool|null,
 *   application_process_is_review_content: bool|null,
 *   application_process_amount_requested: float|null,
 *   application_process_amount_granted: float|null,
 *   application_process_creation_date: string,
 *   application_process_modification_date: string,
 *   application_process_start_date: string|null,
 *   application_process_end_date: string|null,
 * }
 *
 * @extends AbstractDTO<fundingCaseInfoT>
 *
 * @codeCoverageIgnore
 */
final class FundingCaseInfo extends AbstractDTO {

  public function getFundingCaseId(): int {
    return $this->values['funding_case_id'];
  }

  /**
   * @phpstan-return array<string>
   */
  public function getFundingCasePermissions(): array {
    return $this->values['funding_case_permissions'];
  }

  public function getFundingCaseStatus(): string {
    return $this->values['funding_case_status'];
  }

  public function getFundingCaseCreationDate(): \DateTimeInterface {
    return new \DateTime($this->values['funding_case_creation_date']);
  }

  public function getFundingProgramId(): int {
    return $this->values['funding_program_id'];
  }

  public function getFundingProgramCurrency(): string {
    return $this->values['funding_program_currency'];
  }

  public function getFundingProgramTile(): string {
    return $this->values['funding_program_title'];
  }

  public function getApplicationProcessId(): int {
    return $this->values['application_process_id'];
  }

  public function getApplicationProcessTitle(): string {
    return $this->values['application_process_title'];
  }

  public function getApplicationProcessShortDescription(): string {
    return $this->values['application_process_short_description'];
  }

  public function getApplicationProcessStatus(): string {
    return $this->values['application_process_status'];
  }

  public function getApplicationProcessIsReviewCalculative(): ?bool {
    return $this->values['application_process_is_review_calculative'];
  }

  public function getApplicationProcessIsReviewContent(): ?bool {
    return $this->values['application_process_is_review_content'];
  }

  public function getApplicationProcessAmountRequested(): ?float {
    return $this->values['application_process_amount_requested'];
  }

  public function getApplicationProcessAmountGranted(): ?float {
    return $this->values['application_process_amount_granted'];
  }

  public function getApplicationProcessCreationDate(): \DateTimeInterface {
    return new \DateTime($this->values['application_process_creation_date']);
  }

  public function getApplicationProcessModificationDate(): \DateTimeInterface {
    return new \DateTime($this->values['application_process_modification_date']);
  }

  public function getApplicationProcessStartDate(): ?\DateTimeInterface {
    return static::toDateTimeOrNull($this->values['application_process_start_date']);
  }

  public function getApplicationProcessEndDate(): ?\DateTimeInterface {
    return static::toDateTimeOrNull($this->values['application_process_end_date']);
  }

}
