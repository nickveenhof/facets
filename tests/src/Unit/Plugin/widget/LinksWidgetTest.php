<?php

/**
 * @file
 * Contains \Drupal\Tests\facets\Plugin\widget\LinksWidgetTest.
 */

namespace Drupal\Tests\facets\Unit\Plugin\widget;

use Drupal\facets\Entity\Facet;
use Drupal\facets\Plugin\facets\widget\LinksWidget;
use Drupal\facets\Result\Result;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @group facets
 */
class LinksWidgetTest extends UnitTestCase {

  /**
   * The processor to be tested.
   *
   * @var \drupal\facets\Widget\WidgetInterface
   */
  protected $widget;

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

    /** @var \Drupal\facets\Result\Result[] $original_results */
    $original_results = [
      new Result('llama', 'Llama', 10),
      new Result('badger', 'Badger', 20),
      new Result('duck', 'Duck', 15),
      new Result('alpaca', 'Alpaca', 9),
    ];

    foreach ($original_results as $original_result) {
      $original_result->setUrl(new \Drupal\Core\Url('test'));
    }
    $this->original_results = $original_results;

    $link_generator = $this->getMockBuilder('\Drupal\Core\Utility\LinkGenerator')
      ->disableOriginalConstructor()
      ->getMock();
    $link_generator->expects($this->atLeastOnce())
      ->method('generate')
      ->will($this->returnArgument(0));

    $container_builder = new ContainerBuilder();
    $container_builder->set('link_generator', $link_generator);
    \Drupal::setContainer($container_builder);

    $this->widget = new LinksWidget();
  }

  /**
   * Test widget
   */
  public function testNoFilterResults() {
    $facet = new Facet([], 'facet');
    $facet->setResults($this->original_results);
    $facet->set('widget_configs', ['show_numbers' => 1]);

    $output = $this->widget->build($facet);

    $this->assertInternalType('array', $output);
    $this->assertCount(4, $output['#items']);

    $expected_links = ['Llama (10)', 'Badger (20)', 'Duck (15)', 'Alpaca (9)'];
    foreach ($expected_links as $index => $value) {
      $this->assertEquals($value, $output['#items'][$index]);
    }
  }

  /**
   * Test widget
   */
  public function testHideEmptyCount() {
    $original_results = $this->original_results;
    $original_results[1] = new Result('badger', 'Badger', 0);

    $facet = new Facet([], 'facet');
    $facet->setResults($original_results);
    $facet->set('widget_configs', ['show_numbers' => 1]);

    $output = $this->widget->build($facet);

    $this->assertInternalType('array', $output);
    $this->assertCount(3, $output['#items']);

    $expected_links = ['Llama (10)', 'Duck (15)', 'Alpaca (9)'];
    foreach ($expected_links as $index => $value) {
      $this->assertEquals($value, $output['#items'][$index]);
    }
  }

  /**
   * Test widget
   */
  public function testActiveItems() {
    $original_results = $this->original_results;
    $original_results[0]->setActiveState(TRUE);
    $original_results[3]->setActiveState(TRUE);

    $facet = new Facet([], 'facet');
    $facet->setResults($original_results);
    $facet->set('widget_configs', ['show_numbers' => 1]);

    $output = $this->widget->build($facet);

    $this->assertInternalType('array', $output);
    $this->assertCount(4, $output['#items']);

    $expected_links = ['(-) Llama (10)', 'Badger (20)', 'Duck (15)', '(-) Alpaca (9)'];
    foreach ($expected_links as $index => $value) {
      $this->assertEquals($value, $output['#items'][$index]);
    }
  }

  /**
   * Test widget, make sure hiding and showing numbers works.
   */
  public function testHideNumbers() {
    $original_results = $this->original_results;
    $original_results[1]->setActiveState(TRUE);

    $facet = new Facet([], 'facet');
    $facet->setResults($original_results);
    $facet->set('widget_configs', ['show_numbers' => 0]);

    $output = $this->widget->build($facet);

    $this->assertInternalType('array', $output);
    $this->assertCount(4, $output['#items']);

    $expected_links = ['Llama', '(-) Badger', 'Duck', 'Alpaca'];
    foreach ($expected_links as $index => $value) {
      $this->assertEquals($value, $output['#items'][$index]);
    }

    // Enable the 'show_numbers' setting again to make sure that the switch
    // between those settings works.
    $facet->set('widget_configs', ['show_numbers' => 1]);

    $output = $this->widget->build($facet);

    $this->assertInternalType('array', $output);
    $this->assertCount(4, $output['#items']);

    $expected_links = ['Llama (10)', '(-) Badger (20)', 'Duck (15)', 'Alpaca (9)'];
    foreach ($expected_links as $index => $value) {
      $this->assertEquals($value, $output['#items'][$index]);
    }
  }

}

