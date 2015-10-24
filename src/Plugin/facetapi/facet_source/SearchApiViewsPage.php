<?php

/**
 * @file
 *   Contains \Drupal\facetapi\Plugin\facet_api\facet_source\SearchApiViewsPage
 */

namespace Drupal\facetapi\Plugin\facetapi\facet_source;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\facetapi\FacetInterface;
use Drupal\facetapi\FacetSource\FacetSourceInterface;
use Drupal\facetapi\FacetSource\FacetSourcePluginBase;
use Drupal\search_api\Plugin\views\query\SearchApiQuery;
use Drupal\search_api\Query\ResultSetInterface;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Represents a facet source which represents the search api views.
 *
 * @FacetApiFacetSource(
 *   id = "search_api_views",
 *   deriver = "Drupal\facetapi\Plugin\facetapi\facet_source\SearchApiViewsPageDeriver"
 * )
 */
class SearchApiViewsPage extends FacetSourcePluginBase {

  use StringTranslationTrait;

  use DependencySerializationTrait;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface|null
   */
  protected $entityManager;

  /**
   * The typed data manager.
   *
   * @var \Drupal\Core\TypedData\TypedDataManager|null
   */
  protected $typedDataManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|null
   */
  protected $configFactory;

  /**
   * The search result cache.
   *
   * @var \Drupal\search_api\Query\ResultsCacheInterface
   */
  protected $searchResultsCache;

  /**
   * The search index.
   *
   * @var \Drupal\search_api\IndexInterface
   */
  protected $index;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, $query_type_plugin_manager, $search_results_cache) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $query_type_plugin_manager);
    // Since defaultConfiguration() depends on the plugin definition, we need to
    // override the constructor and set the definition property before calling
    // that method.
    $this->pluginDefinition = $plugin_definition;
    $this->pluginId = $plugin_id;
    $this->configuration = $configuration + $this->defaultConfiguration();
    $this->searchResultsCache = $search_results_cache;
  }

  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    // Insert the plugin manager for query types.
    /** @var \Drupal\facetapi\QueryType\QueryTypePluginManager $query_type_plugin_manager */
    $query_type_plugin_manager = $container->get('plugin.manager.facetapi.query_type');

    // Get the ResultsCache from the container.
    /** @var \Drupal\search_api\Query\ResultsCacheInterface $results_cache */
    $search_results_cache = $container->get('search_api.results_static_cache');
    return new static($configuration, $plugin_id, $plugin_definition, $query_type_plugin_manager, $search_results_cache);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet, FacetSourceInterface $facet_source) {

    // Load facet plugin definition and depending on those settings; load the
    // corresponding view with the correct view with the correct display set.
    // Get that display's query so we can check if this is a search API based
    // view.
    $plugin_def = $facet_source->getPluginDefinition();
    $view = Views::getView($plugin_def['view_id']);
    $view->setDisplay($plugin_def['view_display']);
    $query = $view->getQuery();

    // Early return when the view is not based on a search API query.
    if (!$query instanceof SearchApiQuery) {
      return [];
    }

    // Set the Search Api Index
    $this->index = $query->getIndex();

    $form['field_identifier'] = [
      '#type' => 'select',
      '#options' => $this->getFields(),
      '#title' => $this->t('Facet field'),
      '#description' => $this->t('Choose the indexed field.'),
      '#required' => TRUE,
      '#default_value' => $facet->getFieldIdentifier()
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getQueryTypes() {
    return $this->query_type_plugin_manager->getDefinitions();
  }

  /**
   * {@inheritdoc}
   */
  public function getFields() {
    $indexed_fields = [];
    foreach ($this->index->getDatasources() as $datasource_id => $datasource) {
      $fields = $this->index->getFieldsByDatasource($datasource_id);
      foreach ($fields as $field) {
        $indexed_fields[$field->getFieldIdentifier()] = $field->getLabel();
      }
    }
    return $indexed_fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getQueryTypesForFacet(FacetInterface $facet) {
    // @todo call the mappings hook here. All possible modules are allowed
    // to alter this.
    // 1. Get data_type from the facet
    // 2. Call a hook to find out the query_type plugin for this data type.
    // 3. Search API will need to implement this hook and make the data type
    //    and query_type mapping accordingly. Different backends can implement
    //    this mapping. We will call all mappings and give that back to the
    //    facet. depending on the backend, it will call the appropriate query
    //    type.
    return [];
  }

  public function addResults($facets) {
    // Get the facet values from the query that has been done.
    // Store all information in $this->facets.
    $results = $this->searchResultsCache->getResults($this->pluginId);

    if (! $results instanceof ResultSetInterface) {
      // If there are no results, execute the view. and check for results again!
      $view = Views::getView($this->pluginDefinition['view_id']);
      $view->setDisplay($this->pluginDefinition['view_display']);
      $view->execute();
      // Set the path of all facets.
      $path = $view->getDisplay()->getOption('path');
      if ($path) {
        foreach ($facets as $facet) {
          $facet->setPath($path);
        }

      }
      $results = $this->searchResultsCache->getResults($this->pluginId);
    }


    if ($results instanceof ResultSetInterface) {
      $facet_results = $results->getExtraData('search_api_facets');

      foreach ($facets as $facet) {
        $configuration = array(
          'query' => NULL,
          'facet' => $facet,
          'results' => $facet_results[$facet->getFieldIdentifier()],
        );
        // allow multiple query types here
        // $query_types = $this->getQueryTypesForField($facet->getFieldIdentifier())
        // send multiple query types to the widget.
        $query_type_plugin = $this->query_type_plugin_manager->createInstance($facet->getQueryType(),
          $configuration
        );
        // @TODO: This should be done somewhere else.
        $query_type_plugin->build();
      }
    }
    else {
      // @Todo: perform the query so there are results.
    }

  }

  /**
   * {@inheritdoc}
   */
  public function isRenderedInCurrentRequest() {
    $request = \Drupal::requestStack()->getMasterRequest();
    if ($request->attributes->get('_controller') === 'Drupal\views\Routing\ViewPageController::handle') {
      list(, $search_api_view_id, $search_api_view_display) = explode(':', $this->getPluginId());

      if ($request->attributes->get('view_id') != $search_api_view_id || $request->attributes->get('display_id') != $search_api_view_display) {
        return FALSE;
      }
    }
    return TRUE;
  }
}
