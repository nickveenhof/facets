<?php

namespace Drupal\facetapi\Plugin\FacetApi\Sort;

use Drupal\facetapi\Sort\SortPluginBase;

/**
 * @FacetApiSort(
 *    id = "facetapi_sort_display",
 *    label = @Translation("Display value"),
 *    description = @Translation("Sort by the value displayed to the user.")
 * )
 */
class SortDisplay extends SortPluginBase {
  public function sort(array $a, array $b) {
    return strcasecmp($a['#markup'], $b['#markup']);
  }
}
