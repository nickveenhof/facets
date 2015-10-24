<?php

/**
 * @file
 * Contains Drupal\facetapi\Plugin\Block\FacetBlock.
 *
 * NOTE: There should be a facetblock or settings for the facets later.
 */

namespace Drupal\facetapi\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\facetapi\Entity\Facet;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'FacetBlock' block.
 *
 * @Block(
 *  id = "facet_block",
 *  admin_label = @Translation("Facet block"),
 *  context = {
 *    "facet" = @ContextDefinition("entity:facetapi_facet", label=@Translation("Facet"))
 *  }
 * )
 *
 */
class FacetBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The facet_manager plugin manager.
   *
   * @var \Drupal\facetapi\FacetManager\DefaultFacetManager
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
   * @param \Drupal\facetapi\FacetManager\DefaultFacetManager $facetManager
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, \Drupal\facetapi\FacetManager\DefaultFacetManager $facetManager) {
    $this->facetManager = $facetManager;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {

    /** @var \Drupal\facetapi\FacetManager\DefaultFacetManager $facetManager */
    $facetManager = $container->get('facetapi.manager');

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $facetManager
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
      $build['#contextual_links']['facetapi_facet'] = array(
        'route_parameters' => array('facetapi_facet' => $facet->id()),
      );
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->configuration['facet_identifier'] = $form_state->getValue('facet_identifier');
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    // Makes sure a facet block is never cached.
    // @TODO Make blocks cacheable, see: https://www.drupal.org/node/2581629
    return 0;
  }

}
