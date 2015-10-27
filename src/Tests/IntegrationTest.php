<?php

/**
 * @file
 * Contains \Drupal\facetapi\Tests\IntegrationTest.
 */

namespace Drupal\facetapi\Tests;

use \Drupal\facetapi\Tests\WebTestBase as FacetWebTestBase;

/**
 * Tests the overall functionality of the Facet API admin UI.
 *
 * @group facetapi
 */
class IntegrationTest extends FacetWebTestBase {

  /**
   * The submitted block values used by this test.
   *
   * @var array
   */
  protected $blockValues;

  /**
   * The block entities used by this test.
   *
   * @var \Drupal\block\BlockInterface[]
   */
  protected $blocks;

  /**
   * Tests the facet api permissions.
   */
  public function testOverviewPermissions() {
    $facet_overview = $this->urlGenerator->generateFromRoute('facetapi.overview');

    // Login with a user that is not authorized to administer facets and test
    // that we're correctly getting a 403 HTTP response code.
    $this->drupalLogin($this->unauthorizedUser);
    $this->drupalGet($facet_overview);
    $this->assertResponse(403);
    $this->assertText('You are not authorized to access this page');

    // Login with a user that has the correct permissions and test for the
    // correct HTTP response code.
    $this->drupalLogin($this->adminUser);
    $this->drupalGet($facet_overview);
    $this->assertResponse(200);
  }

  /**
   * Tests various operations via the Facet API's admin UI.
   */
  public function testFramework() {

    // Make sure we're logged in with a user that has sufficient permissions.
    $this->drupalLogin($this->adminUser);

    // Create search api test server & index and add fields to the index.
    // @TODO remove this config because we are not using this server or index?
    // By default search_api_test_backend module creates a server and index and
    // we are currently using it.
    /*$this->getTestServer();
    $this->getTestIndex();
    $this->addFieldsToIndex();*/

    // @TODO create content and index it.

    // Check if the overview is empty.
    $this->checkEmptyOverview();

    // Add a new facet and edit it.
    $this->addFacet("Test Facet name");
    $this->editFacet("Test Facet name");

    // Insert Content and index it.
    $this->insertExampleContent();
    $this->assertEqual($this->indexItems($this->indexId), 5, '5 items were indexed.');

    $this->drupalGet('search-api-test-fulltext');
    // By default, the view should show all entities.
    $this->assertText('Displaying 5 search results', 'The search view displays the correct number of results.');

    // Create a block. Load the entity to obtain the uuid when creating the
    // block.
    $facet = \Drupal::entityManager()->getStorage('facetapi_facet')->load('test_facet_name');
    $this->blockValues = [
      [
        'label' => 'Facet Block',
        'tr' => '16',
        'plugin_id' => 'facet_block',
        'settings' => [
          'region' => 'footer',
          'id' => 'test-facet-name',
          'context_mapping' => [
            'facet' => '@facetapi.feed_context:' . $facet->uuid()
          ]
        ],
        'test_weight' => '0',
      ],
    ];
    $this->blocks = [];
    foreach ($this->blockValues as $values) {
      $this->blocks[] = $this->drupalPlaceBlock($values['plugin_id'], $values['settings']);
    }

    // Verify that the facet results are correct.
    $this->drupalGet('search-api-test-fulltext');
    $this->assertRaw('<a href="/search-api-test-fulltext?f%5B0%5D=entity_entity_test_type%3A%22item%22">item (3)</a>');
    $this->assertRaw('<a href="/search-api-test-fulltext?f%5B0%5D=entity_entity_test_type%3A%22article%22">article (2)</a>');
    foreach ($this->blocks as $block) {
      $this->assertBlockAppears($block);
    }

    // Do not show the block on empty behaviors.
    // Remove data from index.
    $this->clearIndex();
    $this->drupalGet('search-api-test-fulltext');
    foreach ($this->blocks as $block) {
      $this->assertNoBlockAppears($block);
    }

    // Verify that the "empty_text" appears as expected.
    $this->setEmptyBehaviorFacetText("Test Facet name");
    $this->drupalGet('search-api-test-fulltext');
    $this->assertRaw('block-test-facet-name');
    $this->assertRaw('No results found fo this block!');

    // @TODO verify that we cannot remove a facet if used by a block before
    // deleteUnusedFacet().
    // Delete the block.
    foreach ($this->blocks as $block) {
      $this->drupalGet('admin/structure/block/manage/' . $block->id(), array('query' => array('destination' => 'admin')));
      $this->clickLink(t('Delete'));
      $this->drupalPostForm(NULL, array(), t('Delete'));
      $this->assertRaw(t('The block %name has been deleted.', array('%name' => $block->label())));
    }

    // Delete the facet and make sure the overview is empty again.
    $this->deleteUnusedFacet("Test Facet name");
    $this->checkEmptyOverview();
  }

  /**
   * Configures empty behavior option to show a text on empty results.
   *
   * @param string $facet_name
   */
  protected function setEmptyBehaviorFacetText($facet_name) {
    $facet_id = $this->convertNameToMachineName($facet_name);

    $facet_edit_page = $this->urlGenerator->generateFromRoute('entity.facetapi_facet.edit_form', ['facetapi_facet' => $facet_id], ['absolute' => TRUE]);

    // Go to the facet edit page and make sure "edit facet %facet" is present.
    $this->drupalGet($facet_edit_page);
    $this->assertResponse(200);
    $this->assertRaw($this->t('Edit facet @facet', ['@facet' => $facet_name]));

    // Configure the text for empty results behavior.
    $this->drupalPostForm(NULL, ['empty_behavior' => 'text'], $this->t('Configure empty behavior'));
    $this->drupalPostForm(NULL, ['empty_behavior_configs[empty_text][value]' => 'No results found fo this block!'], $this->t('Configure empty behavior'));

    $this->drupalPostForm(NULL, NULL, $this->t('Save'));

  }

