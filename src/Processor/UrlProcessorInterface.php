<?php
/**
 * @file
 * Contains Drupal\facetapi\Processor\UrlProcessorInterface.
 */

namespace Drupal\facetapi\Processor;

/**
 * Interface UrlProcessorInterface.
 *
 * The url processor takes care of retrieving facet information
 * from the url, and also handles the generation of facet links.
 *
 * A facetapi facet_manager has one url processor.
 *
 * @package Drupal\facetapi\UrlProcessor
 */
interface UrlProcessorInterface extends PreQueryProcessorInterface, BuildProcessorInterface {

  /**
   * Returns the filter key.
   *
   * @return string
   *   A string containing the filter key.
   */
  public function getFilterKey();

}
