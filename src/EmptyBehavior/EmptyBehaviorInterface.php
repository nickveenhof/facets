<?php

/**
 * @file
 * Contains Drupal\facetap\EmptyBehavior\EmptyBehaviorInterface
 */

namespace Drupal\facetapi\EmptyBehavior;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Specifies the publicly available methods of an empty behavior plugin.
 *
 * @see \Drupal\facetapi\Annotation\FacetApiEmptyBehavior
 * @see \Drupal\facetapi\Plugin\EmptyBehavior\EmptyBehaviorPluginManager
 * @see \Drupal\facetapi\Plugin\EmptyBehavior\EmptyBehaviorInterface
 * @see plugin_api
 */
interface EmptyBehaviorInterface extends PluginInspectionInterface, PluginFormInterface {

  /**
   * Returns the render array used for the facet that is empty, or has no items.
   *
   * @param array $facet_empty_behavior_configs
   *   Configuration for the empty behavior.
   *
   * @return
   *   The element's render array.
   */
  public function build(array $facet_empty_behavior_configs);

}
