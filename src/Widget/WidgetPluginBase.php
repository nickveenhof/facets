<?php

/**+
 * @file
 * Contains \Drupal\facetapi\Widget\WidgetPluginBase.
 */

namespace Drupal\facetapi\Widget;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\facetapi\Widget\WidgetInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


abstract class WidgetPluginBase extends PluginBase implements WidgetInterface, ContainerFactoryPluginInterface {

  protected $empty_behavior_plugin_manager;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, $empty_behavior_plugin_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->empty_behavior_plugin_manager = $empty_behavior_plugin_manager;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    // Insert the plugin manager for empty behaviors.
    /** @var \Drupal\facetapi\EmptyBehavior\EmptyBehaviorPluginManager $empty_behavior_plugin_manager */
    $empty_behavior_plugin_manager = $container->get('plugin.manager.facetapi.empty_behavior');

    return new static($configuration, $plugin_id, $plugin_definition, $empty_behavior_plugin_manager);

  }

}
