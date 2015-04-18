<?php
/**
 * User: jur
 * Date: 17-04-15
 * Time: 16:28
 */

namespace Drupal\search_api_facets\Plugin\Facetapi\QueryType;

use Drupal\facetapi\Adapter\AdapterInterface;
use \Drupal\facetapi\QueryType\QueryTypePluginBase;


/**
 * @FacetApiQueryType(
 *   id = "search_api_term",
 *   label = @Translation("Search api term"),
 *   description = @Translation("Search api term"),
 * )
 *
 */
class QueryTypeTerm extends QueryTypePluginBase {

  /**
   * Indicate if the queryType interface supports the adapter.
   *
   * @param \Drupal\facetapi\Adapter\AdapterInterface $adapter
   *
   * @return mixed
   */
  static public function supportsAdapter(AdapterInterface $adapter) {
    // TODO: Implement supportsAdapter() method.
  }
}