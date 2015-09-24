<?php
/**
 * @file
 * Contains  Drupal\facetapi\Facet\FacetInterface
 */

namespace Drupal\facetapi\Facet;


interface FacetInterface {

  /**
   * Get the field alias used to identify the facet in the url.
   *
   * @return mixed
   */
  public function getFieldAlias();

  /**
   * Sets an item with value to active.
   *
   * @param $value
   */
  public function setActiveItem($value);

  /**
   * Get all the active items in the facet.
   *
   * @return mixed
   */
  public function getActiveItems();

}