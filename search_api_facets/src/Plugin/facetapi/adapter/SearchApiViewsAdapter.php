<?php
/**
 * Search api adapter.
 */
namespace Drupal\search_api_facets\Plugin\facetapi\adapter;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\facetapi\Adapter\AdapterPluginBase;
use Drupal\facetapi\QueryType\QueryTypePluginManager;
use Drupal\facetapi\UrlProcessor\UrlProcessorPluginManager;
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
  protected $searchApiQuery;

  protected $searchResultsCache;

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    // Insert the module handler.
    // @var ModuleHandlerInterface
    $module_handler = $container->get('module_handler');

    // Insert the plugin manager for query types.
    // @var PluginManagerInterface
    $query_type_plugin_manager = $container->get('plugin.manager.facetapi.query_type');

    // Get the ResultsCache from the container.
    // @var ResultsCacheInterface
    $results_cache = $container->get('search_api.results_static_cache');

    // Insert the plugin manager for url processors.
    /** @var UrlProcessorPluginManager $url_processor_plugin_manager */
    $url_processor_plugin_manager = $container->get('plugin.manager.facetapi.url_processor');


    $plugin = new static($configuration, $plugin_id, $plugin_definition, $module_handler, $query_type_plugin_manager, $results_cache, $url_processor_plugin_manager);


    return $plugin;
  }

  public function __construct(
    array $configuration,
    $plugin_id, $plugin_definition,
    ModuleHandlerInterface $module_handler,
    QueryTypePluginManager $query_type_plugin_manager,
    ResultsCacheInterface $results_cache,
    UrlProcessorPluginManager $url_processor_plugin_manager
  ) {

    parent::__construct($configuration, $plugin_id, $plugin_definition, $module_handler, $query_type_plugin_manager, $url_processor_plugin_manager);
    $this->searchResultsCache = $results_cache;
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
    $results = $this->searchResultsCache->getResults($this->searcher_id);

    return $results->getExtraData('search_api_facets');
  }

}