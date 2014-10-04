<?php
/**
 * @file
 * Contains Drupal\facetapi_dummy_backend\Plugin\DummyBackend.
 */

namespace Drupal\facetapi_dummy_backend\Plugin\Backend;

use Drupal\search_api\Backend\BackendPluginBase;
use Drupal\search_api\Index\IndexInterface;
use Drupal\search_api\Query\QueryInterface;

/**
 * @FacetApiDummyBackend(
 *   id = "facetapi_dummy_backend",
 *   label = @Translation("FacetAPI Dummy Backend"),
 *   description = @Translation("Dummy SearchAPI backend for testing FacetAPI.")
 * )
 */
class Backend extends BackendPluginBase {

  /**
   * Indexes the specified items.
   *
   * @param \Drupal\search_api\Index\IndexInterface $index
   *   The search index for which items should be indexed.
   * @param \Drupal\search_api\Item\ItemInterface[] $items
   *   An array of items to be indexed, keyed by their item IDs.
   *   The value of fields with the "tokenized_text" type is an array of tokens.
   *   Each token is an array containing the following keys:
   *   - value: The word that the token represents.
   *   - score: A score for the importance of that word.
   *
   * @return string[]
   *   The IDs of all items that were successfully indexed.
   *
   * @throws \Drupal\search_api\Exception\SearchApiException
   *   If indexing was prevented by a fundamental configuration error.
   */
  public function indexItems(IndexInterface $index, array $items) {
    // TODO: Implement indexItems() method.
  }

  /**
   * Deletes the specified items from the index.
   *
   * @param \Drupal\search_api\Index\IndexInterface $index
   *   The index from which items should be deleted.
   * @param string[]                                $item_ids
   *   The IDs of the deleted items.
   *
   * @throws \Drupal\search_api\Exception\SearchApiException
   *   If an error occurred while trying to delete the items.
   */
  public function deleteItems(IndexInterface $index, array $item_ids) {
    // TODO: Implement deleteItems() method.
  }

  /**
   * Deletes all the items from the index.
   *
   * @param \Drupal\search_api\Index\IndexInterface $index
   *   The index for which items should be deleted.
   *
   * @throws \Drupal\search_api\Exception\SearchApiException
   *   If an error occurred while trying to delete the items.
   */
  public function deleteAllIndexItems(IndexInterface $index) {
    // TODO: Implement deleteAllIndexItems() method.
  }

  /**
   * Executes a search on this server.
   *
   * @param \Drupal\search_api\Query\QueryInterface $query
   *   The query to execute.
   *
   * @return \Drupal\search_api\Query\ResultSetInterface
   *   The search results.
   *
   * @throws \Drupal\search_api\Exception\SearchApiException
   *   If an error prevented the search from completing.
   */
  public function search(QueryInterface $query) {
    // TODO: Implement search() method.
  }
}
