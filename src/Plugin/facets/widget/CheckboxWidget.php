<?php

/**
 * @file
 */

namespace Drupal\facets\Plugin\facets\widget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\facets\FacetInterface;
use Drupal\facets\Widget\WidgetInterface;

/**
 * @FacetsWidget(
 *   id = "checkbox",
 *   label = @Translation("List of checkboxes"),
 *   description = @Translation("A configurable widget that shows a list of checkboxes"),
 * )
 */
class CheckboxWidget implements WidgetInterface {

  use StringTranslationTrait;

  /**
   * @var \Drupal\Core\Utility\LinkGeneratorInterface $linkGenerator
   */
  protected $linkGenerator;

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
    /** @var \Drupal\facets\Result\Result[] $results */
    $results = $facet->getResults();
    $items = [];

    $configuration = $facet->get('widget_configs');
    $show_numbers = (bool) $configuration['show_numbers'];

    foreach ($results as $result) {
      if ($result->getCount()) {
        // Get the link.
        $text = $result->getDisplayValue();
        if($show_numbers){
          $text .= ' (' . $result->getCount() . ')';
        }
        if ($result->isActive()) {
          $text = '(-) ' . $text;
        }
        $link = $this->linkGenerator()->generate($text, $result->getUrl());
        $items[] = $link;
      }
    }
    $build = [
      '#theme' => 'item_list',
      '#items' => $items,
    ];

    $build['#prefix'] = $this->t('Checkboxes');
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, $config) {

    $form['show_numbers'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show the amount of results'),
    ];

    if (!is_null($config)) {
      $widget_configs = $config->get('widget_configs');
      if (isset($widget_configs['show_numbers'])) {
        $form['show_numbers']['#default_value'] = $widget_configs['show_numbers'];
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getQueryType($query_types) {
    return $query_types['string'];
  }

  /**
   * Gets the link generator.
   *
   * @return \Drupal\Core\Utility\LinkGeneratorInterface
   */
  protected function linkGenerator() {
    if (!isset($this->linkGenerator)) {
      $this->linkGenerator = \Drupal::linkGenerator();
    }
    return $this->linkGenerator;
  }
}
