<?php

/**
 * @file
 * Contains \Drupal\facets\Tests\WidgetIntegrationTest.
 */

namespace Drupal\facets\Tests;

use \Drupal\facets\Tests\WebTestBase as FacetWebTestBase;

/**
 * Tests the overall functionality of the Facets admin UI.
 *
 * @group facets
 */
class WidgetIntegrationTest extends FacetWebTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'views',
    'node',
    'search_api',
    'search_api_test_backend',
    'facets',
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

    $this->setUpExampleStructure();
    $this->insertExampleContent();
    $this->assertEqual($this->indexItems($this->indexId), 5, '5 items were indexed.');
  }

  /**
   * Tests various url integration things.
   */
  public function testCheckboxWidget() {
    $id = 't';
    $name = 'Facet & checkbox~';
    $facet_add_page = 'admin/config/search/facets/add-facet';

    $this->drupalGet($facet_add_page);

    $form_values = [
      'id' => $id,
      'status' => 1,
      'url_alias' => $id,
      'name' => $name,
      'facet_source_id' => 'search_api_views:search_api_test_view:page_1',
      'facet_source_configs[search_api_views:search_api_test_view:page_1][field_identifier]' => 'type',
    ];
    $this->drupalPostForm(NULL, ['facet_source_id' => 'search_api_views:search_api_test_view:page_1'], $this->t('Configure facet source'));
    $this->drupalPostForm(NULL, $form_values, $this->t('Save'));
    $this->drupalPostForm(NULL, ['widget' => 'checkbox'], $this->t('Save'));

    $block_values = [
      'plugin_id' => 'facet_block:' . $id,
      'settings' => [
        'region' => 'footer',
        'id' => str_replace('_', '-', $id),
      ],
    ];
    $this->drupalPlaceBlock($block_values['plugin_id'], $block_values['settings']);

    $this->drupalGet('search-api-test-fulltext');
    $this->drupalPostForm(NULL, array('type[item]' => 'item'), $this->t('submit'));
    $this->assertFieldChecked('edit-type-item');
  }

  /**
   * Tests links widget's basic functionality.
   */
  public function testLinksWidget() {
    $id = 'links_widget';
    $name = '>.Facet &* Links';
    $facet_add_page = 'admin/config/search/facets/add-facet';

    $this->drupalGet($facet_add_page);

    $form_values = [
      'id' => $id,
      'status' => 1,
      'url_alias' => $id,
      'name' => $name,
      'facet_source_id' => 'search_api_views:search_api_test_view:page_1',
      'facet_source_configs[search_api_views:search_api_test_view:page_1][field_identifier]' => 'type',
    ];
    $this->drupalPostForm(NULL, ['facet_source_id' => 'search_api_views:search_api_test_view:page_1'], $this->t('Configure facet source'));
    $this->drupalPostForm(NULL, $form_values, $this->t('Save'));
    $this->drupalPostForm(NULL, ['widget' => 'links'], $this->t('Save'));

    $block_values = [
      'plugin_id' => 'facet_block:' . $id,
      'settings' => [
        'region' => 'footer',
        'id' => str_replace('_', '-', $id),
      ],
    ];
    $this->drupalPlaceBlock($block_values['plugin_id'], $block_values['settings']);

    $this->drupalGet('search-api-test-fulltext');
    $this->assertLink('item');
    $this->assertLink('article');

    $this->clickLink('item');
    $this->assertLink('(-) item');
  }

  /**
   * Tests the functionality of a widget to hide/show the item-count.
   */
  public function testLinksShowHideCount() {
    $id = 'links_widget';
    $name = '>.Facet &* Links';
    $facet_add_page = 'admin/config/search/facets/add-facet';
    $facet_edit_page = 'admin/config/search/facets/' . $id . '/display';

    $this->drupalGet($facet_add_page);

    $form_values = [
      'id' => $id,
      'status' => 1,
      'url_alias' => $id,
      'name' => $name,
      'facet_source_id' => 'search_api_views:search_api_test_view:page_1',
      'facet_source_configs[search_api_views:search_api_test_view:page_1][field_identifier]' => 'type',
    ];
    $this->drupalPostForm(NULL, ['facet_source_id' => 'search_api_views:search_api_test_view:page_1'], $this->t('Configure facet source'));
    $this->drupalPostForm(NULL, $form_values, $this->t('Save'));
    $this->drupalPostForm(NULL, ['widget' => 'links'], $this->t('Save'));

    $block_values = [
      'plugin_id' => 'facet_block:' . $id,
      'settings' => [
        'region' => 'footer',
        'id' => str_replace('_', '-', $id),
      ],
    ];
    $this->drupalPlaceBlock($block_values['plugin_id'], $block_values['settings']);

    // Go to the view and check that the facet links are shown with their
    // default settings.
    $this->drupalGet('search-api-test-fulltext');
    $this->assertLink('item');
    $this->assertLink('article');

    $this->drupalGet($facet_edit_page);
    $this->drupalPostForm(NULL, ['widget' => 'links', 'widget_configs[show_numbers]' => TRUE], $this->t('Save'));

    // Go back to the same view and check that links now display the count.
    $this->drupalGet('search-api-test-fulltext');
    $this->assertLink('item (3)');
    $this->assertLink('article (2)');

    $this->drupalGet($facet_edit_page);
    $this->drupalPostForm(NULL, ['widget' => 'links', 'widget_configs[show_numbers]' => FALSE], $this->t('Save'));

    // The count should be hidden again.
    $this->drupalGet('search-api-test-fulltext');
    $this->assertLink('item');
    $this->assertLink('article');
  }

}
