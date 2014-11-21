<?php
/**
 * @file
 * Contains \Drupal\Tests\facet_api\Plugin\Url\FacetUrlProcessorStandardTest.
 */

namespace Drupal\Tests\facet_api\Plugin\Adapter;

use Drupal\facet_api\Plugin\Adapter\AdapterBase;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\facet_api\Plugin\Url\FacetUrlProcessorStandard
 *
 * @group facet_api
 *
 */
class AdapterBaseTest extends UnitTestCase {

  /**
   * Stores the processor which is not tested here.
   *
   * @var \Drupal\facet_api\Plugin\Url\FacetUrlProcessorStandard|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $processor;

  /**
   * Stores the adapter under test.
   *
   * @var \Drupal\facet_api\Adapter\AdapterInterface
   */
  protected $adapter;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // @TODO: implement the setup.
//    // Create a mock for the URL to be returned.
//    $this->adapter = $this->getMock('Drupal\facet_api\Adapter\AdapterInterface');
    // Create the URL-Processor and set the mocked indexer.
//    $this->processor = $this->getMock('Drupal\facet_api\Ur');
  }

  public function testGetPageTotal() {
    // @TODO: implement more tests.
  }

}
