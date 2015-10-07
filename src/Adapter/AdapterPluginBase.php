<?php

/**
 * @file
 * Contains Drupal\facetapi\Plugin\Adapter\AdapterBase.
 */

namespace Drupal\facetapi\Adapter;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\facetapi\FacetInterface;
use Drupal\facetapi\QueryType\QueryTypePluginManager;
use Drupal\facetapi\Result\Result;
use Drupal\facetapi\UrlProcessor\UrlProcessorInterface;
use Drupal\facetapi\UrlProcessor\UrlProcessorPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Plugin\PluginManagerInterface;
use \Drupal\facetapi\Entity\Facet;

/**
 * Base class for Facet API adapters.
 *
 * @TODO: rewrite D7 comment block:
 * Adapters are responsible for abstracting interactions with the Search backend
 * that are necessary for faceted search. The adapter is also responsible for
 * retrieving facet information passed by the user via the a processor plugin
 * taking the appropriate action, whether it is checking dependencies for all
 * enabled facets or passing the appropriate query type plugin to the backend
 * so that it can execute the actual facet query.
 */
abstract class AdapterPluginBase extends PluginBase implements AdapterInterface, ContainerFactoryPluginInterface {

  /**
   * The plugin manager.
   */
  protected $query_type_plugin_manager;

  /**
   * The url processor plugin manager.
   *
   * @var UrlProcessorPluginManager
   */
  protected $url_processor_plugin_manager;

  /**
   * @var ModuleHandlerInterface
   */
  protected $module_handler;

  /**
   * The search keys, or query text, submitted by the user.
   *
   * @var string
   */
  protected $keys;

  /**
   * An array of FacetInterface objects for facets being rendered.
   *
   * @var FacetInterface[]
   *
   * @see FacetapiFacet
   */
  protected $facets = array();

  /**
   * @TODO: generalize to ProcessorInterface and properly type hint in __construct().
   * The url processor plugin associated with this adapter.
   *
   * @var UrlProcessorInterface
   */
  protected $urlProcessor;

  /**
   * A boolean flagging whether the facets have been processed, or built.
   *
   * This variable acts as a per-adapter semaphore that ensures facet data is
   * processed only once.
   *
   * @var boolean
   *
   * @see FacetapiAdapter::processFacets()
   */
  protected $processed = FALSE;

  /**
   * Stores the search path associated with this searcher.
   *
   * @var string
   */
  protected $searchPath;

  /**
   * Stores settings with defaults.
   *
   * @var array
   *
   * @see FacetapiAdapter::getFacetSettings()
   */
  protected $settings = array();

  /**
   * Searcher id.
   *
   * @var string
   */
  protected $searcher_id;

  /**
   * Returns the search path associated with this searcher.
   *
   * @return string
   *   A string containing the search path.
   *
   * @todo D8 should provide an API function for this.
   */
  public function getSearchPath() {
    // TODO: Implement getSearchPath() method.
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    // Insert the module handler.
    // @var ModuleHandlerInterface
    $module_handler = $container->get('module_handler');

    // Insert the plugin manager for query types.
    // @var PluginManagerInterface
    $query_type_plugin_manager = $container->get('plugin.manager.facetapi.query_type');

    // Insert the plugin manager for url processors.
    $url_processor_plugin_manager = $container->get('plugin.manager.facetapi.url_processor');

    $plugin = new static($configuration, $plugin_id, $plugin_definition, $module_handler, $query_type_plugin_manager, $url_processor_plugin_manager);
    return $plugin;
  }

  /**
   * Set the search id.
   *
   * @return mixed
   */
  public function setSearchId($search_id) {
    $this->searcher_id = $search_id;
  }

  /**
   * Constructs a new instance.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   * @param \Drupal\facetapi\QueryType\QueryTypePluginManager $query_type_plugin_manager
   */
  public function __construct(
    array $configuration,
    $plugin_id, $plugin_definition,
    ModuleHandlerInterface $module_handler,
    QueryTypePluginManager $query_type_plugin_manager,
    UrlProcessorPluginManager $url_processor_plugin_manager
  ) {

    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->module_handler = $module_handler;
    $this->query_type_plugin_manager = $query_type_plugin_manager;
    $this->url_processor_plugin_manager = $url_processor_plugin_manager;

    // Immediately initialize the facets.
    // This can be done directly because the only thing needed is
    // the url.
    $this->initFacets();
  }

  /**
   * Sets the search keys, or query text, submitted by the user.
   *
   * @param string $keys
   *   The search keys, or query text, submitted by the user.
   *
   * @return self
   *   An instance of this class.
   */
  public function setSearchKeys($keys) {
    $this->keys = $keys;
    return $this;
  }

  /**
   * Gets the search keys, or query text, submitted by the user.
   *
   * @return string
   *   The search keys, or query text, submitted by the user.
   */
  public function getSearchKeys() {
    return $this->keys;
  }

  /**
   * Returns the number of results returned by the search query.
   *
   * @return int
   *   The number of results returned by the search query.
   */
  public function getResultCount() {
    // TODO: Implement getResultCount() method.
  }

  /**
   * Returns the number of results per page.
   *
   * @return int
   *   The number of results per page, or the limit.
   */
  public function getPageLimit() {
    // TODO: Implement getPageLimit() method.
  }

