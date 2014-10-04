<?php

/**
 * @file
 * Contains \Drupal\facetapi\Searcher\SearcherInterface.
 */

namespace Drupal\facetapi\Searcher;

use Drupal\Component\Plugin\ConfigurablePluginInterface;

/**
 * Defines the interface for searchers.
 *
 * @see plugin_api
 */
interface SearcherInterface extends PluginInspectionInterface {

  /**
   * Returns the machine readable name of the searcher.
   *
   * @return string
   */
  public function getName();

  /**
   * Returns the human readable name of the searcher displayed in 
   * the admin UI.
   *
   * @return string
   */
  public function getLabel();

  /**
   * Returns the adapter plugin ID associated with the searcher.
   *
   * @return string
   */
  public function getAdapter();

  /**
   * Returns the URL processor plugin ID associated with the searcher.
   *
   * @return string
   */
  public function getUrlProcessor();

  /**
   * Returns an array containing the types of content indexed by the searcher.
   *
   * @return array
   */
  public function getTypes();
 
  /**
   * Returns the MENU_DEFAULT_LOCAL_TASK item which the admin UI page is added
   * to as a MENU_LOCAL_TASK. An empty string if the backend manages the admin
   * UI menu items internally.
   *
   * @return mixed
   */
  public function getPath();

  /**
   * Returns TRUE if the searcher supports "missing" facets.
   *
   * @return boolean
   */
  public function getSupportFacetsMissing();

  /**
   * Returns TRUE if the searcher supports the minimum facet count setting.
   *
   * @return boolean
   */
  public function getSupportFacetsMincount();

 
  /**
   * Returns TRUE if the searcher should include the facets
   * defined in facetapi_facetapi_facet_info() when indexing node content,
   * FALSE if they should be skipped.
   *
   * @return boolean
   */
  public function getIncludeDefaultFacets();
}
