<?php

/**
 * @file
 * Contains \Drupal\Tests\facets\Plugin\widget\CheckboxWidgetTest.
 */

namespace Drupal\Tests\facets\Unit\Plugin\widget;

use Drupal\facets\Entity\Facet;
use Drupal\facets\Plugin\facets\widget\CheckboxWidget;
use Drupal\facets\Result\Result;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Unit test for widget.
 *
 * @group facets
 */
class CheckboxWidgetTest extends UnitTestCase {

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
  protected $originalResults;

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
    $this->originalResults = $original_results;

    $link_generator = $this->getMockBuilder('\Drupal\Core\Utility\LinkGenerator')
      ->disableOriginalConstructor()
      ->getMock();
    $link_generator->expects($this->atLeastOnce())
      ->method('generate')
      ->will($this->returnArgument(0));

    $string_translation = $this->getMockBuilder('\Drupal\Core\StringTranslation\TranslationManager')
      ->disableOriginalConstructor()
      ->getMock();

    $container_builder = new ContainerBuilder();
    $container_builder->set('link_generator', $link_generator);
    $container_builder->set('string_translation', $string_translation);
    \Drupal::setContainer($container_builder);

    $this->widget = new CheckboxWidget();
  }

  /**
   * Test widget with default settings.
   */
  public function testDefaultSettings() {
    $facet = new Facet([], 'facet');
    $facet->setResults($this->originalResults);

    $output = $this->widget->build($facet);

    $this->assertInternalType('array', $output);
    $this->assertCount(4, $output['#items']);

    $expected_links = ['Llama', 'Badger', 'Duck', 'Alpaca'];
    foreach ($expected_links as $index => $value) {
      $this->assertEquals($value, $output['#items'][$index]);
    }
  }

  /**
   * Test widget with show numbers enabled.
   */
  public function testShowAmount() {
    $facet = new Facet([], 'facet');
    $facet->setResults($this->originalResults);
    $facet->set('widget_configs', ['show_numbers' => 1]);

    $output = $this->widget->build($facet);

    $this->assertInternalType('array', $output);
    $this->assertCount(4, $output['#items']);

    $expected_links = ['Llama (10)', 'Badger (20)', 'Duck (15)', 'Alpaca (9)'];
    foreach ($expected_links as $index => $value) {
      $this->assertEquals($value, $output['#items'][$index]);
    }
  }

}
