<?php

/**
 * @file
 * Contains \Drupal\Tests\facets\Plugin\Processor\MinimumCountProcessorTest.
 */

namespace Drupal\Tests\facets\Unit\Plugin\Processor;

use Drupal\facets\Entity\Facet;
use Drupal\facets\Plugin\facets\processor\MinimumCountProcessor;
use Drupal\facets\Result\Result;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @group facets
 */
class MinimumCountProcessorTest extends UnitTestCase {

  /**
   * The processor to be tested.
   *
   * @var \Drupal\facets\processor\BuildProcessorInterface
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
      new Result('llama', 'llama', 10),
      new Result('badger', 'badger', 5),
      new Result('duck', 'duck', 15),
    ];

    $processor_id = 'minimum_count';
    $this->processor = new MinimumCountProcessor([], $processor_id, []);

    $processorDefinitions = [
      $processor_id => [
        'id' => $processor_id,
        'class' => 'Drupal\facets\Plugin\facets\processor\MinimumCountProcessor',
      ],
    ];

    $manager = $this->getMockBuilder('Drupal\facets\Processor\ProcessorPluginManager')
      ->disableOriginalConstructor()
      ->getMock();
    $manager->expects($this->once())
      ->method('getDefinitions')
      ->willReturn($processorDefinitions);
    $manager->expects($this->once())
      ->method('createInstance')
      ->willReturn($this->processor);

    $container_builder = new ContainerBuilder();
    $container_builder->set('plugin.manager.facets.processor', $manager);
    \Drupal::setContainer($container_builder);

  }

  /**
   * Test no filtering happens.
   */
  public function testNoFilter() {

    $facet = new Facet([], 'facet');
    $facet->setResults($this->original_results);
    $facet->setOption('processors', [
      'minimum_count' => [
        'settings' => ['minimum_items' => 4],
      ],
    ]);
    $this->processor->setConfiguration(['minimum_items' => 4]);
    $sorted_results = $this->processor->build($facet, $this->original_results);

    $this->assertCount(3, $sorted_results);

    $this->assertEquals('llama', $sorted_results[0]->getDisplayValue());
    $this->assertEquals('badger', $sorted_results[1]->getDisplayValue());
    $this->assertEquals('duck', $sorted_results[2]->getDisplayValue());
  }

  /**
   * Test no filtering happens.
   */
  public function testMinEqualsValue() {

    $facet = new Facet([], 'facet');
    $facet->setResults($this->original_results);
    $facet->setOption('processors', [
      'minimum_count' => [
        'settings' => ['minimum_items' => 5],
      ],
    ]);
    $this->processor->setConfiguration(['minimum_items' => 5]);

    $sorted_results = $this->processor->build($facet, $this->original_results);

    $this->assertCount(3, $sorted_results);

    $this->assertEquals('llama', $sorted_results[0]->getDisplayValue());
    $this->assertEquals('badger', $sorted_results[1]->getDisplayValue());
    $this->assertEquals('duck', $sorted_results[2]->getDisplayValue());
  }

  /**
   * Test filtering of results.
   */
  public function testFilterResults() {

    $facet = new Facet([], 'facet');
    $facet->setResults($this->original_results);
    $facet->setOption('processors', [
      'minimum_count' => [
        'settings' => ['minimum_items' => 8],
      ],
    ]);
    $this->processor->setConfiguration(['minimum_items' => 8]);

    $sorted_results = $this->processor->build($facet, $this->original_results);

    $this->assertCount(2, $sorted_results);

    $this->assertEquals('llama', $sorted_results[0]->getDisplayValue());
    $this->assertEquals('duck', $sorted_results[2]->getDisplayValue());
  }

}
