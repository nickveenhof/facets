<?php
/**
 * Contains Drupal\facetapi\Plugin\TestAdapter
 */

namespace Drupal\facetapi\Plugin\Adapter;

/**
 * @FacetApiAdapter(
 *   id = "test",
 *   label = @Translation("Test"),
 *   description = @Translation("Test class for facetapi adapter")
 * )
 */
class TestAdapter extends AdapterBase {

  /**
   * Returns a boolean flagging whether $this->searcher['searcher'] executed a
   * search.
   *
   * @return boolean
   *   A boolean flagging whether $this->searcher['searcher'] executed a search.
   *
   * @todo Generic search API should provide consistent functionality.
   */
  public function searchExecuted() {
    return TRUE;
  }

  /**
   * Returns a boolean flagging whether facets in a realm shoud be displayed.
   *
   * Useful, for example, for suppressing sidebar blocks in some cases. Apache
   * Solr Search Integration used this method to prevent blocks from being
   * displayed when the module was configured to render them in the search body
   * on "empty" searches instead of the normal facet location.
   *
   * @param string $realm_name
   *   The machine readable name of the realm.
   *
   * @return boolean
   *   A boolean flagging whether to display a given realm.
   *
   * @todo It appears that no implementing modules are leveraging this anymore.
   *   Let's discuss whether to deprecate this method or even remove it from
   *   future versions of Facet API at http://drupal.org/node/1661410.
   */
  public function suppressOutput($realm_name) {
    return TRUE;
  }
}