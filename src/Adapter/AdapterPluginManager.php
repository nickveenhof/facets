<?php
/**
 * Contains Drupal\facetapi\AdapterManager
 */

namespace Drupal\facetapi\Adapter;


use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;

class AdapterPluginManager extends DefaultPluginManager {

  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/Adapter', $namespaces, $module_handler, 'Drupal\facetapi\AdapterInterface', 'Drupal\facetapi\Annotation\FacetApiAdapter');
    $this->alterInfo('facetapi_adapter_info');
    $this->setCacheBackend($cache_backend, 'facetapi_adapter_plugins');
  }

} 