<?php

/**
 * Contains \Drupal\facetapi\EmptyBehavior\EmptyBehaviorPluginBase
 */

namespace Drupal\facetapi\EmptyBehavior;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\Plugin\PluginBase;


abstract class EmptyBehaviorPluginBase extends PluginBase implements EmptyBehaviorInterface, ConfigurablePluginInterface {

  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

}