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
   * Drupal\facetapi\Adapter definition.
   *
   * @var \Drupal\facetapi\Adapter\AdapterInterface
   */
  protected $facetapiAdapter;

  /**
   * The adapter plugin manager.
   *
   * @var \Drupal\facetapi\Adapter\AdapterPluginManagerInterface
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
   * @param \Drupal\facetapi\Adapter\AdapterPluginManagerInterface pluginManager
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, \Drupal\facetapi\Adapter\AdapterPluginManagerInterface $plugin_manager) {
    $this->pluginManager = $plugin_manager;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $plugin_manager = $container->get('plugin.manager.facetapi.adapter');
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
    // Get the adapter.
    // For now hard code the id.
    // This should be based on facet definitions.
    // The plugin manager should be injected.

    /** @var Facet $facet */
//    $facet = $this->getContextValue('facet');

    $plugin_id = 'search_api_views';
    $facets = facetapi_get_enabled_facets();
    $facet = $facets[$this->configuration['facet_identifier']];

    /** @var \Drupal\facetapi\Adapter\AdapterInterface $adapter */
    $adapter = $this->pluginManager->getMyOwnChangeLaterInstance(
      $plugin_id,
      $facet->getSearcherName()
    );

    // Let the adapter build the facets.
    $build = $adapter->build($facet);

    return $build;
  }

  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    // Get the facet definitions.
    $facets = facetapi_get_enabled_facets();
    $facet_options = array();

    foreach ($facets as $facet_name => $facet) {
      $identifier = $facet_name;
      $facet_options[$identifier] = $facet->getSearcherName() . ' facet: ' . $facet->getName();
    }

    $form['facet_identifier'] = array(
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => t('Facet to render'),
      '#default_value' => $this->configuration['facet_identifier'],
      '#empty_option' => t('- Select -'),
      '#options' => $facet_options,
    );
    return $form;
  }

  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->configuration['facet_identifier'] = $form_state->getValue('facet_identifier');
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
