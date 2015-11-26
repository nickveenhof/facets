<?php

/**
 * @file
 * Contains Drupal\facetapi\FacetManager\DefaultFacetManager.
 */

namespace Drupal\facetapi\FacetManager;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\facetapi\EmptyBehavior\EmptyBehaviorPluginManager;
use Drupal\facetapi\Exception\InvalidProcessorException;
use Drupal\facetapi\FacetInterface;
use Drupal\facetapi\FacetSource\FacetSourcePluginManager;
use Drupal\facetapi\Processor\BuildProcessorInterface;
use Drupal\facetapi\Processor\PreQueryProcessorInterface;
use Drupal\facetapi\Processor\ProcessorInterface;
use Drupal\facetapi\Processor\ProcessorPluginManager;
use Drupal\facetapi\QueryType\QueryTypePluginManager;
use Drupal\facetapi\Widget\WidgetPluginManager;

/**
 * The facet manager.
 *
 * The manager is responsible for interactions with the Search backend, such as
 * altering the query, it is also responsible for executing and building the
 * facet. It is also responsible for running the processors.
 */
class DefaultFacetManager {

  use StringTranslationTrait;

  /**
   * The query type plugin manager.
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
   * An array of facets that are being rendered.
   *
   * @var \Drupal\facetapi\FacetInterface[]
   *
   * @see \Drupal\facetapi\FacetInterface
   * @see \Drupal\facetapi\Entity\Facet
   */
  protected $facets = [];

  /**
   * A boolean flagging whether the facets have been processed, or built.
   *
   * This variable acts as a semaphore that ensures facet data is processed
   * only once.
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
  protected $settings = [];

  /**
   * The id of the facet source.
   *
   * @var string
   * @see \Drupal\facetapi\FacetSource\FacetSourceInterface
   */
  protected $facetsource_id;

  /**
   * Set the search id.
   *
   * @param string
   *   The id of the facet source.
   */
  public function setFacetSourceId($facetsource_id) {
    $this->facetsource_id = $facetsource_id;
  }

  /**
   * Constructs a new instance of the DefaultFacetManager.
   *
   * @param \Drupal\facetapi\QueryType\QueryTypePluginManager $query_type_plugin_manager
   * @param \Drupal\facetapi\Widget\WidgetPluginManager $widget_plugin_manager
   * @param \Drupal\facetapi\FacetSource\FacetSourcePluginManager $facet_source_manager
   * @param \Drupal\facetapi\Processor\ProcessorPluginManager $processor_plugin_manager
   * @param \Drupal\facetapi\EmptyBehavior\EmptyBehaviorPluginManager $empty_behavior_plugin_manager
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   */
  public function __construct(QueryTypePluginManager $query_type_plugin_manager, WidgetPluginManager $widget_plugin_manager, FacetSourcePluginManager $facet_source_manager, ProcessorPluginManager $processor_plugin_manager, EmptyBehaviorPluginManager $empty_behavior_plugin_manager, EntityTypeManager $entity_type_manager) {

    $this->query_type_plugin_manager = $query_type_plugin_manager;
    $this->widget_plugin_manager = $widget_plugin_manager;
    $this->facet_source_manager = $facet_source_manager;
    $this->processor_plugin_manager = $processor_plugin_manager;
    $this->empty_behavior_plugin_manager = $empty_behavior_plugin_manager;
    $this->facet_storage = $entity_type_manager->getStorage('facetapi_facet');

    // Immediately initialize the facets. This can be done directly because the
    // only thing needed is the url.
    $this->initFacets();
  }

  /**
   * Allows the backend to add facet queries to its native query object.
   *
   * This method is called by the implementing module to initialize the facet
   * display process.
   *
   * @param mixed $query
   *   The backend's native query object.
   */
  public function alterQuery(&$query) {
    /** @var \Drupal\facetapi\FacetInterface[] $facets */
    foreach ($this->facets as $facet) {

      // Make sure we don't alter queries for facets with a different source.
      if ($facet->getFacetSourceId() == $this->facetsource_id) {
        /** @var \Drupal\facetapi\QueryType\QueryTypeInterface $query_type_plugin */
        $query_type_plugin = $this->query_type_plugin_manager->createInstance($facet->getQueryType(), ['query' => $query, 'facet' => $facet]);
        $query_type_plugin->execute();
      }
    }
  }

  /**
   * Returns enabled facets for the searcher associated with this FacetManager.
   *
   * @return \Drupal\facetapi\FacetInterface[]
   *   An array of enabled facets.
   */
  public function getEnabledFacets() {
    return $this->facet_storage->loadMultiple();
  }

