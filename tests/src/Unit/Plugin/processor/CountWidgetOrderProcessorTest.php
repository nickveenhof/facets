<?php

/**
 * @file
 * Contains \Drupal\Tests\facets\Plugin\Processor\CountWidgetOrderProcessorTest.
 */

namespace Drupal\Tests\facets\Unit\Plugin\processor;

use Drupal\facets\Plugin\facets\processor\CountWidgetOrderProcessor;
use Drupal\facets\Result\Result;
use Drupal\Tests\UnitTestCase;

/**
 * Unit test for processor.
 *
 * @group facets
 */
class CountWidgetOrderProcessorTest extends UnitTestCase {

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
  protected $originalResults;

  /**
   * Creates a new processor object for use in the tests.
   */
  protected function setUp() {
    parent::setUp();

    $this->originalResults = [
      new Result('llama', 'llama', 10),
      new Result('badger', 'badger', 5),
      new Result('duck', 'duck', 15),
    ];

    $this->processor = new CountWidgetOrderProcessor([], 'count_widget_order', []);
  }

  /**
   * Test sorting ascending.
   */
  public function testAscending() {

    $sorted_results = $this->processor->sortResults($this->originalResults, 'ASC');

    $this->assertEquals(5, $sorted_results[0]->getCount());
    $this->assertEquals('badger', $sorted_results[0]->getDisplayValue());
    $this->assertEquals(10, $sorted_results[1]->getCount());
    $this->assertEquals('llama', $sorted_results[1]->getDisplayValue());
    $this->assertEquals(15, $sorted_results[2]->getCount());
    $this->assertEquals('duck', $sorted_results[2]->getDisplayValue());
  }

  /**
   * Test sorting descending.
   */
  public function testDescending() {

    $sorted_results = $this->processor->sortResults($this->originalResults, 'DESC');

    $this->assertEquals(15, $sorted_results[0]->getCount());
    $this->assertEquals('duck', $sorted_results[0]->getDisplayValue());
    $this->assertEquals(10, $sorted_results[1]->getCount());
    $this->assertEquals('llama', $sorted_results[1]->getDisplayValue());
    $this->assertEquals(5, $sorted_results[2]->getCount());
    $this->assertEquals('badger', $sorted_results[2]->getDisplayValue());
  }

}
