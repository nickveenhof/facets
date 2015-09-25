<?php

/**
 * @file
 * Contains \Drupal\facetapi\Entity\Facet.
 */

namespace Drupal\facetapi\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\facetapi\FacetInterface;

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
 *     "description",
 *     "options",
 *   },
 *   links = {
 *     "canonical" = "/admin/config/search/search-api/index/{search_api_index}/facets/{facet}",
 *     "add-form" = "/admin/config/search/search-api/index/{search_api_index}/facets/add-facet",
 *     "edit-form" = "/admin/config/search/search-api/index/{search_api_index}/facets/{facet}/edit",
 *     "delete-form" = "/admin/config/search/search-api/index/{search_api_index}/facets/{facet}/delete",
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
   * The searcher name.
   *
   * @var string
   */
  protected $searcher_name;

  /**
   * The plugin name of the url processor.
   *
   * @var string
   */
  protected $url_processor_name;

  /**
   * The ids of the configured contexts
   *
   * @var array
   */
  protected $context_ids = [];

  protected $active_values = array();

  /**
   * {@inheritdoc}
   */
  public function __construct(array $values, $entity_type) {
    parent::__construct($values, $entity_type);
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

  public function getQueryType() {
    return $this->query_type_name;
  }

  public function getSearcherName() {
    return $this->searcher_name;
  }

  public function getUrlProcessorName() {
    return $this->url_processor_name;
  }

  public function getName() {
    return $this->name;
  }

  public function getContextIds() {
    return $this->context_ids;
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $parameters = parent::urlRouteParameters($rel);
    $parameters['search_api_index'] = 'default_index';

    //$parameters['search_api_index'] = $this->search_api_index;
    return $parameters;
  }
}
