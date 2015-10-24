<?php

/**
 * @file
 * Contains \Drupal\facetapi\Entity\Facet.
 */

namespace Drupal\facetapi\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\facetapi\FacetInterface;
use Drupal\facetapi\Result\Result;
use Drupal\facetapi\Result\ResultInterface;

/**
 * Defines the search index configuration entity.
 *
 * @ConfigEntityType(
 *   id = "facetapi_facet",
 *   label = @Translation("Facet"),
 *   handlers = {
 *     "storage" = "Drupal\Core\Config\Entity\ConfigEntityStorage",
 *     "list_builder" = "Drupal\facetapi\FacetListBuilder",
 *     "form" = {
 *       "default" = "Drupal\facetapi\Form\FacetForm",
 *       "edit" = "Drupal\facetapi\Form\FacetForm",
 *       "delete" = "Drupal\facetapi\Form\FacetDeleteConfirmForm",
 *     },
 *   },
 *   admin_permission = "administer facetapi",
 *   config_prefix = "facet",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "status" = "status"
 *   },
 *   config_export = {
 *     "id",
 *     "name",
 *     "field_identifier",
 *     "query_type_name",
 *     "facet_source_id",
 *     "widget",
 *     "widget_configs",
 *     "processor_configs",
 *     "empty_behavior",
 *     "empty_behavior_configs",
 *     "only_visible_when_facet_source_is_visible",
 *   },
 *   links = {
 *     "canonical" = "/admin/config/search/facet-api",
 *     "add-form" = "/admin/config/search/facet-api/add-facet",
 *     "edit-form" = "/admin/config/search/facet-api/{facetapi_facet}/edit",
 *     "delete-form" = "/admin/config/search/facet-api/{facetapi_facet}/delete",
 *   }
 * )
 */
class Facet extends ConfigEntityBase implements FacetInterface {

  /**
   * The ID of the index.
   *
   * @var string
   */
  protected $id;

  /**
   * A name to be displayed for the index.
   *
   * @var string
   */
  protected $name;

  /**
   * A string describing the index.
   *
   * @var string
   */
  protected $description;

  /**
   * A string describing the widget.
   *
   * @var string
   */
  protected $widget;

  /**
   * Configuration for the widget. This is a key-value stored array.
   *
   * @var string
   */
  protected $widget_configs;

  /**
   * Configuration for the empty behavior.
   *
   * @var string
   */
  protected $empty_behavior;

  /**
   * An array of options configuring this index.
   *
   * @var array
   *
   * @see getOptions()
   */
  protected $options = array();

  /**
   * The field identifier.
   *
   * @var string
   */
  protected $field_identifier;

  /**
   * The query type name.
   *
   * @var string
   */
  protected $query_type_name;

  /**
   * The plugin name of the url processor.
   *
   * @var string
   */
  protected $url_processor_name;

  /**
   * The id of the facet source.
   *
   * @var string
   */
  protected $facet_source_id;

  /**
   * The path all the links should point to.
   *
   * @var string
   */
  protected $path;

  /**
   * The results.
   *
   * @var Result[]
   */
  protected $results = [];

  protected $active_values = [];

  /**
   * An array containing the facet source plugins.
   *
   * @var array
   */
  protected $facetSourcePlugins;

  /**
   * An array containing all processors and their configuration.
   *
   * @var array
   */
  protected $processor_configs;


