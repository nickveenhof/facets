<?php

/**
 * @file
 * Contains \Drupal\Tests\facetapi\Plugin\Processor\MinimumCountProcessorTest.
 */

namespace Drupal\Tests\facetapi\Unit\Plugin\Processor;

use Drupal\facetapi\Entity\Facet;
use Drupal\facetapi\Plugin\facetapi\processor\MinimumCountProcessor;
use Drupal\facetapi\Processor\BuildProcessorInterface;
use Drupal\facetapi\Result\Result;
use Drupal\Tests\UnitTestCase;

/**
 * @group facetapi
 */
class MinimumCountProcessorTest extends UnitTestCase {

  /**
   * The processor to be tested.
   *
   * @var BuildProcessorInterface
   */
  protected $processor;

  /**
   * An array containing the results before the processor has ran.
   *
   * @var \Drupal\facetapi\Result\Result[]
   */
  protected $original_results;

  /**
   * Creates a new processor object for use in the tests.
   */
  protected function setUp() {
    parent::setUp();

    $this->original_results = [
      new Result('llama', 'llama', 10),
      new Result('badger', 'badger', 5),
      new Result('duck', 'duck', 15),
    ];

    $this->processor = new MinimumCountProcessor([], 'minimum_count', []);
  }

  /**
   * Test filtering of results
   */
  public function testFilterResults() {

    $facet = new Facet([], 'facet');
    $facet->setResults($this->original_results);
    $facet->setProcessorConfigs([
      'minimum_count' => [
        'settings' => ['minimum_items' => 6]
      ]
    ]);

    $sorted_results = $this->processor->build($facet, $this->original_results);

    $this->assertCount(2, $sorted_results);

    $this->assertEquals(10, $sorted_results[0]->getCount());
    $this->assertEquals('llama', $sorted_results[0]->getDisplayValue());
    $this->assertEquals(15, $sorted_results[2]->getCount());
    $this->assertEquals('duck', $sorted_results[2]->getDisplayValue());
  }

}
