<?php
/**
 * @file
 * Contains Drupal\facetapi\UrlProcessor\UrlProcessorInterface
 */

namespace Drupal\facetapi\UrlProcessor;

use Drupal\facetapi\FacetInterface;

/**
 * Interface UrlProcessorInterface
 *
 * The url processor takes care of retrieving facet information
 * from the url, and also handles the generation of facet links.
 *
 * A facetapi adapter has one url processor.
 *
 * @package Drupal\facetapi\UrlProcessor
 */
interface UrlProcessorInterface {

  /**
   * Add the uri's to all results of the facets.
   *
   * The facet knows which values are active or not.
   *
   * @param FacetInterface $facet
   *
   * @return mixed
   */
  public function addUriToResults(FacetInterface $facet);

  /**
   * Returns the filter key.
   *
   * @return string
   *   A string containing the filter key.
   */
  public function getFilterKey();

  /**
   * Process the facet.
   *
   * This method sets the active items in a facet.
   *
   * @param FacetInterface $facet
   * @return mixed
   */
  public function processFacet(FacetInterface $facet);

}
