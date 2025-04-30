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

namespace Drupal\civiremote_funding\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;

/**
 * @Block(
 *   id = "civiremote_funding_dashboard_group_tasks",
 *   admin_label = @Translation("CiviRemote Funding Dashboard Group Tasks"),
 *   category = @Translation("CiviRemote Funding"),
 * )
 */
final class CiviremoteFundingDashboardGroupTasks extends BlockBase {

  /**
   * {@inheritDoc}
   *
   * @phpstan-return array<string, mixed>
   */
  public function build(): array {
    return [
      '#title' => $this->t('Tasks'),
      '#type' => 'civiremote_funding_dashboard_group',
      '#elements' => [
        [
          '#type' => 'civiremote_funding_dashboard_element',
          '#title' => $this->t('Tasks'),
          '#url' => Url::fromUri('base:civiremote/funding/task'),
          '#content' => [
            '#markup' => '<div>' . $this->t('Pending tasks') . '</div>',
          ],
        ],
      ],
    ];
  }

}
