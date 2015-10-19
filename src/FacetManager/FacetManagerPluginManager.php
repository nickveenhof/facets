<?php
/**
 * Contains Drupal\facetapi\FacetManager\FacetManagerPluginManager
 */

namespace Drupal\facetapi\FacetManager;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;

class FacetManagerPluginManager extends DefaultPluginManager implements FacetManagerPluginManagerInterface {

  /**
   * @var \Drupal\facetapi\FacetManager\FacetManagerInterface[]
   */
  protected $facet_managers = [];

  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/facetapi/facet_manager', $namespaces, $module_handler, 'Drupal\facetapi\FacetManager\FacetManagerInterface', 'Drupal\facetapi\Annotation\FacetApiFacetManager');
    $this->alterInfo('facetapi_facet_manager_info');
    $this->setCacheBackend($cache_backend, 'facetapi_facet_manager_plugins');
  }

  public function getMyOwnChangeLaterInstance($plugin_id, $search_id) {
    if (isset($this->facet_managers[$search_id])) {
      return $this->facet_managers[$search_id];
    }
    /** @var FacetManagerInterface $facet_manager */
    $facet_manager = $this->createInstance($plugin_id, array());
    $facet_manager->setSearchId($search_id);
    $this->facet_managers[$search_id] = $facet_manager;
    return $facet_manager;
  }
}
