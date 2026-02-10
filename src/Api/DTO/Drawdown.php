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
 * @phpstan-type fundingCaseT array{
 *   id: int,
 *   payout_process_id: int,
 *   status: string,
 *   creation_date: string,
 *   amount: float,
 *   acception_date: ?string,
 *   requester_contact_id: int,
 *   reviewer_contact_id: ?int,
 *   submit_confirmation_document_uri: ?string,
 * }
 *
 * @extends AbstractDTO<fundingCaseT>
 *
 * @codeCoverageIgnore
 */
final class Drawdown extends AbstractDTO {

  public function getId(): int {
    return $this->values['id'];
  }

  public function getPayoutProcessId(): int {
    return $this->values['payout_process_id'];
  }

  public function getStatus(): string {
    return $this->values['status'];
  }

  public function getRecipientContactId(): int {
    return $this->values['recipient_contact_id'];
  }

  public function getCreationDate(): \DateTimeInterface {
    return new \DateTime($this->values['creation_date']);
  }

  public function getAmount(): float {
    return $this->values['amount'];
  }

  public function getAcceptionDate(): ?\DateTimeInterface {
    return self::toDateTimeOrNull($this->values['acception_date']);
  }

  public function getRequesterContactId(): int {
    return $this->values['requester_contact_id'];
  }

  public function getReviewerContactId(): int {
    return $this->values['reviewer_contact_id'];
  }

  public function getSubmitConfirmationDocumentUri(): ?string {
    return $this->values['submit_confirmation_document_uri'];
  }

}
