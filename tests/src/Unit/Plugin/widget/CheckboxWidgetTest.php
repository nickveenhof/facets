<?php

/**
 * @file
 * Contains \Drupal\Tests\facetapi\Plugin\widget\CheckboxWidgetTest.
 */

namespace Drupal\Tests\facetapi\Unit\Plugin\widget;

use Drupal\facetapi\Entity\Facet;
use Drupal\facetapi\Plugin\facetapi\Widget\CheckboxWidget;
use Drupal\facetapi\Result\Result;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @group facetapi
 */
class CheckboxWidgetTest extends UnitTestCase {

  /**
   * The processor to be tested.
   *
   * @var \drupal\facetapi\Widget\WidgetInterface
   */
  protected $widget;

  /**
   * An array containing the results before the processor has ran.
   *
   * @var \Drupal\facetapi\Result\Result[]
   */
  protected $original_results;

  /**
   * Creates a new processor object for use in the tests.
   */
  protected function setUp() {
    parent::setUp();

    /** @var \Drupal\facetapi\Result\Result[] $original_results */
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
   * Test widget
   */
  public function testNoFilterResults() {
    $facet = new Facet([], 'facet');
    $facet->setResults($this->original_results);

    $output = $this->widget->build($facet);

    $this->assertInternalType('array', $output);
    $this->assertCount(4, $output['#items']);

    $expected_links = ['Llama (10)', 'Badger (20)', 'Duck (15)', 'Alpaca (9)'];
    foreach ($expected_links as $index => $value) {
      $this->assertEquals($value, $output['#items'][$index]);
    }
  }

}
