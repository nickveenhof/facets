<?php

namespace Drupal\facetapi\Plugin\FacetApi\Sort;

use Drupal\facetapi\Sort\SortPluginBase;

/**
 * @FacetApiSort(
 *    id = "facetapi_sort_count",
 *    label = @Translation("Count"),
 *    description = @Translation("Sort by the facet count.")
 * )
 */
class SortCount extends SortPluginBase {
  public function sort(array $a, array $b) {
    $a_count = (isset($a['#count'])) ? $a['#count'] : 0;
    $b_count = (isset($b['#count'])) ? $b['#count'] : 0;
    if ($a_count == $b_count) {
      return 0;
    }
    return ($a_count < $b_count) ? -1 : 1;
  }
}
