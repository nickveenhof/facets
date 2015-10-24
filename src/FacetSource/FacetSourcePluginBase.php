<?php

/**+
 * @file
 * Contains \Drupal\facetapi\FacetSource\FacetSourcePluginBase.
 */

namespace Drupal\facetapi\FacetSource;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a base class from which other facet sources may extend.
 *
 * Plugins extending this class need to define a plugin definition array through
 * annotation. The definition includes the following keys:
 * - id: The unique, system-wide identifier of the datasource.
 * - label: The human-readable name of the datasource, translated.
 * - description: A human-readable description for the datasource, translated.
 *
 * @see \Drupal\facetapi\Annotation\FacetApiFacetSource
 * @see \Drupal\facetapi\FacetSource\FacetSourcePluginManager
 * @see \Drupal\facetapi\FacetSource\FacetSourceInterface
 * @see plugin_api
 */
abstract class FacetSourcePluginBase extends PluginBase implements FacetSourceInterface, ContainerFactoryPluginInterface {

  /**
   * The plugin manager.
   */
  protected $query_type_plugin_manager;

  public function getAllowedQueryTypes() {
    return [];
  }

  public function getFields() {
    return [];
  }

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    $query_type_plugin_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->query_type_plugin_manager = $query_type_plugin_manager;
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

    return new static($configuration, $plugin_id, $plugin_definition, $query_type_plugin_manager);

  }

  /**
   * {@inheritdoc}
   */
  public function isRenderedInCurrentRequest() {
    return FALSE;
  }
}
