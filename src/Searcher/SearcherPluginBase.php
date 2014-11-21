<?php
/**
 * @file
 * Provides \Drupal\facetapi\SearcherPluginBase;
 */

namespace Drupal\facet_api\Searcher;

use Drupal\Component\Plugin\PluginBase;
use Drupal\facet_api\Searcher\SearcherInterface;

class SearcherPluginBase extends PluginBase implements SearcherInterface {

  /**
   * Returns the machine readable name of the searcher.
   *
   * @return string
   */
  public function getName()  {
    return $this->pluginDefinition['name'];
  }

  /**
   * Returns the human readable name of the searcher displayed in 
   * the admin UI.
   *
   * @return string
   */
  public function getLabel() {
    return $this->pluginDefinition['label'];
  }

  /**
   * Returns the adapter plugin ID associated with the searcher.
   *
   * @return string
   */
  public function getAdapter() {
    return $this->pluginDefinition['adapter'];
  }

  /**
   * Returns the URL processor plugin ID associated with the searcher.
   *
   * @return string
   */
  public function getUrlProcessor() {
    return $this->pluginDefinition['urlProcessor'];
  }


  /**
   * Returns an array containing the types of content indexed by the searcher.
   *
   * @return array
   */
  public function getTypes() {
    return $this->pluginDefinition['types'];
  }
 
  /**
   * Returns the MENU_DEFAULT_LOCAL_TASK item which the admin UI page is added
   * to as a MENU_LOCAL_TASK. An empty string if the backend manages the admin
   * UI menu items internally.
   *
   * @return mixed
   */
  public function getPath() {
    return $this->pluginDefinition['path'];
  }

  /**
   * Returns TRUE if the searcher supports "missing" facets.
   *
   * @return boolean
   */
  public function getSupportFacetsMissing() {
    return $this->pluginDefinition['supportFacetsMissing'];
  }

  /**
   * Returns TRUE if the searcher supports the minimum facet count setting.
   *
   * @return boolean
   */
  public function getSupportFacetsMincount() {
    return $this->pluginDefinition['supportFacetsMincount'];
  }

  /**
   * Returns TRUE if the searcher should include the facets
   * defined in facetapi_facetapi_facet_info() when indexing node content,
   * FALSE if they should be skipped.
   *
   * @return boolean
   */
  public function getIncludeDefaultFacets() {
    return $this->pluginDefinition['includeDefaultFacets'];
  }
}
