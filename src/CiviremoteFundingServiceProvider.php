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

namespace Drupal\civiremote_funding;

use Drupal\civiremote_funding\JsonForms\Configurator\FileUploadArrayFactoryConfigurator;
use Drupal\civiremote_funding\JsonForms\FileUploadArrayFactory;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;
use Drupal\json_forms\Form\Util\FactoryRegistrator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @codeCoverageIgnore
 */
final class CiviremoteFundingServiceProvider implements ServiceProviderInterface {

  public function register(ContainerBuilder $container): void {
    FactoryRegistrator::registerFactories(
      $container,
      __DIR__ . '/JsonForms',
      'Drupal\\civiremote_funding\\JsonForms'
    );

    $container->getDefinition(FileUploadArrayFactory::class)
      ->setArgument('$requestStack', new Reference('request_stack'))
      ->setConfigurator(new Reference(FileUploadArrayFactoryConfigurator::class));
  }

}
