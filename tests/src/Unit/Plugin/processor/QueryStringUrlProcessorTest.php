<?php

/**
 * @file
 * Contains \Drupal\Tests\facetapi\Plugin\Processor\QueryStringUrlProcessorTest.
 */

namespace Drupal\Tests\facetapi\Unit\Plugin\Processor;

use Drupal\facetapi\Entity\Facet;
use Drupal\facetapi\Plugin\facetapi\processor\QueryStringUrlProcessor;
use Drupal\facetapi\Result\Result;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group facetapi
 */
class QueryStringUrlProcessorTest extends UnitTestCase {

  /**
   * The processor to be tested.
   *
   * @var \Drupal\facetapi\Plugin\facetapi\processor\QueryStringUrlProcessor
   */
  protected $processor;

  /**
   * An array containing the results before the processor has ran.
   *
   * @var \Drupal\facetapi\Result\Result[]
   */
  protected $original_results;

  /**
   * A mocked config storage stub.
   */
  protected $config;

  /**
   * Creates a new processor object for use in the tests.
   */
  protected function setUp() {
    parent::setUp();

    $this->original_results = [
      new Result('llama', 'Llama', 15),
      new Result('badger', 'Badger', 5),
      new Result('mushroom', 'Mushroom', 5),
      new Result('duck', 'Duck', 15),
      new Result('alpaca', 'Alpaca', 25),
    ];

    $this->config = $this->getConfigFactoryStub(['facetapi.facet_source' => ['filter_key' => 'f']]);
  }

  public function testSetSingleActiveItem() {
    $facet = new Facet([], 'facet');
    $facet->setResults($this->original_results);
    $facet->setFieldIdentifier('test');

    $request = new Request;
    $request->query->set('f', ['test:badger']);

    $this->processor = new QueryStringUrlProcessor([], 'query_string', [], $request, $this->config);
    $this->processor->preQuery($facet);

    $this->assertEquals(['badger'], $facet->getActiveItems());
  }

  public function testSetMultipleActiveItems() {
    $facet = new Facet([], 'facet');
    $facet->setResults($this->original_results);
    $facet->setFieldIdentifier('test');

    $request = new Request;
    $request->query->set('f', ['test:badger', 'test:mushroom', 'donkey:kong']);

    $this->processor = new QueryStringUrlProcessor([], 'query_string', [], $request, $this->config);
    $this->processor->preQuery($facet);

    $this->assertEquals(['badger', 'mushroom'], $facet->getActiveItems());
  }

  public function testSetActiveWithConfigOverride() {
    $facet = new Facet([], 'facet');
    $facet->setResults($this->original_results);
    $facet->setFieldIdentifier('test');

    $config = $this->getConfigFactoryStub(['facetapi.facet_source' => ['filter_key' => 'g']]);

    $request = new Request;
    $request->query->set('f', ['test:badger', 'test:mushroom', 'donkey:kong']);

    $this->processor = new QueryStringUrlProcessor([], 'query_string', [], $request, $config);
    $this->processor->preQuery($facet);


    // Configuration was overridden but the request was still using the old 'f'
    // parameter. This means there are no active items found in the request.
    $this->assertEquals([], $facet->getActiveItems());

    $request = new Request;
    $request->query->set('g', ['test:badger', 'test:mushroom', 'donkey:kong']);

    $this->processor = new QueryStringUrlProcessor([], 'query_string', [], $request, $config);
    $this->processor->preQuery($facet);

    // Now that the request also uses 'g' as query parameter, we expect the same
    // response as ::testSetMultipleActiveItems expects.
    $this->assertEquals(['badger', 'mushroom'], $facet->getActiveItems());
  }

  public function testEmptyBuild() {
    $facet = new Facet([], 'facet');

    $request = new Request;
    $request->query->set('f', []);

    $this->processor = new QueryStringUrlProcessor([], 'query_string', [], $request, $this->config);
    $results = $this->processor->build($facet, []);
    $this->assertEmpty($results);
  }

  public function testBuild() {
    $facet = new Facet([], 'facet');
    $facet->setFieldIdentifier('test');

    $request = new Request;
    $request->query->set('f', []);

    $this->setRouter();

    $this->processor = new QueryStringUrlProcessor([], 'query_string', [], $request, $this->config);
    $results = $this->processor->build($facet, $this->original_results);

    /** @var \Drupal\facetapi\Result\ResultInterface $r */
    foreach ($results as $r) {
      $this->assertInstanceOf('\Drupal\facetapi\Result\ResultInterface', $r);
      $this->assertEquals('route:test?f[0]=test%3A' . $r->getRawValue(), $r->getUrl()->toUriString());
    }
  }

  public function testBuildWithActiveItem() {
    $facet = new Facet([], 'facet');
    $facet->setFieldIdentifier('test');

    $original_results = $this->original_results;
    $original_results[2]->setActiveState(TRUE);

    $request = new Request;
    $request->query->set('f', ['king:kong']);

    $this->setRouter();

    $this->processor = new QueryStringUrlProcessor([], 'query_string', [], $request, $this->config);
    $results = $this->processor->build($facet, $original_results);

    /** @var \Drupal\facetapi\Result\ResultInterface $r */
    foreach ($results as $k => $r) {
      $this->assertInstanceOf('\Drupal\facetapi\Result\ResultInterface', $r);
      if ($k === 2) {
        $this->assertEquals('route:test?f[0]=king%3Akong', $r->getUrl()->toUriString());
      }
      else {
        $this->assertEquals('route:test?f[0]=king%3Akong&f[1]=test%3A' . $r->getRawValue(), $r->getUrl()->toUriString());
      }
    }
  }

  protected function setRouter() {
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

    $container = new ContainerBuilder();
    $container->set('router.no_access_checks', $router);
    \Drupal::setContainer($container);
  }

}
