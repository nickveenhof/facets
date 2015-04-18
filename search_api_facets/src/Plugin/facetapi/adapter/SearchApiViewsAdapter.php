<?php
/**
 * Search api adapter.
 */
namespace Drupal\search_api_facets\Plugin\facetapi\adapter;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\facetapi\Adapter\AdapterPluginBase;
use Drupal\search_api\Query\QueryInterface;
use Drupal\search_api\Query\ResultsCache;
use MyProject\Proxies\__CG__\stdClass;
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

  /**
   * @var Drupal\search_api\Query\ResultsCache
   */
  protected $search_results_cache;

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    // Get the ResultsCache from the container.
    $results_cache = $container->get('search_api.results_static_cache');

    $query_type_plugin_manager = $container->get('plugin.manager.facetapi.query_type');

    $plugin = new static($configuration, $plugin_id, $plugin_definition, $query_type_plugin_manager, $results_cache);

    // Insert the module handler.
    // @var ModuleHandlerInterface
    $module_handler = $container->get('module_handler');
    $plugin->setModuleHandler($module_handler);

    return $plugin;
  }

  public function __construct(array $configuration, $plugin_id, $plugin_definition, PluginManagerInterface $query_type_plugin_manager, ResultsCache $results_cache) {
    $this->search_results_cache = $results_cache;

    parent::__construct($configuration, $plugin_id, $plugin_definition, $query_type_plugin_manager);
  }


  public function alterQuery(&$query) {
    // Get enabled facets.
    $facets = $this->getEnabledFacets();
    // Alter the query here.
    // For now this is done in the Adapter implementation itself.
    // Later on, this should be done using query type plugins for a backend.
    // We need a working proof of concept, so perform it directly here.
    $options = &$query->getOptions();
    $options['search_api_facets']['entity:node/type'] = array(
      'field'             => 'entity:node/type',
      'limit'             => 50,
      'operator'          => 'and',
      'min_count'         => 0,
    );
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
    $dummy = 'dummy';
    // Get the facet values from the query that has been done.
    // Store all information in $this->facets.
    $results = $this->search_results_cache->getResults('search_api_views:search:page_1');

    return $results->getExtraData('search_api_facets');
  }

  /**
   * Build the render array for facets.
   */
  public function build() {
    $this->processFacets();

    $facet_data = $this->processFacets();
    // Create an itemlist for the field 'entity:node/type'
    $items = array();
    foreach ($facet_data['entity:node/type'] as $data) {
      $text = $data['filter'] . '(' . $data['count'] . ')';
      $items[] = $text;
    }

    $output = array(
      '#theme' => 'item_list',
      '#items' => $items,
    );
    return $output;

  }
}