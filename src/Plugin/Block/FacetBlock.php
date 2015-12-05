<?php

/**
 * @file
 * Contains Drupal\facets\Plugin\Block\FacetBlock.
 *
 * NOTE: There should be a facetblock or settings for the facets later.
 */

namespace Drupal\facets\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\facets\FacetManager\DefaultFacetManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'FacetBlock' block.
 *
 * @Block(
 *  id = "facet_block",
 *  admin_label = @Translation("Facet block"),
 *  context = {
 *    "facet" = @ContextDefinition("entity:facets_facet", label=@Translation("Facet"))
 *  }
 * )
 */
class FacetBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The facet_manager plugin manager.
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
    /** @var Facet $facet */
    $facet = $this->getContextValue('facet');

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
    $facet_context_mapping = $this->configuration['context_mapping']['facet'];
    $facet_id = explode(':', $facet_context_mapping)[1];

    $em = \Drupal::getContainer()->get('entity_type.manager');

    /** @var \Drupal\facets\FacetInterface $facet */
    $facets = $em->getStorage('facets_facet')
      ->loadByProperties(['uuid' => $facet_id]);

    $keys = array_keys($facets);

    $facet = $facets[$keys[0]];

    $config_name = $facet->getConfigDependencyName();

    return ['config' => [$config_name]];
  }

}
