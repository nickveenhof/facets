<?php
/**
 * Contains Drupal\facet_api_search_api_dummy_backend\Plugin\TestSearcher
 */

namespace Drupal\facet_api_search_api_dummy_backend\Plugin\facet_api\Searcher;

use Drupal\Core\Annotation\Translation;

/**
 * @FacetApiSearcher (
 *   id = "test_searcher",
 *   name = @Translation("Test Searcher"),
 *   label = @Translation("Test Searcher"),
 *   adapter = "TestAdapter",
 *   urlProcessor = "FacetUrlProcessorStandard",
 *   types = { "node" },
 *   path = "",
 *   supportFacetsMissing = TRUE,
 *   supportFacetsMincount = TRUE,
 *   includeDefaultFacets = FALSE
 * )
 */
class TestSearcher extends SearcherPluginBase {
  // Do nothing at the moment.
}
