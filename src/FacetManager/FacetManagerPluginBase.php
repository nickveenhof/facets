<?php

/**
 * @file
 * Contains Drupal\facetapi\Plugin\FacetManager\FacetManagerBase.
 */

namespace Drupal\facetapi\FacetManager;

use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\facetapi\EmptyBehavior\EmptyBehaviorPluginManager;
use Drupal\facetapi\FacetApiException;
use Drupal\facetapi\FacetInterface;
use Drupal\facetapi\FacetSource\FacetSourcePluginManager;
use Drupal\facetapi\Processor\BuildProcessorInterface;
use Drupal\facetapi\Processor\PreQueryProcessorInterface;
use Drupal\facetapi\Processor\ProcessorInterface;
use Drupal\facetapi\Processor\ProcessorPluginManager;
use Drupal\facetapi\Processor\UrlProcessorInterface;
use Drupal\facetapi\QueryType\QueryTypePluginManager;
use Drupal\facetapi\Result\Result;
use Drupal\facetapi\Widget\WidgetPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use \Drupal\facetapi\Entity\Facet;

/**
 * Base class for Facet API FacetManagers.
 *
 * @TODO: rewrite D7 comment block:
 * FacetManagers are responsible for abstracting interactions with the Search backend
 * that are necessary for faceted search. The FacetManager is also responsible for
 * retrieving facet information passed by the user via the a processor plugin
 * taking the appropriate action, whether it is checking dependencies for all
 * enabled facets or passing the appropriate query type plugin to the backend
 * so that it can execute the actual facet query.
 */
abstract class FacetManagerPluginBase extends PluginBase implements FacetManagerInterface, ContainerFactoryPluginInterface {

  /**
   * The plugin manager.
   */
  protected $query_type_plugin_manager;

  /**
   * The facet source plugin manager.
   *
   * @var FacetSourcePluginManager
   */
  protected $facet_source_manager;

  /**
   * The processor plugin manager.
   *
   * @var \Drupal\facetapi\Processor\ProcessorPluginManager
   */
  protected $processor_plugin_manager;

  /**
   * The empty behavior plugin manager.
   *
   * @var \Drupal\facetapi\EmptyBehavior\EmptyBehaviorPluginManager
   */
  protected $empty_behavior_plugin_manager;

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
   * The url processor plugin associated with this FacetManager.
   *
   * @var UrlProcessorInterface
   */
  protected $urlProcessor;

  /**
   * A boolean flagging whether the facets have been processed, or built.
   *
   * This variable acts as a per-FacetManager semaphore that ensures facet data is
   * processed only once.
   *
   * @var boolean
   *
   * @see FacetapiFacetManager::processFacets()
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
   * @see FacetapiFacetManager::getFacetSettings()
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
    /** @var \Drupal\Core\Extension\ModuleHandlerInterface $module_handler */
    $module_handler = $container->get('module_handler');

    // Insert the plugin manager for query types.
    /** @var \Drupal\facetapi\QueryType\QueryTypePluginManager $query_type_plugin_manager */
    $query_type_plugin_manager = $container->get('plugin.manager.facetapi.query_type');

    /** @var \Drupal\facetapi\Widget\WidgetPluginManager $widget_plugin_manager */
    $widget_plugin_manager = $container->get('plugin.manager.facetapi.widget');

    /** @var \Drupal\facetapi\FacetSource\FacetSourcePluginManager $facet_source_plugin_manager */
    $facet_source_plugin_manager = $container->get('plugin.manager.facetapi.facet_source');

    /** @var \Drupal\facetapi\Processor\ProcessorPluginManager $processor_plugin_manager */
    $processor_plugin_manager = $container->get('plugin.manager.facetapi.processor');

    /** @var \Drupal\facetapi\EmptyBehavior\EmptyBehaviorPluginManager $empty_behavior_plugin_manager */
    $empty_behavior_plugin_manager = $container->get('plugin.manager.facetapi.empty_behavior');

