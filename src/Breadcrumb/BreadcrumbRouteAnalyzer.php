<?php

declare(strict_types=1);

namespace Drupal\civiremote_funding\Breadcrumb;

use Drupal\civiremote_funding\Route\RouteAnalyzer;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;

final class BreadcrumbRouteAnalyzer implements ContainerInjectionInterface {

  private RouteAnalyzer $routeAnalyzer;

  /**
   * {@inheritDoc}
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new self(RouteAnalyzer::create($container));
  }

  public function __construct(RouteAnalyzer $routeAnalyzer) {
    $this->routeAnalyzer = $routeAnalyzer;
  }

  /**
   * @return bool
   *   TRUE if the breadcrumb for the specified route contains a funding case
   *   name.
   */
  public function containsFundingCaseName(Route $route): bool {
    return $this->routeAnalyzer->isSubPath('civiremote_funding.transfer_contract', $route->getPath());
  }

  /**
   * @bool bool
   *   TRUE if the breadcrumb for the given route contains data that might be
   *   changed.
   */
  public function containsVariableData(Route $route): bool {
    return $this->containsFundingCaseName($route);
  }

}
