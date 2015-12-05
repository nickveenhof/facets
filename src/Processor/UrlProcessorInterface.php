<?php
/**
 * @file
 * Contains Drupal\facets\Processor\UrlProcessorInterface.
 */

namespace Drupal\facets\Processor;

/**
 * Interface UrlProcessorInterface.
 *
 * The url processor takes care of retrieving facet information from the url.
 * It also handles the generation of facet links. This extends the pre query and
 * build processor interfaces, those methods are where the bulk of the work
 * should be done.
 *
 * The facet manager has one url processor.
 *
 * @package Drupal\facets\UrlProcessor
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
