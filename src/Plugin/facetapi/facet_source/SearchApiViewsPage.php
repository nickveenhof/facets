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
use Drupal\facetapi\Exception;
use Drupal\search_api\FacetApiQueryTypeMappingInterface;
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
  protected $searchApiResultsCache;

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
    $this->searchApiResultsCache = $search_results_cache;

    // Load facet plugin definition and depending on those settings; load the
    // corresponding view with the correct view with the correct display set.
    // Get that display's query so we can check if this is a search API based
    // view.
    $view = Views::getView($plugin_definition['view_id']);
    if (!empty($view)) {
      $view->setDisplay($plugin_definition['view_display']);
      $query = $view->getQuery();

      // Only add the index if the $query is a Search API Query.
      if ($query instanceof SearchApiQuery) {
        // Set the Search Api Index
        $this->index = $query->getIndex();
      }
    }
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
  public function fillFacetsWithResults($facets) {
    // Check if there are results in the static cache.
    $results = $this->searchApiResultsCache->getResults($this->pluginId);

    // If our results are not there, execute the view to get the results.
    if (!$results) {
      // If there are no results, execute the view. and check for results again!
      $view = Views::getView($this->pluginDefinition['view_id']);
      $view->setDisplay($this->pluginDefinition['view_display']);
      $view->execute();
      // Set the path of all facets.
      // @todo Does that need to happen here?
      $path = $view->getDisplay()->getOption('path');
      if ($path) {
        foreach ($facets as $facet) {
          $facet->setPath($path);
        }
      }
      $results = $this->searchApiResultsCache->getResults($this->pluginId);
    }

    // Get the results from the cache. It is possible it still errored out.
    // @todo figure out what to do when this errors out.
    if ($results instanceof ResultSetInterface) {
      // Get our facet data.
      $facet_results = $results->getExtraData('search_api_facets');

      // Loop over each facet and execute the build method from the given
      // query type
      foreach ($facets as $facet) {
        $configuration = array(
          'query' => NULL,
          'facet' => $facet,
          'results' => $facet_results[$facet->getFieldIdentifier()],
        );

        // Get the Facet Specific Query Type so we can process the results
        // using the build() function of the query type.
        $query_type = $this->queryTypePluginManager->createInstance($facet->getQueryType(), $configuration);
        $query_type->build();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getFields() {
    $indexed_fields = [];
    $fields = $this->index->getFields(true);
    foreach ($fields as $field) {
      $indexed_fields[$field->getFieldIdentifier()] = $field->getLabel();
    }
    return $indexed_fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getQueryTypesForFacet(FacetInterface $facet) {
    // Get our FacetApi Field Identifier, which is equal to the Search API Field
    // identifier.
    $field_id = $facet->getFieldIdentifier();
    // Get the Search API Server.
    $server = $this->index->getServer();
    // Get the Search API Backend.
    $backend = $server->getBackend();
    $query_types = [];
    if ($backend instanceof FacetApiQueryTypeMappingInterface) {
      $fields = $this->index->getFields(true);
      foreach ($fields as $field) {
        if ($field->getFieldIdentifier() == $field_id) {
          return $backend->getQueryTypesForDataType($field->getType());
        }
      }
    }
    throw new Exception($this->t("No available query types were found for facet @facet", ['@facet' => $facet->getName()]));
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
