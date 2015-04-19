<?php
/**
 * @file Contains Drupal\facetapi\Adapter\AdapterPluginManagerInterface
 */

namespace Drupal\facetapi\Adapter;

use Drupal\Component\Plugin\PluginManagerInterface;

interface AdapterPluginManagerInterface extends PluginManagerInterface {

  /**
   * Get an instance based on search id.
   *
   * @TODO: Rename to getInstance when http://drupal.org/node/1894130 is fixed.
   *
   * @param string $plugin_id
   * @param string $search_id
   *
   * @return mixed
   */
  public function getMyOwnChangeLaterInstance($plugin_id, $search_id);
}