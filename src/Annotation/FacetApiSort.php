<?php

/**
 * @file
 * Contains \Drupal\facetapi\Annotation\FacetApiSort.
 */

namespace Drupal\facetapi\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Facet API Sorter annotation object.
 *
 * @see \Drupal\facetapi\FacetApiSortManager
 * @see plugin_api
 *
 * @Annotation
 */
class FacetApiSort extends Plugin {

  /**
   * The sorter plugin id
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the sorter plugin
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * The sorter description.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $description;
}
