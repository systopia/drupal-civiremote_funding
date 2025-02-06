<?php

/*
 * Copyright (C) 2024 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
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

namespace Drupal\civiremote_funding\Views;

use Drupal\Core\Url;
use Drupal\views\Plugin\views\field\Dropbutton;
use Drupal\views\ResultRow;

/**
 * Acts like a decorator (a real decorator is not possible) to add application
 * process document creation links.
 */
final class ApplicationProcessDropButton extends Dropbutton {

  /**
   * @var array<int, list<\Drupal\civiremote_funding\Api\DTO\ApplicationProcessTemplate>>
   *   Mapping of application process IDs to ApplicationProcessTemplate
   *   instances. Set in civiremote_funding_views_post_execute().
   */
  public static $templates = [];

  private Dropbutton $dropbutton;

  private ?int $applicationProcessId = NULL;

  public function __construct(Dropbutton $dropbutton) {
    $this->dropbutton = $dropbutton;
    parent::__construct($dropbutton->configuration, $dropbutton->getPluginId(), $dropbutton->getPluginDefinition());
    // @phpstan-ignore-next-line
    if (NULL !== $dropbutton->view) {
      $this->init($dropbutton->view, $dropbutton->view->getDisplay(), $dropbutton->options);
    }
  }

  /**
   * {@inheritDoc}
   */
  public function render(ResultRow $values) {
    $this->applicationProcessId = NULL;

    // @phpstan-ignore-next-line
    foreach ($values as $key => $value) {
      if (str_ends_with($key, '_application_process_id')) {
        $this->applicationProcessId = $value;

        break;
      }
    }

    if (get_class($this->dropbutton) !== Dropbutton::class) {
      $this->dropbutton->render($values);
    }

    return parent::render($values);
  }

  /**
   * {@inheritDoc}
   *
   * @phpstan-return array<mixed>
   *
   * @throws \Drupal\civiremote_funding\Api\Exception\ApiCallFailedException
   */
  protected function getLinks(): array {
    $links = $this->dropbutton->getLinks();
    if (NULL === $this->applicationProcessId) {
      // Should not happen.
      return $links;
    }

    foreach (self::$templates[$this->applicationProcessId] as $template) {
      $links[] = [
        'url' => Url::fromRoute('civiremote_funding.application_template_render', [
          'applicationProcessId' => $this->applicationProcessId,
          'templateId' => $template->getId(),
        ]),
        'title' => $this->t('Create: @label', ['@label' => $template->getLabel()]),
        'attributes' => [
          'target' => '_blank',
        ],
      ];
    }

    return $links;
  }

}
