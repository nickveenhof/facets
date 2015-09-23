<?php
/**
 * Contains Drupal\facet_api\Adapter\AdapterPluginManager
 */

namespace Drupal\facet_api\Adapter;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;

class AdapterPluginManager extends DefaultPluginManager {

  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/facet_api/adapter', $namespaces, $module_handler, 'Drupal\facet_api\AdapterInterface', 'Drupal\facet_api\Annotation\FacetApiAdapter');
    $this->alterInfo('facet_api_adapter_info');
    $this->setCacheBackend($cache_backend, 'facet_api_adapter_plugins');
  }
} 