<?php

/**
 * @file
 * Contains \Drupal\facetapi\Processor\BuildProcessorInterface.
 */

use \Drupal\facetapi\Processor\ProcessorInterface;
use Drupal\facetapi\Result\Result;

/**
 * Processor runs before the renderable array is created.
 */
interface BuildProcessorInterface extends ProcessorInterface {

  /**
   * Processor runs before the renderable array is created.
   *
   * @param \Drupal\facetapi\Result\Result[] $result
   */
  public function build(array $results);

}
