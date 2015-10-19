<?php
/**
 * @file
 * Contains \Drupal\Tests\facetapi\Plugin\Url\FacetUrlProcessorStandardTest.
 */

namespace Drupal\Tests\facetapi\Plugin\FacetManager;

use Drupal\facetapi\FacetManager\FacetManagerPluginBase;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\facetapi\Plugin\Url\FacetUrlProcessorStandard
 *
 * @group facetapi
 *
 */
class FacetManagerBaseTest extends UnitTestCase {

  /**
   * Stores the processor which is not tested here.
   *
   * @var \Drupal\facetapi\Plugin\Url\FacetUrlProcessorStandard|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $processor;

  /**
   * Stores the facet_manager under test.
   *
   * @var \Drupal\facetapi\FacetManager\FacetManagerInterface
   */
  protected $manager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // @TODO: implement the setup.
//    // Create a mock for the URL to be returned.
//    $this->facet_manager = $this->getMock('Drupal\facetapi\FacetManager\FacetManagerInterface');
    // Create the URL-Processor and set the mocked indexer.
//    $this->processor = $this->getMock('Drupal\facetapi\Ur');
  }

  public function testGetPageTotal() {
    // @TODO: implement more tests.
  }

}