  /**
   * A boolean that defines whether or not the facet is only visible when the
   * facet source is visible.
   *
   * @var boolean
   */
  protected $only_visible_when_facet_source_is_visible;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $values, $entity_type) {
    parent::__construct($values, $entity_type);
    // @TODO Added only for test.
    $this->query_type_name = 'search_api_term';
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->id;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function setWidget($widget) {
    $this->widget = $widget;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getWidget() {
    return $this->widget;
  }

  /**
   * Get the field alias used to identify the facet in the url.
   *
   * @return mixed
   */
  public function getFieldAlias() {
    // For now, create the field alias based on the field identifier.
    $field_alias = preg_replace('/[:\/]+/', '_', $this->field_identifier);
    return $field_alias;
  }

  /**
   * Sets an item with value to active.
   *
   * @param $value
   */
  public function setActiveItem($value) {
    if (!in_array($value, $this->active_values)) {
      $this->active_values[] = $value;
    }
  }

  /**
   * Get all the active items in the facet.
   *
   * @return mixed
   */
  public function getActiveItems() {
    return $this->active_values;
  }

  /**
   * {@inheritdoc}
   */
  public function getOption($name, $default = NULL) {
    return isset($this->options[$name]) ? $this->options[$name] : $default;
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    return $this->options;
  }

  /**
   * {@inheritdoc}
   */
  public function setOption($name, $option) {
    $this->options[$name] = $option;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOptions(array $options) {
    $this->options = $options;
    return $this;
  }

  public function getFieldIdentifier() {
    return $this->field_identifier;
  }

  public function setFieldIdentifier($field_identifier) {
    $this->field_identifier = $field_identifier;
    return $this;
  }

  public function getQueryType() {
    return $this->query_type_name;
  }

  public function setFieldEmptyBehavior($behavior_id) {
    $this->empty_behavior = $behavior_id;
    return $this;
  }

  public function getFieldEmptyBehavior() {
    return $this->empty_behavior;
  }

  public function getUrlProcessorName() {
    // @Todo: for now if the url processor is not set, defualt to query_string.
    return isset($this->url_processor_name) ? $this->url_processor_name : 'query_string';
  }

  public function getName() {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function setFacetSourceId($facet_source_id) {
    $this->facet_source_id = $facet_source_id;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getFacetSource() {

    /** @var $facet_source_plugin_manager \Drupal\facetapi\FacetSource\FacetSourcePluginManager */
    $facet_source_plugin_manager = \Drupal::service('plugin.manager.facetapi.facet_source');

    return $facet_source_plugin_manager->createInstance($this->facet_source_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getFacetSourceId() {
    return $this->facet_source_id;
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $parameters = parent::urlRouteParameters($rel);
    return $parameters;
  }

  public function getResults() {
    return $this->results;
  }

  /**
   * Set an array of Result objects.
   *
   * @param array $results
   *   Array containing \Drupal\facetapi\Result\Result objects.
   */
  public function setResults(array $results) {
    $this->results = $results;
    // If there are active values,
    // set the results which are active to active.
    if (count($this->active_values)) {
      foreach ($this->results as $result) {
        if (in_array($result->getRawValue(), $this->active_values)) {
          $result->setActiveState(TRUE);
        }
      }
    }
  }

  /**
   * Until facet api supports more than just search api, this is enough.
   *
   * @return string
   */
  public function getManagerPluginId() {
    return 'facetapi_default';
  }

  /**
   * @inheritdoc
   */
  public function isActiveValue($value) {
    $is_active = FALSE;
    if (in_array($value, $this->active_values)) {
      $is_active = TRUE;
    }
    return $is_active;
  }

  /**
   * {@inheritdoc}
   */
  public function getFacetSources($only_enabled = false) {
    if (!isset($this->facetSourcePlugins)) {
      $this->facetSourcePlugins = [];

      /** @var $facet_source_plugin_manager \Drupal\facetapi\FacetSource\FacetSourcePluginManager */
      $facet_source_plugin_manager = \Drupal::service('plugin.manager.facetapi.facet_source');

      foreach ($facet_source_plugin_manager->getDefinitions() as $name => $facet_source_definition) {
        if (class_exists($facet_source_definition['class']) && empty($this->facetSourcePlugins[$name])) {
          // Create our settings for this facet source..
          $config = isset($this->facetSourcePlugins[$name]) ? $this->facetSourcePlugins[$name] : [];

          /** @var $facet_source \Drupal\facetapi\FacetSource\FacetSourceInterface */
          $facet_source = $facet_source_plugin_manager->createInstance($name, $config);
          $this->facetSourcePlugins[$name] = $facet_source;
        }
        elseif (!class_exists($facet_source_definition['class'])) {
          \Drupal::logger('facetapi')->warning('Facet Source @id specifies a non-existing @class.', ['@id' => $name, '@class' => $facet_source_definition['class']]);
        }
      }
    }

    // Filter datasources by status if required.
    if (!$only_enabled) {
      return $this->facetSourcePlugins;
    }

    return array_intersect_key($this->facetSourcePlugins, array_flip($this->facetSourcePlugins));
  }

  public function setPath($path) {
    $this->path = $path;
  }

  public function getPath() {
    return $this->path;
  }

  /**
   * {@inheritdoc}
   */
  public function getProcessorConfigs() {
    return $this->processor_configs;
  }
  /**
   * {@inheritdoc}
   */
  public function setProcessorConfigs($processor_config = []) {
    $this->processor_configs = $processor_config;
  }

  /**
   * {@inheritdoc}
   */
  public function setOnlyVisibleWhenFacetSourceIsVisible($only_visible_when_facet_source_is_visible) {
    $this->only_visible_when_facet_source_is_visible = $only_visible_when_facet_source_is_visible;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOnlyVisibleWhenFacetSourceIsVisible() {
    return $this->only_visible_when_facet_source_is_visible;
  }

}
