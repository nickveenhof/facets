<?php

/**
 * @file
 * Contains \Drupal\facets\Plugin\facets\query_type\SearchApiString.
 */

namespace Drupal\facets\Plugin\facets\query_type;

use Drupal\facets\QueryType\QueryTypePluginBase;
use Drupal\facets\Result\Result;


/**
 * Provides support for string facets within the Search API scope.
 *
 * This is the default implementation that works with all backends and data
 * types. While you could use this query type for every data type, other query
 * types will usually be better suited for their specific data type.
 *
 * For example, the SearchApiDate query type will handle its input as a DateTime
 * value, while this class would only be able to work with it as a string.
 *
 * @FacetsQueryType(
 *   id = "search_api_string",
 *   label = @Translation("String"),
 * )
 */
class SearchApiString extends QueryTypePluginBase {

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
    $query = $this->query;

    // Alter the query here.
    if (!empty($query)) {
      $options = &$query->getOptions();

      $field_identifier = $this->facet->getFieldIdentifier();
      $options['search_api_facets'][$field_identifier] = array(
        'field' => $field_identifier,
        'limit' => 50,
        'operator' => 'and',
        'min_count' => 0,
        'missing' => FALSE,
      );

      // Add the filter to the query if there are active values.
      $active_items = $this->facet->getActiveItems();
      if (count($active_items)) {
        foreach ($active_items as $value) {
          $filter = $query->createConditionGroup();
          $filter->addCondition($this->facet->getFieldIdentifier(), $value);
          $query->addConditionGroup($filter);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    if (!empty($this->results)) {
      $facet_results = array();
      foreach ($this->results as $result) {
        if ($result['count']) {
          $facet_results[] = new Result(trim($result['filter'], '"'), trim($result['filter'], '"'), $result['count']);
        }
      }
      $this->facet->setResults($facet_results);
    }
    return $this->facet;
  }

}
