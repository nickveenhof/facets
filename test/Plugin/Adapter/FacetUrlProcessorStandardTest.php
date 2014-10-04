<?php
/**
 * @file
 * Contains \Drupal\Tests\facetapi\Plugin\Url\FacetUrlProcessorStandardTest.
 */

namespace Drupal\Tests\facetapi\Plugin\Url;

use Drupal\facetapi\Plugin\Url\FacetUrlProcessorStandard;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the "URL field" processor.
 *
 * @group facetapi
 *
 * @see \Drupal\facetapi\Plugin\Url\FacetUrlProcessorStandard
 */
class FacetUrlProcessorStandardTest extends UnitTestCase {

  /**
   * Stores the processor to be tested.
   *
   * @var \Drupal\facetapi\Plugin\Url\FacetUrlProcessorStandard
   */
  protected $processor;

  /**
   * Stores the processor to be tested.
   *
   * @var \Drupal\facetapi\Adapter\AdapterInterface
   */
  protected $adapter;

  /**
   * Creates a new processor object for use in the tests.
   */
  protected function setUp() {
    parent::setUp();

    // Create a mock for the URL to be returned.
    $this->adapter = $this->getMock('Drupal\facetapi\Plugin\Adapter\AdapterInterface');
    // Create the URL-Processor and set the mocked indexer.
    $this->processor = new FacetUrlProcessorStandard($this->adapter);
  }

  /**
   * Tests processIndexItems.
   *
   * Check if the items are processed as expected.
   */
  public function testNormalizeParams() {
    // Process the items.
    $params = array('foo' => 'bar', 'bar' => 'baz', 'q' => 'testing', 'page' => 'blabla');
    $normalized_param = $this->processor->normalizeParams($params);
    $this->assertArrayNotHasKey('q', $normalized_param, 'q parameter was removed');
    $this->assertArrayNotHasKey('page', $normalized_param, 'page parameter was removed');
    $this->assertArrayHasKey('foo', $normalized_param, 'foo parameter is still there');
    $this->assertArrayHasKey('bar', $normalized_param, 'bar parameter is still there');
  }
}
