<?php

/**
 * @file
 * Contains \Drupal\facetapi\Tests\IntegrationTest.
 */

namespace Drupal\facetapi\Tests;

use \Drupal\facetapi\Tests\WebTestBase as FacetWebTestBase;
use Drupal\search_api\Entity\Index;

/**
 * Tests the overall functionality of the Facet API admin UI.
 *
 * @group facetapi
 */
class IntegrationTest extends FacetWebTestBase {

  /**
   * @var Index $index
   *   A search api index.
   */
  protected $index;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests various operations via the Facet API's admin UI.
   */
  public function testFramework() {
    $this->drupalLogin($this->adminUser);

    // Create search api test server & index.
    $this->getTestServer();
    $this->index = $this->getTestIndex();

    $this->addFieldsToIndex();

    // Clear all the caches.
    $this->resetAll();

    $this->addFacet();
  }

  protected function addFacet() {
    $facet_overview = $this->urlGenerator->generateFromRoute('facetapi.overview');
    $this->drupalGet($facet_overview);
    $this->assertResponse(200);
    $this->assertText($this->t('There are no facets defined.'));

    $facet_add_page = $this->urlGenerator->generateFromRoute('entity.facetapi_facet.add_form', [], ['absolute' => TRUE]);
    $this->drupalGet($facet_add_page);
    $this->assertResponse(200);

    $edit = [
      'name' => '',
      'id' => 'test_facet',
      'field_identifier' => 'entity:node/title',
      'widget' => 'links',
      'status' => 1,
    ];

    $this->drupalPostForm($facet_add_page, $edit, $this->t('Save'));
    $this->assertText($this->t('Facet name field is required.'));
    $this->assertText($this->t('1 error has been found'));

    $facetName = "Test Facet Name";
    $edit = [
      'name' => $facetName,
      'id' => 'test_facet',
      'field_identifier' => 'entity:node/title',
      'widget' => 'links',
      'status' => 1,
    ];
    // Configure the widget
    $this->drupalPostForm(NULL, $edit, $this->t('Configure'));

    // Save the facet
    $this->drupalPostForm(NULL, $edit, $this->t('Save'));
    $this->assertUrl($facet_overview);
    $this->assertText($facetName);

  }

  protected function addFieldsToIndex() {
    $edit = array(
      'fields[entity:node/nid][indexed]' => 1,
      'fields[entity:node/title][indexed]' => 1,
      'fields[entity:node/title][type]' => 'text',
      'fields[entity:node/title][boost]' => '21.0',
      'fields[entity:node/body][indexed]' => 1,
      'fields[entity:node/uid][indexed]' => 1,
      'fields[entity:node/uid][type]' => 'search_api_test_data_type',
    );

    $this->drupalPostForm('admin/config/search/search-api/index/webtest_index/fields', $edit, $this->t('Save changes'));
    $this->assertText($this->t('The changes were successfully saved.'));
  }

}
