<?php

/**
 * @file
 * Contains Drupal\facetapi\Plugin\Block\FacetBlock.
 *
 * NOTE: There should be a facetblock or settings for the facets later.
 */

namespace Drupal\facetapi\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'FacetBlock' block.
 *
 * @Block(
 *  id = "facet_block",
 *  admin_label = @Translation("Facet block")
 * )
 */
class FacetBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\facetapi\Adapter definition.
   *
   * @var Drupal\facetapi\Adapter\AdapterInterface
   */
  protected $facetapi_adapter;

  /**
   * The adapter plugin manager.
   *
   * @var Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $plugin_manager;

  /**
   * Construct.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param Drupal\Component\Plugin\PluginManagerInterface pluginManager
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, $plugin_manager) {
    $this->plugin_manager = $plugin_manager;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $plugin_manager = $container->get('plugin.manager.facetapi.adapter');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $plugin_manager
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the adapter.
    // For now hard code the id.
    // This should be based on facet definitions.
    // The plugin manager should be injected.
    $plugin_id = 'search_api';
    $adapter = $this->plugin_manager->createInstance($plugin_id);
    $build = $adapter->build();

    // Let the adapter build the facets.
//    $adapter->

    return $build;
  }

}
