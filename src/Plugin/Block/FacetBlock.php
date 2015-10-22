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
   * Drupal\facetapi\FacetManager definition.
   *
   * @var \Drupal\facetapi\FacetManager\FacetManagerInterface
   */
  protected $facetapiFacetManager;

  /**
   * The facet_manager plugin manager.
   *
   * @var \Drupal\facetapi\FacetManager\FacetManagerPluginManagerInterface
   */
  protected $pluginManager;

  /**
   * Construct.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\facetapi\FacetManager\FacetManagerPluginManagerInterface pluginManager
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, \Drupal\facetapi\FacetManager\FacetManagerPluginManagerInterface $plugin_manager) {
    $this->pluginManager = $plugin_manager;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {

    /** @var \Drupal\facetapi\FacetManager\FacetManagerPluginManager $plugin_manager */
    $plugin_manager = $container->get('plugin.manager.facetapi.manager');

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
    /** @var Facet $facet */
    $facet = $this->getContextValue('facet');

    if (is_null($facet->getFacetSource())) {
      return ['#markup' => "This is why we can't have nice things."];
    }

    // This should be changeable when we support more than just search API.
    $plugin_id = 'facetapi_default';

    /** @var \Drupal\facetapi\FacetManager\FacetManagerInterface $manager */
    $manager = $this->pluginManager->getMyOwnChangeLaterInstance(
      $plugin_id,
      $facet->getFacetSource()
    );

    // Let the facet_manager build the facets.
    $build = $manager->build($facet);

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
