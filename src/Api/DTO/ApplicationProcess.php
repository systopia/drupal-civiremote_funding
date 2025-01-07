<?php

/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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
 * @phpstan-type applicationProcessT array{
 *   id: int,
 *   identifier: string,
 *   funding_case_id: int,
 *   title: string,
 *   short_description: string,
 *   status: string,
 *   is_review_calculative: bool|null,
 *   is_review_content: bool|null,
 *   amount_requested: float|null,
 *   creation_date: string,
 *   modification_date: string,
 *   start_date: string|null,
 *   end_date: string|null,
 *   is_eligible: bool|null,
 * }
 *
 * @extends AbstractDTO<applicationProcessT>
 *
 * @codeCoverageIgnore
 */
final class ApplicationProcess extends AbstractDTO {

  public function getId(): int {
    return $this->values['id'];
  }

  public function getIdentifier(): string {
    return $this->values['identifier'];
  }

  public function getFundingCaseId(): int {
    return $this->values['funding_case_id'];
  }

  public function getTitle(): string {
    return $this->values['title'];
  }

  public function getShortDescription(): string {
    return $this->values['short_description'];
  }

  public function getStatus(): string {
    return $this->values['status'];
  }

  public function getIsReviewCalculative(): ?bool {
    return $this->values['is_review_calculative'];
  }

  public function getIsReviewContent(): ?bool {
    return $this->values['is_review_content'];
  }

  public function getAmountRequested(): ?float {
    return $this->values['amount_requested'];
  }

  public function getCreationDate(): \DateTimeInterface {
    return new \DateTime($this->values['creation_date']);
  }

  public function getModificationDate(): \DateTimeInterface {
    return new \DateTime($this->values['modification_date']);
  }

  public function getStartDate(): ?\DateTimeInterface {
    return static::toDateTimeOrNull($this->values['start_date']);
  }

  public function getEndDate(): ?\DateTimeInterface {
    return static::toDateTimeOrNull($this->values['end_date']);
  }

  public function getIsEligible(): ?bool {
    return $this->values['is_eligible'];
  }

}
