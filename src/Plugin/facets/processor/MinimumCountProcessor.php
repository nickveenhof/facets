<?php
/**
 * @file
 * Contains \Drupal\facets\Plugin\facets\processor.
 */

namespace Drupal\facets\Plugin\facets\processor;

use Drupal\Core\Form\FormStateInterface;
use Drupal\facets\FacetInterface;
use Drupal\facets\Processor\BuildProcessorInterface;
use Drupal\facets\Processor\ProcessorPluginBase;

/**
 * Provides a minimum count processor..
 *
 * @FacetsProcessor(
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
    $processors = $facet->getProcessors();
    $config = $processors[$this->getPluginId()];

    /** @var \Drupal\facets\Result\Result $result */
    foreach ($results as $id => $result) {
      if ($result->getCount() < $config->getConfiguration()['minimum_items']) {
        unset($results[$id]);
      }
    }

    return $results;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet) {
    $processors = $facet->getProcessors();
    $config = isset($processors[$this->getPluginId()]) ? $processors[$this->getPluginId()] : null;

    $build['minimum_items'] = array(
      '#title' => $this->t('Minimum items'),
      '#type' => 'number',
      '#min' => 1,
      '#default_value' => !is_null($config) ? $config->getConfiguration()['minimum_items'] : $this->defaultConfiguration()['minimum_items'],
      '#description' => $this->t('Hide block if the facet contains less than this number of results.'),
    );

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array('minimum_items' => 1);
  }

}
