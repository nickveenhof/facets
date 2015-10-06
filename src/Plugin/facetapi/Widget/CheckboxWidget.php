<?php

namespace Drupal\facetapi\Plugin\facetapi\Widget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\facetapi\Widget\WidgetInterface;

/**
 * @FacetApiWidget(
 *   id = "checkbox",
 *   label = @Translation("List of checkboxes"),
 *   description = @Translation("A widget that shows checkboxes"),
 * )
 *
 * Class CheckboxWidget
 */
class CheckboxWidget implements WidgetInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function execute() {
    // Execute all the things.
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return ['#markup' => 'checkbox widget'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, $config) {
    $checkbox_options = [
      'before' => $this->t('Before'),
      'after' => $this->t('After'),
    ];

    $form['checkbox_placement'] = [
      '#type' => 'radios',
      '#title' => $this->t('Checkbox placement'),
      '#description' => $this->t('Choose where the checkboxes should be placed'),
      '#options' => $checkbox_options,
      '#required' => TRUE,
    ];
    if (!is_null($config)) {
      $widget_configs = $config->get('widget_configs');
      if (isset($widget_configs['checkbox_placement'])) {
        $form['checkbox_placement']['#default_value'] = $widget_configs['checkbox_placement'];
      }
    }

    return $form;
  }

}
