<?php

/**
 * @file
 * Contains \Drupal\facetapi\Plugin\facetapi\empty_behavior\EmptyBehaviorText.
 */

namespace Drupal\facetapi\Plugin\facetapi\empty_behavior;

use Drupal\Core\Form\FormStateInterface;
use Drupal\facetapi\EmptyBehavior\EmptyBehaviorPluginBase;

/**
 * @FacetApiEmptyBehavior(
 *   id = "text",
 *   label = @Translation("Display text"),
 *   description = @Translation("Display a text when no results"),
 * )
 */
class EmptyBehaviorText extends EmptyBehaviorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function build(array $config) {
    return ['#markup' => $config['empty_text']['value']];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // Get the facet.
    $facet = $form_state->getFormObject()->getEntity();
    // Get the configuration for the facet.
    $config = $this->configFactory->get('facetapi.facet.' . $facet->id());

    // Get the empty behavior configuration from the current facet.
    $value_empty_text = isset($config->get('empty_behavior_configs')['empty_text']) ? $config->get('empty_behavior_configs')['empty_text']['value'] : '';
    $value_empty_format = isset($config->get('empty_behavior_configs')['empty_text']) ? $config->get('empty_behavior_configs')['empty_text']['format'] : 'plain_text';

    // Add the new field to the form.
    $form['empty_text'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Empty text'),
      '#format' => $value_empty_format ?: 'plain_text',
      '#editor' => FALSE,
      '#default_value' => $value_empty_text ?: $value_empty_text,
    ];

    return $form;
  }

}