    return new static($configuration, $plugin_id, $plugin_definition, $module_handler, $query_type_plugin_manager, $widget_plugin_manager, $facet_source_plugin_manager, $processor_plugin_manager, $empty_behavior_plugin_manager);
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
    $plugin_id,
    $plugin_definition,
    ModuleHandlerInterface $module_handler,
    QueryTypePluginManager $query_type_plugin_manager,
    WidgetPluginManager $widget_plugin_manager,
    FacetSourcePluginManager $facet_source_manager,
    ProcessorPluginManager $processor_plugin_manager,
    EmptyBehaviorPluginManager $empty_behavior_plugin_manager,
    EntityManager $entityManager
  ) {

    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->module_handler = $module_handler;
    $this->query_type_plugin_manager = $query_type_plugin_manager;
    $this->widget_plugin_manager = $widget_plugin_manager;
    $this->facet_source_manager = $facet_source_manager;
    $this->processor_plugin_manager = $processor_plugin_manager;
    $this->empty_behavior_plugin_manager = $empty_behavior_plugin_manager;
    $this->facet_storage = $entityManager->getStorage('facetapi_facet');

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
   * - FacetapiFacetManager::initActiveFilters() hook is invoked.
   * - Dependency plugins are instantiated and executed.
   * - Query type plugins are executed.
   *
   * @param mixed $query
   *   The backend's native query object.
   *
   * @todo Should this method be deprecated in favor of one name init()? This
   *   might make the code more readable in implementing modules.
   *
   * @see FacetapiFacetManager::initActiveFilters()
   */
  public function alterQuery(&$query) {
    /** @var Facet[] $facets */
    // Get the searcher name from the query.
    $search_id = $this->searcher_id;
    foreach ($this->facets as $facet) {
      // Only if the facet is for this query, alter the query.
      // @TODO use the line for tests only.
      if ($facet->getFacetSourceId() == $search_id) {
        // Create the query type plugin.
        $query_type_plugin = $this->query_type_plugin_manager->createInstance($facet->getQueryType(),
          array('query' => $query, 'facet' => $facet));
        // Let the query type alter the query.
        $query_type_plugin->execute();
      }
    }
  }

  /**
   * Returns enabled facets for the searcher associated with this FacetManager.
   *
   * @return Facet[]
   *   An array of enabled facets.
   */
  public function getEnabledFacets() {
    return $this->facet_storage->loadMultiple();
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
   * in. The FacetapiFacetManager::processed semaphore is set when this method is
   * called ensuring that facets are built only once regardless of how many
   * times this method is called.
   *
   * @todo For clarity, should this method be named buildFacets()?
   */
  public function processFacets() {
    if (!$this->processed) {
      // First add the results to the facets.
      $this->updateResults();

      // Set the facets to be processed.
      $this->processed = TRUE;
    }
  }

  /**
   * Initialize enabled facets.
   *
   * In this method all pre-query processors get called and their contents are
   * executed.
   */
  protected function initFacets() {
    if (empty($this->facets)) {
      $this->facets = $this->getEnabledFacets();
      foreach ($this->facets as $facet) {

        foreach ($facet->getProcessorConfigs() as $processor_configuration) {
          $processor_definition = $this->processor_plugin_manager->getDefinition($processor_configuration['processor_id']);
          if (is_array($processor_definition['stages']) && array_key_exists(ProcessorInterface::STAGE_PRE_QUERY, $processor_definition['stages'])) {
            /** @var PreQueryProcessorInterface $pre_query_processor */
            $pre_query_processor = $this->processor_plugin_manager->createInstance($processor_configuration['processor_id']);
            if (!$pre_query_processor instanceof PreQueryProcessorInterface) {
              throw new FacetApiException($this->t("The processor @processor has a pre_query definition but doesn't implement the required PreQueryProcessorInterface interface", ['@processor' => $processor_configuration['processor_id']]));
            }
            $pre_query_processor->preQuery($facet);
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet) {
    // It might be that the facet received here,
    // is not the same as the already loaded facets in the FacetManager.
    // For that reason, get the facet from the already loaded facets
    // in the FacetManager.
    // If this is omitted, building will fail.
    $facet = $this->facets[$facet->id()];

    // Process the facets.
    // @TODO: inject the searcher id on create of the FacetManager.
    $this->searcher_id = $facet->getFacetSourceId();

    if ($facet->getOnlyVisibleWhenFacetSourceIsVisible()) {
      // Block rendering and processing should be stopped when the facet source
      // is not available on the page. Returning an empty array here should be
      // enough to halt all further processing. This should probably go in an
      // earlier step of the facet building process but here's fine for now.
      $facet_source = $facet->getFacetSource();
      if(!$facet_source->isRenderedInCurrentRequest()){
        return [];
      }
    }

    // For clarity, process facets is called each build.
    // The first facet therefor will trigger the processing. Note that
    // processing is done only once, so repeatedly calling this method will not
    // trigger the processing more than once.
    // Furthermore: don't add any processing after this method call! All
    // processing should be done in the processFacets method.
    //
    // After the processFacets is finished, all information for rendering
    // is added to the facet.
    $this->processFacets();

    // Get the current results from the facets and let all processors that
    // trigger on the build step do their build processing.

    // @see \Drupal\facetapi\Processor\BuildProcessorInterface
    // @see \Drupal\facetapi\Processor\WidgetOrderProcessorInterface
    $results = $facet->getResults();

    foreach ($facet->getProcessorConfigs() as $processor_configuration) {
      $processor_definition = $this->processor_plugin_manager->getDefinition($processor_configuration['processor_id']);
      if (is_array($processor_definition['stages']) && array_key_exists(ProcessorInterface::STAGE_BUILD, $processor_definition['stages'])) {
        /** @var BuildProcessorInterface $build_processor */
        $build_processor = $this->processor_plugin_manager->createInstance($processor_configuration['processor_id']);
        if (!$build_processor instanceof BuildProcessorInterface) {
          throw new FacetApiException($this->t("The processor @processor has a build definition but doesn't implement the required BuildProcessorInterface interface", ['@processor' => $processor_configuration['processor_id']]));
        }
        $results = $build_processor->build($facet, $results);
      }
    }
    $facet->setResults($results);


    // Returns the render array, this render array contains the empty behaviour
    // if no results are found. If there are results we're going to initialize
    // the widget from the widget plugin manager and return it's build method.
    if (empty($facet->getResults())) {
      // Get the empty behavior id and the configuration.
      $facet_empty_behavior_configs = $facet->get('empty_behavior_configs');
      $behavior_id = $facet->get('empty_behavior');

      // Build the result using the empty behavior configuration.
      $empty_behavior_plugin = $this->empty_behavior_plugin_manager->createInstance($behavior_id);
      return $empty_behavior_plugin->build($facet_empty_behavior_configs);
    }

    // Let the widget plugin render the facet.
    /** @var \Drupal\facetapi\Widget\WidgetInterface $widget */
    $widget = $this->widget_plugin_manager->createInstance($facet->getWidget());

    return $widget->build($facet);
  }

  abstract public function updateResults();
}
