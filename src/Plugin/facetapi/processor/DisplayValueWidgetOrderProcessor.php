<?php

namespace Drupal\facetapi\Plugin\facetapi\processor;

use Drupal\facetapi\Processor\WidgetOrderPluginBase;
use Drupal\facetapi\Processor\WidgetOrderProcessorInterface;
use Drupal\facetapi\Result\Result;

/**
 * @FacetApiProcessor(
 *   id = "display_value_widget_order",
 *   label = @Translation("Sort by display value"),
 *   description = @Translation("Sorts the widget results by display value."),
 *   stages = {
 *     "build" = 50
 *   }
 * )
 */
class DisplayValueWidgetOrderProcessor extends WidgetOrderPluginBase implements WidgetOrderProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function sortResults(array $results, $order = 'ASC') {
    if ($order === 'ASC') {
      usort($results, 'self::sortDisplayValueAsc');
    }
    else {
      usort($results, 'self::sortDisplayValueDesc');
    }

    return $results;
  }

  protected static function sortDisplayValueAsc(Result $a, Result $b) {
    return strnatcasecmp($a->getValue(), $b->getValue());
  }

  protected static function sortDisplayValueDesc(Result $a, Result $b) {
    return strnatcasecmp($b->getValue(), $a->getValue());
  }

}
