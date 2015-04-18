<?php
/**
 * @file
 * Contains Drupal\facetapi\QueryType\QueryType
 */

namespace Drupal\facetapi\QueryType;

use Drupal\facetapi\Adapter\AdapterInterface;

interface QueryTypeInterface {

  /**
   * Add facet info to the query using the backend native query object.
   *
   * @param $query
   *
   * @return mixed
   */
  public function execute($query);

  /**
   * Build the facet information,
   * so it can be rendered.
   *
   * @return mixed
   */
  public function build();

}