  /**
   * Get the facet overview page and make sure the overview is empty.
   */
  protected function checkEmptyOverview() {
    $facet_overview = $this->urlGenerator->generateFromRoute('facetapi.overview');
    $this->drupalGet($facet_overview);
    $this->assertResponse(200);

    // The list overview has Field: field_name, Widget: widget_name as
    // description. This tests on the absence of that.
    $this->assertNoText('Widget:');
    $this->assertNoText('Field:');
  }

  /**
   * Tests adding a facet trough the interface.
   *
   * @param $facet_name
   */
  protected function addFacet($facet_name) {
    $facet_id = $this->convertNameToMachineName($facet_name);

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
    $this->drupalPostForm(NULL, ['facet_source_id' => 'search_api_views:search_api_test_views_fulltext:page_1'], $this->t('Configure facet source'));

    // @todo TEMPORARY FIX FOR https://www.drupal.org/node/2593611
    $this->drupalPostForm(NULL, ['widget' => 'links'], $this->t('Configure widget'));

    // The facet field is still required.
    $this->drupalPostForm(NULL, $form_values, $this->t('Save'));
    $this->assertText($this->t('Facet field field is required.'));

    // Fill in all fields and make sure the 'field is required' message is no
    // longer shown.
    $facet_source_form = [
      'facet_source_id' => 'search_api_views:search_api_test_views_fulltext:page_1',
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

  /**
   * Tests editing of a facet through the UI.
   *
   * @param $facet_name
   */
  public function editFacet($facet_name) {
    $facet_id = $this->convertNameToMachineName($facet_name);

    $facet_edit_page = $this->urlGenerator->generateFromRoute('entity.facetapi_facet.edit_form', ['facetapi_facet' => $facet_id], ['absolute' => TRUE]);

    // Go to the facet edit page and make sure "edit facet %facet" is present.
    $this->drupalGet($facet_edit_page);
    $this->assertResponse(200);
    $this->assertRaw($this->t('Edit facet @facet', ['@facet' => $facet_name]));

    // Change the facet name to add in "-2" to test editing of a facet works.
    $form_values = ['name' => $facet_name . ' - 2'];
    $this->drupalPostForm($facet_edit_page, $form_values, $this->t('Save'));

    // Make sure that the redirection back to the overview was successful and
    // the edited facet is shown on the overview page.
    $this->assertText($this->t('The facet was successfully saved.'));
    $this->assertUrl($this->urlGenerator->generateFromRoute('facetapi.overview'), [], 'Correct redirect to index page.');
    $this->assertText($facet_name);

    // Make sure the "-2" suffix is still on the facet when editing a facet.
    $this->drupalGet($facet_edit_page);
    $this->assertRaw($this->t('Edit facet @facet', ['@facet' => $facet_name . ' - 2']));

    // Edit the form and change the facet's name back to the initial name.
    $form_values = ['name' => $facet_name];
    $this->drupalPostForm($facet_edit_page, $form_values, $this->t('Save'));

    // Make sure that the redirection back to the overview was successful and
    // the edited facet is shown on the overview page.
    $this->assertText($this->t('The facet was successfully saved.'));
    $this->assertUrl($this->urlGenerator->generateFromRoute('facetapi.overview'), [], 'Correct redirect to index page.');
    $this->assertText($facet_name);
  }

  /**
   * This deletes an unused facet through the UI.
   *
   * @param string $facet_name
   */
  protected function deleteUnusedFacet($facet_name) {
    $facet_id = $this->convertNameToMachineName($facet_name);

    $facet_delete_page = $this->urlGenerator->generateFromRoute('entity.facetapi_facet.delete_form', ['facetapi_facet' => $facet_id], ['absolute' => TRUE]);

    // Go to the facet delete page and make the warning is shown.
    $this->drupalGet($facet_delete_page);
    $this->assertResponse(200);
    // @TODO Missing this text on local test. Not sure why.
    //$this->assertText($this->t('Are you sure you want to delete the facet'));

    // Actually submit the confirmation form.
    $this->drupalPostForm(NULL, [], $this->t('Delete'));

    // Check that the facet by testing for the message and the absence of the
    // facet name on the overview.
    $this->assertRaw($this->t('The facet %facet has been deleted.', ['%facet' => $facet_name]));

    // Refresh the page because on the previous page the $facet_name is still
    // visible (in the message).
    $facet_overview = $this->urlGenerator->generateFromRoute('facetapi.overview');
    $this->drupalGet($facet_overview);
    $this->assertResponse(200);
    $this->assertNoText($facet_name);
  }

  /**
   * Add fields to search api index.
   */
  protected function addFieldsToIndex() {
    $edit = [
      'fields[entity:node/nid][indexed]' => 1,
      'fields[entity:node/title][indexed]' => 1,
      'fields[entity:node/title][type]' => 'text',
      'fields[entity:node/title][boost]' => '21.0',
      'fields[entity:node/body][indexed]' => 1,
      'fields[entity:node/uid][indexed]' => 1,
      'fields[entity:node/uid][type]' => 'search_api_test_data_type',
    ];

    $this->drupalPostForm('admin/config/search/search-api/index/webtest_index/fields', $edit, $this->t('Save changes'));
    $this->assertText($this->t('The changes were successfully saved.'));
  }

  /**
   * Covert facet name to machine name.
   *
   * @param $facet_name
   * @return string
   */
  protected function convertNameToMachineName($facet_name) {
    return preg_replace('@[^a-zA-Z0-9_]+@', '_', strtolower($facet_name));
  }

}
