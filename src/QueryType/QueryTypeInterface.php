<?php
/**
 * @file
 * Contains Drupal\facetapi\QueryType\QueryType
 */

namespace Drupal\facetapi\QueryType;

use Drupal\facetapi\Adapter\AdapterInterface;

interface QueryTypeInterface {

  /**
   * Indicate if the queryType interface supports the adapter.
   *
   * @param \Drupal\facetapi\Adapter\AdapterInterface $adapter
   *
   * @return mixed
   */
  static public function supportsAdapter(AdapterInterface $adapter);

}