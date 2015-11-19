<?php

/**
 * @file
 * Contains \Drupal\core_search_facetapi\Plugin\facet_api\facet_source\CoreNodeSearchFacetSourceDeriver.
 */

namespace Drupal\core_search_facetapi\Plugin\facetapi\facet_source;

use Drupal\Component\Plugin\PluginBase;
use Drupal\facetapi\FacetSource\FacetSourceDeriverBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Derives a facet source plugin definition for every search api view.
 *
 * @see \Drupal\facetapi\Plugin\facetapi\facet_source\SearchApiViewsPage
 */
class CoreNodeSearchFacetSourceDeriver extends FacetSourceDeriverBase {

  protected $searchManager;

  public function __construct(ContainerInterface $container, $base_plugin_id, $search_manager) {
    $this->searchManager = $search_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container,
      $base_plugin_id,
      $container->get('plugin.manager.search')
      );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $base_plugin_id = $base_plugin_definition['id'];

    if (!isset($this->derivatives[$base_plugin_id])) {
      $plugin_derivatives = [];
      // @TODO inject entity plugin manager.
      $pages = \Drupal::entityManager()->getListBuilder('search_page')->load();

      foreach($pages as $machine_name => $page) {
        /** @var \Drupal\search\Entity\SearchPage $page * */
        if ($page->get('plugin') == 'node_search') {
          // Detect if the plugin has "faceted" definition.
          $plugin_derivatives[$machine_name] = [
              'id' => $base_plugin_id . PluginBase::DERIVATIVE_SEPARATOR . $machine_name,
              'label' => $this->t('Core Search Page: %page_name', ['%page_name' => $page->get('label')]),
              'description' => $this->t('Provides a facet source.'),
            ] + $base_plugin_definition;
        }
        uasort($plugin_derivatives, array($this, 'compareDerivatives'));

        $this->derivatives[$base_plugin_id] = $plugin_derivatives;
      }
    }
    return $this->derivatives[$base_plugin_id];
  }

}
