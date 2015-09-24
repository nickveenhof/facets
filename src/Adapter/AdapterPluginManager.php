<?php
/**
 * Contains Drupal\facetapi\Adapter\AdapterManager
 */

namespace Drupal\facetapi\Adapter;


use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;

class AdapterPluginManager extends DefaultPluginManager implements AdapterPluginManagerInterface {

  /**
   * @var \Drupal\facetapi\Adapter\AdapterInterface[]
   */
  protected $adapters = [];

  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/facetapi/Adapter', $namespaces, $module_handler, 'Drupal\facetapi\Adapter\AdapterInterface', 'Drupal\facetapi\Annotation\FacetApiAdapter');
    $this->alterInfo('facetapi_adapter_info');
    $this->setCacheBackend($cache_backend, 'facetapi_adapter_plugins');
  }

  public function getMyOwnChangeLaterInstance($plugin_id, $search_id) {
    if ( isset($this->adapters[$search_id])) {
      return $this->adapters[$search_id];
    }
    /** @var AdapterInterface $adapter */
    $adapter = $this->createInstance($plugin_id, array());
    $adapter->setSearchId($search_id);
    $this->adapters[$search_id] = $adapter;
    return $adapter;
  }
}