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
use Drupal\file\Element\ManagedFile;
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
   * @phpstan-param array<mixed> $element
   * @phpstan-param array<mixed> $completeForm
   *
   * @phpstan-return array<mixed>
   */
  public static function processElement(array $element, FormStateInterface $formState, array &$completeForm): array {
    // #validate is set to an empty array in ManagedFile::processManagedFile()
    // to prevent validation. However, this doesn't prevent the default
    // validation. https://www.drupal.org/project/drupal/issues/3503297.
    $element = ManagedFile::processManagedFile($element, $formState, $completeForm);
    if (isset($element['upload_button'])) {
      $element['upload_button']['#validate'] = [static::class . '::noValidate'];
    }

    if (isset($element['remove_button'])) {
      $element['remove_button']['#validate'] = [static::class . '::noValidate'];
    }

    return $element;
  }

  public static function noValidate(): void {
  }

  /**
   * @param array<int|string, mixed> $element
   * @param mixed $input
   *
   * @return mixed
   */
  public static function valueCallback(array &$element, $input, FormStateInterface $formState) {
    if (isset($input['fids'])
      && $input['fids'] === $formState->get(array_merge($element['#parents'], ['initial_file_id']))
    ) {
      // If the given file ID matches the initial file ID we accept it without
      // access check because it might have been uploaded by a different user.
      // Actually access has already been checked because the file ID results
      // from form data returned by CiviCRM.
      $input['fids'] = [$input['fids']];

      return $input;
    }

    return ManagedFile::valueCallback($element, $input, $formState);
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
      '#process' => [
        [static::class, 'processElement'],
      ],
      '#value_callback' => [static::class, 'valueCallback'],
    ] + BasicFormPropertiesFactory::createFieldProperties($definition, $formState);

    if (NULL !== $this->validFileExtensions) {
      // @phpstan-ignore-next-line
      $form['#upload_validators']['file_validate_extensions'] = [$this->validFileExtensions];
    }

    if (is_string($form['#default_value'] ?? NULL)) {
      $initialFileId = $this->getFileIdForCiviUri($form['#default_value']);
      $form['#default_value'] = [$initialFileId];
      $formState->set(array_merge($form['#parents'], ['initial_file_id']), $initialFileId);
    }

    if (is_string($form['#value'] ?? NULL)) {
      $initialFileId = $this->getFileIdForCiviUri($form['#value']);
      $form['#value'] = [$initialFileId];
      $formState->set(array_merge($form['#parents'], ['initial_file_id']), $initialFileId);
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

  private function getFileIdForCiviUri(string $uri): string {
    $fundingFile = $this->fundingFileManager->loadOrCreateByCiviUri($uri);
    /** @var string $fileId */
    $fileId = $fundingFile->getFileId();

    return $fileId;
  }

}
