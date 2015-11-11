<?php

/**
 * @file
 * Contains Drupal\facetapi\FacetManager\CoreSearchFacetManager.
 */

namespace Drupal\facetapi\FacetManager;


class CoreSearchFacetManager extends DefaultFacetManager {

  /**
   * The facet query being executed.
   */
  protected $facetQueryExtender;

  /**
   * Sets the facet query object.
   *
   * @return FacetapiQuery
   */
  public function getFacetQueryExtender() {
    if (!$this->facetQueryExtender) {

      //$this->facetQueryExtender = db_select('search_index', 'i', array('target' => 'replica'))->extend('Drupal\search\ViewsSearchQuery');
      //$this->searchQuery->searchExpression($input, $this->searchType);
      //$this->searchQuery->publicParseSearchExpression();

      $this->facetQueryExtender = db_select('search_index', 'i', array('target' => 'replica'))->extend('Drupal\facetapi\FacetapiQuery');
      $this->facetQueryExtender->join('node', 'n', 'n.nid = i.sid');
      $this->facetQueryExtender
        //->condition('n.status', 1)
        ->addTag('node_access')
        ->searchExpression($this->keys, 'node_search');
    }
    return $this->facetQueryExtender;
  }

}
