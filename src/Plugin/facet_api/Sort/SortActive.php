<?php

namespace Drupal\facet_api\Plugin\FacetApi\Sort;

use Drupal\facet_api\Sort\SortPluginBase;

/**
 * @FacetApiSort(
 *    id = "facet_api_sort_active",
 *    label = @Translation("Facet Active"),
 *    description = @Translation("Sort by whether the facet is active or not.")
 * )
 */
class SortActive extends SortPluginBase {
  public function sort(array $a, array $b) {
    $a_active = (isset($a['#active'])) ? $a['#active'] : 0;
    $b_active = (isset($b['#active'])) ? $b['#active'] : 0;
    if ($a_active == $b_active) {
      return 0;
    }
    return ($a_active < $b_active) ? -1 : 1;
  }
}
