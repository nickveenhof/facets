<?php

namespace Drupal\facetapi\Plugin\facetapi\processor;


use Drupal\facetapi\Processor\WidgetOrderPluginBase;
use Drupal\facetapi\Processor\WidgetOrderProcessorInterface;
use Drupal\facetapi\Result\Result;

/**
 * @FacetApiProcessor(
 *   id = "count_widget_order",
 *   label = @Translation("Sort by count"),
 *   description = @Translation("Sorts the widget results by count."),
 *   stages = {
 *     "build" = 50
 *   }
 * )
 */
class CountWidgetOrderProcessor extends WidgetOrderPluginBase implements WidgetOrderProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function sortResults(array $results, $order = 'ASC') {
    if ($order === 'ASC') {
      usort($results, 'self::sortCountAsc');
    }
    else {
      usort($results, 'self::sortCountDesc');
    }

    return $results;
  }

  protected static function sortCountAsc(Result $a, Result $b) {
    if ($a->getCount() == $b->getCount()) {
      return 0;
    }
    return ($a->getCount() < $b->getCount()) ? -1 : 1;
  }

  protected static function sortCountDesc(Result $a, Result $b) {
    if ($a->getCount() == $b->getCount()) {
      return 0;
    }
    return ($a->getCount() > $b->getCount()) ? -1 : 1;
  }

}
