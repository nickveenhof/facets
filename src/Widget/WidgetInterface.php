<?php
/**
 * @file
 * Contains Drupal\facetapi\Widget\WidgetInterface.
 */

namespace Drupal\facetapi\Widget;

use Drupal\facetapi\FacetInterface;
/**
 *
 */
interface WidgetInterface {

  /**
   * Add facet info to the query using the selected query type.
   *
   * @return mixed
   */
  public function execute();

  /**
   * Builds the widget for rendering.
   */
  public function build(FacetInterface $facet);

  /**
   * Pick the query type that this widget prefers given an array with
   * query type classes.
   *
   * @param $query_types
   *   An array keyed with query type name and it's plugin class to load.
   *
   * @return mixed
   */
  public function getQueryType($query_types);

}
