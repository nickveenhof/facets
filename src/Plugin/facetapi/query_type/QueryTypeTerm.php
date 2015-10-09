<?php

/**
 * @file
 * Contains \Drupal\facetapi\Plugin\facetapi\query_type\QueryTypeTerm
 */

namespace Drupal\facetapi\Plugin\facetapi\query_type;

use Drupal\facetapi\Adapter\AdapterInterface;
use \Drupal\facetapi\QueryType\QueryTypePluginBase;
use Drupal\facetapi\Result\Result;
use Drupal\search_api\Query\Query;


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
   * Add facet info to the query using the backend native query object.
   *
   * @return mixed
   */
  public function execute() {
    /** @var  Query $query */
    $query = $this->query;
    // Alter the query here.
    if (! empty($query)) {
      $options = &$query->getOptions();

      $field_name = $this->facet->getName();
      $options['search_api_facets'][$field_name] = array(
        'field'     => $field_name,
        'limit'     => 50,
        'operator'  => 'and',
        'min_count' => 0,
      );

      // Add the filter to the query if there are active values.
      $active_items = $this->facet->getActiveItems();
      if (count($active_items)) {
        foreach ($active_items as $value) {
          $filter = $query->createFilter();
          $filter->condition($this->facet->getFieldIdentifier(), $value);
          $query->filter($filter);
        }
      }
    }
  }

  /**
   * Build the facet information,
   * so it can be rendered.
   *
   * @return mixed
   */
  public function build() {
    if (! empty ($this->results)) {
      $facet_results = array();
      foreach ($this->results as $result) {
        if ($result['count']) {
          $facet_results[] = new Result(trim($result['filter'], '"'), $result['count']);
        }
      }
      $this->facet->setResults($facet_results);
    }
    return $this->facet;
  }
}