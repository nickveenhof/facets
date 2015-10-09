<?php

/**
 * @file
 * Contains Drupal\facetapi\Adapter\AdapterInterface.
 */

namespace Drupal\facetapi\Adapter;

use Drupal\facetapi\FacetInterface;

interface AdapterInterface {

  /**
   * Set the search id.
   *
   * @return mixed
   */
  public function setSearchId($search_id);


  /**
   * Returns the search path associated with this searcher.
   *
   * @return string
   *   A string containing the search path.
   *
   * @todo D8 should provide an API function for this.
   */
  public function getSearchPath();

  /**
   * Sets the search keys, or query text, submitted by the user.
   *
   * @param string $keys
   *   The search keys, or query text, submitted by the user.
   *
   * @return FacetapiAdapter
   *   An instance of this class.
   */
  public function setSearchKeys($keys);

  /**
   * Gets the search keys, or query text, submitted by the user.
   *
   * @return string
   *   The search keys, or query text, submitted by the user.
   */
  public function getSearchKeys();

  /**
   * Returns the number of results returned by the search query.
   *
   * @return int
   *   The number of results returned by the search query.
   */
  public function getResultCount();

  /**
   * Returns the number of results per page.
   *
   * @return int
   *   The number of results per page, or the limit.
   */
  public function getPageLimit();

  /**
   * Returns the page number of the search result set.
   *
   * @return int
   *   The current page of the result set.
   */
  public function getPageNumber();

  /**
   * Returns the total number of pages in the result set.
   *
   * @return int
   *   The total number of pages.
   */
  public function getPageTotal();

  /**
   * Allows the backend to add facet queries to its native query object.
   *
   * This method is called by the implementing module to initialize the facet
   * display process. The following actions are taken:
   * - FacetapiAdapter::initActiveFilters() hook is invoked.
   * - Dependency plugins are instantiated and executed.
   * - Query type plugins are executed.
   *
   * @param mixed $query
   *   The backend's native query object.
   *
   * @todo Should this method be deprecated in favor of one name init()? This
   *   might make the code more readable in implementing modules.
   *
   * @see FacetapiAdapter::initActiveFilters()
   */
  public function alterQuery(&$query);

  /**
   * Returns enabled facets for the searcher associated with this adapter.
   *
   * @return array
   *   An array of enabled facets.
   */
  public function getEnabledFacets();

  /**
   * Returns the searcher id.
   *
   * @return string
   */
  public function getSearcherId();

  /**
   * Initializes facet builds, sets the breadcrumb trail.
   *
   * Facets are built via FacetapiFacetProcessor objects. Facets only need to be
   * processed, or built, once regardless of how many realms they are rendered
   * in. The FacetapiAdapter::processed semaphore is set when this method is
   * called ensuring that facets are built only once regardless of how many
   * times this method is called.
   *
   * @todo For clarity, should this method be named buildFacets()?
   */
  public function processFacets();

  /**
   * Update the facet results.
   *
   * Each facet should be updated with a list of Result objects.
   */
  public function updateResults();

  /**
   * Build the facets and get the render arrays for all facets.
   *
   * @param FacetInterface $facet
   *
   * @return array
   *   Facet render arrays.
   */
  public function build($facet);

  public function setResults($facet);

}
