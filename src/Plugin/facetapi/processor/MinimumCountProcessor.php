<?php
/**
 * @file
 * Contains \Drupal\facetapi\Plugin\facetapi\processor.
 */

namespace Drupal\facetapi\Plugin\facetapi\processor;

use Drupal\Core\Form\FormStateInterface;
use Drupal\facetapi\FacetInterface;
use Drupal\facetapi\Processor\BuildProcessorInterface;
use Drupal\facetapi\Processor\ProcessorPluginBase;
use Drupal\facetapi\Result\Result;

/**
 * Provides a minimum count processor..
 *
 * @FacetApiProcessor(
 *   id = "minimum_count",
 *   label = @Translation("Minimum count"),
 *   description = @Translation("Hide facets with less than x items."),
 *   stages = {
 *     "build" = 50
 *   }
 * )
 */
class MinimumCountProcessor extends ProcessorPluginBase implements BuildProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet, array $results) {
    $processor_configs = $facet->getProcessorConfigs();
    $config = $processor_configs[$this->getPluginId()];

    /** @var Result $result */
    foreach ($results as $id => $result) {
      if ($result->getCount() < $config['settings']['minimum_items']) {
        unset($results[$id]);
      }
    }

    return $results;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet) {
    $processor_configs = $facet->getProcessorConfigs();
    $config = $processor_configs[$this->getPluginId()];

    $build['minimum_items'] = array(
      '#title' => 'Minimum items',
      '#type' => 'number',
      '#min' => 1,
      '#default_value' => $config['settings']['minimum_items'],
      '#description' => 'Hide block if the facet contains less than this number of results',
    );

    return $build;
  }

}