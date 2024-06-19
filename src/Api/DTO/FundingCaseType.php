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
 * @phpstan-type fundingCaseTypeT array{
 *   id: int,
 *   abbreviation: string,
 *   title: string,
 *   name: string,
 *   is_combined_application: bool,
 *   application_process_label: string|null,
 *   properties: array<string, mixed>
 * }
 *
 * @extends AbstractDTO<fundingCaseTypeT>
 */
final class FundingCaseType extends AbstractDTO {

  public function getId(): int {
    return $this->values['id'];
  }

  public function getTitle(): string {
    return $this->values['title'];
  }

  public function setTitle(string $title): self {
    $this->values['title'] = $title;

    return $this;
  }

  public function getAbbreviation(): string {
    return $this->values['abbreviation'];
  }

  public function setAbbreviation(string $abbreviation): self {
    $this->values['abbreviation'] = $abbreviation;

    return $this;
  }

  public function getName(): string {
    return $this->values['name'];
  }

  public function setName(string $name): self {
    $this->values['name'] = $name;

    return $this;
  }

  public function getIsCombinedApplication(): bool {
    return $this->values['is_combined_application'];
  }

  public function getApplicationProcessLabel(): ?string {
    return $this->values['application_process_label'];
  }

  /**
   * @phpstan-return array<string, mixed>
   *   JSON serializable array.
   */
  public function getProperties(): array {
    return $this->values['properties'];
  }

  /**
   * @phpstan-param array<string, mixed> $properties
   *   JSON serializable array.
   */
  public function setProperties(array $properties): self {
    $this->values['properties'] = $properties;

    return $this;
  }

}
