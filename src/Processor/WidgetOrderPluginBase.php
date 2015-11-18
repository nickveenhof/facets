<?php

/**
 * @file
 */

namespace Drupal\facetapi\Processor;

use Drupal\Core\Form\FormStateInterface;
use Drupal\facetapi\FacetInterface;

/**
 * A base class for plugins that implements most of the boilerplate.
 */
abstract class WidgetOrderPluginBase extends ProcessorPluginBase implements WidgetOrderProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet) {
    $processors = $facet->getProcessors();
    $config = isset($processors[$this->getPluginId()]) ? $processors[$this->getPluginId()] : null;

    $build['sort'] = [
      '#type' => 'radios',
      '#title' => $this->t('Sort order'),
      '#options' => [
        'ASC' => $this->t('Ascending'),
        'DESC' => $this->t('Descending'),
      ],
      '#default_value' => !is_null($config) ? $config->getConfiguration()['sort'] : $this->defaultConfiguration()['sort'],
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet, array $results) {
    $processors = $facet->getProcessors();
    $config = $processors[$this->getPluginId()];

    // This should load the facet's config to find the ordering direction.
    return $this->sortResults($results, $config->getConfiguration()['sort']);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['sort' => 'ASC'];
  }

}
