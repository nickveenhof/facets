<?php

/**
 * @file
 * Contains \Drupal\facetapi\Controller\FacetController.
 */

namespace Drupal\facetapi\Controller;

use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Controller\ControllerBase;
use Drupal\facetapi\FacetInterface;
use Drupal\search_api\IndexInterface;

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
  public function addForm(IndexInterface $search_api_index) {
    $facet = $this->entityManager()->getStorage('facetapi_facet')->create(array('search_api_index' => $search_api_index->id()));

    $form_state_additions = ['search_api_index' => $search_api_index];
    return $this->entityFormBuilder()->getForm($facet, 'default', $form_state_additions);
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
  public function editForm(IndexInterface $search_api_index, FacetInterface $facetapi_facet) {
    $facet = $this->entityManager()->getStorage('facetapi_facet')->load($facetapi_facet->id());

    $form_state_additions = ['search_api_index' => $search_api_index];
    return $this->entityFormBuilder()->getForm($facet, 'default', $form_state_additions);
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
