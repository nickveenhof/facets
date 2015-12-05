<?php

/**
 * @file
 * Contains \Drupal\Tests\facets\Plugin\Processor\ActiveWidgetOrderProcessorTest.
 */

namespace Drupal\Tests\facets\Unit\Plugin\Processor;

use Drupal\facets\Plugin\facets\processor\ActiveWidgetOrderProcessor;
use Drupal\facets\Result\Result;
use Drupal\Tests\UnitTestCase;

/**
 * @group facets
 */
class ActiveWidgetOrderProcessorTest extends UnitTestCase {

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

    /** @var \Drupal\facets\Result\Result[] $original_results */
    $original_results = [
      new Result('Boxer', 'Boxer', 10),
      new Result('Old Major', 'Old Major', 3),
      new Result('Minimus', 'Minimus', 60),
      new Result('Mr Whymper', 'Mr. Whymper', 1),
      new Result('Clover', 'Clover', 50),
    ];

    $original_results[1]->setActiveState(TRUE);
    $original_results[2]->setActiveState(TRUE);
    $original_results[3]->setActiveState(TRUE);

    $this->original_results = $original_results;

    $this->processor = new ActiveWidgetOrderProcessor([], 'active_widget_order', []);
  }

  /**
   * Test sorting ascending.
   */
  public function testAscending() {
    $sorted_results = $this->processor->sortResults($this->original_results, 'ASC');
    $expected_values = [TRUE, TRUE, TRUE, FALSE, FALSE];
    foreach ($expected_values as $index => $value) {
      $this->assertEquals($value, $sorted_results[$index]->isActive());
    }
  }

  /**
   * Test sorting descending.
   */
  public function testDescending() {
    $sorted_results = $this->processor->sortResults($this->original_results, 'DESC');
    $expected_values = array_reverse([TRUE, TRUE, TRUE, FALSE, FALSE]);
    foreach ($expected_values as $index => $value) {
      $this->assertEquals($value, $sorted_results[$index]->isActive());
    }
  }

}
