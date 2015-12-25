<?php

/**
 * @file
 * Contains Drupal\facets\Plugin\Block\FacetBlock.
 */

namespace Drupal\facets\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\facets\FacetManager\DefaultFacetManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'FacetBlock' block.
 *
 * @Block(
 *   id = "facet_block",
 *   deriver = "Drupal\facets\Plugin\block\FacetBlockDeriver"
 * )
 */
class FacetBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The facet manager service.
   *
   * @var DefaultFacetManager
   */
  protected $facetManager;

  /**
   * Construct.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\facets\FacetManager\DefaultFacetManager $facet_manager
   *   The facet manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, DefaultFacetManager $facet_manager) {
    $this->facetManager = $facet_manager;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {

    /** @var \Drupal\facets\FacetManager\DefaultFacetManager $facet_manager */
    $facet_manager = $container->get('facets.manager');

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $facet_manager
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $em = \Drupal::getContainer()->get('entity_type.manager');

    // The id saved in the configuration is in the format of
    // base_plugin:facet_id. We're splitting that to get to the facet id.
    $facet_mapping = $this->configuration['id'];
    $facet_id = explode(PluginBase::DERIVATIVE_SEPARATOR, $facet_mapping)[1];

    /** @var \Drupal\facets\FacetInterface $facet */
    $facet = $em->getStorage('facets_facet')->load($facet_id);

    // Let the facet_manager build the facets.
    $build = $this->facetManager->build($facet);

    // Add contextual links only when we have results.
    if (!empty($build)) {
      $build['#contextual_links']['facets_facet'] = array(
        'route_parameters' => array('facets_facet' => $facet->id()),
      );
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    // Makes sure a facet block is never cached.
    // @TODO Make blocks cacheable, see: https://www.drupal.org/node/2581629
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $em = \Drupal::getContainer()->get('entity_type.manager');

    // The id saved in the configuration is in the format of
    // base_plugin:facet_id. We're splitting that to get to the facet id.
    $facet_mapping = $this->configuration['id'];
    $facet_id = explode(PluginBase::DERIVATIVE_SEPARATOR, $facet_mapping)[1];

    /** @var \Drupal\facets\FacetInterface $facet */
    $facet = $em->getStorage('facets_facet')->load($facet_id);
    $config_name = $facet->getConfigDependencyName();

    return ['config' => [$config_name]];
  }

}
