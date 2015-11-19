<?php

/**
 * @file
 * Contains \Drupal\facetapi\Controller\FacetSourceConfigController.
 */

namespace Drupal\facetapi\Controller;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Controller\ControllerBase;

/**
 * Provides route responses for facet source configuration.
 */
class FacetSourceConfigController extends ControllerBase {

  /**
   * Configuration for the facet source.
   *
   * @param string $source_id
   *   The plugin id.
   *
   * @return array
   *   A renderable array containing the form
   */
  public function facetSourceConfigForm($source_id) {
    /** @var \Drupal\facetapi\FacetSource\FacetSourcePluginManager $facet_source_plugin_manager */
    $facet_source_plugin_manager = \Drupal::service('plugin.manager.facetapi.facet_source');

    try {
      $facet_source_plugin_manager->createInstance($source_id);
    } catch (PluginNotFoundException $e) {
      // Return a renderable array with a short lifetime that represents the plugin not found.
      return ['#markup' => 'Plugin not found.', '#cache' => ['max-age' => 5]];
    }

    // Returns the render array of the FacetSourceConfigForm.
    return $this->formBuilder()->getForm('\Drupal\facetapi\Form\FacetSourceConfigForm');
  }

}
