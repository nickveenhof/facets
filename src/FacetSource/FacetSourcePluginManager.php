<?php

/**
 * @file
 * Contains \Drupal\facets\FacetSource\FacetSourcePluginManager.
 */

namespace Drupal\facets\FacetSource;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manages facet source plugins.
 *
 * @see \Drupal\facets\Annotation\FacetsFacetSource
 * @see \Drupal\facets\FacetSource\FacetSourcePluginBase
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
    parent::__construct('Plugin/facets/facet_source', $namespaces, $module_handler, 'Drupal\facets\FacetSource\FacetSourcePluginInterface', 'Drupal\facets\Annotation\FacetsFacetSource');
  }

}
