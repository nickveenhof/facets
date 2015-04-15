<?php
/**
 * Search api adapter.
 */
namespace Drupal\search_api_facets\Plugin\facetapi\adapter;

use Drupal\facetapi\Adapter\AdapterPluginBase;

/**
 * @FacetApiAdapter(
 *   id = "search_api",
 *   label = @Translation("Search api"),
 *   description = @Translation("Search api facet api adapter"),
 *   deriver = "Drupal\search_api_facets\Plugin\facetapi\adapter\SearchApiAdapterDeriver"
 * )
 */
class SearchApiAdapter extends AdapterPluginBase {

}