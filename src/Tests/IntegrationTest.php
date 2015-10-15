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

    $this->checkEmptyOverview();
    $this->addFacet("Test Facet name");
  }

  /**
   * Get the facet overview page and make sure no other facets have been defined
   * yet, make sure that the "Add new facet" link is showing.
   */
  protected function checkEmptyOverview() {
    $facet_overview = $this->urlGenerator->generateFromRoute('facetapi.overview');
    $this->drupalGet($facet_overview);
    $this->assertResponse(200);
    $this->assertText($this->t('There are no facets defined.'));
    $this->assertText($this->t('Add new facet'));
  }

  /**
   * Tests adding a facet trough the interface.
   */
  protected function addFacet($facet_name) {
    $facet_id = preg_replace('@[^a-zA-Z0-9_]+@', '_', strtolower($facet_name));

    // Go to the Add facet page and make sure that returns a 200
    $facet_add_page = $this->urlGenerator->generateFromRoute('entity.facetapi_facet.add_form', [], ['absolute' => TRUE]);
    $this->drupalGet($facet_add_page);
    $this->assertResponse(200);

    $form_values = [
      'name' => '',
      'id' => $facet_id,
      'widget' => 'links',
      'status' => 1,
    ];

    // Try filling out the form, but without having filled in a name for the
    // facet to test for form errors.
    $this->drupalPostForm($facet_add_page, $form_values, $this->t('Save'));
    $this->assertText($this->t('Facet name field is required.'));
    $this->assertText($this->t('Facet source field is required.'));

    // Make sure that when filling out the name, the form error disappears.
    $form_values['name'] = $facet_name;
    $this->drupalPostForm(NULL, $form_values, $this->t('Save'));
    $this->assertNoText($this->t('Facet name field is required.'));

    // Configure the facet source by selecting one of the search api views.
    $this->drupalGet($facet_add_page);
    $this->drupalPostForm(NULL, ['facet_source' => 'search_api_views:search_api_test_views_fulltext:page_1'], $this->t('Configure facet source'));

    // @todo TEMPORARY FIX FOR https://www.drupal.org/node/2593611
    $this->drupalPostForm(NULL, ['widget' => 'links'], $this->t('Configure widget'));

    $this->drupalPostForm(NULL, $form_values, $this->t('Save'));
    $this->assertText($this->t('Facet field field is required.'));

    $facet_source_form = [
      'facet_source' => 'search_api_views:search_api_test_views_fulltext:page_1',
      'facet_source_configs[search_api_views:search_api_test_views_fulltext:page_1][field_identifier]' => 'entity:entity_test/type',
    ];
    $this->drupalPostForm(NULL, $form_values + $facet_source_form, $this->t('Save'));
    $this->assertNoText('field is required.');

    // Make sure that the redirection back to the overview was successful and
    // the newly added facet is shown on the overview page.
    $this->assertText($this->t('The facet was successfully saved.'));
    $this->assertUrl($this->urlGenerator->generateFromRoute('facetapi.overview'), [], 'Correct redirect to index page.');
    $this->assertText($facet_name);
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
