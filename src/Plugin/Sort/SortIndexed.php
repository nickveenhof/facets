<?php

namespace Drupal\facetapi\Plugin\FacetApi\Sort;

use Drupal\Core\Annotation\Translation;
use Drupal\facetapi\Annotation\FacetApiSort;
use Drupal\facetapi\Sort\FacetApiSortBase;

/**
 * @FacetApiSort(
 *    id = "facetapi_sort_indexed",
 *    label = @Translation("Indexed value"),
 *    description = @Translation("Sort by the raw value stored in the index.")
 * )
 */
class SortIndexed extends FacetApiSortBase {
  public function sort(array $a, array $b) {
    $a_value = (isset($a['#indexed_value'])) ? $a['#indexed_value'] : '';
    $b_value = (isset($b['#indexed_value'])) ? $b['#indexed_value'] : '';
    if ($a_value == $b_value) {
      return 0;
    }
    return ($a_value < $b_value) ? -1 : 1;
  }
}
