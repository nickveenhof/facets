<?php
/**
 * Tests Facet API's Adapter implementation.
 */

namespace Drupal\facetapi\Tests;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\simpletest\WebTestBase;

/**
 * @coversDefaultClass Drupal\facetapi\Tests\Adapter
 * @group facetapi
 */
class AdapterTest extends WebTestBase {

  use StringTranslationTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('facetapi_dummy');

  /**
   * The URL generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  //protected $urlGenerator;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }


}
