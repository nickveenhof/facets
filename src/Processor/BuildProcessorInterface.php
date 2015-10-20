<?php

/**
 * @file
 * Contains \Drupal\facetapi\Processor\BuildProcessorInterface.
 */
namespace Drupal\facetapi\Processor;


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
