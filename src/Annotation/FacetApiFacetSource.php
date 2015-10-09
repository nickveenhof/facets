<?php

/**
 * @file
 * Contains \Drupal\facetapi\Annotation\FacetApiFacet.
 */

namespace Drupal\facetapi\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Facet API backend annotation object.
 *
 * @see \Drupal\facetapi\FacetSource\FacetSourcePluginManager
 * @see \Drupal\facetapi\FacetSource\FacetSourceInterface
 * @see \Drupal\facetapi\FacetSource\FacetSourcePluginBase
 * @see plugin_api
 *
 * @Annotation
 */
class FacetApiFacetSource extends Plugin {

  /**
   * The facet source plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the facet soruce plugin.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * The facet source description.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $description;

}
