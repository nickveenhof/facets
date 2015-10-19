<?php
/**
 * Search api facet_manager.
 */
namespace Drupal\facetapi\Plugin\facetapi\facet_manager;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\facetapi\FacetManager\FacetManagerPluginBase;
use Drupal\facetapi\FacetInterface;
use Drupal\facetapi\FacetSource\FacetSourceInterface;
use Drupal\facetapi\FacetSource\FacetSourcePluginManager;
use Drupal\facetapi\QueryType\QueryTypePluginManager;
use Drupal\facetapi\UrlProcessor\UrlProcessorPluginManager;
use Drupal\facetapi\Widget\WidgetPluginManager;
use Drupal\search_api\Query\QueryInterface;
use Drupal\search_api\Query\ResultsCacheInterface;
use Drupal\search_api\Query\ResultSetInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @FacetApiFacetManager(
 *   id = "facetapi_default",
 *   label = @Translation("Dafault manager"),
 *   description = @Translation("Search api facet api facet_manager"),
 * )
 */
class DefaultFacetManager extends FacetManagerPluginBase {

  /**
   * @var \Drupal\search_api\Query\QueryInterface
   */
  protected $searchApiQuery;

  /**
   * @var \Drupal\search_api\Query\ResultsCacheInterface
   */
  protected $searchResultsCache;

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    // Insert the module handler.
    /** @var \Drupal\Core\Extension\ModuleHandlerInterface $module_handler */
    $module_handler = $container->get('module_handler');

    // Insert the plugin manager for query types.
    /** @var \Drupal\facetapi\QueryType\QueryTypePluginManager $query_type_plugin_manager */
    $query_type_plugin_manager = $container->get('plugin.manager.facetapi.query_type');

    // Get the ResultsCache from the container.
    /** @var \Drupal\search_api\Query\ResultsCacheInterface $results_cache */
    $results_cache = $container->get('search_api.results_static_cache');

    // Insert the plugin manager for url processors.
    /** @var UrlProcessorPluginManager $url_processor_plugin_manager */
    $url_processor_plugin_manager = $container->get('plugin.manager.facetapi.url_processor');

    /** @var \Drupal\facetapi\Widget\WidgetPluginManager $widget_plugin_manager */
    $widget_plugin_manager = $container->get('plugin.manager.facetapi.widget');

    /** @var FacetSourcePluginManager $facet_plugin_manager */
    $facet_plugin_manager = $container->get('plugin.manager.facetapi.facet_source');


    return new static($configuration, $plugin_id, $plugin_definition, $module_handler, $query_type_plugin_manager, $results_cache, $url_processor_plugin_manager, $widget_plugin_manager, $facet_plugin_manager);
  }

  public function __construct(
    array $configuration,
    $plugin_id, $plugin_definition,
    ModuleHandlerInterface $module_handler,
    QueryTypePluginManager $query_type_plugin_manager,
    ResultsCacheInterface $results_cache,
    UrlProcessorPluginManager $url_processor_plugin_manager,
    WidgetPluginManager $widget_plugin_manager,
    FacetSourcePluginManager $facet_source_manager
  ) {

    parent::__construct($configuration, $plugin_id, $plugin_definition, $module_handler, $query_type_plugin_manager, $url_processor_plugin_manager, $widget_plugin_manager, $facet_source_manager);
    $this->searchResultsCache = $results_cache;
  }

  /**
   * Process the facets in this facet_manager in this facet_manager for a test only. This
   * method should disappear later when facetapi does it.
   */
  public function updateResults() {
    // Get an instance of the facet source.
    /** @var FacetSourceInterface $facet_source_plugin */
    $facet_source_plugin = $this->facet_source_manager->createInstance($this->searcher_id);

    $facet_source_plugin->addResults($this->facets);

  }
}
