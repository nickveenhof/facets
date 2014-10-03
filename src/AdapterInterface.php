<?php

/**
 * @file
 * Contains Drupal\facetapi\Plugin\AdapterInterface.
 */

namespace Drupal\facetapi;

interface AdapterInterface {

  /**
   * Constructs a FacetapiAdapter object.
   *
   * Stores information about the searcher that the adapter is associated with.
   * Registers and instantiates all query type plugins that are associated with
   * the searcher's active facets. Instantiates the url processor plugin
   * associated with this adapter and retrieves facet information from some
   * source, usually $_GET. See the url processor plugin's implementation of
   * FacetapiUrlProcessor::fetchParams() for details on the source containing
   * the facet data.
   *
   * @param array $searcher_info
   *   The searcher information as returned by facetapi_get_searcher_info().
   */
  public function __construct(SearcherInterface $searcher);

  /**
   * Returns a boolean flagging whether $this->searcher['searcher'] executed a
   * search.
   *
   * @return boolean
   *   A boolean flagging whether $this->searcher['searcher'] executed a search.
   *
   * @todo Generic search API should provide consistent functionality.
   */
  public function searchExecuted();

  /**
   * Returns a boolean flagging whether facets in a realm shoud be displayed.
   *
   * Useful, for example, for suppressing sidebar blocks in some cases. Apache
   * Solr Search Integration used this method to prevent blocks from being
   * displayed when the module was configured to render them in the search body
   * on "empty" searches instead of the normal facet location.
   *
   * @param string $realm_name
   *   The machine readable name of the realm.
   *
   * @return boolean
   *   A boolean flagging whether to display a given realm.
   *
   * @todo It appears that no implementing modules are leveraging this anymore.
   *   Let's discuss whether to deprecate this method or even remove it from
   *   future versions of Facet API at http://drupal.org/node/1661410.
   */
  public function suppressOutput($realm_name);

  /**
   * Loads the URL processor associated with this adapter.
   *
   * Use FacetapiAdapter::getUrlProcessor() in favor of this method when getting
   * the adapter for use in other classes. This method is separated out form the
   * constructor for testing purposes only.
   *
   * @param string $id
   *   The machine name of the url processor plugin.
   *
   * @return FacetapiUrlProcessor
   *   An instance of the url processor plugin.
   *
   * @see http://drupal.org/node/1668484
   */
  public function loadUrlProcessor($id);

  /**
   * Extracts, stores, and processes facet data.
   *
   * Wrapper around FacetapiAdapter::setParams() that fetches the params via the
   * url processor plugin from some source, usually $_GET, and passes the filter
   * key that is set in the plugin. This method is useful when the params and
   * filter key are reset directly through the url processor and the active
   * items need to be reprocessed by the adapter.
   *
   * @see FacetapiAdapter::setParams()
   */
  public function initUrlProcessor();

  /**
   * Processes and stores the extracted facet data.
   *
   * Uses the url processor plugin to normalize the data extracted from the
   * source and store it for later retrieval. Calls the active item processing
   * routine, see FacetapiAdapter::processActiveItems() for more details.
   *
   * @param array $params
   *   An array of keyed params, such as $_GET.
   * @param string $filter_key
   *   The array key in $params containing the facet data.
   *
   * @return FacetapiAdapter
   *   An instance of this class.
   *
   * @see FacetapiUrlProcessor::normalizeParams()
   * @see FacetapiAdapter::processActiveItems()
   */
  public function setParams(array $params = array(), $filter_key = 'f');

  /**
   * Processes active facet items.
   *
   * Instantiates the query type plugins for all enabled facets. Extracts active
   * items from the source, usually the query string, and uses the query type
   * plugins to extract any additional information such as the start and end
   * values for ranges.
   *
   * @see FacetapiAdapter::setParams()
   */
  public function processActiveItems();

  /**
   * Returns an array of instantiated query type plugins for enabled facets.
   *
   * Iterates over the adapter's enabled facets and loads the appropriate query
   * type plugin. If the adapter does not support the plugin, FALSE is set in
   * place of a FacetapiQueryTypeInterface object.
   *
   * @return array
   *   An associative array keyed by facet name to FacetapiQueryTypeInterface
   *   object, FALSE if the query type is not supported bu this searcher.
   *
   * @see FacetapiAdapter::processActiveItems()
   */
  public function loadQueryTypePlugins();

  /**
   * Return the instantiated url processor plugin.
   *
   * @return FacetapiUrlProcessor
   *   The url processor plugin.
   */
  public function getUrlProcessor();

  /**
   * Return all active items keyed by raw filter, usually in field:value format.
   *
   * @return array
   *   An array of active filters keyed by raw filter.
   */
  public function getAllActiveItems();

