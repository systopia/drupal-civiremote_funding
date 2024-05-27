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
 * @phpstan-type payoutProcessT array{
 *   id: int,
 *   funding_case_id: int,
 *   status: string,
 *   creation_date: string,
 *   modification_date: string,
 *   report_data: array<string, mixed>,
 * }
 *
 * @extends AbstractDTO<payoutProcessT>
 *
 * @codeCoverageIgnore
 */
final class ClearingProcess extends AbstractDTO {

  public function getId(): int {
    return $this->values['id'];
  }

  public function getFundingCaseId(): int {
    return $this->values['funding_case_id'];
  }

  public function getStatus(): string {
    return $this->values['status'];
  }

  public function getCreationDate(): \DateTimeInterface {
    return new \DateTime($this->values['creation_date']);
  }

  public function getModificationDate(): \DateTimeInterface {
    return new \DateTime($this->values['modification_date']);
  }

  /**
   * @phpstan-return array<string, mixed> JSON serializable.
   */
  public function getReportData(): array {
    return $this->values['report_data'];
  }

}
