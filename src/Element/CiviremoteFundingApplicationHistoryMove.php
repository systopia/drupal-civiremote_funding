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

declare(strict_types=1);

namespace Drupal\civiremote_funding\Element;

use Assert\Assertion;
use Drupal\civiremote_funding\Api\DTO\ApplicationProcessActivity;
use Drupal\Core\Render\Attribute\RenderElement;
use Drupal\Core\Render\Element\RenderElementBase;

#[RenderElement('civiremote_funding_application_history_move')]
final class CiviremoteFundingApplicationHistoryMove extends RenderElementBase {

  /**
   * {@inheritDoc}
   */
  public function getInfo(): array {
    return [
      // Instance of ApplicationProcessActivity.
      '#activity' => NULL,
      '#title' => $this->t('Moved to funding case @identifier'),
      '#status_label' => NULL,
      '#source_contact_title' => $this->t('Performed by'),
      '#previous_funding_case_title' => $this->t('Previous funding case'),
      '#previous_application_identifier_title' => $this->t('Previous application identifier'),
      '#pre_render' => [
        [__CLASS__, 'preRenderActivity'],
      ],
    ];
  }

  /**
   * @phpstan-param array<string, mixed> $element
   *
   * @phpstan-return array<string, mixed>
   */
  public static function preRenderActivity(array $element): array {
    /** @var \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter */
    $dateFormatter = \Drupal::service('date.formatter');

    if (is_scalar($element['#title']) ||
      (is_object($element['#title']) && method_exists($element['#title'], '__toString'))
    ) {
      $element['#title'] = (string) $element['#title'];
    }
    else {
      throw new \InvalidArgumentException('Expected string for "#title", got ' . gettype($element['#title']));
    }

    Assertion::isInstanceOf($element['#activity'], ApplicationProcessActivity::class);
    /** @var \Drupal\civiremote_funding\Api\DTO\ApplicationProcessActivity $activity */
    $activity = $element['#activity'];

    $element['activity'] = [
      '#type' => 'civiremote_funding_application_history_entry',
      '#attributes' => ['data-activity-kind' => 'workflow'],
      '#icon' => $element['#icon'],
      '#icon_color' => $element['#icon_color'],
      '#title' => \Drupal::translation()->translate($element['#title'], [
        '@identifier' => $activity->getToFundingCaseIdentifier(),
      ]),
      '#date' => $dateFormatter->format($activity->getCreatedDate()->getTimestamp()),
      '#content' => [
        '#type' => 'container',
        'source_contact' => [
          '#type' => 'item',
          '#title' => $element['#source_contact_title'],
          '#markup' => htmlentities($activity->getSourceContactName()),
        ],
        'previous_funding_case' => [
          '#type' => 'item',
          '#title' => $element['#previous_funding_case_title'],
          '#markup' => $activity->getFromFundingCaseIdentifier(),
        ],
        'previous_application_identifier' => [
          '#type' => 'item',
          '#title' => $element['#previous_application_identifier_title'],
          '#markup' => $activity->getPreviousApplicationProcessIdentifier(),
        ],
      ],
    ];

    return $element;
  }

}
