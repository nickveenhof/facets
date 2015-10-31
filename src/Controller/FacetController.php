<?php

/**
 * @file
 * Contains \Drupal\facetapi\Controller\FacetController.
 */

namespace Drupal\facetapi\Controller;

use Drupal\Component\Render\FormattableMarkup;
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
   * Returns a form to add a new facet to a search api index.
   *
   * @param \Drupal\search_api\IndexInterface $search_api_index
   *   The search api index this facet will be added to.
   *
   * @return array
   *   The facet add form.
   */
  public function addForm() {
    $facet = \Drupal::service('entity_type.manager')->getStorage('facetapi_facet')->create();
    return $this->entityFormBuilder()->getForm($facet, 'default');
  }

  /**
   * Returns a form to edit a facet on a search api index.
   *
   * @param \Drupal\search_api\IndexInterface $search_api_index
   *   The search api index this facet will be added to.
   * @param \Drupal\facetapi\FacetInterface $facetapi_facet
   *   Facet currently being edited
   *
   * @return array
   *   The facet edit form.
   */
  public function editForm(FacetInterface $facetapi_facet) {
    $facet = \Drupal::service('entity_type.manager')->getStorage('facetapi_facet')->load($facetapi_facet->id());
    return $this->entityFormBuilder()->getForm($facet, 'default');
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
    return new FormattableMarkup('@title', array('@title' => $facet->label()));
  }

}
