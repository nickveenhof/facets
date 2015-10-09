<?php

/**
 * @file
 * Contains \Drupal\facetapi\FacetSource\FacetSourcePluginManager.
 */

namespace Drupal\facetapi\FacetSource;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manages facet source plugins.
 *
 * @see \Drupal\facetapi\Annotation\FacetApiFacetSource
 * @see \Drupal\facetapi\FacetSource\FacetSourcePluginBase
 * @see plugin_api
 */
class FacetSourcePluginManager extends DefaultPluginManager {

  /**
   * Constructs a FacetSourcePluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/facetapi/facet_source', $namespaces, $module_handler, 'Drupal\facetapi\FacetSource\FacetSourceInterface', 'Drupal\facetapi\Annotation\FacetApiFacetSource');
  }

}
