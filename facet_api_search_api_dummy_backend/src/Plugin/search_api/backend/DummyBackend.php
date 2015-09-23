<?php

/**
 * @file
 * Contains \Drupal\facet_api_search_api_dummy_backend\Plugin\search_api\backend\DummyBackend
 */

namespace Drupal\facet_api_search_api_dummy_backend\Plugin\search_api\backend;

use Drupal\Core\Form\FormStateInterface;
use Drupal\search_api\Backend\BackendPluginBase;
use Drupal\search_api\SearchApiException;
use Drupal\search_api\IndexInterface;
use Drupal\search_api\Query\QueryInterface;
use Drupal\search_api\Utility;

/**
 * Provides a dummy backend for testing purposes.
 *
 * @SearchApiBackend(
 *   id = "facet_api_search_api_dummy_backend",
 *   label = @Translation("Facet Api Test backend"),
 *   description = @Translation("Dummy backend implementation")
 * )
 */
class DummyBackend extends BackendPluginBase {

  /**
   * {@inheritdoc}
   */
  public function viewSettings() {
    return array(
      array(
        'label' => 'Facet Api Dummy Backend',
        'info' => 'Dummy Value',
      )
    );
  }

  /**
   * {@inheritdoc}
   */
  public function supportsFeature($feature) {
    return $feature == 'search_api_facets';
  }

  /**
   * {@inheritdoc}
   */
  public function supportsDataType($type) {
    return $type == 'search_api_test_data_type' || $type == 'search_api_altering_test_data_type';
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array('test' => '');
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['test'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Test'),
      '#default_value' => $this->configuration['test'],
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function indexItems(IndexInterface $index, array $items) {
    // @todo implement
    return array_keys($items);
  }

  /**
   * {@inheritdoc}
   */
  public function addIndex(IndexInterface $index) {
    // @todo implement
  }

  /**
   * {@inheritdoc}
   */
  public function updateIndex(IndexInterface $index) {
    // @todo implement
    $index->reindex();
  }

  /**
   * {@inheritdoc}
   */
  public function removeIndex($index) {
    // @todo implement
  }

  /**
   * {@inheritdoc}
   */
  public function deleteItems(IndexInterface $index, array $item_ids) {
    // @todo implement
  }

  /**
   * {@inheritdoc}
   */
  public function deleteAllIndexItems(IndexInterface $index) {
    // @todo implement

  }

  /**
   * {@inheritdoc}
   */
  public function search(QueryInterface $query) {
    // @todo implement
  }
}
