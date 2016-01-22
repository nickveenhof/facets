<?php

/**
 * @file
 * Contains \Drupal\facets\Tests\FacetSourceTest.
 */

namespace Drupal\facets\Tests;

use Drupal\facets\Tests\WebTestBase as FacetWebTestBase;

/**
 * Tests the functionality of the facet source config entity.
 *
 * @group facets
 */
class FacetSourceTest extends FacetWebTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'views',
    'search_api',
    'search_api_test_backend',
    'facets',
    'facets_search_api_dependency',
    'facets_query_processor',
  ];

  /**
   * Test the facet source editing.
   */
  public function testEditFilterKey() {
    // Make sure we're logged in with a user that has sufficient permissions.
    $this->drupalLogin($this->adminUser);

    // Test the overview.
    $this->drupalGet('admin/config/search/facets');
    $this->assertLink($this->t('Configure'));
    $this->clickLink($this->t('Configure'));

    // Test the edit page.
    $edit = array(
      'filterKey' => 'fq',
    );
    $this->assertField('filterKey');
    $this->assertField('urlProcessor');
    $this->drupalPostForm(NULL, $edit, $this->t('Save'));
    $this->assertResponse(200);

    // Test that saving worked filterkey has the new value
    $this->assertField('filterKey');
    $this->assertField('urlProcessor');
    $this->assertRaw('fq');
  }

  /**
   * Tests editing the url processor.
   */
  public function testEditUrlProcessor() {
    // Make sure we're logged in with a user that has sufficient permissions.
    $this->drupalLogin($this->adminUser);

    // Test the overview.
    $this->drupalGet('admin/config/search/facets');
    $this->assertLink($this->t('Configure'));
    $this->clickLink($this->t('Configure'));

    // Test the edit page.
    $edit = array(
      'urlProcessor' => 'dummy_query',
    );
    $this->assertField('filterKey');
    $this->assertField('urlProcessor');
    $this->drupalPostForm(NULL, $edit, $this->t('Save'));
    $this->assertResponse(200);

    // Test that saving worked and that the url processor has the new value.
    $this->assertField('filterKey');
    $this->assertField('urlProcessor');
    $elements = $this->xpath('//input[@id=:id]', [':id' => 'edit-urlprocessor-dummy-query']);
    $this->assertEqual('dummy_query', $elements[0]['value']);
  }

}
