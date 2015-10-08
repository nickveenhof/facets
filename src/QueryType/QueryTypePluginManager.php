<?php
/**
 * Contains Drupal\facetapi\QueryType\QueryTypePluginManager
 */

namespace Drupal\facetapi\QueryType;


use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

class QueryTypePluginManager extends DefaultPluginManager {

  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/facetapi/query_type', $namespaces, $module_handler, 'Drupal\facetapi\QueryType\QueryTypeInterface', 'Drupal\facetapi\Annotation\FacetApiQueryType');
  }

}