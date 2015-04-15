<?php
/**
 * @file
 * Contains \Drupal\Tests\facetapi\Plugin\Url\FacetUrlProcessorStandardTest.
 */

namespace Drupal\Tests\facetapi\Plugin\Adapter;

use Drupal\facetapi\Plugin\Adapter\AdapterBase;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\facetapi\Plugin\Url\FacetUrlProcessorStandard
 *
 * @group facetapi
 *
 */
class AdapterBaseTest extends UnitTestCase {

  /**
   * Stores the processor which is not tested here.
   *
   * @var \Drupal\facetapi\Plugin\Url\FacetUrlProcessorStandard|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $processor;

  /**
   * Stores the adapter under test.
   *
   * @var \Drupal\facetapi\Adapter\AdapterInterface
   */
  protected $adapter;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // @TODO: implement the setup.
//    // Create a mock for the URL to be returned.
//    $this->adapter = $this->getMock('Drupal\facetapi\Adapter\AdapterInterface');
    // Create the URL-Processor and set the mocked indexer.
//    $this->processor = $this->getMock('Drupal\facetapi\Ur');
  }

  public function testGetPageTotal() {
    // @TODO: implement more tests.
  }

}
