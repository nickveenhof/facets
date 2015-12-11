<?php

/**
 * @file
 * Contains \Drupal\core_search_facets\Plugin\facets\facet_source\CoreNodeSearchFacetSource.
 */

namespace Drupal\core_search_facets\Plugin\facets\facet_source;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\core_search_facets\Plugin\CoreSearchFacetSourceInterface;
use Drupal\facets\FacetInterface;
use Drupal\facets\FacetSource\FacetSourceInterface;
use Drupal\facets\FacetSource\FacetSourcePluginBase;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\search\SearchPageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Represents a facet source which represents the search api views.
 *
 * @FacetsFacetSource(
 *   id = "core_node_search",
 *   deriver = "Drupal\core_search_facets\Plugin\facets\facet_source\CoreNodeSearchFacetSourceDeriver"
 * )
 */
class CoreNodeSearchFacetSource extends FacetSourcePluginBase implements CoreSearchFacetSourceInterface {

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
   * The facet query being executed.
   */
  protected $facetQueryExtender;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, $query_type_plugin_manager, $search_manager, RequestStack $request_stack) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $query_type_plugin_manager);
    $this->searchManager = $search_manager;
    $this->setSearchKeys($request_stack->getMasterRequest()->query->get('keys'));
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Symfony\Component\HttpFoundation\RequestStack $request_stack */
    $request_stack = $container->get('request_stack');

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.facets.query_type'),
      $container->get('plugin.manager.search'),
      $request_stack
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
    return '';
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
      /** @var \Drupal\facets\Entity\Facet $facet **/
      $query_type = $this->queryTypePluginManager->createInstance($facet->getQueryType(), $configuration);
      $query_type->build();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getQueryTypesForFacet(FacetInterface $facet) {
    // Get our Facets Field Identifier.
    $field_id = $facet->getFieldIdentifier();

    // @TODO missing the field type here to understand what query type I should
    // use from getQueryTypesForDataType or maybe add the query type name
    // directly when creating the facet Config Entity.

    return $this->getQueryTypesForDataType($field_id);
  }

  /**
   * Get the query types for a data type.
   *
   * @param string $field_id
   *   The field id.
   *
   * @return array
   *   An array of query types.
   */
  public function getQueryTypesForDataType($field_id) {
    $query_types = [];
    switch ($field_id) {
      case 'type':
      case 'uid':
      case 'langcode':
        $query_types['string'] = 'core_node_search_string';
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
      $facet_source_id = 'core_node_search:' . $search_page->id();
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
    $default_fields = [
      'type' => $this->t('Content Type'),
      'uid' => $this->t('Author'),
      'langcode' => $this->t('Language'),
    ];
    // @TODO Only taxonomy terms for the moment.
    // Get the current field instances and detect if the field type is allowed.
    $fields = FieldConfig::loadMultiple();
    foreach ($fields as $field) {
      if ($field->getFieldStorageDefinition()->getSetting('target_type') == 'taxonomy_term') {
        /** @var \Drupal\field\Entity\FieldConfig $field */
        $default_fields[$field->getName()] = $this->t('@label', ['@label' => $field->getLabel()]);
      }
    }

    return $default_fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getFacetQueryExtender() {
    // If (!$this->facetQueryExtender) {
    // $this->facetQueryExtender = db_select('search_index',
    // 'i',
    // array('target' => 'replica'))
    // ->extend('Drupal\search\ViewsSearchQuery');
    // $this->searchQuery->searchExpression($input, $this->searchType);
    // $this->searchQuery->publicParseSearchExpression();
    $this->facetQueryExtender = db_select('search_index', 'i', array('target' => 'replica'))->extend('Drupal\core_search_facets\FacetsQuery');
    $this->facetQueryExtender->join('node_field_data', 'n', 'n.nid = i.sid');
    $this->facetQueryExtender
      // ->condition('n.status', 1).
      ->addTag('node_access')
      ->searchExpression($this->keys, 'node_search');
    // }.
    return $this->facetQueryExtender;
  }

  /**
   * {@inheritdoc}
   */
  public function getQueryInfo(FacetInterface $facet) {
    // If (!$facet['field api name']) {
    // We add the language code of the indexed item to the result of the query.
    // So in this case we need to use the search_index table alias (i) for the
    // langcode field. Otherwise we will have same nid for multiple languages
    // as result. For more details see NodeSearch::findResults().
    $table_alias = $facet->getFieldIdentifier() == 'langcode' ? 'i' : 'n';
    $query_info = [
      'fields' => [
        $table_alias . '.' . $facet->getFieldIdentifier() => [
          'table_alias' => $table_alias,
          'field' => $facet->getFieldIdentifier(),
        ],
      ],
    ];
    // }
    /*else {
    $query_info = array();

    // Gets field info, finds table name and field name.
    $field = field_info_field($facet['field api name']);
    $table = _field_sql_storage_tablename($field);

    // Iterates over columns, adds fields to query info.
    foreach ($field['columns'] as $column_name => $attributes) {
    $column = _field_sql_storage_columnname($field['field_name'], $column_name);
    $query_info['fields'][$table . '.' . $column] = array(
    'table_alias' => $table,
    'field' => $column,
    );
    }

    // Adds the join on the node table.
    $query_info['joins'] = array(
    $table => array(
    'table' => $table,
    'alias' => $table,
    'condition' => "n.vid = $table.revision_id",
    ),
    );
    }*/

    // Returns query info, makes sure all keys are present.
    return $query_info + [
      'joins' => [],
      'fields' => [],
    ];
  }

  /**
   * Checks if the search has facets.
   *
   * @TODO move to the Base class???
   */
  public function hasFacets() {
    $manager = \Drupal::service('entity_type.manager')->getStorage('facets_facet');
    $facets = $manager->loadMultiple();
    foreach ($facets as $facet) {
      if ($facet->getFacetSourceId() == $this->getPluginId()) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
