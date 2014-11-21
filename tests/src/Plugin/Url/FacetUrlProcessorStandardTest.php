<?php
/**
 * @file
 * Contains \Drupal\Tests\facet_api\Plugin\Url\FacetUrlProcessorStandardTest.
 */

namespace Drupal\Tests\facet_api\Plugin\Url;

use Drupal\facet_api\Plugin\Url\FacetUrlProcessorStandard;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\facet_api\Plugin\Url\FacetUrlProcessorStandard
 *
 * @group facet_api
 *
 */
class FacetUrlProcessorStandardTest extends UnitTestCase {

  /**
   * Stores the processor under test.
   *
   * @var \Drupal\facet_api\Plugin\Url\FacetUrlProcessorStandard
   */
  protected $processor;

  /**
   * Stores the adapter which is not tested here.
   *
   * @var \Drupal\facet_api\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $adapter;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Create a mock for the URL to be returned.
    $this->adapter = $this->getMock('Drupal\facet_api\Adapter\AdapterInterface');
    // Create the URL-Processor and set the mocked indexer.
    $this->processor = new FacetUrlProcessorStandard($this->adapter);
  }

  /**
   * @covers ::normalizeParams
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

  /**
   * @covers ::getQueryString
   *
   * @dataProvider providerTestQueryString
   */
  public function testGetQueryString($active, $value_item) {
    $facet = array(
      'field alias' => $this->randomMachineName(),
      'name' => $this->randomMachineName(),
    );
    $values = array($this->randomMachineName(), $value_item);

    $active_items = array(
      'foo' => array(
        'field alias' => $this->randomMachineName(),
        'value' => array($this->randomMachineName()),
        'pos' => mt_rand(0,2),
      ),
    );
    $this->adapter->expects($this->atLeastOnce())
      ->method('getActiveItems')
      ->with($facet)
      ->willReturn($active_items);

    $settings = new \stdClass();
    $settings->settings = array(
      'limit_active_items' => (bool) mt_rand(0, 1),
    );


    $this->adapter->expects($active == 0 ? $this->atLeastOnce() : $this->never())
      ->method('getFacetSettingsGlobal')
      ->with($facet)
      ->willReturn($settings);

    $this->processor->setParams(array(
        $this->processor->getFilterKey() => array(
          $this->randomMachineName(),
          $this->randomMachineName(),
          $this->randomMachineName(),
        ),
      ));
    $this->processor->getQueryString($facet, $values, $active);

  }

  /**
   * @covers ::limitActiveItems
   */
  public function testLimitActiveItems() {
    $facet = array(
      'field alias' => $this->randomMachineName(),
      'name' => $this->randomMachineName(),
    );

    $settings = new \stdClass();
    $settings->settings = array(
      'limit_active_items' => (bool) mt_rand(0, 1),
    );

    $this->adapter->expects($this->atLeastOnce())
      ->method('getFacetSettingsGlobal')
      ->with($facet)
      ->willReturn($settings);

    $limit_active_items = $this->processor->limitActiveItems($facet);
    $this->assertEquals($settings->settings['limit_active_items'], $limit_active_items);

  }

  /**
   * Provides data for self::testGetQueryString().
   *
   * @return array
   */
  public function providerTestQueryString() {
    return array(
      array(0, 'foo'),
      array(1, 'foo'),
      array(0, 'bar'),
      array(1, 'bar'),
    );
  }

}
