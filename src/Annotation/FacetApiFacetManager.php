<?php

/**
 * @file
 * Contains \Drupal\facetapi\Annotation\FacetApiFacetManager.
 */

namespace Drupal\facetapi\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Facet API FacetManager annotation object.
 *
 * @see \Drupal\facetapi\FacetApiFacetManagerManager
 * @see plugin_api
 *
 * @ingroup plugin_api
 *
 * @Annotation
 */
class FacetApiFacetManager extends Plugin {

  /**
   * The facet_manager plugin id
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the facet_manager plugin
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * The facet_manager description.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $description;

  /**
   * Class used to retrieve derivative definitions of the facet_manager.
   *
   * @var string
   */
  public $derivative = '';

}