  /**
   * Returns the page number of the search result set.
   *
   * @return int
   *   The current page of the result set.
   */
  public function getPageNumber() {
    // TODO: Implement getPageNumber() method.
  }

  /**
   * Returns the total number of pages in the result set.
   *
   * @return int
   *   The total number of pages.
   */
  public function getPageTotal() {
    // TODO: Implement getPageTotal() method.
  }

  /**
   * Allows the backend to add facet queries to its native query object.
   *
   * This method is called by the implementing module to initialize the facet
   * display process. The following actions are taken:
   * - FacetapiAdapter::initActiveFilters() hook is invoked.
   * - Dependency plugins are instantiated and executed.
   * - Query type plugins are executed.
   *
   * @param mixed $query
   *   The backend's native query object.
   *
   * @todo Should this method be deprecated in favor of one name init()? This
   *   might make the code more readable in implementing modules.
   *
   * @see FacetapiAdapter::initActiveFilters()
   */
  public function alterQuery(&$query) {
    /** @var Facet[] $facets */
    // Get the searcher name from the query.
    $search_id = $this->searcher_id;
    foreach ($this->facets as $facet) {
      // Only if the facet is for this query, alter the query.
      if ($facet->getSearcherName() == $search_id) {
        // Create the query type plugin.
        $query_type_plugin = $this->query_type_plugin_manager->createInstance($facet->getQueryType(),
          array('query' => $query, 'facet' => $facet));
        // Let the query type alter the query.
        $query_type_plugin->execute();
      }
    }
  }

  /**
   * Returns enabled facets for the searcher associated with this adapter.
   *
   * @return Facet[]
   *   An array of enabled facets.
   */
  public function getEnabledFacets() {
    // Use the hook_info to discover facets.
    /** @var Facet[] $facet_definitions */
    $facet_definitions = $this->module_handler->invokeAll('facetapi_facet_info');
    // Maybe also add different discovery methods later,
    // for instance in the adapter itself.
    return $facet_definitions;
  }


  /**
   * @return string
   */
  public function getSearcherId() {
    return $this->searcher_id;
  }

  /**
   * Initializes facet builds, sets the breadcrumb trail.
   *
   * Facets are built via FacetapiFacetProcessor objects. Facets only need to be
   * processed, or built, once regardless of how many realms they are rendered
   * in. The FacetapiAdapter::processed semaphore is set when this method is
   * called ensuring that facets are built only once regardless of how many
   * times this method is called.
   *
   * @todo For clarity, should this method be named buildFacets()?
   */
  public function processFacets() {
    if (! $this->processed) {
      // First add the results to the facets.
      $this->updateResults();

      // Then update the urls
      $this->updateResultUrls();

      // Set the facets to be processed.
      $this->processed = TRUE;
    }
  }

  /**
   * Initialize enabled facets.
   *
   * In this method the url processor is used
   * to check for each facet what the active items are.
   */
  protected function initFacets() {
    if (empty($this->facets)) {
      $this->facets = $this->getEnabledFacets();
      foreach ($this->facets as $facet) {
        /** @var UrlProcessorInterface $url_processor */
        $url_processor_name = $facet->getUrlProcessorName();
        $url_processor = $this->url_processor_plugin_manager->createInstance($url_processor_name);
        $url_processor->processFacet($facet);
      }
    }

  }

  public function build($facet) {
    // Process the facets.
    // @TODO: inject the searcher id on create of the adapter.
    $this->searcher_id = $facet->getSearcherName();

    $this->processFacets();
    // Let the plugin render the facet.

    // @TODO: functionality to alter the state of the facet should
    // somewhere else. Now here for speed reasons for proof of concept.

    // Return the render array.
    return $this->buildRenderArray($facet);
  }

  abstract public function updateResults();

  /**
   * Build the facet information,
   * so it can be rendered.
   *
   * @TODO: REMOVE THIS: this only for demo purposes.
   * Later this should be replaced by a render plugin.
   *
   * @return mixed
   */
  protected function buildRenderArray(FacetInterface $facet) {
    // @TODO: Move the rendering to it's own object.
    // Here only the links should be gererated.
    $build = array();
    /** @var Result[] $results */
    $results = $facet->getResults();
    if (! empty ($results)) {
      $items = array();
      foreach ($results as $result) {
        if ($result->getCount()) {
          // Get the link.
          $text = $result->getValue() . ' (' . $result->getCount() . ')';
          if ($result->isActive()) {
            $text = '(-) ' . $text;
          }
          $link_generator = \Drupal::linkGenerator();
          $link = $link_generator->generate($text, $result->getUrl());
          $items[] = $link;
        }
      }
      $build = array(
        '#theme' => 'item_list',
        '#items' => $items,
      );
    }
    return $build;
  }

  protected function updateResultUrls() {
    // Create the urls for the facets using the url processor.
    foreach ($this->facets as $facet) {
      /** @var UrlProcessorInterface $url_processor */
      $url_processor_name = $facet->getUrlProcessorName();
      $url_processor = $this->url_processor_plugin_manager->createInstance($url_processor_name);
      $url_processor->addUriToResults($facet);
    }
  }
}
