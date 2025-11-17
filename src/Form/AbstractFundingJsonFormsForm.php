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

namespace Drupal\civiremote_funding\Form;

use Assert\Assertion;
use Drupal\civiremote_funding\Api\Exception\ApiCallFailedException;
use Drupal\civiremote_funding\Api\Form\FundingForm;
use Drupal\civiremote_funding\Form\RequestHandler\FormRequestHandlerInterface;
use Drupal\civiremote_funding\Form\ResponseHandler\FormResponseHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\json_forms\Form\AbstractJsonFormsForm;
use Drupal\json_forms\Form\FormArrayFactoryInterface;
use Drupal\json_forms\Form\Validation\FormValidationMapperInterface;
use Drupal\json_forms\Form\Validation\FormValidatorInterface;
use Opis\JsonSchema\JsonPointer;

abstract class AbstractFundingJsonFormsForm extends AbstractJsonFormsForm {

  protected FormRequestHandlerInterface $formRequestHandler;

  protected FormResponseHandlerInterface $formResponseHandler;

  public function __construct(
    FormArrayFactoryInterface $formArrayFactory,
    FormValidatorInterface $formValidator,
    FormValidationMapperInterface $formValidationMapper,
    FormRequestHandlerInterface $formRequestHandler,
    FormResponseHandlerInterface $formResponseHandler
  ) {
    parent::__construct($formArrayFactory, $formValidator, $formValidationMapper);
    $this->formRequestHandler = $formRequestHandler;
    $this->formResponseHandler = $formResponseHandler;
  }

  public function getFormId(): string {
    return 'funding_form';
  }

  public function buildForm(
    array $form,
    FormStateInterface $form_state,
    ?\stdClass $jsonSchema = NULL,
    ?\stdClass $uiSchema = NULL,
    int $flags = 0
  ): array {
    if (!$form_state->isCached()) {
      try {
        $fundingForm = $this->formRequestHandler->getForm($this->getRequest());
      }
      catch (ApiCallFailedException $e) {
        $this->messenger()->addError($this->t('Loading form failed: @error', ['@error' => $e->getMessage()]));
        $fundingForm = new FundingForm(new \stdClass(), new \stdClass(), []);
      }
      $form_state->set('jsonSchema', $fundingForm->getJsonSchema());
      $form_state->set('uiSchema', $fundingForm->getUiSchema());
      $form_state->setTemporary($fundingForm->getData());
    }

    $form = $this->buildJsonFormsForm(
      $form,
      $form_state,
      // @phpstan-ignore-next-line
      $form_state->get('jsonSchema'),
      // @phpstan-ignore-next-line
      $form_state->get('uiSchema'),
      static::FLAG_RECALCULATE_ONCHANGE
    );

    // @phpstan-ignore-next-line
    $form['#attributes']['class'][] = 'civiremote-funding-form';

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $formState): void {
    parent::validateForm($form, $formState);
    if (!$formState->isSubmitted() && !$formState->isValidationEnforced()) {
      return;
    }

    if ([] === $formState->getErrors()) {
      $data = $this->getSubmittedData($formState);
      try {
        $validationResponse = $this->formRequestHandler->validateForm($this->getRequest(), $data);
      }
      catch (ApiCallFailedException $e) {
        $formState->setErrorByName(
          '',
          $this->t('Error validating form: @error', ['@error' => $e->getMessage()])->render()
        );

        return;
      }

      if (!$validationResponse->isValid()) {
        $this->mapResponseErrors($validationResponse->getErrors(), $formState);
      }
    }
  }

  /**
   * {@inheritDoc}
   *
   * @param array<int|string, mixed> $form
   * @param \Drupal\Core\Form\FormStateInterface $formState
   */
  public function submitForm(array &$form, FormStateInterface $formState): void {
    $data = $this->getSubmittedData($formState);
    try {
      $submitResponse = $this->formRequestHandler->submitForm($this->getRequest(), $data);
    }
    catch (ApiCallFailedException $e) {
      $this->messenger()->addError($this->t('Submitting form failed: @error', ['@error' => $e->getMessage()]));
      $formState->disableRedirect();

      return;
    }

    $this->formResponseHandler->handleSubmitResponse($this->getRequest(), $submitResponse, $formState);
  }

  /**
   * @param array<string, non-empty-array<string>> $errors
   * @param \Drupal\Core\Form\FormStateInterface $formState
   */
  private function mapResponseErrors(array $errors, FormStateInterface $formState): void {
    foreach ($errors as $pointer => $messages) {
      $pointer = JsonPointer::parse($pointer);
      Assertion::notNull($pointer);
      $element = ['#parents' => $pointer->absolutePath()];
      $formState->setError($element, implode("\n", $messages));
    }
  }

}
