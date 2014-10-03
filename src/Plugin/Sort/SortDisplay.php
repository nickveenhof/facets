<?php

namespace Drupal\facetapi\Plugin\FacetApi\Sort;

use Drupal\Core\Annotation\Translation;
use Drupal\facetapi\Annotation\FacetApiSort;
use Drupal\facetapi\Sort\FacetApiSortBase;

/**
 * @FacetApiSort(
 *    id = "facetapi_sort_display",
 *    label = @Translation("Display value"),
 *    description = @Translation("Sort by the value displayed to the user.")
 * )
 */
class SortDisplay extends FacetApiSortBase {
  public function sort(array $a, array $b) {
    return strcasecmp($a['#markup'], $b['#markup']);
  }
}
