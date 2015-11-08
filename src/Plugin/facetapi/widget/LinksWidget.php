<?php

/**
 * @file
 */

namespace Drupal\facetapi\Plugin\facetapi\widget;

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
    /** @var \Drupal\facetapi\Result\Result[] $results */
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
          $items[] = $text;
        }
        else {
          $items[] = $this->linkGenerator()->generate($text, $result->getUrl());
        }
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
