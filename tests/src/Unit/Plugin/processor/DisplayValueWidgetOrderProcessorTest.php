<?php

/**
 * @file
 * Contains \Drupal\Tests\facetapi\Plugin\Processor\DisplayValueWidgetOrderProcessorTest.
 */

namespace Drupal\Tests\facetapi\Unit\Plugin\Processor;

use Drupal\facetapi\Plugin\facetapi\processor\DisplayValueWidgetOrderProcessor;
use Drupal\facetapi\Processor\WidgetOrderProcessorInterface;
use Drupal\facetapi\Result\Result;
use Drupal\Tests\UnitTestCase;

/**
 * @group facetapi
 */
class DisplayValueWidgetOrderProcessorTest extends UnitTestCase {

  /**
   * The processor to be tested.
   *
   * @var WidgetOrderProcessorInterface
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
      new Result('thetans', 10),
      new Result('xenu', 5),
      new Result('Tom', 15),
      new Result('Hubbard', 666),
      new Result('FALSE', 1),
      new Result('1977', 20),
      new Result('2', 22),
    ];

    $this->processor = new DisplayValueWidgetOrderProcessor([], 'display_value_widget_order', []);
  }

  /**
   * Test sorting ascending.
   */
  public function testAscending() {
    $sorted_results = $this->processor->sortResults($this->original_results, 'ASC');
    $expected_values = array('2', '1977', 'FALSE', 'Hubbard', 'thetans', 'Tom', 'xenu');
    foreach ($expected_values as $index => $value) {
      $this->assertEquals($value, $sorted_results[$index]->getValue());
    }
  }

  /**
   * Test sorting descending.
   */
  public function testDescending() {
    $sorted_results = $this->processor->sortResults($this->original_results, 'DESC');
    $expected_values = array_reverse(array('2', '1977', 'FALSE', 'Hubbard', 'thetans', 'Tom', 'xenu'));
    foreach ($expected_values as $index => $value) {
      $this->assertEquals($value, $sorted_results[$index]->getValue());
    }
  }

}
