<?php

/**
 * @file
 * Contains \Drupal\facets\FacetSource\FacetSourcePluginBase.
 */

namespace Drupal\facets\FacetSource;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Facets\FacetInterface;
use Drupal\Core\Form\FormStateInterface;


/**
 * Defines a base class from which other facet sources may extend.
 *
 * Plugins extending this class need to define a plugin definition array through
 * annotation. The definition includes the following keys:
 * - id: The unique, system-wide identifier of the datasource.
 * - label: The human-readable name of the datasource, translated.
 * - description: A human-readable description for the datasource, translated.
 *
 * @see \Drupal\facets\Annotation\FacetsFacetSource
 * @see \Drupal\facets\FacetSource\FacetSourcePluginManager
 * @see \Drupal\facets\FacetSource\FacetSourcePluginInterface
 * @see plugin_api
 */
abstract class FacetSourcePluginBase extends PluginBase implements FacetSourcePluginInterface, ContainerFactoryPluginInterface {

  /**
   * The plugin manager.
   *
   * @var \Drupal\facets\QueryType\QueryTypePluginManager
   */
  protected $queryTypePluginManager;

  /**
   * The search keys, or query text, submitted by the user.
   *
   * @var string
   */
  protected $keys;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    $query_type_plugin_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->queryTypePluginManager = $query_type_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    // Insert the plugin manager for query types.
    /** @var \Drupal\facets\QueryType\QueryTypePluginManager $query_type_plugin_manager */
    $query_type_plugin_manager = $container->get('plugin.manager.facets.query_type');

    return new static($configuration, $plugin_id, $plugin_definition, $query_type_plugin_manager);
  }

  /**
   * {@inheritdoc}
   */
  public function getFields() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getQueryTypesForFacet(FacetInterface $facet) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function isRenderedInCurrentRequest() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function setSearchKeys($keys) {
    $this->keys = $keys;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSearchKeys() {
    return $this->keys;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(FormStateInterface $form_state, FacetInterface &$facet) {
    $facet_source_id = $facet->getFacetSourceId();
    $field_identifier = $form_state->getValue('facet_source_configs')[$facet_source_id]['field_identifier'];
    $facet->setFieldIdentifier($field_identifier);
  }

}
