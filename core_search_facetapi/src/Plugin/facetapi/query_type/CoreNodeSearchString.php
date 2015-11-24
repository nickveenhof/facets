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

    /** @var \Drupal\core_search_facetapi\Plugin\CoreSearchFacetSourceInterface $facetSource */
    $facetSource = $this->facet->getFacetSource();
    $query_info = $facetSource->getQueryInfo($this->facet);
    /** @var \Drupal\core_search_facetapi\FacetapiQuery $facet_query */
    $facet_query = $facetSource->getFacetQueryExtender();
    $tables_joined = [];

    // Add the filter to the query if there are active values.
    $active_items = $this->facet->getActiveItems();

    foreach ($active_items as $item) {
      foreach ($query_info['fields'] as $field_info) {

        // Adds join to the facet query.
        /*$facet_query->addFacetJoin($query_info, $field_info['table_alias']);

        // Adds adds join to search query, makes sure it is only added once.
        if (isset($query_info['joins'][$field_info['table_alias']])) {
          if (!isset($tables_joined[$field_info['table_alias']])) {
            $tables_joined[$field_info['table_alias']] = TRUE;
            $join_info = $query_info['joins'][$field_info['table_alias']];
            $this->query->join($join_info['table'], $join_info['alias'], $join_info['condition']);
          }
        }*/

        // Adds facet conditions to the queries.
        $field = $field_info['table_alias'] . '.' . $field_info['field'];
        $this->query->condition($field, $item);
        $facet_query->condition($field, $item);
      }
    }
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

