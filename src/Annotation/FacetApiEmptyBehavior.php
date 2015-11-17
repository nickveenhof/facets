<?php

/**
 * @file
 * Contains \Drupal\facetapi\Annotation\FacetApiEmptyBehavior.
 */

namespace Drupal\facetapi\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Facet API EmptyBehavior annotation.
 *
 * @see \Drupal\facetapi\EmptyBehavior\EmptyBehaviorPluginManager
 * @see plugin_api
 *
 * @ingroup plugin_api
 *
 * @Annotation
 */
class FacetApiEmptyBehavior extends Plugin {

  /**
   * The empty behavior plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the empty behavior plugin.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * The empty behavior description.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $description;

}
