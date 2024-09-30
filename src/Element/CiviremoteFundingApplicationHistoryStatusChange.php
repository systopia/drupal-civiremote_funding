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

namespace Drupal\civiremote_funding\Element;

use Assert\Assertion;
use Drupal\civiremote_funding\Api\DTO\ApplicationProcessActivity;
use Drupal\Core\Render\Element\RenderElement;

/**
 * @RenderElement("civiremote_funding_application_history_status_change")
 */
final class CiviremoteFundingApplicationHistoryStatusChange extends RenderElement {

  /**
   * {@inheritDoc}
   */
  public function getInfo(): array {
    return [
      // Instance of ApplicationProcessActivity.
      '#activity' => NULL,
      // Array mapping status to \Drupal\civiremote_funding\Api\DTO\Option.
      // Instance of \Drupal\civiremote_funding\Api\DTO\Option.
      '#status_option' => NULL,
      '#title' => $this->t('Status: @status'),
      '#status_label' => NULL,
      '#source_contact_title' => $this->t('Performed by'),
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
    $activity = $element['#activity'];

    $element['activity'] = [
      '#type' => 'civiremote_funding_application_history_entry',
      '#attributes' => ['data-activity-kind' => 'workflow'],
      '#icon' => $element['#icon'],
      '#icon_color' => $element['#icon_color'],
      '#title' => \Drupal::translation()->translate($element['#title'], [
        '@status' => $element['#status_label'],
      ]),
      '#date' => $dateFormatter->format($activity->getCreatedDate()->getTimestamp()),
      '#content' => [
        '#type' => 'container',
        'source_contact' => [
          '#type' => 'item',
          '#title' => $element['#source_contact_title'],
          '#markup' => htmlentities($activity->getSourceContactName()),
        ],
      ],
    ];

    return $element;
  }

}
