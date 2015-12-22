<?php

/**
 * @file
 * Contains \Drupal\Tests\facets\Plugin\Processor\RawValueWidgetOrderProcessorTest.
 */

namespace Drupal\Tests\facets\Unit\Plugin\processor;

use Drupal\facets\Plugin\facets\processor\RawValueWidgetOrderProcessor;
use Drupal\facets\Result\Result;
use Drupal\Tests\UnitTestCase;

/**
 * Unit test for processor.
 *
 * @group facets
 */
class RawValueWidgetOrderProcessorTest extends UnitTestCase {

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
      new Result('C', 'thetans', 10),
      new Result('B', 'xenu', 5),
      new Result('A', 'Tom', 15),
      new Result('D', 'Hubbard', 666),
      new Result('E', 'FALSE', 1),
      new Result('G', '1977', 20),
      new Result('F', '2', 22),
    ];

    $this->processor = new RawValueWidgetOrderProcessor([], 'raw_value_widget_order', []);
  }

  /**
   * Test sorting ascending.
   */
  public function testAscending() {
    $sorted_results = $this->processor->sortResults($this->originalResults, 'ASC');
    $expected_values = [
      'Tom',
      'xenu',
      'thetans',
      'Hubbard',
      'FALSE',
      '2',
      '1977',
    ];
    foreach ($expected_values as $index => $value) {
      $this->assertEquals($value, $sorted_results[$index]->getDisplayValue());
    }
  }

  /**
   * Test sorting descending.
   */
  public function testDescending() {
    $sorted_results = $this->processor->sortResults($this->originalResults, 'DESC');
    $expected_values = array_reverse([
      'Tom',
      'xenu',
      'thetans',
      'Hubbard',
      'FALSE',
      '2',
      '1977',
    ]);
    foreach ($expected_values as $index => $value) {
      $this->assertEquals($value, $sorted_results[$index]->getDisplayValue());
    }
  }

}
