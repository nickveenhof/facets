<?php

namespace Drupal\facetapi\Plugin\facetapi\Widget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\facetapi\FacetInterface;
use Drupal\facetapi\Result\Result;
use Drupal\facetapi\Widget\WidgetInterface;

/**
 * @FacetApiWidget(
 *   id = "checkbox",
 *   label = @Translation("List of checkboxes"),
 *   description = @Translation("A configurable widget that shows a list of checkboxes"),
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
  public function build(FacetInterface $facet) {
    $build = [];
    /** @var Result[] $results */
    $results = $facet->getResults();
    if (! empty ($results)) {
      $items = [];
      foreach ($results as $result) {
        if ($result->getCount()) {
          // Get the link.
          $text = $result->getValue() . ' (' . $result->getCount() . ')';
          if ($result->isActive()) {
            $text = '(-) ' . $text;
          }
          $link_generator = \Drupal::linkGenerator();
          $link = $link_generator->generate($text, $result->getUrl());
          $items[] = $link;
        }
      }
      $build = [
        '#theme' => 'item_list',
        '#items' => $items,
      ];
    }
    $build['#prefix'] = $this->t('Checkboxes');
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, $config) {
    $checkbox_options = [
      'radio' => $this->t('Radio'),
      'checkboxes' => $this->t('Checkboxes'),
    ];

    $form['checkbox_placement'] = [
      '#type' => 'radios',
      '#title' => $this->t('Type of selection'),
      '#description' => $this->t('Choose if checkboxes or radio boxes should be used.'),
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
