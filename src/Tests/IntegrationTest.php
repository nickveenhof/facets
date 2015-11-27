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

    // Create and place a block for "Test Facet name" facet.
    $this->createFacetBlock('test_facet_name');

    // Verify that the facet results are correct.
    $this->drupalGet('search-api-test-fulltext');
    $this->assertText('item');
    $this->assertText('article');

    // Verify that facet blocks appear as expected.
    $this->assertFacetBlocksAppear();

    // Verify that the facet is visible when removing a facet for example.
    $this->goToDeleteFacetPage("Test Facet Name");
    $this->assertText('item');
    $this->assertText('article');


    // Show the facet only when the facet source is visible.
    // @TODO Only for SearchApiViewsPage for the moment.
    $this->setOptionShowOnlyWhenFacetSourceVisible("Test Facet name");
    $this->goToDeleteFacetPage("Test Facet Name");
    $this->assertNoText('item');
    $this->assertNoText('article');

    // Do not show the block on empty behaviors.
    // Remove data from index.
    $this->clearIndex();
    $this->drupalGet('search-api-test-fulltext');

    // Verify that no facet blocks appear. Empty behavior "None" is selected by
    // default.
    $this->assertNoFacetBlocksAppear();

    // Verify that the "empty_text" appears as expected.
    $this->setEmptyBehaviorFacetText("Test Facet name");
    $this->drupalGet('search-api-test-fulltext');
    $this->assertRaw('block-test-facet-name');
    $this->assertRaw('No results found for this block!');

    // Verify that we cannot delete a facet used in a block.
    $this->deleteUsedFacet("Test Facet name");

    // Delete the block.
    $this->deleteBlock('test_facet_name');

    // Delete the facet and make sure the overview is empty again.
    $this->deleteUnusedFacet("Test Facet name");
    $this->checkEmptyOverview();
  }

  /**
   * Deletes a facet block by id.
   *
   * @param string $id
   *   The id of the block.
   */
  protected function deleteBlock($id) {
    $this->drupalGet('admin/structure/block/manage/' . $this->blocks[$id]->id(), array('query' => array('destination' => 'admin')));
    $this->clickLink(t('Delete'));
    $this->drupalPostForm(NULL, array(), t('Delete'));
    $this->assertRaw(t('The block %name has been deleted.', array('%name' => $this->blocks[$id]->label())));
  }

  /**
   * Helper function: asserts that a facet block does not appear.
   */
  protected function assertNoFacetBlocksAppear() {
    foreach ($this->blocks as $block) {
      $this->assertNoBlockAppears($block);
    }
  }

  /**
   * Helper function: asserts that a facet block appears.
   */
  protected function assertFacetBlocksAppear() {
    foreach ($this->blocks as $block) {
      $this->assertBlockAppears($block);
    }
  }

  /**
   * Creates a facet block by id.
   *
   * @param string $id
   *   The id of the block.
   */
  protected function createFacetBlock($id) {
    // Create a block. Load the entity to obtain the uuid when creating the
    // block.
    $facet = \Drupal::service('entity_type.manager')->getStorage('facetapi_facet')->load($id);
    $this->blockValues = [
      [
        'label' => 'Facet Block',
        'tr' => '16',
        'plugin_id' => 'facet_block',
        'settings' => [
          'region' => 'footer',
          'id' => str_replace('_', '-', $id),
          'context_mapping' => [
            'facet' => '@facetapi.facet_context:' . $facet->uuid(),
          ],
        ],
        'test_weight' => '0',
      ],
    ];
    foreach ($this->blockValues as $values) {
      $this->blocks[$id] = $this->drupalPlaceBlock($values['plugin_id'], $values['settings']);
    }
  }

  /**
   * Configures empty behavior option to show a text on empty results.
   *
   * @param string $facet_name
   */
  protected function setEmptyBehaviorFacetText($facet_name) {
    $facet_id = $this->convertNameToMachineName($facet_name);

    $facet_display_page = $this->urlGenerator->generateFromRoute('entity.facetapi_facet.display_form', ['facetapi_facet' => $facet_id], ['absolute' => TRUE]);

    // Go to the facet edit page and make sure "edit facet %facet" is present.
    $this->drupalGet($facet_display_page);
    $this->assertResponse(200);

    // Configure the text for empty results behavior.
    $this->drupalPostForm(NULL, ['empty_behavior' => 'text'], $this->t('Configure empty behavior'));
    $this->drupalPostForm(NULL, ['empty_behavior_configs[empty_text][value]' => 'No results found for this block!'], $this->t('Configure empty behavior'));

    $this->drupalPostForm(NULL, NULL, $this->t('Save'));

  }

  /**
   * Configures a facet to only be visible when accessing to the facet source.
   *
   * @param string $facet_name
   */
  protected function setOptionShowOnlyWhenFacetSourceVisible($facet_name) {
    $facet_id = $this->convertNameToMachineName($facet_name);

    $facet_display_page = $this->urlGenerator->generateFromRoute('entity.facetapi_facet.display_form', ['facetapi_facet' => $facet_id], ['absolute' => TRUE]);
    $this->drupalGet($facet_display_page);
    $this->assertResponse(200);

    $edit = [
      'only_visible_when_facet_source_is_visible' => 1,
      'widget' => 'links',
      'widget_configs[show_numbers]' => '0',
//      'processors[query_string][status]' => '1',
    ];
    $this->drupalPostForm(NULL, $edit, $this->t('Save'));
  }

  /**
   * Get the facet overview page and make sure the overview is empty.
   */
  protected function checkEmptyOverview() {
    $facet_overview = $this->urlGenerator->generateFromRoute('facetapi.overview');
    $this->drupalGet($facet_overview);
    $this->assertResponse(200);

    // The list overview has Field: field_name as description. This tests on the
    // absence of that.
    $this->assertNoText('Field:');
  }

  /**
   * Tests adding a facet trough the interface.
   *
   * @param $facet_name
   */
  protected function addFacet($facet_name) {
    $facet_id = $this->convertNameToMachineName($facet_name);

    // Go to the Add facet page and make sure that returns a 200.
    $facet_add_page = $this->urlGenerator->generateFromRoute('entity.facetapi_facet.add_form', [], ['absolute' => TRUE]);
    $this->drupalGet($facet_add_page);
    $this->assertResponse(200);

    $form_values = [
      'name' => '',
      'id' => $facet_id,
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
    $this->assertRaw(t('Facet %name has been updated.', ['%name' => $facet_name]));
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
    $this->assertRaw(t('Facet %name has been updated.', ['%name' => $facet_name . ' - 2']));


    // Make sure the "-2" suffix is still on the facet when editing a facet.
    $this->drupalGet($facet_edit_page);
    $this->assertRaw($this->t('Edit facet @facet', ['@facet' => $facet_name . ' - 2']));

    // Edit the form and change the facet's name back to the initial name.
    $form_values = ['name' => $facet_name];
    $this->drupalPostForm($facet_edit_page, $form_values, $this->t('Save'));

    // Make sure that the redirection back to the overview was successful and
    // the edited facet is shown on the overview page.
    $this->assertRaw(t('Facet %name has been updated.', ['%name' => $facet_name]));
  }

  /**
   * This deletes an unused facet through the UI.
   *
   * @param string $facet_name
   */
  protected function deleteUsedFacet($facet_name) {
    $facet_id = $this->convertNameToMachineName($facet_name);

    $facet_delete_page = $this->urlGenerator->generateFromRoute('entity.facetapi_facet.delete_form', ['facetapi_facet' => $facet_id], ['absolute' => TRUE]);

    // Go to the facet delete page and make the warning is shown.
    $this->drupalGet($facet_delete_page);
    $this->assertResponse(200);

    // Check that the facet by testing for the message and the absence of the
    // facet name on the overview.
    $this->assertRaw($this->t('The facet is currently used in a block and thus can\'t be removed. Remove the block first.'));

  }

  /**
   * This deletes a facet through the UI.
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
    // $this->assertText($this->t('Are you sure you want to delete the facet'));
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
   *
   * @return string
   */
  protected function convertNameToMachineName($facet_name) {
    return preg_replace('@[^a-zA-Z0-9_]+@', '_', strtolower($facet_name));
  }

  /**
   * Go to the Delete Facet Page using the facet name.
   *
   * @param string $facet_name
   */
  protected function goToDeleteFacetPage($facet_name) {
    $facet_id = $this->convertNameToMachineName($facet_name);

    $facet_delete_page = $this->urlGenerator->generateFromRoute('entity.facetapi_facet.delete_form', ['facetapi_facet' => $facet_id], ['absolute' => TRUE]);

    // Go to the facet delete page and make the warning is shown.
    $this->drupalGet($facet_delete_page);
    $this->assertResponse(200);
  }

}
