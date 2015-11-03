<?php
/**
 * @file
 *   Contains \Drupal\facetapi\Plugin\facet_api\facet_source\SearchApiBaseFacetSource
 */

namespace Drupal\facetapi\Plugin\facetapi\facet_source;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\facetapi\Exception\InvalidQueryTypeException;
use Drupal\facetapi\FacetInterface;
use Drupal\facetapi\FacetSource\FacetSourceInterface;
use Drupal\facetapi\FacetSource\FacetSourcePluginBase;
use Drupal\search_api\FacetApiQueryTypeMappingInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class SearchApiBaseFacetSource extends FacetSourcePluginBase {

  use StringTranslationTrait;

  /**
   * The search index.
   *
   * @var \Drupal\search_api\IndexInterface
   */
  protected $index;

  /**
   * The search result cache.
   *
   * @var \Drupal\search_api\Query\ResultsCacheInterface
   */
  protected $searchApiResultsCache;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, $query_type_plugin_manager, $search_results_cache) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $query_type_plugin_manager);
    // Since defaultConfiguration() depends on the plugin definition, we need to
    // override the constructor and set the definition property before calling
    // that method.
    $this->pluginDefinition = $plugin_definition;
    $this->pluginId = $plugin_id;
    $this->configuration = $configuration;
    $this->searchApiResultsCache = $search_results_cache;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\facetapi\QueryType\QueryTypePluginManager $query_type_plugin_manager */
    $query_type_plugin_manager = $container->get('plugin.manager.facetapi.query_type');

    /** @var \Drupal\search_api\Query\ResultsCacheInterface $results_cache */
    $search_results_cache = $container->get('search_api.results_static_cache');
    return new static($configuration, $plugin_id, $plugin_definition, $query_type_plugin_manager, $search_results_cache);
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet, FacetSourceInterface $facet_source) {

    $form['field_identifier'] = [
      '#type' => 'select',
      '#options' => $this->getFields(),
      '#title' => $this->t('Facet field'),
      '#description' => $this->t('Choose the indexed field.'),
      '#required' => TRUE,
      '#default_value' => $facet->getFieldIdentifier()
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFields() {
    $indexed_fields = [];
    $fields = $this->index->getFields(true);
    foreach ($fields as $field) {
      $indexed_fields[$field->getFieldIdentifier()] = $field->getLabel();
    }
    return $indexed_fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getQueryTypesForFacet(FacetInterface $facet) {
    // Get our FacetApi Field Identifier, which is equal to the Search API Field
    // identifier.
    $field_id = $facet->getFieldIdentifier();
    // Get the Search API Server.
    $server = $this->index->getServer();
    // Get the Search API Backend.
    $backend = $server->getBackend();

    if ($backend instanceof FacetApiQueryTypeMappingInterface) {
      $fields = $this->index->getFields(true);
      foreach ($fields as $field) {
        if ($field->getFieldIdentifier() == $field_id) {
          return $backend->getQueryTypesForDataType($field->getType());
        }
      }
    }
    throw new InvalidQueryTypeException($this->t("No available query types were found for facet @facet", ['@facet' => $facet->getName()]));
  }

}
