<?php
/**
 * @file
 * Contains Drupal\facets\QueryType\QueryType.
 */

namespace Drupal\facets\QueryType;

/**
 * The interface defining the required methods for a query type.
 */
interface QueryTypeInterface {

  /**
   * Add facet info to the query using the backend native query object.
   *
   * @return mixed
   */
  public function execute();

  /**
   * Build the facet information, so it can be rendered.
   */
  public function build();

}
