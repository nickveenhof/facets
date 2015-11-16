<?php

/**
 * @file
 * Contains Drupal\core_search_facetapi\FacetManager\CoreSearchFacetManager.
 */

namespace Drupal\core_search_facetapi\FacetManager;

use Drupal\facetapi\FacetInterface;
use Drupal\facetapi\FacetManager\DefaultFacetManager;

class CoreSearchFacetManager extends DefaultFacetManager {

  /**
   * The facet query being executed.
   */
  protected $facetQueryExtender;

  /**
   * Sets the facet query object.
   *
   * @return FacetapiQuery
   */
  public function getFacetQueryExtender() {
    //if (!$this->facetQueryExtender) {

      //$this->facetQueryExtender = db_select('search_index', 'i', array('target' => 'replica'))->extend('Drupal\search\ViewsSearchQuery');
      //$this->searchQuery->searchExpression($input, $this->searchType);
      //$this->searchQuery->publicParseSearchExpression();

      $this->facetQueryExtender = db_select('search_index', 'i', array('target' => 'replica'))->extend('Drupal\core_search_facetapi\FacetapiQuery');
      $this->facetQueryExtender->join('node_field_data', 'n', 'n.nid = i.sid');
      $this->facetQueryExtender
        //->condition('n.status', 1)
        ->addTag('node_access')
        ->searchExpression($this->keys, 'node_search');
    //}
    return $this->facetQueryExtender;
  }

  /**
   * Returns the query info for this facet field.
   *
   * @param array $facet
   *   The facet definition as returned by facetapi_facet_load().
   *
   * @return array
   *   An associative array containing:
   *   - fields: An array of field information, each of which are associative
   *      arrays containing:
   *      - table_alias: The table alias the field belongs to.
   *      - field: The name of the field containing the facet data.
   *    - joins: An array of join info, each of which are associative arrays
   *      containing:
   *      - table: The table being joined.
   *      - alias: The alias of the table being joined.
   *      - condition: The condition that joins the table.
   */
  public function getQueryInfo(FacetInterface $facet) {
    //if (!$facet['field api name']) {
      $query_info = [
        'fields' => [
          'n.' . $facet->getFieldIdentifier() => [
            'table_alias' => 'n',
            'field' => $facet->getFieldIdentifier(),
          ],
        ],
      ];
    //}
    /*else {
      $query_info = array();

      // Gets field info, finds table name and field name.
      $field = field_info_field($facet['field api name']);
      $table = _field_sql_storage_tablename($field);

      // Iterates over columns, adds fields to query info.
      foreach ($field['columns'] as $column_name => $attributes) {
        $column = _field_sql_storage_columnname($field['field_name'], $column_name);
        $query_info['fields'][$table . '.' . $column] = array(
          'table_alias' => $table,
          'field' => $column,
        );
      }

      // Adds the join on the node table.
      $query_info['joins'] = array(
        $table => array(
          'table' => $table,
          'alias' => $table,
          'condition' => "n.vid = $table.revision_id",
        ),
      );
    }*/

    // Returns query info, makes sure all keys are present.
    return $query_info + [
      'joins' => [],
      'fields' => [],
    ];
  }

}
