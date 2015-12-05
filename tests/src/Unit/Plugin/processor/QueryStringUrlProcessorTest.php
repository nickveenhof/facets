<?php

/**
 * @file
 * Contains \Drupal\Tests\facets\Plugin\Processor\QueryStringUrlProcessorTest.
 */

namespace Drupal\Tests\facets\Unit\Plugin\Processor;

use Drupal\facets\Entity\Facet;
use Drupal\facets\Plugin\facets\processor\QueryStringUrlProcessor;
use Drupal\facets\Result\Result;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * Unit test for processor.
 *
 * @group facets
 */
class QueryStringUrlProcessorTest extends UnitTestCase {

  /**
   * The processor to be tested.
   *
   * @var \Drupal\facets\Plugin\facets\processor\QueryStringUrlProcessor
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
      new Result('llama', 'Llama', 15),
      new Result('badger', 'Badger', 5),
      new Result('mushroom', 'Mushroom', 5),
      new Result('duck', 'Duck', 15),
      new Result('alpaca', 'Alpaca', 25),
    ];
  }

  /**
   * Basic test with one active item.
   */
  public function testSetSingleActiveItem() {
    $facet = new Facet([], 'facet');
    $facet->setResults($this->originalResults);
    $facet->setFieldIdentifier('test');

    $request = new Request();
    $request->query->set('f', ['test:badger']);

    $this->processor = new QueryStringUrlProcessor([], 'query_string', [], $request);
    $this->processor->preQuery($facet);

    $this->assertEquals(['badger'], $facet->getActiveItems());
  }

  /**
   * Basic test with multiple active items.
   */
  public function testSetMultipleActiveItems() {
    $facet = new Facet([], 'facet');
    $facet->setResults($this->originalResults);
    $facet->setFieldIdentifier('test');

    $request = new Request();
    $request->query->set('f', ['test:badger', 'test:mushroom', 'donkey:kong']);

    $this->processor = new QueryStringUrlProcessor([], 'query_string', [], $request);
    $this->processor->preQuery($facet);

    $this->assertEquals(['badger', 'mushroom'], $facet->getActiveItems());
  }

  /**
   * Basic test with an empty build.
   */
  public function testEmptyBuild() {
    $facet = new Facet([], 'facet');
    $facet->setFacetSourceId('facet_source__dummy');

    $request = new Request();
    $request->query->set('f', []);

    $this->processor = new QueryStringUrlProcessor([], 'query_string', [], $request);
    $results = $this->processor->build($facet, []);
    $this->assertEmpty($results);
  }

  /**
   * Test with default build.
   */
  public function testBuild() {
    $facet = new Facet([], 'facet');
    $facet->setFieldIdentifier('test');
    $facet->setFacetSourceId('facet_source__dummy');

    $request = new Request();
    $request->query->set('f', []);

    $this->setContainer();

    $this->processor = new QueryStringUrlProcessor([], 'query_string', [], $request);
    $results = $this->processor->build($facet, $this->originalResults);

    /** @var \Drupal\facets\Result\ResultInterface $r */
    foreach ($results as $r) {
      $this->assertInstanceOf('\Drupal\facets\Result\ResultInterface', $r);
      $this->assertEquals('route:test?f[0]=test%3A' . $r->getRawValue(), $r->getUrl()->toUriString());
    }
  }

  /**
   * Test with an active item already from url.
   */
  public function testBuildWithActiveItem() {
    $facet = new Facet([], 'facet');
    $facet->setFieldIdentifier('test');
    $facet->setFacetSourceId('facet_source__dummy');

    $original_results = $this->originalResults;
    $original_results[2]->setActiveState(TRUE);

    $request = new Request();
    $request->query->set('f', ['king:kong']);

    $this->setContainer();

    $this->processor = new QueryStringUrlProcessor([], 'query_string', [], $request);
    $results = $this->processor->build($facet, $original_results);

    /** @var \Drupal\facets\Result\ResultInterface $r */
    foreach ($results as $k => $r) {
      $this->assertInstanceOf('\Drupal\facets\Result\ResultInterface', $r);
      if ($k === 2) {
        $this->assertEquals('route:test?f[0]=king%3Akong', $r->getUrl()->toUriString());
      }
      else {
        $this->assertEquals('route:test?f[0]=king%3Akong&f[1]=test%3A' . $r->getRawValue(), $r->getUrl()->toUriString());
      }
    }
  }

  /**
   * Set the container for use in unit tests.
   */
  protected function setContainer() {
    $router = $this->getMockBuilder('Drupal\Tests\Core\Routing\TestRouterInterface')
      ->disableOriginalConstructor()
      ->getMock();
    $router->expects($this->any())
      ->method('matchRequest')
      ->willReturn(
        [
          '_raw_variables' => new ParameterBag([]),
          '_route' => 'test',
        ]
      );

    $fsi = $this->getMockBuilder('\Drupal\facets\FacetSource\FacetSourceInterface')
      ->disableOriginalConstructor()
      ->getMock();
    $fsi->method('getPath')
      ->willReturn('search/test');

    $manager = $this->getMockBuilder('Drupal\facets\FacetSource\FacetSourcePluginManager')
      ->disableOriginalConstructor()
      ->getMock();
    $manager->method('createInstance')
      ->willReturn($fsi);

    $container = new ContainerBuilder();
    $container->set('router.no_access_checks', $router);
    $container->set('plugin.manager.facets.facet_source', $manager);
    \Drupal::setContainer($container);
  }

}
