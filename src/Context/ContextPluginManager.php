<?php

/**
 * @file
 * Contains \Drupal\search_api\Datasource\DatasourcePluginManager.
 */

namespace Drupal\facetapi\Context;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manages context plugins.
 *
 * @see \Drupal\facetapi\Annotation\FacetApiContext
 * @see plugin_api
 */
class ContextPluginManager extends DefaultPluginManager {

  /**
   * Constructs a DatasourcePluginManager object.
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
    parent::__construct('Plugin/facetapi/context', $namespaces, $module_handler, 'Drupal\facetapi\context\ContextInterface', 'Drupal\facetapi\Annotation\FacetApiContext');
    $this->setCacheBackend($cache_backend, 'facetapi_contexts');
    $this->alterInfo('facetapi_context_info');
  }

}
