<?php

/**
 * @file
 * Contains Drupal\facets\FacetSourceInterface.
 */

namespace Drupal\facets;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * The facet source entity.
 */
interface FacetSourceInterface extends ConfigEntityInterface {

  /**
   * Returns the label of the facet source.
   *
   * @return string
   *   The facet name.
   */
  public function getName();

  /**
   * Gets the filter key for this facet source.
   *
   * @return string
   *   The filter key.
   */
  public function getFilterKey();

  /**
   * Sets the filter key for this facet source.
   *
   * @param string $filter_key
   *   The filter key.
   */
  public function setFilterKey($filter_key);

}
