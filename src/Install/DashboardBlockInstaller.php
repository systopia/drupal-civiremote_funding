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

namespace Drupal\civiremote_funding\Install;

use Drupal\block\Entity\Block;

final class DashboardBlockInstaller {

  public static function addDashboardBlocks(): void {
    $themeName = \Drupal::theme()->getActiveTheme()->getName();
    $dashboardPath = '/civiremote/funding';

    $block = Block::create([
      'id' => 'civiremote_funding_dashboard_group_applications',
      'theme' => $themeName,
      'region' => 'content',
      'weight' => 1,
      'plugin' => 'civiremote_funding_dashboard_group_applications',
      'settings' => [
        'id' => 'civiremote_funding_dashboard_group_applications',
        'label' => 'CiviRemote Funding Dashboard Group "Applications"',
        'label_display' => 'visible',
        'provider' => 'civiremote_funding',
      ],
      'visibility' => [
        'request_path' => [
          'id' => 'request_path',
          'negate' => FALSE,
          'pages' => $dashboardPath,
        ],
      ],
    ]);
    $block->save();

    $block = Block::create([
      'id' => 'civiremote_funding_dashboard_group_approvements',
      'theme' => $themeName,
      'region' => 'content',
      'weight' => 2,
      'plugin' => 'civiremote_funding_dashboard_group_approvements',
      'settings' => [
        'id' => 'civiremote_funding_dashboard_group_approvements',
        'label' => 'CiviRemote Funding Dashboard Group "Approvements"',
        'label_display' => 'visible',
        'provider' => 'civiremote_funding',
      ],
      'visibility' => [
        'request_path' => [
          'id' => 'request_path',
          'negate' => FALSE,
          'pages' => $dashboardPath,
        ],
      ],
    ]);
    $block->save();

    $block = Block::create([
      'id' => 'civiremote_funding_dashboard_group_tasks',
      'theme' => $themeName,
      'region' => 'content',
      'weight' => 2,
      'plugin' => 'civiremote_funding_dashboard_group_tasks',
      'settings' => [
        'id' => 'civiremote_funding_dashboard_group_tasks',
        'label' => 'CiviRemote Funding Dashboard Group "Tasks"',
        'label_display' => 'visible',
        'provider' => 'civiremote_funding',
      ],
      'visibility' => [
        'request_path' => [
          'id' => 'request_path',
          'negate' => FALSE,
          'pages' => $dashboardPath,
        ],
      ],
    ]);
    $block->save();
  }

}
