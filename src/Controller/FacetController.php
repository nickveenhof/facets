<?php

/**
 * @file
 * Contains \Drupal\facetapi\Controller\FacetController.
 */

namespace Drupal\facetapi\Controller;

use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Controller\ControllerBase;
use Drupal\facetapi\FacetInterface;

/**
 * Provides route responses for facets.
 */
class FacetController extends ControllerBase {

  /**
   * Displays information about a search facet.
   *
   * @param \Drupal\facetapi\FacetInterface $facet
   *   The facet to display.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function page(FacetInterface $facet) {
    // Build the search index information.
    $render = array(
      'view' => array(
        '#theme' => 'facetapi_facet',
        '#facet' => $facet,
      ),
    );
    return $render;
  }

  /**
   * Returns the page title for an facets's "View" tab.
   *
   * @param \Drupal\facetapi/FacetInterface $facet
   *   The facet that is displayed.
   *
   * @return string
   *   The page title.
   */
  public function pageTitle(FacetInterface $facet) {
    return SafeMarkup::format('@title', array('@title' => $facet->label()));
  }

}
