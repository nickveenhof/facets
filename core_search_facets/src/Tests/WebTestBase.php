<?php

/**
 * @file
 * Contains \Drupal\core_search_facets\Tests\WebTestBase.
 */

namespace Drupal\core_search_facets\Tests;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\simpletest\WebTestBase as SimpletestWebTestBase;

/**
 * Provides the base class for web tests for Core Search Facets.
 */
abstract class WebTestBase extends SimpletestWebTestBase {

  use StringTranslationTrait;

  /**
   * Exempt from strict schema checking.
   *
   * @see \Drupal\Core\Config\Testing\ConfigSchemaChecker
   *
   * @var bool
   */
  protected $strictConfigSchema = FALSE;

  /**
   * Modules to enable for this test.
   *
   * @var string[]
   */
  public static $modules = [
    'views',
    'node',
    'facets',
    'block',
    'core_search_facets',
  ];

  /**
   * An admin user used for this test.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $adminUser;

  /**
   * A user without Search / Facet admin permission.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $unauthorizedUser;

  /**
   * The anonymous user used for this test.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $anonymousUser;

  /**
   * The URL generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * A search index ID.
   *
   * @var string
   */
  protected $indexId = 'database_search_index';

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Create the users used for the tests.
    $this->adminUser = $this->drupalCreateUser([
      'administer search',
      'administer facets',
      'access administration pages',
      'administer nodes',
      'access content overview',
      'administer content types',
      'administer blocks',
      'search content',
    ]);

    $this->unauthorizedUser = $this->drupalCreateUser(['access administration pages']);
    $this->anonymousUser = $this->drupalCreateUser();

    // Get the URL generator.
    $this->urlGenerator = $this->container->get('url_generator');
  }

}
