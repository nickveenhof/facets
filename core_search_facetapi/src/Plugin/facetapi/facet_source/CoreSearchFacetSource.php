<?php

/**
 * @file
 *   Contains \Drupal\core_search_facetapi\Plugin\facet_api\facet_source\CoreSearchFacetSource
 */

namespace Drupal\core_search_facetapi\Plugin\facetapi\facet_source;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\facetapi\Exception\InvalidQueryTypeException;
use Drupal\facetapi\FacetInterface;
use Drupal\facetapi\FacetSource\FacetSourceInterface;
use Drupal\facetapi\FacetSource\FacetSourcePluginBase;
use Drupal\search\Entity\SearchPage;
use Drupal\search\SearchPageInterface;
use Drupal\search_api\Plugin\views\query\SearchApiQuery;
use Drupal\search_api\Query\ResultSetInterface;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Represents a facet source which represents the search api views.
 *
 * @FacetApiFacetSource(
 *   id = "core_search",
 *   deriver = "Drupal\core_search_facetapi\Plugin\facetapi\facet_source\CoreSearchFacetSourceDeriver"
 * )
 */
class CoreSearchFacetSource extends FacetSourcePluginBase {

  use DependencySerializationTrait;
  use StringTranslationTrait;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager|null
   */
  protected $entityTypeManager;

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

  protected $searchManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, $query_type_plugin_manager, $search_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $query_type_plugin_manager);
    $this->searchManager = $search_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.facetapi.query_type'),
      $container->get('plugin.manager.search')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function getPath() {
    /*$view = Views::getView($this->pluginDefinition['view_id']);
    $view->setDisplay($this->pluginDefinition['view_display']);
    $view->execute();

    return $view->getDisplay()->getOption('path');*/
    return;
  }

  /**
   * {@inheritdoc}
   */
  public function fillFacetsWithResults($facets) {
    foreach ($facets as $facet) {
      $configuration = array(
        'query' => NULL,
        'facet' => $facet,
      );

      // Get the Facet Specific Query Type so we can process the results
      // using the build() function of the query type.
      $query_type = $this->queryTypePluginManager->createInstance($facet->getQueryType(), $configuration);
      $query_type->build();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getQueryTypesForFacet(FacetInterface $facet) {
    // Get our FacetApi Field Identifier.
    $field_id = $facet->getFieldIdentifier();

    return $this->getQueryTypesForDataType($field_id);
  }

  /**
   * @param $field_id
   * @return array
   */
  public function getQueryTypesForDataType($field_id) {
    $query_types = [];
    switch ($field_id) {
      case 'bundle':
        $query_types['string'] = 'core_search_string';
        break;
    }

    return $query_types;
  }

  /**
   * {@inheritdoc}
   */
  public function isRenderedInCurrentRequest() {
    // @TODO Avoid the use of \Duupal so maybe inject?
    $request = \Drupal::requestStack()->getMasterRequest();
    $search_page = $request->attributes->get('entity');
    if ($search_page instanceof SearchPageInterface) {
      $facet_source_id = 'core_search:' . $search_page->id();
      if ($facet_source_id == $this->getPluginId()) {
        return TRUE;
      }
    }

    return FALSE;

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
      '#default_value' => $facet->getFieldIdentifier(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFields() {
    // Default options. @TODO is this best way?
    // see facetapi_facetapi_facet_info() - D7.
    $default_fields = [
      'bundle' => $this->t('Content Type'),
    ];

    return $default_fields;
  }

}
