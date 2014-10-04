<?php

/**
 * @file
 * Contains \Drupal\facet_api\Sort\SortPluginManager.
 */

namespace Drupal\facet_api\Sort;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manages facet_api sort plugins.
 *
 * @see plugin_api
 */
class SortPluginManager extends DefaultPluginManager {

  /**
   * Constructs a new FacetapiSortManager.
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
    parent::__construct('Plugin/FacetApi/Sort', $namespaces, $module_handler, 'Drupal\facet_api\SortInterface', 'Drupal\facet_api\Annotation\FacetApiSort');
    $this->alterInfo('facet_api_sort_info');
    $this->setCacheBackend($cache_backend, 'facet_api_sort_plugins');
  }

}
