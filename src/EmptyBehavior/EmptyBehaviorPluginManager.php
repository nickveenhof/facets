<?php

/**
 * Contains \Drupal\facetapi\EmptyBehavior\EmptyBehaviorPluginManager
 */

namespace Drupal\facetapi\EmptyBehavior;


use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;


/**
 * Provides an EmptyBehavior plugin manager.
 *
 * @see \Drupal\facetapi\Annotation\FacetApiEmptyBehavior
 * @see \Drupal\facetapi\EmptyBehavior\EmptyBehaviorInterface
 */
class EmptyBehaviorPluginManager extends DefaultPluginManager {
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/facetapi/empty_behavior', $namespaces, $module_handler, 'Drupal\facetapi\EmptyBehavior\EmptyBehaviorInterface', 'Drupal\facetapi\Annotation\FacetApiEmptyBehavior');
  }
}