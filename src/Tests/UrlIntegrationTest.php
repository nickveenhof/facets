<?php

/**
 * @file
 * Contains \Drupal\facets\Tests\UrlIntegrationTest.
 */

namespace Drupal\facets\Tests;

use \Drupal\facets\Tests\WebTestBase as FacetWebTestBase;

/**
 * Tests the overall functionality of the Facets admin UI.
 *
 * @group facets
 */
class UrlIntegrationTest extends FacetWebTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'views',
    'node',
    'search_api',
    'search_api_test_backend',
    'facets',
    'search_api_test_views',
    'block',
    'facets_search_api_dependency',
    'facets_query_processor',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->drupalLogin($this->adminUser);

    $this->insertExampleContent();
    $this->assertEqual($this->indexItems($this->indexId), 5, '5 items were indexed.');
  }

  /**
   * Tests various url integration things.
   */
  public function testUrlIntegration() {
    $id = 'facet';
    $name = '&^Facet@#1';
    $facet_add_page = 'admin/config/search/facets/add-facet';

    $this->drupalGet($facet_add_page);

    $form_values = [
      'id' => $id,
      'status' => 1,
      'url_alias' => $id,
      'name' => $name,
      'facet_source_id' => 'search_api_views:search_api_test_views_fulltext:page_1',
      'facet_source_configs[search_api_views:search_api_test_views_fulltext:page_1][field_identifier]' => 'entity:entity_test/type',
    ];
    $this->drupalPostForm(NULL, ['facet_source_id' => 'search_api_views:search_api_test_views_fulltext:page_1'], $this->t('Configure facet source'));
    $this->drupalPostForm(NULL, $form_values, $this->t('Save'));

    // Go to the only enabled facet source's config.
    $this->drupalGet('admin/config/search/facets');
    $this->clickLink($this->t('Configure'));

    $edit = [
      'filterKey' => 'y',
      'urlProcessor' => 'dummy_query',
    ];
    $this->drupalPostForm(NULL, $edit, $this->t('Save'));

    /** @var \Drupal\facets\FacetInterface $facet */
    $facet = \Drupal::service('entity_type.manager')->getStorage('facets_facet')->load($id);
    $block_values = [
      'region' => 'footer',
      'id' => str_replace('_', '-', $id),
      'context_mapping' => [
        'facet' => '@facets.facet_context:' . $facet->uuid(),
      ],
    ];
    $this->drupalPlaceBlock('facet_block', $block_values);

    $this->drupalGet('search-api-test-fulltext');
    $this->assertResponse(200);
    $this->assertLink('item');
    $this->assertLink('article');

    $this->clickLink('item');

    $this->assertResponse(200);
    $this->assertLink('(-) item');
    $this->assertNoLink('article');
    $this->assertUrl('search-api-test-fulltext?y[0]=facet||item');
  }

}
