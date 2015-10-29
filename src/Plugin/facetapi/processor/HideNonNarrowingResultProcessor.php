<?php
/**
 * @file
 * Contains \Drupal\facetapi\Plugin\facetapi\HideNonNarrowingResultProcessor.
 */

namespace Drupal\facetapi\Plugin\facetapi\processor;

use Drupal\Core\Form\FormStateInterface;
use Drupal\facetapi\FacetInterface;
use Drupal\facetapi\Processor\BuildProcessorInterface;
use Drupal\facetapi\Processor\ProcessorPluginBase;
use Drupal\facetapi\Result\Result;

/**
 * Provides a processor that hides results that don't narrow results.
 *
 * @FacetApiProcessor(
 *   id = "hide_non_narrowing_result_processor",
 *   label = @Translation("Hide non narrowing results"),
 *   description = @Translation("Do not display items that do not narrow results."),
 *   stages = {
 *     "build" = 40
 *   }
 * )
 */
class HideNonNarrowingResultProcessor extends ProcessorPluginBase implements BuildProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet, array $results) {
    $facetResults = $facet->getResults();
    $resultCount = 0;
    foreach ($facetResults as $result) {
      if ($result->isActive()) {
        $resultCount += $result->getCount();
      }
    }

    /** @var Result $result */
    foreach ($results as $id => $result) {
      if ($result->getCount() == $resultCount && !$result->isActive()) {
        unset($results[$id]);
      }
    }

    return $results;
  }

}
