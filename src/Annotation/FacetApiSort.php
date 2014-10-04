<?php

/**
 * @file
 * Contains \Drupal\facet_api\Annotation\FacetApiSort.
 */

namespace Drupal\facet_api\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Facet API Sorter annotation object.
 *
 * @see \Drupal\facet_api\FacetApiSortManager
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
