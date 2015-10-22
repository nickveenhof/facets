<?php

/**+
 * @file
 * Contains \Drupal\facetapi\Widget\WidgetPluginBase.
 */

namespace Drupal\facetapi\Widget;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


abstract class WidgetPluginBase extends PluginBase implements WidgetInterface, ContainerFactoryPluginInterface {

  /**
   * The empty behavior manager service.
   *
   * @var \Drupal\facetapi\EmptyBehavior\EmptyBehaviorPluginManager
   */
  protected $empty_behavior_plugin_manager;

  /**
   * Constructs a WidgetPluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\facetapi\EmptyBehavior\EmptyBehaviorPluginManager $empty_behavior_plugin_manager
   *   The empty behavior manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, $empty_behavior_plugin_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->empty_behavior_plugin_manager = $empty_behavior_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    // Insert the plugin manager for empty behaviors.
    /** @var \Drupal\facetapi\EmptyBehavior\EmptyBehaviorPluginManager $empty_behavior_plugin_manager */
    $empty_behavior_plugin_manager = $container->get('plugin.manager.facetapi.empty_behavior');

    return new static($configuration, $plugin_id, $plugin_definition, $empty_behavior_plugin_manager);

  }

}