  /**
   * Returns a facet's active items.
   *
   * @param array|string $facet
   *   Either the facet definition as returned by facetapi_facet_load() or the
   *   machine readable name of the facet.
   *
   * @return array
   *   An associative array containing (but not limited to):
   * - field alias: The facet alias defined in the facet definition.
   * - value: The active value passed through the source (usually $_GET) to
   *   filter the result set.
   * - pos: The zero-based position of the value in the source data. The url
   *   processor plugin uses the "pos" to efficiently remove certain values when
   *   building query strings in FacetapiQueryTypeInterface::getQueryString().
   * - ...: Additional keys added to the array by the query type plugin's
   *   FacetapiQueryTypeInterface::extract() method. For example, date and range
   *   query types add the "start" and "end" values of the range.
   */
  public function getActiveItems(array $facet);

  /**
   * Tests whether a facet item is active by passing it's value.
   *
   * @param string $facet_name
   *   The machine readable name of the facet.
   * @param string $value
   *   The facet item's value.
   *
   * @return int
   *   Returns 1 if the item is active, 0 if it is inactive.
   */
  public function itemActive($facet_name, $value);

  /**
   * Returns the id of the adapter plugin.
   *
   * @return string
   *   The machine readable if of the adapter plugin.
   */
  public function getId();

  /**
   * Returns the machine readable name of the searcher.
   *
   * @return string
   *   The machine readable name of the searcher.
   */
  public function getSearcher();

  /**
   * Returns the type of content indexed by $this->searcher['searcher'].
   *
   * @return
   *   The type of content indexed by $this->searcher['searcher'].
   */
  public function getTypes();

  /**
   * Returns the path to the the realm's admin settings page.
   *
   * @param string $realm_name
   *   The machine readable name of the realm.
   *
   * @return
   *   The path to the admin settings.
   *
   * @todo This method is too nondescript. It cannot be changed since it is used
   *   heavily by implementing modules, but it should be deprecated in favor of
   *   a method with a more descript name.
   */
  public function getPath($realm_name);

  /**
   * Returns the search path associated with this searcher.
   *
   * @return string
   *   A string containing the search path.
   *
   * @todo D8 should provide an API function for this.
   */
  public function getSearchPath();

  /**
   * Sets the search keys, or query text, submitted by the user.
   *
   * @param string $keys
   *   The search keys, or query text, submitted by the user.
   *
   * @return FacetapiAdapter
   *   An instance of this class.
   */
  public function setSearchKeys($keys);

  /**
   * Gets the search keys, or query text, submitted by the user.
   *
   * @return string
   *   The search keys, or query text, submitted by the user.
   */
  public function getSearchKeys();

  /**
   * Returns the number of results returned by the search query.
   *
   * @return int
   *   The number of results returned by the search query.
   */
  public function getResultCount();

  /**
   * Returns the number of results per page.
   *
   * @return int
   *   The number of results per page, or the limit.
   */
  public function getPageLimit();

  /**
   * Returns the page number of the search result set.
   *
   * @return int
   *   The current page of the result set.
   */
  public function getPageNumber();

  /**
   * Returns the total number of pages in the result set.
   *
   * @return int
   *   The total number of pages.
   */
  public function getPageTotal();

  /**
   * Allows for backend specific overrides to the settings form.
   *
   * @see facetapi_facet_display_form()
   */
  public function settingsForm(&$form, &$form_state);

  /**
   * Provides default values for the backend specific settings.
   *
   * All settings added via FacetapiAdapter::settingsForm() should have
   * corresponding defaults in this method.
   *
   * @return array
   *   The defaults keyed by setting name to value.
   */
  public function getDefaultSettings();

  /**
   * Returns TRUE if the backend supports "missing" facets.
   *
   * @return bool
   *   TRUE if the backend supports "missing" facets, FALSE otherwise.
   */
  public function supportsFacetMissing();

  /**
   * Returns TRUE if the back-end supports "minimum facet counts".
   *
   * @return bool
   *   TRUE if the backend supports "minimum facet counts" facets, FALSE
   *   otherwise.
   */
  public function supportsFacetMincount();

  /**
   * Allows the backend to add facet queries to its native query object.
   *
   * This method is called by the implementing module to initialize the facet
   * display process. The following actions are taken:
   * - FacetapiAdapter::initActiveFilters() hook is invoked.
   * - Dependency plugins are instantiated and executed.
   * - Query type plugins are executed.
   *
   * @param mixed $query
   *   The backend's native query object.
   *
   * @todo Should this method be deprecated in favor of one name init()? This
   *   might make the code more readable in implementing modules.
   *
   * @see FacetapiAdapter::initActiveFilters()
   */
  function addActiveFilters($query);

  /**
   * Hook that allows the backend to initialize its query object for faceting.
   *
   * @param mixed $query
   *   The backend's native object.
   */
  public function initActiveFilters($query);

  /**
   * Initializes a new settings object.
   *
   * @param string $name
   *   A string containing the unique name of the configuration.
   * @param string $facet_name
   *   The machine readable name of the facet.
   * @param string $realm_name
   *   A string containing the machine readable name of the realm, NULL if we
   *   are initializing global settings.
   *
   * @return stdClass
   *   An object containing the initialized settings.
   *
   * @see ctools_export_crud_new()
   */
  public function initSettingsObject($name, $facet_name, $realm_name = NULL);

