<?php

/**
 * @file
 */

namespace Drupal\facetapi\Plugin\facetapi\Widget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\facetapi\FacetInterface;
use Drupal\facetapi\Widget\WidgetInterface;

/**
 * @FacetApiWidget(
 *   id = "links",
 *   label = @Translation("List of links"),
 *   description = @Translation("A simple widget that shows a list of links"),
 * )
 *
 * Class LinksWidget
 */
class LinksWidget implements WidgetInterface {

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
    $items = [];
    foreach ($results as $result) {
      if ($result->getCount()) {
        // Get the link.
        $text = $result->getDisplayValue() . ' (' . $result->getCount() . ')';
        if ($result->isActive()) {
          $text = '(-) ' . $text;
        }

        if (is_null($result->getUrl())) {
          $link = $text;
        }
        else {
          $link_generator = \Drupal::linkGenerator();
          $link = $link_generator->generate($text, $result->getUrl());
        }
        $items[] = $link;
      }
    }
    $build = [
      '#theme' => 'item_list',
      '#items' => $items,
    ];
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getQueryType($query_types) {
    return $query_types['string'];
  }

}
