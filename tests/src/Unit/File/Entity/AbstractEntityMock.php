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

namespace Drupal\Tests\civiremote_funding\Unit\File\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Session\AccountInterface;

abstract class AbstractEntityMock implements EntityInterface {

  /**
   * {@inheritDoc}
   */
  public function access($operation, ?AccountInterface $account = NULL, $return_as_object = FALSE) {
    throw new \BadMethodCallException('Not implemented');
  }

  /**
   * {@inheritDoc}
   */
  public function getCacheContexts() {
    throw new \BadMethodCallException('Not implemented');
  }

  /**
   * {@inheritDoc}
   */
  public function getCacheTags() {
    throw new \BadMethodCallException('Not implemented');
  }

  /**
   * {@inheritDoc}
   */
  public function getCacheMaxAge() {
    throw new \BadMethodCallException('Not implemented');
  }

  /**
   * {@inheritDoc}
   */
  public function language() {
    throw new \BadMethodCallException('Not implemented');
  }

  /**
   * {@inheritDoc}
   */
  public function isNew(): bool {
    return NULL === $this->id();
  }

  /**
   * {@inheritDoc}
   */
  public function enforceIsNew($value = TRUE) {
    throw new \BadMethodCallException('Not implemented');
  }

  /**
   * {@inheritDoc}
   */
  public function bundle() {
    throw new \BadMethodCallException('Not implemented');
  }

  /**
   * {@inheritDoc}
   */
  public function toUrl($rel = 'canonical', array $options = []) {
    throw new \BadMethodCallException('Not implemented');
  }

  /**
   * {@inheritDoc}
   */
  public function toLink($text = NULL, $rel = 'canonical', array $options = []) {
    throw new \BadMethodCallException('Not implemented');
  }

  /**
   * {@inheritDoc}
   */
  public function hasLinkTemplate($key) {
    throw new \BadMethodCallException('Not implemented');
  }

  /**
   * {@inheritDoc}
   */
  public function uriRelationships() {
    throw new \BadMethodCallException('Not implemented');
  }

  /**
   * {@inheritDoc}
   */
  public static function load($id) {
    throw new \BadMethodCallException('Not implemented');
  }

  /**
   * {@inheritDoc}
   */
  public static function loadMultiple(?array $ids = NULL) {
    throw new \BadMethodCallException('Not implemented');
  }

  /**
   * {@inheritDoc}
   */
  public static function create(array $values = []) {
    throw new \BadMethodCallException('Not implemented');
  }

  /**
   * {@inheritDoc}
   */
  public function save() {
    throw new \BadMethodCallException('Not implemented');
  }

  /**
   * {@inheritDoc}
   */
  public function delete(): void {
    throw new \BadMethodCallException('Not implemented');
  }

  /**
   * {@inheritDoc}
   */
  public function preSave(EntityStorageInterface $storage): void {
  }

  /**
   * {@inheritDoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE): void {
  }

  /**
   * {@inheritDoc}
   */
  public static function preCreate(EntityStorageInterface $storage, array &$values): void {
  }

  /**
   * {@inheritDoc}
   */
  public function postCreate(EntityStorageInterface $storage): void {
  }

  /**
   * {@inheritDoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities): void {
  }

  /**
   * {@inheritDoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities): void {
  }

  /**
   * {@inheritDoc}
   */
  public static function postLoad(EntityStorageInterface $storage, array &$entities): void {
  }

  /**
   * {@inheritDoc}
   */
  public function createDuplicate() {
    throw new \BadMethodCallException('Not implemented');
  }

  /**
   * {@inheritDoc}
   */
  public function getEntityType() {
    throw new \BadMethodCallException('Not implemented');
  }

  /**
   * {@inheritDoc}
   */
  public function referencedEntities() {
    throw new \BadMethodCallException('Not implemented');
  }

  /**
   * {@inheritDoc}
   */
  public function getOriginalId() {
    throw new \BadMethodCallException('Not implemented');
  }

  /**
   * {@inheritDoc}
   */
  public function getCacheTagsToInvalidate() {
    throw new \BadMethodCallException('Not implemented');
  }

  /**
   * {@inheritDoc}
   */
  public function setOriginalId($id) {
    throw new \BadMethodCallException('Not implemented');
  }

  /**
   * {@inheritDoc}
   */
  public function toArray() {
    throw new \BadMethodCallException('Not implemented');
  }

  /**
   * {@inheritDoc}
   */
  public function getTypedData() {
    throw new \BadMethodCallException('Not implemented');
  }

  /**
   * {@inheritDoc}
   */
  public function getConfigDependencyKey() {
    throw new \BadMethodCallException('Not implemented');
  }

  /**
   * {@inheritDoc}
   */
  public function getConfigDependencyName() {
    throw new \BadMethodCallException('Not implemented');
  }

  /**
   * {@inheritDoc}
   */
  public function getConfigTarget() {
    throw new \BadMethodCallException('Not implemented');
  }

  /**
   * {@inheritDoc}
   */
  public function addCacheContexts(array $cache_contexts) {
    return $this;
  }

  /**
   * {@inheritDoc}
   */
  public function addCacheTags(array $cache_tags) {
    return $this;
  }

  /**
   * {@inheritDoc}
   */
  public function mergeCacheMaxAge($max_age) {
    return $this;
  }

  /**
   * {@inheritDoc}
   */
  public function addCacheableDependency($other_object) {
    return $this;
  }

}
