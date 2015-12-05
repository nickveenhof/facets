<?php

/**
 * @file
 * Contains \Drupal\facets\Processor\BuildProcessorInterface.
 */
namespace Drupal\facets\Processor;

use Drupal\facets\FacetInterface;


/**
 * Processor runs before the renderable array is created.
 */
interface BuildProcessorInterface extends ProcessorInterface {

  /**
   * Processor runs before the renderable array is created.
   *
   * @param \Drupal\facets\FacetInterface $facet
   * @param \Drupal\facets\Result\Result[] $results
   *
   * @return \Drupal\facets\Result\Result[] $results
   */
  public function build(FacetInterface $facet, array $results);

}
