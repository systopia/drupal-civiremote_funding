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

namespace Drupal\civiremote_funding\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\file\FileInterface;

/**
 * @ContentEntityType(
 *   id = "civiremote_funding_file",
 *   label = @Translation("CiviRemote Funding file entity"),
 *   handlers = {
 *     "storage" = "Drupal\civiremote_funding\File\FundingFileStorage",
 *     "storage_schema" = "Drupal\civiremote_funding\Entity\Sql\CiviremoteFundingContentEntityStorageSchema",
 *   },
 *   base_table = "civiremote_funding_file",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "file_id" = "file_id",
 *     "token" = "token",
 *     "civi_uri" = "civi_uri",
 *   },
 *   unique_fields = {
 *     "token",
 *     "civi_uri",
 *   }
 * )
 */
final class FundingFile extends ContentEntityBase implements FundingFileInterface {

  public static function baseFieldDefinitions(EntityTypeInterface $entityType): array {

    $fields = parent::baseFieldDefinitions($entityType);

    $fields['file_id'] = BaseFieldDefinition::create('entity_reference')
      ->setSetting('target_type', 'file')
      ->setRequired(TRUE)
      ->setLabel(t('Referenced file'))
      ->setDescription(t('Referenced File entity'));

    $fields['token'] = BaseFieldDefinition::create('string')
      ->setSetting('max_length', 255)
      ->setLabel('Token')
      ->setDescription('Used for access by CiviCRM');

    $fields['civi_uri'] = BaseFieldDefinition::create('string')
      ->setSetting('max_length', 255)
      ->setLabel('CiviCRM URI')
      ->setDescription('URI of file in CiviCRM');

    $fields['last_modified'] = BaseFieldDefinition::create('string')
      ->setSetting('max_length', 255)
      ->setDefaultValue('Thu, 01 Jan 1970 00:00:00 GMT')
      ->setRequired(TRUE)
      ->setLabel('Last modified date')
      ->setDescription('Value of the Last-Modified header from CiviCRM');

    $fields['last_access'] = BaseFieldDefinition::create('integer')
      ->setDefaultValue(0)
      ->setRequired(TRUE)
      ->setLabel('Last access date')
      ->setDescription('Timestamp of the last file access');

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

  public function getFile(): FileInterface {
    return $this->get('file_id')->entity;
  }

  public function getFileId(): ?string {
    return $this->get('file_id')->target_id;
  }

  public function setFile(FileInterface $file): FundingFileInterface {
    $this->set('file_id', $file->id());

    return $this;
  }

  public function getToken(): string {
    return $this->get('token')->value;
  }

  public function setToken(string $token): FundingFileInterface {
    $this->set('token', $token);

    return $this;
  }

  public function getCiviUri(): ?string {
    return $this->get('civi_uri')->value;
  }

  public function setCiviUri(string $civiUri): self {
    $this->set('civi_uri', $civiUri);

    return $this;
  }

  public function getLastModified(): string {
    return $this->get('last_modified')->value;
  }

  public function setLastModified(string $lastModified): FundingFileInterface {
    $this->set('last_modified', $lastModified);

    return $this;
  }

  public function getLastAccess(): int {
    return $this->get('last_access')->value;
  }

  public function setLastAccess(int $lastAccess): FundingFileInterface {
    $this->set('last_access', $lastAccess);

    return $this;
  }

}
