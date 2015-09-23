<?php

namespace Drupal\facet_api\Plugin\FacetApi\Sort;

use Drupal\facet_api\Sort\SortPluginBase;

/**
 * @FacetApiSort(
 *    id = "facet_api_sort_display",
 *    label = @Translation("Display value"),
 *    description = @Translation("Sort by the value displayed to the user.")
 * )
 */
class SortDisplay extends SortPluginBase {
  public function sort(array $a, array $b) {
    return strcasecmp($a['#markup'], $b['#markup']);
  }
}
