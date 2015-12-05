<?php
/**
 * @file
 * Tests Facets' FacetManager implementation.
 */

namespace Drupal\facets\Tests;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\simpletest\WebTestBase;

/**
 * @coversDefaultClass Drupal\facets\Tests\FacetManager
 * @group facets
 */
class FacetManagerTest extends WebTestBase {

  use StringTranslationTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('facets');

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * A mocked test, to make sure the test runner doesn't crash.
   */
  public function testMock() {
    $this->verbose("We need to have at least one test method in a test or otherwise all tests fail.");
  }

}
