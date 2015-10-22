<?php

namespace Drupal\facetapi\Plugin\facetapi\Widget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\facetapi\FacetInterface;
use Drupal\facetapi\Result\Result;
use Drupal\facetapi\Widget\WidgetPluginBase;

/**
 * @FacetApiWidget(
 *   id = "links",
 *   label = @Translation("List of links"),
 *   description = @Translation("A simple widget that shows a list of links"),
 * )
 *
 * Class LinksWidget
 */
class LinksWidget extends WidgetPluginBase {

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
    /** @var Result[] $results */
    $results = $facet->getResults();
    if (!empty($results)) {
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
    else {
      // Get the empty behavior id and the configuration.
      $facet_empty_behavior_configs = $facet->get('empty_behavior_configs');
      $behavior_id = $facet->get('empty_behavior');

      // Build the result using the empty behavior configuration.
      $empty_behavior_plugin = $this->empty_behavior_plugin_manager->createInstance($behavior_id);
      $build = $empty_behavior_plugin->build($facet_empty_behavior_configs);
    }
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    return false;
  }

}
