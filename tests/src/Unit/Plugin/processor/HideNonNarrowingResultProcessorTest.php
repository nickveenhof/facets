<?php

/**
 * @file
 * Contains \Drupal\Tests\facets\Plugin\Processor\HideNonNarrowingResultProcessorTest.
 */

namespace Drupal\Tests\facets\Unit\Plugin\Processor;

use Drupal\facets\Entity\Facet;
use Drupal\facets\Plugin\facets\processor\HideNonNarrowingResultProcessor;
use Drupal\facets\Result\Result;
use Drupal\Tests\UnitTestCase;

/**
 * @group facets
 */
class HideNonNarrowingResultProcessorTest extends UnitTestCase {

  /**
   * The processor to be tested.
   *
   * @var \Drupal\facets\processor\WidgetOrderProcessorInterface
   */
  protected $processor;

  /**
   * An array containing the results before the processor has ran.
   *
   * @var \Drupal\facets\Result\Result[]
   */
  protected $original_results;

  /**
   * Creates a new processor object for use in the tests.
   */
  protected function setUp() {
    parent::setUp();

    $this->original_results = [
      new Result('llama', 'llama', 10),
      new Result('badger', 'badger', 15),
      new Result('duck', 'duck', 15),
    ];

    $this->processor = new HideNonNarrowingResultProcessor([], 'hide_non_narrowing_result_processor', []);
  }


  /**
   * Test filtering of results.
   */
  public function testNoFilterResults() {

    $facet = new Facet([], 'facet');
    $facet->setResults($this->original_results);

    $filtered_results = $this->processor->build($facet, $this->original_results);

    $this->assertCount(3, $filtered_results);

    $this->assertEquals(10, $filtered_results[0]->getCount());
    $this->assertEquals('llama', $filtered_results[0]->getDisplayValue());
    $this->assertEquals(15, $filtered_results[1]->getCount());
    $this->assertEquals('badger', $filtered_results[1]->getDisplayValue());
    $this->assertEquals(15, $filtered_results[2]->getCount());
    $this->assertEquals('duck', $filtered_results[2]->getDisplayValue());
  }

  /**
   * Test filtering of results.
   */
  public function testFilterResults() {

    $results = $this->original_results;
    $results[2]->setActiveState(TRUE);

    $facet = new Facet([], 'facet');
    $facet->setResults($results);

    $filtered_results = $this->processor->build($facet, $results);

    $this->assertCount(2, $filtered_results);

    // Llama is shown because it narrows results.
    $this->assertEquals(10, $filtered_results[0]->getCount());
    $this->assertEquals('llama', $filtered_results[0]->getDisplayValue());

    // Duck is shown because it's already active.
    $this->assertEquals(15, $filtered_results[2]->getCount());
    $this->assertEquals('duck', $filtered_results[2]->getDisplayValue());
  }

}
