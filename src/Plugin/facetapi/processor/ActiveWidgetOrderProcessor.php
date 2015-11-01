<?php

namespace Drupal\facetapi\Plugin\facetapi\processor;


use Drupal\facetapi\Processor\WidgetOrderPluginBase;
use Drupal\facetapi\Processor\WidgetOrderProcessorInterface;
use Drupal\facetapi\Result\Result;

/**
 * @FacetApiProcessor(
 *   id = "active_widget_order",
 *   label = @Translation("Sort by active state"),
 *   description = @Translation("Sorts the widget results by active state."),
 *   stages = {
 *     "build" = 50
 *   }
 * )
 */
class ActiveWidgetOrderProcessor extends WidgetOrderPluginBase implements WidgetOrderProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function sortResults(array $results, $order = 'ASC') {
    if ($order === 'ASC') {
      usort($results, 'self::sortActiveAsc');
    }
    else {
      usort($results, 'self::sortActiveDesc');
    }

    return $results;
  }

  protected static function sortActiveAsc(Result $a, Result $b) {
    if ($a->isActive() == $b->isActive()) {
      return 0;
    }
    return ($a->isActive()) ? -1 : 1;
  }

  protected static function sortActiveDesc(Result $a, Result $b) {
    if ($a->isActive() == $b->isActive()) {
      return 0;
    }
    return ($a->isActive()) ? 1 : -1;
  }

}
