<?php

/**
 * @file
 * Contains Drupal\facetap\EmptyBehavior\EmptyBehaviorInterface
 */

namespace Drupal\facetapi\EmptyBehavior;


use Drupal\facetapi\FacetInterface;

interface EmptyBehaviorInterface {

  /**
   * Returns the render array used for the facet that is empty, or has no items.
   *
   * @return
   *   The element's render array.
   */
  public function build(array $facet);
}
