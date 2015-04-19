<?php
/**
 * Search api adapter.
 */
namespace Drupal\search_api_facets\Plugin\facetapi\adapter;

use Drupal\facetapi\Adapter\AdapterPluginBase;
use Drupal\search_api\Query\QueryInterface;
use Drupal\search_api\Query\ResultsCacheInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @FacetApiAdapter(
 *   id = "search_api",
 *   label = @Translation("Search api"),
 *   description = @Translation("Search api facet api adapter"),
 * )
 *
 */
class SearchApiViewsAdapter extends AdapterPluginBase {

  /*
   * @var Drupal\search_api\Query\QueryInterface
   */
  protected $search_api_query;

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $plugin = parent::create($container, $configuration, $plugin_id, $plugin_definition);

    // Get the ResultsCache from the container.
    $results_cache = $container->get('search_api.results_static_cache');
    $plugin->setSearchResultsCache($results_cache);

    return $plugin;
  }

  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * Get the search results cache.
   *
   * @return Drupal\search_api\Query\ResultsCacheInterface
   */
  public function getSearchResultsCache() {
    return $this->search_results_cache;
  }

  /**
   * @param Drupal\search_api\Query\ResultsCacheInterface $search_results_cache
   */
  public function setSearchResultsCache(ResultsCacheInterface $search_results_cache) {
    $this->search_results_cache = $search_results_cache;
  }

  /**
   * Add the given facet to the query.
   *
   * Helper method only for search api.
   * Don't move up!!!
   *
   * @param array $facet
   * @param \Drupal\search_api\Query\QueryInterface $query
   */
  public function addFacet(array $facet, QueryInterface $query) {
    if (isset($this->fields[$facet['name']])) {
      $options = &$query->getOptions();
      $facet_info = $this->fields[$facet['name']];
      if (!empty($facet['query_options'])) {
        // Let facet-specific query options override the set options.
        $facet_info = $facet['query_options'] + $facet_info;
      }
      $options['search_api_facets'][$facet['name']] = $facet_info;
    }
  }

  /**
   * Process the facets in this adapter in this adapter
   * for a test only. This method should disappear later
   * when facetapi does it.
   */
  public function processFacets() {
    // Get the facet values from the query that has been done.
    // Store all information in $this->facets.
    $results = $this->search_results_cache->getResults($this->searcher_id);

    return $results->getExtraData('search_api_facets');
  }

}