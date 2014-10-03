<?php

namespace Drupal\facetapi\Plugin\FacetApi\Sort;

use Drupal\Core\Annotation\Translation;
use Drupal\facetapi\Annotation\FacetApiSort;
use Drupal\facetapi\Sort\FacetApiSortBase;

/**
 * @FacetApiSort(
 *    id = "facetapi_sort_count",
 *    label = @Translation("Count"),
 *    description = @Translation("Sort by the facet count.")
 * )
 */
class SortCount extends FacetApiSortBase {
  public function sort(array $a, array $b) {
    $a_count = (isset($a['#count'])) ? $a['#count'] : 0;
    $b_count = (isset($b['#count'])) ? $b['#count'] : 0;
    if ($a_count == $b_count) {
      return 0;
    }
    return ($a_count < $b_count) ? -1 : 1;
  }
}
