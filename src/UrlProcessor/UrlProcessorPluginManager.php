<?php
/**
 * @file
 * Contains Drupal\facetapi\UrlProcessor\UrlProcessorPluginManager
 */

namespace Drupal\facetapi\UrlProcessor;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

class UrlProcessorPluginManager extends DefaultPluginManager {

  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/facetapi/UrlProcessor', $namespaces, $module_handler, 'Drupal\facetapi\UrlProcessor\UrlProcessorInterface', '\Drupal\facetapi\Annotation\FacetApiUrlProcessor');
  }
}