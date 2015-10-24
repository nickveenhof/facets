<?php

/**
 * @file
 * Contains \Drupal\facetapi\Processor\BuildProcessorInterface.
 */
namespace Drupal\facetapi\Processor;

use Drupal\facetapi\FacetInterface;


/**
 * Processor runs before the renderable array is created.
 */
interface BuildProcessorInterface extends ProcessorInterface {

  /**
   * Processor runs before the renderable array is created.
   *
   * @param \Drupal\facetapi\FacetInterface $facet
   * @param \Drupal\facetapi\Result\Result[] $results
   *
   * @return \Drupal\facetapi\Result\Result[] $results
   */
  public function build(FacetInterface $facet, array $results);

}
