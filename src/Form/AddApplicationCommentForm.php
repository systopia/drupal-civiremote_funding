<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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

declare(strict_types = 1);

namespace Drupal\civiremote_funding\Form;

use Assert\Assertion;
use Drupal\civiremote_funding\Api\FundingApi;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class AddApplicationCommentForm extends FormBase {

  /**
   * {@inheritDoc}
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get(FundingApi::class),
    );
  }

  public function __construct(
    private readonly FundingApi $fundingApi,
  ) {}

  public function getFormId(): string {
    return 'funding_add_application_comment';
  }

  public function buildForm(array $form, FormStateInterface $formState): array {
    return [
      '#prefix' => '<div id="funding-add-application-comment-wrapper">',
      '#suffix' => '</div>',
      'commentText' => [
        '#type' => 'textarea',
        '#title' => $this->t('Text'),
        '#required' => TRUE,
        '#maxlength' => 20000,
      ],
      'actions' => [
        'submit' => [
          '#type' => 'submit',
          '#value' => $this->t('Submit'),
          '#ajax' => [
            'wrapper' => 'funding-add-application-comment-wrapper',
            'callback' => [$this, 'ajaxSubmit'],
          ],
        ],
      ],
    ];
  }

  /**
   * @param array<string, mixed> $form
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   *
   * @return array<string, mixed>|AjaxResponse
   */
  public function ajaxSubmit(array &$form, FormStateInterface $formState): array|AjaxResponse {
    if ([] !== $formState->getErrors()) {
      return $form;
    }

    $applicationProcessId = $this->getRouteMatch()->getParameter('applicationProcessId');
    Assertion::integerish($applicationProcessId);
    $applicationProcessId = (int) $applicationProcessId;

    /** @var string $text */
    $text = $formState->getValue('commentText');
    $this->fundingApi->addApplicationComment($applicationProcessId, $text);

    $history = [
      '#type' => 'civiremote_funding_application_history',
      '#activities' => $this->fundingApi->getApplicationActivities($applicationProcessId),
      '#status_options' => $this->fundingApi->getApplicationStatusOptions($applicationProcessId),
      '#clearing_status_options' => $this->fundingApi->getClearingStatusOptions(),
    ];

    $form['commentText']['#value'] = '';

    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('.civiremote-funding-application-history', $history));
    $response->addCommand(new ReplaceCommand('#funding-add-application-comment-wrapper', $form));

    return $response;
  }

  public function submitForm(array &$form, FormStateInterface $formState): void {
  }

}
