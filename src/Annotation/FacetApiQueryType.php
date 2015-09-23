<?php

/**
 * @file
 * Contains \Drupal\facetapi\Annotation\FacetApiQueryType.
 */

namespace Drupal\facetapi\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Facet API QueryType annotation object.
 *
 * @see \Drupal\facetapi\QueryType\QueryTypePluginManager
 * @see plugin_api
 *
 * @ingroup plugin_api
 *
 * @Annotation
 */
class FacetApiQueryType extends Plugin {

  /**
   * The adapter plugin id
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the adapter plugin
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * The adapter description.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $description;

  /**
   * Class used to retrieve derivative definitions of the adapter.
   *
   * @var string
   */
  public $derivative = '';

}
