<?php

/**
 * @file
 * Contains \Drupal\core_search_facetapi\Plugin\facetapi\query_type\CoreSearchString.
 */

namespace Drupal\core_search_facetapi\Plugin\facetapi\query_type;

use Drupal\facetapi\QueryType\QueryTypePluginBase;
use Drupal\facetapi\Result\Result;

/**
 *
 * @FacetApiQueryType(
 *   id = "core_search_string",
 *   label = @Translation("String"),
 * )
 */
class CoreSearchString extends QueryTypePluginBase {

  /**
   * Holds the backend's native query object.
   *
   * @var \Drupal\search_api\Query\QueryInterface
   */
  protected $query;

  /**
   * {@inheritdoc}
   */
  public function execute() {

  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    // @TODO avoid \Drupal here.
    /** @var \Drupal\core_search_facetapi\FacetManager\CoreSearchFacetManager $facet_manager */
    $facet_manager = \Drupal::service('core_search_facetapi.core_manager');

    $query_info = $facet_manager->getQueryInfo($this->facet);

    /** @var \Drupal\core_search_facetapi\FacetapiQuery $facet_query */
    $facet_query = $facet_manager->getFacetQueryExtender();
    $facet_query->addFacetField($query_info);

    // Only build results if a search is executed.
    if ($facet_query->getSearchExpression()) {
      // Executes query, iterates over results.
      $results = $facet_query->execute();
      if (!empty($results)) {
        $facet_results = [];
        foreach ($results as $result) {
          // @TODO replace 'test' here. Testing.
          //$facet_results[] = new Result(trim($result['filter'], '"'), trim($result['filter'], '"'), $result['count']);
          $facet_results[] = new Result('test', $result->value, $result->count);
        }
        $this->facet->setResults($facet_results);
      }
    }

    return $this->facet;

  }

}
