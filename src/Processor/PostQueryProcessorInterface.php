<?php

/**
 * @file
 * Contains \Drupal\facets\Processor\PostQueryProcessorInterface.
 */
namespace Drupal\facets\Processor;


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
   * @param \Drupal\facets\Result\Result[] $results
   */
  public function postQuery(array $results);

}
