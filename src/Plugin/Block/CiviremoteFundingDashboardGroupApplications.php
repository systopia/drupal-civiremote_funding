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

use Drupal\civiremote_funding\Access\RemoteContactIdProviderInterface;
use Drupal\civiremote_funding\Api\FundingApi;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Block(
 *   id = "civiremote_funding_dashboard_group_applications",
 *   admin_label = @Translation("CiviRemote Funding Dashboard Group Applications"),
 *   category = @Translation("CiviRemote Funding"),
 * )
 */
final class CiviremoteFundingDashboardGroupApplications extends BlockBase implements ContainerFactoryPluginInterface {

  private FundingApi $fundingApi;

  private RemoteContactIdProviderInterface $remoteContactIdProvider;

  /**
   * {@inheritDoc}
   *
   * @param array<int|string, mixed> $configuration
   * @param mixed $pluginDefinition
   */
  public function __construct(
    array $configuration,
    string $pluginId,
    $pluginDefinition,
    FundingApi $fundingApi,
    RemoteContactIdProviderInterface $remoteContactIdProvider
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->fundingApi = $fundingApi;
    $this->remoteContactIdProvider = $remoteContactIdProvider;
  }

  /**
   * {@inheritDoc}
   *
   * @param array<int|string, mixed> $configuration
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get(FundingApi::class),
      $container->get(RemoteContactIdProviderInterface::class)
    );
  }

  /**
   * {@inheritDoc}
   *
   * @phpstan-return array<string, mixed>
   */
  public function build(): array {
    return [
      '#title' => $this->t('Funding Applications'),
      '#type' => 'civiremote_funding_dashboard_group',
      '#elements' => [
        [
          '#type' => 'civiremote_funding_dashboard_element',
          '#title' => $this->t('My Applications'),
          '#url' => Url::fromUri('base:civiremote/funding/application'),
          '#content' => [
            '#markup' => '<div>' . $this->t('Manage current funding processes') . '</div>',
          ],
        ],
        [
          '#type' => 'civiremote_funding_dashboard_element',
          '#title' => $this->t('My Combined Applications'),
          '#url' => Url::fromUri('base:civiremote/funding/case'),
          '#content' => [
            '#markup' => '<div>' . $this->t('Manage current combined applications') . '</div>',
          ],
          '#access_callback' => fn() => $this->areCombinedApplicationsPossible(),
        ],
        [
          '#type' => 'civiremote_funding_dashboard_element',
          '#title' => $this->t('New Application'),
          '#url' => Url::fromUri('base:civiremote/funding/application/add'),
          '#content' => [
            '#markup' => '<div>' . $this->t('Place a new application') . '</div>',
          ],
        ],
      ],
    ];
  }

  private function areCombinedApplicationsPossible(): bool {
    if (!$this->remoteContactIdProvider->hasRemoteContactId()) {
      // Should not happen normally.
      // Loading the initial site should not fail, though.
      return FALSE;
    }

    foreach ($this->fundingApi->getFundingPrograms() as $fundingProgram) {
      $fundingCaseTypes = $this->fundingApi->getFundingCaseTypesByFundingProgramId($fundingProgram->getId());
      foreach ($fundingCaseTypes as $fundingCaseType) {
        if ($fundingCaseType->getIsCombinedApplication()) {
          return TRUE;
        }
      }
    }

    return FALSE;
  }

}