  /**
   * Get the ID of the facet source.
   *
   * @return string
   */
  public function getFacetsourceId() {
    return $this->facetsource_id;
  }

  /**
   * Initializes facet builds, sets the breadcrumb trail.
   *
   * Facets are built via FacetapiFacetProcessor objects. Facets only need to be
   * processed, or built, once The FacetapiFacetManager::processed semaphore is
   * set when this method is called ensuring that facets are built only once
   * regardless of how many times this method is called.
   */
  public function processFacets() {
    if (!$this->processed) {
      // First add the results to the facets.
      $this->updateResults();

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

        foreach ($facet->getProcessors() as $processor) {
          $processor_definition = $processor->getPluginDefinition();
          if (is_array($processor_definition['stages']) && array_key_exists(ProcessorInterface::STAGE_PRE_QUERY, $processor_definition['stages'])) {
            /** @var PreQueryProcessorInterface $pre_query_processor */
            $pre_query_processor = $this->processor_plugin_manager->createInstance($processor->getPluginDefinition()['id']);
            if (!$pre_query_processor instanceof PreQueryProcessorInterface) {
              throw new InvalidProcessorException(new FormattableMarkup("The processor @processor has a pre_query definition but doesn't implement the required PreQueryProcessorInterface interface", ['@processor' => $processor_configuration['processor_id']]));
            }
            $pre_query_processor->preQuery($facet);
          }
        }
      }
    }
  }

  /**
   * Build a facet and returns it's render array.
   *
   * This method delegates to the relevant plugins to render a facet, it calls
   * out to a widget plugin to do the actual rendering when results are found.
   * When no results are found it calls out to the correct empty result plugin
   * to build a render array.
   *
   * Before doing any rendering, the processors that implement the
   * BuildProcessorInterface enabled on this facet will run.
   *
   * @param \Drupal\facetapi\FacetInterface $facet
   *
   * @return array
   *   Facet render arrays.
   */
  public function build(FacetInterface $facet) {
    // It might be that the facet received here, is not the same as the already
    // loaded facets in the FacetManager.
    // For that reason, get the facet from the already loaded facets in the
    // FacetManager.
    $facet = $this->facets[$facet->id()];

    // @TODO: inject the searcher id on create of the FacetManager.
    $this->facetsource_id = $facet->getFacetSourceId();

    if ($facet->getOnlyVisibleWhenFacetSourceIsVisible()) {
      // Block rendering and processing should be stopped when the facet source
      // is not available on the page. Returning an empty array here is enough
      // to halt all further processing.
      $facet_source = $facet->getFacetSource();
      if (!$facet_source->isRenderedInCurrentRequest()) {
        return [];
      }
    }

    // For clarity, process facets is called each build.
    // The first facet therefor will trigger the processing. Note that
    // processing is done only once, so repeatedly calling this method will not
    // trigger the processing more than once.
    $this->processFacets();

    // Get the current results from the facets and let all processors that
    // trigger on the build step do their build processing.
    // @see \Drupal\facetapi\Processor\BuildProcessorInterface.
    // @see \Drupal\facetapi\Processor\WidgetOrderProcessorInterface.
    $results = $facet->getResults();

    foreach ($facet->getProcessors() as $processor) {
      $processor_definition = $this->processor_plugin_manager->getDefinition($processor->getPluginDefinition()['id']);
      if (is_array($processor_definition['stages']) && array_key_exists(ProcessorInterface::STAGE_BUILD, $processor_definition['stages'])) {
        /** @var BuildProcessorInterface $build_processor */
        $build_processor = $this->processor_plugin_manager->createInstance($processor->getPluginDefinition()['id']);
        if (!$build_processor instanceof BuildProcessorInterface) {
          throw new InvalidProcessorException(new FormattableMarkup("The processor @processor has a build definition but doesn't implement the required BuildProcessorInterface interface", ['@processor' => $processor['processor_id']]));
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

  /**
   * Updates the facet with the results.
   */
  public function updateResults() {
    // Get an instance of the facet source.
    /** @var \drupal\facetapi\FacetSource\FacetSourceInterface $facet_source_plugin */
    $facet_source_plugin = $this->facet_source_manager->createInstance($this->facetsource_id);

    $facet_source_plugin->fillFacetsWithResults($this->facets);

    foreach ($this->facets as $facet) {
      $facet->setPath($facet_source_plugin->getPath());
    }
  }

}