  /**
   * Returns realm specific settings for a facet.
   *
   * Realm specific settings usually act on the facet data after it has been
   * returned by the backend, for example the display widget and sort settings.
   *
   * @param array $facet
   *   The facet definition as returned by facetapi_facet_load().
   * @param array $realm
   *   The realm definition as returned by facetapi_realm_load().
   *
   * @return stdClass
   *   An object containing the settings.
   *
   * @see FacetapiAdapter::initSettingsObject()
   * @see ctools_export_crud_load()
   */
  public function getFacetSettings(array $facet, array $realm);

  /**
   * Returns global settings for a facet.
   *
   * Global settings are usually things that are processed by the backend such
   * as the hard limit or query type. It isn't practical to execute separate
   * search queries per realm to make these settings realm specific, so they
   * are configured globally and reflected across all realms for this searcher.
   *
   * @param array $facet
   *   The facet definition as returned by facetapi_facet_load().
   *
   * @return
   *   An object containing the settings.
   *
   * @see ctools_export_crud_load()
   */
  public function getFacetSettingsGlobal(array $facet);

  /**
   * Returns enabled facets for the searcher associated with this adapter.
   *
   * @param string $realm_name
   *   The machine readable name of the realm, pass NULL to get the enabled
   *   facets in all realms.
   *
   * @return array
   *   An array of enabled facets.
   *
   * @see facetapi_get_enabled_facets()
   */
  public function getEnabledFacets($realm_name = NULL);

  /**
   * Returns a FacetapiFacet instance for the facet being rendered.
   *
   * @param array $facet
   *   The facet definition as returned by facetapi_facet_load().
   *
   * @return FacetapiFacet
   *   The facet rendering object object.
   */
  public function getFacet(array $facet);

  /**
   * Returns the facet's instantiated query type plugin.
   *
   * @param array|string $facet
   *   Either the facet definition as returned by facetapi_facet_load() or the
   *   machine readable name of the facet.
   *
   * @return FacetapiQueryTypeInterface|NULL
   *   The instantiated query type plugin, NULL if the passed facet is not valid
   *   or does not have a query type plugin associated with it.
   */
  public function getFacetQuery($facet);

  /**
   * Maps a facet's index value to a human readable value displayed to the user.
   *
   * @param string $facet_name
   *   The machine readable name of the facet.
   * @param string $value
   *   The raw value passed through the query string.
   *
   * @return string
   *   The mapped value.
   */
  public function getMappedValue($facet_name, $value);

  /**
   * Returns the processor associated with the facet.
   *
   * @param string $facet_name
   *   The machine readable name of the facet.
   *
   * @return FacetapiFacetProcessor|FALSE
   *   The instantiated processor object, FALSE if the passed facet is not valid
   *   or does not have processor instantiated for it.
   */
  public function getProcessor($facet_name);

  /**
   * Helper function that returns the query string variables for a facet item.
   *
   * @param array $facet
   *   The facet definition as returned by facetapi_facet_load().
   * @param array $values
   *   An array containing the item's values being added to or removed from the
   *   query string dependent on whether or not the item is active.
   * @param int $active
   *   An integer flagging whether the item is active or not.
   *
   * @return array
   *   The query string vriables.
   *
   * @see FacetapiUrlProcessor::getQueryString()
   */
  public function getQueryString(array $facet, array $values, $active);

  /**
   * Helper function that returns the path for a facet link.
   *
   * @param array $facet
   *   The facet definition as returned by facetapi_facet_load().
   * @param array $values
   *   An array containing the item's values being added to or removed from the
   *   query string dependent on whether or not the item is active.
   * @param int $active
   *   An integer flagging whether the item is active or not.
   *
   * @return string
   *   The facet path.
   *
   * @see FacetapiUrlProcessor::getFacetPath()
   */
  public function getFacetPath(array $facet, array $values, $active);

  /**
   * Initializes facet builds, sets the breadcrumb trail.
   *
   * Facets are built via FacetapiFacetProcessor objects. Facets only need to be
   * processed, or built, once regardless of how many realms they are rendered
   * in. The FacetapiAdapter::processed semaphore is set when this method is
   * called ensuring that facets are built only once regardless of how many
   * times this method is called.
   *
   * @todo For clarity, should this method be named buildFacets()?
   */
  public function processFacets();

  /**
   * Uses each facet's widget to build the realm's render array.
   *
   * This array is passed to Drupal's rendering layer for display. The widget
   * plugins are executed to convert the base render arrays constructed by
   * FacetapiAdapter::processFacets() to a realm specific render array.
   *
   * @param string $realm_name
   *   The machine readable name of the realm.
   *
   * @return array
   *   The realm's render array.
   *
   * @see FacetapiAdapter::processFacets()
   */
  public function buildRealm($realm_name);
}
