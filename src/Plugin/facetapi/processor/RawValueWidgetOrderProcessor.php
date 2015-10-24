<?php

namespace Drupal\facetapi\Plugin\facetapi\processor;

use Drupal\facetapi\Processor\WidgetOrderPluginBase;
use Drupal\facetapi\Processor\WidgetOrderProcessorInterface;
use Drupal\facetapi\Result\Result;

/**
 * @FacetApiProcessor(
 *   id = "raw_value_widget_order",
 *   label = @Translation("Sort by raw value"),
 *   description = @Translation("Sorts the widget results by raw value."),
 *   stages = {
 *     "build" = 50
 *   }
 * )
 */
class RawValueWidgetOrderProcessor extends WidgetOrderPluginBase implements WidgetOrderProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function sortResults(array $results, $order = 'ASC') {
    if ($order === 'ASC') {
      usort($results, 'self::sortRawValueAsc');
    }
    else {
      usort($results, 'self::sortRawValueDesc');
    }

    return $results;
  }

  protected static function sortRawValueAsc(Result $a, Result $b) {
    return strnatcasecmp($a->getRawValue(), $b->getRawValue());
  }

  protected static function sortRawValueDesc(Result $a, Result $b) {
    return strnatcasecmp($b->getRawValue(), $a->getRawValue());
  }

}
