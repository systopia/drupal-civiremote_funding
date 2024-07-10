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

namespace Drupal\civiremote_funding\JsonForms;

use Assert\Assertion;
use Drupal\civiremote_funding\Entity\FundingFileInterface;
use Drupal\civiremote_funding\File\FundingFileManager;
use Drupal\civiremote_funding\JsonForms\Callbacks\FileUploadCallback;
use Drupal\Core\Form\FormStateInterface;
use Drupal\json_forms\Form\AbstractConcreteFormArrayFactory;
use Drupal\json_forms\Form\Control\UrlArrayFactory;
use Drupal\json_forms\Form\Control\Util\BasicFormPropertiesFactory;
use Drupal\json_forms\Form\FormArrayFactoryInterface;
use Drupal\json_forms\Form\Util\FormCallbackRegistrator;
use Drupal\json_forms\JsonForms\Definition\Control\ControlDefinition;
use Drupal\json_forms\JsonForms\Definition\DefinitionInterface;

final class FileUploadArrayFactory extends AbstractConcreteFormArrayFactory {

  private FundingFileManager $fundingFileManager;

  private ?string $validFileExtensions = NULL;

  public function __construct(FundingFileManager $fundingFileManager) {
    $this->fundingFileManager = $fundingFileManager;
  }

  public static function getPriority(): int {
    return UrlArrayFactory::getPriority() + 1;
  }

  /**
   * {@inheritDoc}
   */
  public function createFormArray(
    DefinitionInterface $definition,
    FormStateInterface $formState,
    FormArrayFactoryInterface $formArrayFactory
  ): array {
    Assertion::isInstanceOf($definition, ControlDefinition::class);
    /** @var \Drupal\json_forms\JsonForms\Definition\Control\ControlDefinition $definition $form */
    $form = [
      '#type' => 'managed_file',
      '#upload_location' => FundingFileInterface::UPLOAD_LOCATION,
      '#upload_validators' => [],
    ] + BasicFormPropertiesFactory::createFieldProperties($definition, $formState);

    if (NULL !== $this->validFileExtensions) {
      // @phpstan-ignore-next-line
      $form['#upload_validators']['file_validate_extensions'] = [$this->validFileExtensions];
    }

    if (is_string($form['#default_value'] ?? NULL)) {
      $form['#default_value'] = $this->getValueForCiviUri($form['#default_value']);
    }

    if (is_string($form['#value'] ?? NULL)) {
      $form['#value'] = $this->getValueForCiviUri($form['#value']);
    }

    FormCallbackRegistrator::registerPreSchemaValidationCallback(
      $formState,
      $definition->getFullScope(),
      [FileUploadCallback::class, 'convertValue'],
      $form['#parents'],
    );

    return $form;
  }

  public function supportsDefinition(DefinitionInterface $definition): bool {
    return $definition instanceof ControlDefinition && 'string' === $definition->getType()
      && 'uri' === $definition->getPropertyFormat() && 'file' === $definition->getControlFormat();
  }

  /**
   * @param string|null $validFileExtensions
   *   Space separated list of valid file extensions. Empty string to allow all
   *   extensions. NULL to use Drupal's internal list of valid extensions.
   */
  public function setValidFileExtensions(?string $validFileExtensions): void {
    $this->validFileExtensions = $validFileExtensions;
  }

  /**
   * @phpstan-return array<string>
   */
  private function getValueForCiviUri(string $uri): array {
    $fundingFile = $this->fundingFileManager->loadOrCreateByCiviUri($uri);
    /** @var string $fileId */
    $fileId = $fundingFile->getFileId();

    return [$fileId];
  }

}
