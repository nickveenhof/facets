<?php

/**
 * @file
 * Contains \Drupal\core_search_facetapi\Plugin\facetapi\query_type\CoreNodeSearchString.
 */

namespace Drupal\core_search_facetapi\Plugin\facetapi\query_type;

use Drupal\facetapi\QueryType\QueryTypePluginBase;
use Drupal\facetapi\Result\Result;

/**
 *
 * @FacetApiQueryType(
 *   id = "core_node_search_string",
 *   label = @Translation("String"),
 * )
 */
class CoreNodeSearchString extends QueryTypePluginBase {

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
    /** @var \Drupal\core_search_facetapi\Plugin\CoreSearchFacetSourceInterface $facetSource */
    $facetSource = $this->facet->getFacetSource();
    $query_info = $facetSource->getQueryInfo($this->facet);

    /** @var \Drupal\core_search_facetapi\FacetapiQuery $facet_query */
    $facet_query = $facetSource->getFacetQueryExtender();
    $facet_query->addFacetField($query_info);

    // Only build results if a search is executed.
    if ($facet_query->getSearchExpression()) {
      // Executes query, iterates over results.
      $results = $facet_query->execute();
      if (!empty($results)) {
        $facet_results = [];
        foreach ($results as $result) {
          $facet_results[] = new Result($result->value, $result->value, $result->count);
        }
        $this->facet->setResults($facet_results);
      }
    }

    return $this->facet;

  }

}
