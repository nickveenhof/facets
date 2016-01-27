<?php

/**
 * @file
 * Contains \Drupal\Tests\facets\Plugin\widget\CheckboxWidgetTest.
 */

namespace Drupal\Tests\facets\Unit\Plugin\widget;

use Drupal\Core\Form\FormState;
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
   * @var \Drupal\facets\Plugin\facets\widget\CheckboxWidget
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

    $form_builder = $this->getMockBuilder('\Drupal\Core\Form\FormBuilder')
      ->disableOriginalConstructor()
      ->getMock();

    $string_translation = $this->getMockBuilder('\Drupal\Core\StringTranslation\TranslationManager')
      ->disableOriginalConstructor()
      ->getMock();

    $container_builder = new ContainerBuilder();
    $container_builder->set('form_builder', $form_builder);
    $container_builder->set('string_translation', $string_translation);
    \Drupal::setContainer($container_builder);

    $this->widget = new CheckboxWidget();
  }

  /**
   * Tests widget with default settings.
   */
  public function testDefaultSettings() {
    $facet = new Facet([], 'facet');
    $facet->setResults($this->originalResults);
    $facet->setFieldIdentifier('test_field');

    $form_state = new FormState();
    $form_state->addBuildInfo('args', [$facet]);
    $form = [];
    $built_form = $this->widget->buildForm($form, $form_state);

    $this->assertInternalType('array', $built_form);
    $this->assertCount(4, $built_form['test_field']['#options']);
    $this->assertEquals('checkboxes', $built_form['test_field']['#type']);

    $expected_links = [
      'llama' => 'Llama',
      'badger' => 'Badger',
      'duck' => 'Duck',
      'alpaca' => 'Alpaca',
    ];
    foreach ($expected_links as $index => $value) {
      $this->assertEquals($value, $built_form['test_field']['#options'][$index]);
    }
  }

  /**
   * Tests widget, make sure hiding and showing numbers works.
   */
  public function testHideNumbers() {
    $original_results = $this->originalResults;
    $original_results[1]->setActiveState(TRUE);

    $facet = new Facet([], 'facet');
    $facet->setResults($original_results);
    $facet->setFieldIdentifier('test__field');
    $facet->setWidgetConfigs(['show_numbers' => 0]);

    $form_state = new FormState();
    $form_state->addBuildInfo('args', [$facet]);
    $form = [];
    $built_form = $this->widget->buildForm($form, $form_state);

    $this->assertInternalType('array', $built_form);
    $this->assertCount(4, $built_form['test__field']['#options']);
    $expected_links = [
      'llama' => 'Llama',
      'badger' => 'Badger',
      'duck' => 'Duck',
      'alpaca' => 'Alpaca',
    ];
    foreach ($expected_links as $index => $value) {
      $this->assertEquals($value, $built_form['test__field']['#options'][$index]);
    }

    // Enable the 'show_numbers' setting again to make sure that the switch
    // between those settings works.
    $facet->setWidgetConfigs(['show_numbers' => 1]);

    $built_form = $this->widget->buildForm($form, $form_state);

    $this->assertInternalType('array', $built_form);
    $this->assertCount(4, $built_form['test__field']['#options']);

    $expected_links = [
      'llama' => 'Llama (10)',
      'badger' => 'Badger (20)',
      'duck' => 'Duck (15)',
      'alpaca' => 'Alpaca (9)',
    ];
    foreach ($expected_links as $index => $value) {
      $this->assertEquals($value, $built_form['test__field']['#options'][$index]);
    }
  }

}
