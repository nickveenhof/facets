<?php

namespace Drupal\facetapi\Plugin\facetapi\Widget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\facetapi\FacetInterface;
use Drupal\facetapi\Result\Result;
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
    $build = array();
    /** @var Result[] $results */
    $results = $facet->getResults();
    if (! empty ($results)) {
      $items = array();
      foreach ($results as $result) {
        if ($result->getCount()) {
          // Get the link.
          $text = $result->getValue() . ' (' . $result->getCount() . ')';
          if ($result->isActive()) {
            $text = '(-) ' . $text;
          }
          $link_generator = \Drupal::linkGenerator();
          $link = $text;//$link_generator->generate($text, $result->getUrl());
          $items[] = $link;
        }
      }
      $build = array(
        '#theme' => 'item_list',
        '#items' => $items,
      );
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
