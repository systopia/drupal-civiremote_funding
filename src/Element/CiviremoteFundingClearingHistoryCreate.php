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
 * @RenderElement("civiremote_funding_clearing_history_create")
 */
final class CiviremoteFundingClearingHistoryCreate extends RenderElement {

  /**
   * {@inheritDoc}
   */
  public function getInfo(): array {
    return [
      // Instance of ApplicationProcessActivity.
      '#activity' => NULL,
      '#title' => $this->t('Clearing Started'),
      '#source_contact_title' => $this->t('Performed by'),
      '#icon' => NULL,
      '#icon_color' => NULL,
      '#pre_render' => [
        [__CLASS__, 'preRenderActivity'],
      ],
    ];
  }

  public static function preRenderActivity(array $element): array {
    /** @var \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter */
    $dateFormatter = \Drupal::service('date.formatter');

    Assertion::isInstanceOf($element['#activity'], ApplicationProcessActivity::class);
    /** @var \Drupal\civiremote_funding\Api\DTO\ApplicationProcessActivity $activity */
    $activity = $element['#activity'];

    $element['activity'] = [
      '#type' => 'civiremote_funding_application_history_entry',
      '#attributes' => ['data-activity-kind' => 'workflow'],
      '#icon' => $element['#icon'],
      '#icon_color' => $element['#icon_color'],
      '#title' => $element['#title'],
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
