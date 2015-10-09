<?php

/**+
 * @file
 * Contains \Drupal\facetapi\FacetSource\FacetSourcePluginBase.
 */

namespace Drupal\facetapi\FacetSource;

use Drupal\Component\Plugin\PluginBase;

/**
 * Defines a base class from which other facet sources may extend.
 *
 * Plugins extending this class need to define a plugin definition array through
 * annotation. The definition includes the following keys:
 * - id: The unique, system-wide identifier of the datasource.
 * - label: The human-readable name of the datasource, translated.
 * - description: A human-readable description for the datasource, translated.
 *
 * @see \Drupal\facetapi\Annotation\FacetApiFacetSource
 * @see \Drupal\facetapi\FacetSource\FacetSourcePluginManager
 * @see \Drupal\facetapi\FacetSource\FacetSourceInterface
 * @see plugin_api
 */
abstract class FacetSourcePluginBase extends PluginBase implements FacetSourceInterface {

  public function getAllowedQueryTypes() {
    return [];
  }

  public function getFields() {
    return [];
  }
}
