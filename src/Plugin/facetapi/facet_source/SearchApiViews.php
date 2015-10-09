<?php

/**
 * @file
 *   Contains \Drupal\facetapi\Plugin\facet_api\facet_source\SearchApiViews
 */

namespace Drupal\facetapi\Plugin\facetapi\facet_source;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\facetapi\FacetInterface;
use Drupal\facetapi\FacetSource\FacetSourceInterface;
use Drupal\facetapi\FacetSource\FacetSourcePluginBase;
use Drupal\search_api\Plugin\views\query\SearchApiQuery;
use Drupal\views\Views;


/**
 * Represents a facet source which represents the search api views.
 *
 * @FacetApiFacetSource(
 *   id = "search_api_views",
 *   deriver = "Drupal\facetapi\Plugin\facetapi\facet_source\SearchApiViewsDeriver"
 * )
 */
class SearchApiViews extends FacetSourcePluginBase {

  use StringTranslationTrait;

  use DependencySerializationTrait;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface|null
   */
  protected $entityManager;

  /**
   * The typed data manager.
   *
   * @var \Drupal\Core\TypedData\TypedDataManager|null
   */
  protected $typedDataManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|null
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    // Since defaultConfiguration() depends on the plugin definition, we need to
    // override the constructor and set the definition property before calling
    // that method.
    $this->pluginDefinition = $plugin_definition;
    $this->pluginId = $plugin_id;
    $this->configuration = $configuration + $this->defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet, FacetSourceInterface $facet_source) {

    // Load facet plugin definition and depending on those settings; load the
    // corresponding view with the correct view with the correct display set.
    // Get that display's query so we can check if this is a search API based
    // view.
    $plugin_def = $facet_source->getPluginDefinition();
    $view = Views::getView($plugin_def['view_id']);
    $view->setDisplay($plugin_def['view_display']);
    $query = $view->getQuery();

    // Early return when the view is not based on a search API query.
    if (!$query instanceof SearchApiQuery) {
      return [];
    }

    $index = $query->getIndex();

    $indexed_fields = [];
    foreach ($index->getDatasources() as $datasource_id => $datasource) {
      $fields = $index->getFieldsByDatasource($datasource_id);
      foreach ($fields as $field) {
        $indexed_fields[$field->getFieldIdentifier()] = $field->getLabel();
      }
    }

    $form['field_identifier'] = [
      '#type' => 'select',
      '#options' => $indexed_fields,
      '#title' => $this->t('Facet field'),
      '#description' => $this->t('Choose the indexed field.'),
      '#required' => TRUE,
      '#default_value' => $facet->getFieldIdentifier()
    ];

    return $form;
  }

}
