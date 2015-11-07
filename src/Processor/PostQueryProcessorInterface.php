<?php

/**
 * @file
 * Contains \Drupal\facetapi\Processor\PostQueryProcessorInterface.
 */
namespace Drupal\facetapi\Processor;


/**
 * Processor runs after the query was executed.
 */
interface PostQueryProcessorInterface extends ProcessorInterface {

  /**
   * Processor runs after the query was executed.
   *
   * Uses the query results and can alter those results, for example a
   * ValueCallbackProcessor.
   *
   * @param \Drupal\facetapi\Result\Result[] $results
   */
  public function postQuery(array $results);

}
