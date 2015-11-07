<?php
/**
 * @file
 * Tests Facet API's FacetManager implementation.
 */

namespace Drupal\facetapi\Tests;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\simpletest\WebTestBase;

/**
 * @coversDefaultClass Drupal\facetapi\Tests\FacetManager
 * @group facetapi
 */
class FacetManagerTest extends WebTestBase {

  use StringTranslationTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('facetapi');

  /**
   * The URL generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  // Protected $urlGenerator;.
  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   *
   */
  public function testMock() {
    $this->verbose("We need to have at least one test method in a test or otherwise all tests fail.");
  }

}
