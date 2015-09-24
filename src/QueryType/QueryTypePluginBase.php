<?php
/**
 * Contains Drupal\facetapi\QueryType\QueryTypePluginBase
 */

namespace Drupal\facetapi\QueryType;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\Entity\DependencyTrait;
use Drupal\Core\Plugin\PluginBase;
use Drupal\facetapi\FacetInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class QueryTypePluginBase extends PluginBase implements QueryTypeInterface, ConfigurablePluginInterface {

  use DependencyTrait;

  public function __construct(array $configuration,
    $plugin_id,
    $plugin_definition) {
    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
    $this->query = $this->configuration['query'];
    $this->facet = $this->configuration['facet'];
    $this->results = ! empty($this->configuration['results']) ? $this->configuration['results'] : array();
  }


  /**
   * Holds the backend native query object.
   *
   * @var
   */
  protected $query;

  /**
   * Holds the facet.
   *
   * @var FacetInterface
   */
  protected $facet;

  /**
   * Holds the results for the facet.
   *
   * @var array
   */
  protected $results;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration + $this->defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $this->addDependency('module', $this->getPluginDefinition()['provider']);
    return $this->dependencies;
  }
}