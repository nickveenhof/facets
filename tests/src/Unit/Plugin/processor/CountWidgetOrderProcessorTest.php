<?php

/**
 * @file
 * Contains \Drupal\Tests\facetapi\Plugin\Processor\CountWidgetOrderProcessorTest.
 */

namespace Drupal\Tests\facetapi\Unit\Plugin\Processor;

use Drupal\facetapi\Plugin\facetapi\processor\CountWidgetOrderProcessor;
use Drupal\facetapi\Processor\WidgetOrderProcessorInterface;
use Drupal\facetapi\Result\Result;
use Drupal\Tests\UnitTestCase;

/**
 * @group facetapi
 */
class CountWidgetOrderProcessorTest extends UnitTestCase {

  /**
   * The processor to be tested.
   *
   * @var WidgetOrderProcessorInterface
   */
  protected $processor;

  /**
   * Creates a new processor object for use in the tests.
   */
  protected function setUp() {
    parent::setUp();

    $this->processor = new CountWidgetOrderProcessor();
  }

  /**
   * Test sorting ascending
   */
  public function testAscending() {
    $results = [
      new Result('llama', 10),
      new Result('badger', 5),
      new Result('duck', 15),
    ];

    $sorted_results = $this->processor->sortResults($results, 'ASC');

    $this->assertEquals(5, $sorted_results[0]->getCount());
    $this->assertEquals('badger', $sorted_results[0]->getValue());
    $this->assertEquals(10, $sorted_results[1]->getCount());
    $this->assertEquals('llama', $sorted_results[1]->getValue());
    $this->assertEquals(15, $sorted_results[2]->getCount());
    $this->assertEquals('duck', $sorted_results[2]->getValue());
  }

  public function testDescending() {
    $results = [
      new Result('llama', 10),
      new Result('badger', 5),
      new Result('duck', 15),
    ];

    /** @var Result[] $sorted_results */
    $sorted_results = $this->processor->sortResults($results, 'DESC');

    $this->assertEquals(15, $sorted_results[0]->getCount());
    $this->assertEquals('duck', $sorted_results[0]->getValue());
    $this->assertEquals(10, $sorted_results[1]->getCount());
    $this->assertEquals('llama', $sorted_results[1]->getValue());
    $this->assertEquals(5, $sorted_results[2]->getCount());
    $this->assertEquals('badger', $sorted_results[2]->getValue());
  }

}
