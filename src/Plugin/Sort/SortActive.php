<?php

namespace Drupal\facetapi\Plugin\FacetApi\Sort;

use Drupal\Core\Annotation\Translation;
use Drupal\facetapi\Annotation\FacetApiSort;
use Drupal\facetapi\Sort\FacetApiSortBase;

/**
 * @FacetApiSort(
 *    id = "facetapi_sort_active",
 *    label = @Translation("Facet Active"),
 *    description = @Translation("Sort by whether the facet is active or not.")
 * )
 */
class SortActive extends FacetApiSortBase {
  public function sort(array $a, array $b) {
    $a_active = (isset($a['#active'])) ? $a['#active'] : 0;
    $b_active = (isset($b['#active'])) ? $b['#active'] : 0;
    if ($a_active == $b_active) {
      return 0;
    }
    return ($a_active < $b_active) ? -1 : 1;
  }
}
