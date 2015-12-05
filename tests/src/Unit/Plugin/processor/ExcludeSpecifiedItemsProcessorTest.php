<?php
/**
 * @file
 * Contains \Drupal\Tests\facets\Plugin\Processor\ExcludeSpecifiedItemsProcessorTest.
 */

namespace Drupal\Tests\facets\Unit\Plugin\Processor;

use Drupal\facets\Entity\Facet;
use Drupal\facets\Plugin\facets\processor\ExcludeSpecifiedItemsProcessor;
use Drupal\facets\Result\Result;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @group facets
 */
class ExcludeSpecifiedItemsProcessorTest extends UnitTestCase {

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
      new Result('snbke', 'snbke', 10),
      new Result('snake', 'snake', 10),
      new Result('snaake', 'snaake', 10),
      new Result('snaaake', 'snaaake', 10),
      new Result('snaaaake', 'snaaaake', 10),
      new Result('snaaaaake', 'snaaaaake', 10),
      new Result('snaaaaaake', 'snaaaaaake', 10),
    ];

    $processor_id = 'exclude_specified_items';
    $this->processor = new ExcludeSpecifiedItemsProcessor([], $processor_id, []);

    $processorDefinitions = [
      $processor_id => [
        'id' => $processor_id,
        'class' => 'Drupal\facets\Plugin\facets\processor\ExcludeSpecifiedItemsProcessor',
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
      'exclude_specified_items' => [
        'settings' => [
          'exclude' => 'alpaca',
          'regex' => 0
        ],
      ],
    ]);
    $this->processor->setConfiguration([
      'exclude' => 'alpaca',
      'regex' => 0
    ]);
    $filtered_results = $this->processor->build($facet, $this->original_results);

    $this->assertCount(count($this->original_results), $filtered_results);
  }

  /**
   * Test filtering happens for string filter
   */
  public function testStringFilter() {
    $facet = new Facet([], 'facet');
    $facet->setResults($this->original_results);
    $facet->setOption('processors', [
      'exclude_specified_items' => [
        'settings' => [
          'exclude' => 'llama',
          'regex' => 0
        ],
      ],
    ]);
    $this->processor->setConfiguration([
      'exclude' => 'llama',
      'regex' => 0
    ]);
    $filtered_results = $this->processor->build($facet, $this->original_results);

    $this->assertCount((count($this->original_results) -1), $filtered_results);

    foreach ($filtered_results as $result) {
      $this->assertNotEquals('llama', $result->getDisplayValue());
    }
  }

  /**
   * Test filtering happens for regex filter
   *
   * @dataProvider provideRegexTests
   */
  public function testRegexFilter($regex, $expectedResults) {
    $facet = new Facet([], 'facet');
    $facet->setResults($this->original_results);
    $facet->setOption('processors', [
      'exclude_specified_items' => [
        'settings' => [
          'exclude' => $regex,
          'regex' => 1
        ],
      ],
    ]);
    $this->processor->setConfiguration([
      'exclude' => $regex,
      'regex' => 1
    ]);
    $filtered_results = $this->processor->build($facet, $this->original_results);

    $this->assertCount(count($expectedResults), $filtered_results);

    foreach ($filtered_results as $res) {
      $this->assertTrue(in_array($res->getDisplayValue(), $expectedResults));
    }
  }

  /**
   * Provide multiple data sets for ::testRegexFilter
   */
  public function provideRegexTests() {
    return [
      [
        'test',
        ['llama', 'duck', 'badger', 'snake', 'snaake', 'snaaake', 'snaaaake', 'snaaaaake', 'snaaaaaake', 'snbke']
      ],
      [
        'llama',
        ['badger', 'duck', 'snake', 'snaake', 'snaaake', 'snaaaake', 'snaaaaake', 'snaaaaaake', 'snbke']
      ],
      [
        'duck',
        ['llama', 'badger', 'snake', 'snaake', 'snaaake', 'snaaaake', 'snaaaaake', 'snaaaaaake', 'snbke']
      ],
      [
        'sn(.*)ke',
        ['llama', 'duck', 'badger']
      ],
      [
        'sn(a*)ke',
        ['llama', 'duck', 'badger', 'snbke']
      ],
      [
        'sn(a+)ke',
        ['llama', 'duck', 'badger', 'snbke']
      ],
      [
        'sn(a{3,5})ke',
        ['llama', 'duck', 'badger', 'snake', 'snaake', 'snaaaaaake', 'snbke']
      ],
    ];
  }
}
