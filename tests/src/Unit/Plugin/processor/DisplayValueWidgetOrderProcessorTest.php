<?php

/**
 * @file
 * Contains \Drupal\Tests\facets\Plugin\Processor\DisplayValueWidgetOrderProcessorTest.
 */

namespace Drupal\Tests\facets\Unit\Plugin\Processor;

use Drupal\facets\Plugin\facets\processor\DisplayValueWidgetOrderProcessor;
use Drupal\facets\Result\Result;
use Drupal\Tests\UnitTestCase;

/**
 * @group facets
 */
class DisplayValueWidgetOrderProcessorTest extends UnitTestCase {

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
      new Result('thetans', 'thetans', 10),
      new Result('xenu', 'xenu', 5),
      new Result('Tom', 'Tom', 15),
      new Result('Hubbard', 'Hubbard', 666),
      new Result('FALSE', 'FALSE', 1),
      new Result('1977', '1977', 20),
      new Result('2', '2', 22),
    ];

    $this->processor = new DisplayValueWidgetOrderProcessor([], 'display_value_widget_order', []);
  }

  /**
   * Test sorting ascending.
   */
  public function testAscending() {
    $sorted_results = $this->processor->sortResults($this->original_results, 'ASC');
    $expected_values = ['2', '1977', 'FALSE', 'Hubbard', 'thetans', 'Tom', 'xenu'];
    foreach ($expected_values as $index => $value) {
      $this->assertEquals($value, $sorted_results[$index]->getDisplayValue());
    }
  }

  /**
   * Test sorting descending.
   */
  public function testDescending() {
    $sorted_results = $this->processor->sortResults($this->original_results, 'DESC');
    $expected_values = array_reverse(['2', '1977', 'FALSE', 'Hubbard', 'thetans', 'Tom', 'xenu']);
    foreach ($expected_values as $index => $value) {
      $this->assertEquals($value, $sorted_results[$index]->getDisplayValue());
    }
  }

  /**
   * Test that sorting uses the display value.
   */
  public function testUseActualDisplayValue() {
    $original = [
      new Result('bb_test', 'Test AA', 10),
      new Result('aa_test', 'Test BB', 10),
    ];

    $sorted_results = $this->processor->sortResults($original, 'DESC');

    $this->assertEquals('Test BB', $sorted_results[0]->getDisplayValue());
    $this->assertEquals('Test AA', $sorted_results[1]->getDisplayValue());
  }

}
