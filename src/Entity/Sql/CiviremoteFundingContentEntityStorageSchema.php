<?php

/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
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

namespace Drupal\civiremote_funding\Entity\Sql;

use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Allows to set "unique_keys" to a list of field names for which a unique
 * constraint is created.
 */
final class CiviremoteFundingContentEntityStorageSchema extends SqlContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   *
   * @phpstan-param array<string> $columnMapping
   *
   * @phpstan-return array<string, mixed>
   */
  protected function getSharedTableFieldSchema(
    FieldStorageDefinitionInterface $storageDefinition,
    $tableName,
    array $columnMapping
  ): array {
    $schema = parent::getSharedTableFieldSchema($storageDefinition, $tableName, $columnMapping);
    $entityType = $this->entityTypeManager->getDefinition($storageDefinition->getTargetEntityTypeId());
    assert(NULL !== $entityType);
    $uniqueFields = $entityType->get('unique_fields') ?? [];
    assert(is_array($uniqueFields));
    $fieldName = $storageDefinition->getName();

    if (in_array($fieldName, $uniqueFields, TRUE)) {
      $notNull = $schema['fields'][$fieldName]['not null'] ?? FALSE;
      $this->addSharedTableFieldUniqueKey($storageDefinition, $schema);
      // addSharedTableFieldUniqueKey always enables 'not null'.
      if (!$notNull && !in_array($fieldName, $entityType->getKeys(), TRUE)) {
        $schema['fields'][$fieldName]['not null'] = FALSE;
      }
    }

    return $schema;
  }

}
