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

use Assert\Assertion;
use Drupal\civiremote_funding\Api\Exception\NonUniqueResultException;

/**
 * @template T of array<string, mixed>
 *
 * @phpstan-consistent-constructor
 *
 * @codeCoverageIgnore
 */
abstract class AbstractDTO {

  /**
   * @var array
   * @phpstan-var T
   */
  protected array $values;

  /**
   * @phpstan-param array<T> $arrays
   *
   * @return array<static>
   */
  public static function allFromArrays(array $arrays): array {
    return array_map(
      fn (array $values) => static::fromArray($values),
      $arrays,
    );
  }

  /**
   * @phpstan-param T $values
   *
   * @return static
   */
  public static function fromArray(array $values): self {
    return new static($values);
  }

  /**
   * @phpstan-param array{values: array<mixed>} $result
   *
   * @return static|null
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\NonUniqueResultException
   */
  public static function oneOrNullFromApiResult(array $result): ?self {
    $values = $result['values'];
    switch (count($values)) {
      case 0:
        return NULL;

      case 1:
        Assertion::keyIsset($values, 0);
        return new static($values[0]);

      default:
        throw new NonUniqueResultException(sprintf(
          'Expected one or no result for "%s", got %d.',
          static::class,
          count($values),
        ));
    }
  }

  /**
   * @phpstan-param T $values
   */
  protected function __construct(array $values) {
    $this->values = $values;
  }

  /**
   * @param string $key
   * @param mixed $default
   *
   * @return mixed
   */
  public function get(string $key, $default = NULL) {
    return $this->values[$key] ?? $default;
  }

  /**
   * @phpstan-return T
   */
  public function toArray(): array {
    return $this->values;
  }

  protected static function toDateTimeOrNull(?string $dateTimeStr): ?\DateTime {
    return NULL === $dateTimeStr ? NULL : new \DateTime($dateTimeStr);
  }

  protected static function toDateTimeStr(\DateTimeInterface $dateTime): string {
    return $dateTime->format('Y-m-d H:i:s');
  }

  protected static function toDateTimeStrOrNull(?\DateTimeInterface $dateTime): ?string {
    return NULL === $dateTime ? NULL : $dateTime->format('Y-m-d H:i:s');
  }

}
