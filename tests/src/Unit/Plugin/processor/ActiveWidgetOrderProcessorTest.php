<?php

/**
 * @file
 * Contains \Drupal\Tests\facetapi\Plugin\Processor\ActiveWidgetOrderProcessorTest.
 */

namespace Drupal\Tests\facetapi\Unit\Plugin\Processor;

use Drupal\facetapi\Plugin\facetapi\processor\ActiveWidgetOrderProcessor;
use Drupal\facetapi\Result\Result;
use Drupal\Tests\UnitTestCase;

/**
 * @group facetapi
 */
class ActiveWidgetOrderProcessorTest extends UnitTestCase {

  /**
   * The processor to be tested.
   *
   * @var \Drupal\facetapi\processor\WidgetOrderProcessorInterface
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

    /** @var \Drupal\facetapi\Result\Result[] $original_results */
    $original_results = [
      new Result('Boxer', 'Boxer', 10),
      new Result('Old Major', 'Old Major', 3),
      new Result('Minimus', 'Minimus', 60),
      new Result('Mr Whymper', 'Mr. Whymper', 1),
      new Result('Clover', 'Clover', 50),
    ];

    $original_results[1]->setActiveState(true);
    $original_results[2]->setActiveState(true);
    $original_results[3]->setActiveState(true);

    $this->original_results = $original_results;

    $this->processor = new ActiveWidgetOrderProcessor([], 'active_widget_order', []);
  }

  /**
   * Test sorting ascending.
   */
  public function testAscending() {
    $sorted_results = $this->processor->sortResults($this->original_results, 'ASC');
    $expected_values = [true, true, true, false, false];
    foreach ($expected_values as $index => $value) {
      $this->assertEquals($value, $sorted_results[$index]->isActive());
    }
  }

  /**
   * Test sorting descending.
   */
  public function testDescending() {
    $sorted_results = $this->processor->sortResults($this->original_results, 'DESC');
    $expected_values = array_reverse([true, true, true, false, false]);
    foreach ($expected_values as $index => $value) {
      $this->assertEquals($value, $sorted_results[$index]->isActive());
    }
  }

}
