<?php


/**
 * @file
 * Contains Drupal\facet_api\Plugin\Block\FacetUrlProcessor.
 */

namespace Drupal\facet_api\Plugin\Url;

use Drupal\facet_api\Plugin\Adapter\AdapterInterface;

/**
 * In D7 this was the abstract class extended by all url processor plugins.
 * This called FacetApiUrlProcessor but the name would then no longer be consistent
 * with the FacetBlock class. Revisit the naming convention in this module?
 *
 * Url processor plugins provided a pluggable method of retrieving facet data.
 * Most commonly facet data was retrieved from a query string variable via $_GET,
 * however custom plugis could be written to retrieve data from the path as well.
 * In addition to facet data retrieval, the url processor plugin was also
 * responsible for building facet links and setting breadcrumb trails.
 *
 * Each adapter instance was associated with a single url processor plugin. The
 * plugin was associated with the adapter via hook_facet_api_searcher_info()
 * implementations.
 *
 * All functions and comments in this class have currently been copy/pasted verbatim
 * and then tweaked to provide a skeleton that describes what the D7 version used to handle.
 */

abstract class FacetUrlProcessor {
  /**
   * Held the adapter that the url processor plugin was associated with.
   *
   * @var AdapterInterface
   */
  protected $adapter;

  /**
   * An array of facet params, usually $_GET.
   *
   * @var array.
   */
  protected $params = array();

  /**
   * The array key in FacetapiUrlProcessor::params that would contain the facet data.
   * Also gets defined in the function parameters again.
   * Try and keep this in one place if possible?
   *
   * @var string
   */
  protected $filterKey = 'f';

  /**
   * Constructed the FacetapiUrlProcessor object.
   *
   * @param AdapterInterface $adapter
   *   The adapter that the url processor plugin would get associated with.
   */
  public function __construct(AdapterInterface $adapter) {
    $this->adapter = $adapter;
  }

  /**
   * Fetched parameters from the source, usually $_GET.
   *
   * This method would be invoked in FacetapiAdapter::__construct().
   *
   * @return array
   *   An associative array containing the params, usually $_GET.
   */
  abstract public function fetchParams();

  /**
   * Normalized the array returned by FacetapiAdapter::fetchParams().
   *
   * When extracting data from a source such as $_GET, there are certain items
   * that you might not want, for example the "q" or "page" keys. This method is
   * useful for filtering those out. In addition, plugins that do not get data
   * from $_GET can use this method to normalize the data into an associative
   * array that closely matches the data structure of $_GET.
   *
   * @param array $params
   *   An array of keyed params, usually as $_GET.
   * @param string $filter_key
   *   The array key in $params containing the facet data, defaults to "f".
   *   Hardcoded to 'f' in D7 but actually it is already defined in the filterKey property
   *   so it might make sense to no longer hardcode this in here if this function remains?
   *
   * @return array
   *   An associative array containing the normalized params.
   */
  abstract public function normalizeParams(array $params, $filter_key = 'f');

  /**
   * Returned the query string variables for a facet item.
   *
   * The return array must be able to be passed as the "query" key of the
   * options array passed as the second argument to the url() function. See
   * http://api.drupal.org/api/drupal/includes%21common.inc/function/url/7 for
   * more details.
   *
   * @param array $facet
   *   The facet definition as returned by facet_api_facet_load().
   * @param array $values
   *   An array containing the item's values being added to or removed from the
   *   query string dependent on whether or not the item is active.
   * @param int $active
   *   An integer flagging whether the item is active or not. 1 if the item is
   *   active, 0 if it is not.
   *
   * @return array
   *   The query string variables.
   */
  abstract public function getQueryString(array $facet, array $values, $active);

  /**
   * Returned the path for a facet item.
   *
   * This function seems to take more parameters then it needs
   * and is not used in the standard url processor?
   *
   * @param array $facet
   *   The facet definition.
   * @param array $values
   *   An array containing the item's values being added to or removed from the
   *   query string dependent on whether or not the item is active.
   * @param int $active
   *   An integer flagging whether the item is active or not.
   *
   * @return string
   *   The path of the facet.
   */
  public function getFacetPath(array $facet, array $values, $active) {
    // Simply returned the search path defined by the adapter
    return $this->adapter->getSearchPath();
  }

  /**
   * This was used by the breadcrumb trail for active searches.
   *
   * This method was called by FacetapiAdapter::processFacets(), which in turn was called
   * directly by the backend to search the chain of Facet API events.
   *
   * This functionality might be moved in D8 or become an option rather then
   * remain standard functionality
   */
  abstract public function setBreadcrumb();

  /**
   * Used to set the normalized parameters.
   *
   * This method was usually called by FacetapiAdapter::setParams() and would very
   * rarely get called directly.
   *
   * @param array $params
   *   An array of normalized params hat have already been passed through
   *   FacetapiUrlProcessor::normalizeParams().
   * @param string $filter_key
   *   The array key in $params containing the facet data, defaults to "f".
   *   Hardcoded to 'f' in D7 but actually it is already defined in the filterKey property
   *   so it might make sense to no longer hardcode this in here if this function remains?
   *
   * @return FacetUrlProcessor
   *   An instance of this class.
   */
  public function setParams(array $params, $filter_key = 'f') {
    $this->params = $params;
    $this->filterKey = $filter_key;
    if (!isset($this->params[$this->filterKey]) || !is_array($this->params[$this->filterKey])) {
      $this->params[$this->filterKey] = array();
    }
    return $this;
  }

  /**
   * A simple getter that returned the params property.
   *
   * @return array
   *   An array containing the params.
   */
  public function getParams() {
    return $this->params;
  }

  /**
   * Removed an item from the $this->params array.
   *
   * @param int $pos
   *   The zero-based position of the value in the source data.
   */
  public function removeParam($pos) {
    unset($this->params[$this->filterKey][$pos]);
  }

  /**
   * A simple getter that returned the filter key property.
   *
   * @return string
   *   A string containing the filter key.
   */
  public function getFilterKey() {
    return $this->filterKey;
  }

  /**
   * A function that allowed for processor specific overrides to the settings form.
   */
  public function settingsForm(&$form, &$form_state) {
    // Nothing to do...
  }

  /**
   * Provided default values for the backend specific settings.
   *
   * @return array
   *   The defaults keyed by setting name to value.
   */
  public function getDefaultSettings() {
    return array();
  }
}
