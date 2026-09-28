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

namespace Drupal\civiremote_funding\Api\DTO;

/**
 * Reduced to relevant attributes.
 *
 * @phpstan-type activityT array{
 *   id: int,
 *   source_record_id: int,
 *   activity_type_id: int,
 *   'activity_type_id:name': string,
 *   subject: string,
 *   details: string,
 *   created_date: string,
 *   source_contact_name: string,
 *   action?: string,
 *   from_status?: string,
 *   to_status?: string,
 * }
 *
 * @phpstan-extends AbstractDTO<activityT>
 *
 * @codeCoverageIgnore
 */
final class ApplicationProcessActivity extends AbstractDTO {

  public function getId(): int {
    return $this->values['id'];
  }

  public function getFundingCaseId(): int {
    return $this->values['source_record_id'];
  }

  public function getActivityTypeId(): int {
    return $this->values['activity_type_id'];
  }

  public function getActivityTypeName(): string {
    return $this->values['activity_type_id:name'];
  }

  public function getSubject(): string {
    return $this->values['subject'];
  }

  public function getDetails(): string {
    return $this->values['details'];
  }

  public function getCreatedDate(): \DateTimeInterface {
    return new \DateTime($this->values['created_date']);
  }

  public function getSourceContactName(): string {
    return $this->values['source_contact_name'];
  }

  /**
   * @return string Empty if not set, i.e. no comment.
   */
  public function getAction(): string {
    return $this->values['action'] ?? '';
  }

  /**
   * @return string Empty if not set, i.e. no status change.
   */
  public function getFromStatus(): string {
    return $this->values['from_status'] ?? '';
  }

  /**
   * @return string Empty if not set, e.g. no status change.
   */
  public function getToStatus(): string {
    return $this->values['to_status'] ?? '';
  }

  /**
   * @return string Empty if not set, i.e. no move activity.
   */
  public function getFromFundingCaseIdentifier(): string {
    return $this->values['from_funding_case_identifier'] ?? '';
  }

  /**
   * @return string Empty if not set, i.e. no move activity.
   */
  public function getToFundingCaseIdentifier(): string {
    return $this->values['to_funding_case_identifier'] ?? '';
  }

  public function getPreviousApplicationProcessIdentifier(): string {
    return $this->values['previous_application_process_identifier'] ?? '';
  }

}
