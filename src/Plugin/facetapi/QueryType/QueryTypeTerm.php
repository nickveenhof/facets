<?php
/**
 * @file
 * Contains Drupal\facetapi\Plugin\facetapi\querytype\QueryTypeTerm
 */

namespace Drupal\facetapi\Plugin\facetapi\querytype;

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
   * Add facet info to the query using the backend native query object.
   *
   * @return mixed
   */
  public function execute() {
    // Alter the query here.
    if (! empty($this->query)) {
      $options = &$this->query->getOptions();

      $field_name = $this->facet['field'];
      $options['search_api_facets'][$field_name] = array(
        'field'     => $field_name,
        'limit'     => 50,
        'operator'  => 'and',
        'min_count' => 0,
      );
    }
  }

  /**
   * Build the facet information,
   * so it can be rendered.
   *
   * @return mixed
   */
  public function build() {
    // TODO: Implement build() method.
    $build = array();
    if (! empty ($this->results)) {
      $items = array();
      foreach ($this->results as $result) {
        $items[] = $result['filter'] . ' (' . $result['count'] . ')';
      }
      $build = array(
        '#theme' => 'item_list',
        '#items' => $items,
      );
      return $build;
    }
    return;
  }

}