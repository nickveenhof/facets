<?php

/**
 * @file
 * Contains \Drupal\facetapi\Plugin\facetapi\empty_behavior\EmptyBehaviorText
 */

namespace Drupal\facetapi\Plugin\facetapi\empty_behavior;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\facetapi\EmptyBehavior\EmptyBehaviorInterface;

/**
 * @FacetApiEmptyBehavior(
 *   id = "text",
 *   label = @Translation("Display text"),
 *   description = @Translation("Display a text when no results"),
 * )
 */
class EmptyBehaviorText implements EmptyBehaviorInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function build(array $config) {
    return ['#markup' => $config['empty_text']['value']];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, $config) {
    $value_empty_text = $config->get('empty_behavior_configs')['empty_text']['value'];
    $value_empty_format = $config->get('empty_behavior_configs')['empty_text']['format'];
    $form['empty_text'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Empty text'),
      '#format' => $value_empty_format ? $value_empty_format : 'plain_text',
      '#editor' => FALSE,
      '#default_value' => $value_empty_text ?: $value_empty_text,
    ];

    return $form;
  }
}
