<?php

/**
 * @file
 * Contains \Drupal\facetapi\Processor\WidgetOrderProcessorInterface.
 */

namespace Drupal\facetapi\Processor;

/**
 * Processor runs before the renderable array is created.
 */
interface WidgetOrderProcessorInterface extends BuildProcessorInterface {

  /**
   * Order results and return the new order of results
   *
   * @param \Drupal\facetapi\Result\Result[] $results
   *   An array containing results
   * @param string $order
   *   A string denoting the order in which we should sort, either 'ASC' or
   *   'DESC'
   *
   * @return array
   *   The same array that was passed in, ordered by  $order
   */
  public function sortResults(array $results, $order = 'ASC');

}
