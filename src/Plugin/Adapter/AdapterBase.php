<?php

/**
 * @file
 * Contains Drupal\facetapi\Plugin\AdapterBase.
 */

namespace Drupal\facetapi\Plugin\Adapter;

use Drupal\Core\Plugin\PluginBase;
use Drupal\facetapi\Adapter\AdapterInterface;

/**
 * Base class for Facet API adapters.
 *
 * @TODO: rewrite D7 comment block:
 * Adapters are responsible for abstracting interactions with the Search backend
 * that are necessary for faceted search. The adapter is also responsible for
 * retrieving facet information passed by the user via the a processor plugin
 * taking the appropriate action, whether it is checking dependencies for all
 * enabled facets or passing the appropriate query type plugin to the backend
 * so that it can execute the actual facet query.
 */
abstract class AdapterBase extends PluginBase implements AdapterInterface {

  /**
   * The searcher information as returned by facetapi_get_searcher_info().
   *
   * @var array
   */
  protected $info = array();

  /**
   * The search keys, or query text, submitted by the user.
   *
   * @var string
   */
  protected $keys;

  /**
   * An array of FacetapiFacet objects for facets being rendered.
   *
   * @var array
   *
   * @see FacetapiFacet
   */
  protected $facets = array();

  /**
   * An array of FacetapiFacetProcessor objects.
   *
   * @var array
   *
   * @see FacetapiFacetProcessor
   * @see FacetapiAdapter::processFacets()
   */
  protected $processors = array();

  /**
   * An array of executed query type plugins keyed by field name.
   *
   * @var array
   *
   * @see FacetapiQueryTypeInterface
   */
  protected $queryTypes = array();

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
  public function suppressOutput($realm_name) {
    return false;
  }

  /**
   * @TODO: generalize to ProcessorInterface and properly type hint in __construct().
   * The url processor plugin associated with this adapter.
   *
   * @var FacetapiUrlProcessor
   *
   * @see FacetapiUrlProcessor
   */
  protected $urlProcessor;

  /**
   * An array of active items created by FacetapiAdapter::processActiveItems().
   *
   * In order to retrieve data efficiently, the active items are stored in two
   * ways. The "filter" key is an associative array of active items keyed by
   * the raw filter passed through the source, usually in field:value format.
   * The "facet" key is a multidimensional array where the second dimension is
   * keyed by the machine name of the facet and the third dimension is an array
   * of active items keyed by the facet value.
   *
   * The active items are associative arrays containing (but not limited to):
   * - field alias: The facet alias defined in the facet definition.
   * - value: The active value passed through the source (usually $_GET) to
   *   filter the result set.
   * - pos: The zero-based position of the value in the source data. The url
   *   processor plugin uses the "pos" to efficiently remove certain values when
   *   building query strings in FacetapiQueryTypeInterface::getQueryString().
   *
   * Additional keys may be added to this array via the query type plugin's
   * FacetapiQueryTypeInterface::extract() method. For example, date and range
   * query types add the "start" and "end" values of the range.
   *
   * @var array
   *
   * @see FacetapiAdapter::processActiveItems()
   */
  protected $activeItems;

  /**
   * A boolean flagging whether the facets have been processed, or built.
   *
   * This variable acts as a per-adapter semaphore that ensures facet data is
   * processed only once.
   *
   * @var boolean
   *
   * @see FacetapiAdapter::processFacets()
   */
  protected $processed = FALSE;

  /**
   * Stores the search path associated with this searcher.
   *
   * @var string
   */
  protected $searchPath;

  /**
   * An array of facets that passed their dependencies.
   *
   * @var array
   */
  protected $dependenciesPassed = array();

  /**
   * Stores settings with defaults.
   *
   * @var array
   *
   * @see FacetapiAdapter::getFacetSettings()
   */
  protected $settings = array();

  /**
   * @param array $searcher_info
   */
  public function __construct(array $searcher_info) {
    $this->info = $searcher_info;

    // Load and initialize the url processor plugin. Initializing the plugin
    // fetches the data from a source, usually $_GET, and trigger the methods
    // that instantiate the query type plugins and process the active items.
    $this->urlProcessor = $this->loadUrlProcessor($this->info['url processor']);
    $this->initUrlProcessor();
  }

  /**
   * Returns a boolean flagging whether $this->searcher['searcher'] executed a
   * search.
   *
   * @return boolean
   *   A boolean flagging whether $this->searcher['searcher'] executed a search.
   *
   * @todo Generic search API should provide consistent functionality.
   */
  abstract public function searchExecuted();

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
  abstract public function suppressOutput($realm_name);

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
  public function loadUrlProcessor($id) {
    // Ensure all required url processor classes are loaded.
    // See http://drupal.org/node/1306198
    $plugin_path = dirname(__FILE__);
    require_once $plugin_path . '/url_processor.inc';
    require_once $plugin_path . '/url_processor_standard.inc';

    // Get the url processor plugin class. If the class for the passed plugin
    // cannot be retrieved, log the error and load the standard plugin.
    if (!$class = ctools_plugin_load_class('facetapi', 'url_processors', $id, 'handler')) {
      if ('standard' != $id) {
        watchdog('facetapi', 'Url processor plugin "@id" not valid, loading standard plugin.', array('@id' => $id), WATCHDOG_ERROR);
        $class = ctools_plugin_load_class('facetapi', 'url_processors', 'standard', 'handler');
      }
      else {
        // The plugins are not registered, probably because CTools is weighted
        // heavier than Facet API. It is still unclear why this even matters.
        // Let's raise a call to action and explicitly set the class to prevent
        // fatal errors.
        // @see http://drupal.org/node/1816110
        watchdog('facetapi', 'Url processor plugins are not yet registered, loading standard plugin. Please visit <a href="@url">@url</a> for more information.', array('@id' => $id, '@url' => 'http://drupal.org/node/1816110'), WATCHDOG_ERROR);
        $class = 'FacetapiUrlProcessorStandard';
      }
    }

    // Instantiates and initializes plugin.
    return new $class($this);
  }

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
  public function initUrlProcessor() {
    $params = $this->urlProcessor->fetchParams();
    $filter_key = $this->urlProcessor->getFilterKey();
    $this->setParams($params, $filter_key);
  }

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
  public function setParams(array $params = array(), $filter_key = 'f') {
    $this->facets = array();
    $normalized = $this->urlProcessor->normalizeParams($params, $filter_key);
    $this->urlProcessor->setParams($normalized, $filter_key);
    $this->processActiveItems();
    return $this;
  }

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
  public function processActiveItems() {
    $this->activeItems = array('facet' => array(), 'filter' => array());

    // Refresh the query type plugins for all enabled facets.
    $this->queryTypes = $this->loadQueryTypePlugins();

    // Group enabled facets by facet alias. It is possible for aliases to be
    // common across different facets, although they are usually unique.
    $enabled_aliases = array();
    foreach ($this->getEnabledFacets() as $facet) {
      $enabled_aliases[$facet['field alias']][] = $facet['name'];
      $this->activeItems['facet'][$facet['name']] = array();
    }

    // Get the stored facet data and iterate over the passed values.
    $filter_key = $this->urlProcessor->getFilterKey();
    $params = $this->urlProcessor->getParams();
    foreach ($params[$filter_key] as $pos => $filter) {
      // Bail if the value is not a scalar, for example an object or array.
      if (!is_scalar($filter)) {
        continue;
      }

      // Perform basic parsing of the filter.
      $parts = explode(':', $filter, 2);
      $field_alias = rawurldecode($parts[0]);
      if (isset($parts[1]) && isset($enabled_aliases[$field_alias])) {

        // Store basic information about the active item. For details on how
        // this array is structured, refer to the FacetapiAdapter::activeItems
        // docblock.
        $item = array(
          'field alias' => $field_alias,
          'value' => $parts[1],
          'pos' => $pos,
        );

        // Initialize and populate the active items. For details on how this
        // array is structured, refer to the FacetapiAdapter::activeItems
        // docblock.
        $this->activeItems['filter'][$filter] = $item;
        $this->activeItems['filter'][$filter]['facets'] = array();
        foreach ($enabled_aliases[$field_alias] as $facet_name) {
          $item += $this->queryTypes[$facet_name]->extract($item);
          $this->activeItems['filter'][$filter]['facets'][] = $facet_name;
          $this->activeItems['facet'][$facet_name][$parts[1]] = $item;
        }
      }
    }
  }

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
  public function loadQueryTypePlugins() {
    $query_types = array();

    // Gather a whitelist of query type plugins supported by this searcher.
    $plugin_ids = array();
    foreach (ctools_get_plugins('facetapi', 'query_types') as $plugin) {
      if ($this->info['adapter'] == $plugin['handler']['adapter']) {
        $type = call_user_func(array($plugin['handler']['class'], 'getType'));
        $plugin_ids[$type] = $plugin['handler']['class'];
      }
    }

    // Iterate over enabled facets and instantiate each one's query type plugin.
    foreach ($this->getEnabledFacets() as $facet) {

      // Get the machine name of the query type plugin used by this facet.
      if (1 == count($facet['query types'])) {
        // There is only one query type supported by this facet, so use it.
        $query_type = $facet['query types'][0];
      }
      else {
        // Get query type from settings if there is more than one supported by
        // this facet. For example, some facets only support simple term queries
        // while others also support numeric range queries. In those instances,
        // administrators have to select which one to use in the facet's
        // administrative interface.
        $settings = $this->getFacetSettingsGlobal($facet)->settings;
        $query_type = !empty($settings['query_type']) ? $settings['query_type'] : FALSE;
      }

      // Instantiate the query type plugin if it is in the whitelist of query
      // types supported by the backend. Store objects in an instance variable
      // keyed by the machine name of the facet.
      if ($query_type && isset($plugin_ids[$query_type])) {
        $plugin = new $plugin_ids[$query_type]($this, $facet);
        $query_types[$facet['name']] = $plugin;
      }
      else {
        $query_types[$facet['name']] = FALSE;
      }
    }

    // Return the instantiated query type plugins.
    return $query_types;
  }

  /**
   * Return the instantiated url processor plugin.
   *
   * @return FacetapiUrlProcessor
   *   The url processor plugin.
   */
  public function getUrlProcessor() {
    return $this->urlProcessor;
  }

  /**
   * Return all active items keyed by raw filter, usually in field:value format.
   *
   * @return array
   *   An array of active filters keyed by raw filter.
   */
  public function getAllActiveItems() {
    return $this->activeItems['filter'];
  }

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
  public function getActiveItems(array $facet) {
    return $this->activeItems['facet'][$facet['name']];
  }

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
  public function itemActive($facet_name, $value) {
    return (int) isset($this->activeItems['facet'][$facet_name][$value]);
  }

  /**
   * Returns the id of the adapter plugin.
   *
   * @return string
   *   The machine readable if of the adapter plugin.
   */
  public function getId() {
    return $this->info['adapter'];
  }

  /**
   * Returns the machine readable name of the searcher.
   *
   * @return string
   *   The machine readable name of the searcher.
   */
  public function getSearcher() {
    return $this->info['name'];
  }

  /**
   * Returns the type of content indexed by $this->searcher['searcher'].
   *
   * @return
   *   The type of content indexed by $this->searcher['searcher'].
   */
  public function getTypes() {
    return $this->info['types'];
  }

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
  public function getPath($realm_name) {
    return $this->info['path'] . '/facets/' . $realm_name;
  }

  /**
   * Returns the search path associated with this searcher.
   *
   * @return string
   *   A string containing the search path.
   *
   * @todo D8 should provide an API function for this.
   */
  public function getSearchPath() {
    if (NULL === $this->searchPath) {
      // Backwards compatibility with apachesolr <= beta8.
      // @see http://drupal.org/node/1305748#comment-5102352
      foreach (array($this->info['module'], $this->info['module'] . '_search') as $module) {
        if ($path = module_invoke($module, 'search_info')) {
          $this->searchPath = 'search/' . $path['path'];
          if (!isset($_GET['keys']) && ($keys = $this->getSearchKeys())) {
            $this->searchPath .= '/' . $keys;
          }
          break;
        }
      }
    }
    return $this->searchPath;
  }

  /**
   * Sets the search keys, or query text, submitted by the user.
   *
   * @param string $keys
   *   The search keys, or query text, submitted by the user.
   *
   * @return FacetapiAdapter
   *   An instance of this class.
   */
  public function setSearchKeys($keys) {
    $this->keys = $keys;
    return $this;
  }

  /**
   * Gets the search keys, or query text, submitted by the user.
   *
   * @return string
   *   The search keys, or query text, submitted by the user.
   */
  public function getSearchKeys() {
    return $this->keys;
  }

  /**
   * Returns the number of results returned by the search query.
   *
   * @return int
   *   The number of results returned by the search query.
   */
  public function getResultCount() {
    global $pager_total;
    return isset($pager_total[0]) ? $pager_total[0] : 0;
  }

  /**
   * Returns the number of results per page.
   *
   * @return int
   *   The number of results per page, or the limit.
   */
  public function getPageLimit() {
    global $pager_limits;
    return isset($pager_limits[0]) ? $pager_limits[0] : 10;
  }

  /**
   * Returns the page number of the search result set.
   *
   * @return int
   *   The current page of the result set.
   */
  public function getPageNumber() {
    return pager_find_page() + 1;
  }

  /**
   * Returns the total number of pages in the result set.
   *
   * @return int
   *   The total number of pages.
   */
  public function getPageTotal() {
    global $pager_total;
    return isset($pager_total[0]) ? $pager_total[0] : 0;
  }

  /**
   * Allows for backend specific overrides to the settings form.
   *
   * @see facetapi_facet_display_form()
   */
  public function settingsForm(&$form, &$form_state) {
    // Nothing to do...
  }

  /**
   * Provides default values for the backend specific settings.
   *
   * All settings added via FacetapiAdapter::settingsForm() should have
   * corresponding defaults in this method.
   *
   * @return array
   *   The defaults keyed by setting name to value.
   */
  public function getDefaultSettings() {
    return array();
  }

  /**
   * Returns TRUE if the backend supports "missing" facets.
   *
   * @return bool
   *   TRUE if the backend supports "missing" facets, FALSE otherwise.
   */
  public function supportsFacetMissing() {
    return $this->info['supports facet missing'];
  }

  /**
   * Returns TRUE if the back-end supports "minimum facet counts".
   *
   * @return bool
   *   TRUE if the backend supports "minimum facet counts" facets, FALSE
   *   otherwise.
   */
  public function supportsFacetMincount() {
    return $this->info['supports facet mincount'];
  }

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
  function addActiveFilters($query) {
    module_load_include('inc', 'facetapi', 'facetapi.callbacks');
    facetapi_add_active_searcher($this->info['name']);

    // Invoke initActiveFilters hook.
    $this->initActiveFilters($query);

    foreach ($this->getEnabledFacets() as $facet) {
      $settings = $this->getFacet($facet)->getSettings();

      // Instantiate and execute dependency plugins.
      $display = TRUE;
      foreach ($facet['dependency plugins'] as $id) {
        $class = ctools_plugin_load_class('facetapi', 'dependencies', $id, 'handler');
        $plugin = new $class($id, $this, $facet, $settings, $this->activeItems['facet']);
        if (NULL !== ($return = $plugin->execute())) {
          $display = $return;
        }
      }

      // Store whether this facet passed its dependencies.
      $this->dependenciesPassed[$facet['name']] = $display;

      // Execute query type plugin if dependencies were met, otherwise remove
      // the facet's active items so they don't display in the current search
      // block or appear as active in the breadcrumb trail.
      if ($display && $this->queryTypes[$facet['name']]) {
        $this->queryTypes[$facet['name']]->execute($query);
      }
      else {
        foreach ($this->activeItems['facet'][$facet['name']] as $item) {
          $this->urlProcessor->removeParam($item['pos']);
          $filter = $item['field alias'] . ':' . $item['value'];
          unset($this->activeItems['filter'][$filter]);
        }
        $this->activeItems['facet'][$facet['name']] = array();
      }
    }
  }

  /**
   * Hook that allows the backend to initialize its query object for faceting.
   *
   * @param mixed $query
   *   The backend's native object.
   */
  public function initActiveFilters($query) {
    // Nothing to do ...
  }

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
  public function initSettingsObject($name, $facet_name, $realm_name = NULL) {
    $cached_settings = facetapi_get_searcher_settings($this->info['name']);
    if (!isset($cached_settings[$name])) {
      $settings = ctools_export_crud_new('facetapi');
      $settings->name = $name;
      $settings->searcher = $this->info['name'];
      $settings->realm = (string) $realm_name;
      $settings->facet = $facet_name;
      $settings->enabled = 0;
      $settings->settings = array();
    }
    else {
      $settings = $cached_settings[$name];
    }
    return $settings;
  }

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
  public function getFacetSettings(array $facet, array $realm) {
    // Build the unique name of the configuration and check whether the setting
    // has already be loaded so defaults are processed only once per setting.
    $name = $this->info['name'] . ':' . $realm['name'] . ':' . $facet['name'];
    if (!isset($this->settings[$name])) {

      // Initialize settings and flag whether it is "new" meaning that all
      // setting defaults are used.
      $this->settings[$name] = $this->initSettingsObject($name, $facet['name'], $realm['name']);
      $is_new = empty($this->settings[$name]->settings);

      // Use realm's default widget if facet doesn't define one.
      if (!empty($facet['default widget'])) {
        $widget = $facet['default widget'];
      }
      else {
        $widget = $realm['default widget'];
      }

      // Apply defaults common across all configs.
      $this->settings[$name]->settings += array(
        'weight' => 0,
        'widget' => $widget,
        'filters' => array(),
        'active_sorts' => array(),
        'sort_weight' => array(),
        'sort_order' => array(),
        'empty_behavior' => 'none',
      );

      // Apply default sort info only if the configuration is "new".
      if ($is_new) {
        $weight = -50;
        foreach ($facet['default sorts'] as $sort => $default) {
          $this->settings[$name]->settings['active_sorts'][$default[0]] = $default[0];
          $this->settings[$name]->settings['sort_weight'][$default[0]] = $weight++;
          $this->settings[$name]->settings['sort_order'][$default[0]] = $default[1];
        }
      }

      // Apply the widget plugin's default settings.
      $id = $this->settings[$name]->settings['widget'];
      $class = ctools_plugin_load_class('facetapi', 'widgets', $id, 'handler');
      // If we have an invalid widget, fall back to the realm's default widget.
      if (!$class) {
        $id = $this->settings[$name]->settings['widget'] = $realm['default widget'];
        $class = ctools_plugin_load_class('facetapi', 'widgets', $id, 'handler');
      }
      $plugin = new $class($id, $realm, $this->getFacet($facet), $this->settings[$name]);
      $this->settings[$name]->settings += $plugin->getDefaultSettings();

      // @todo Save for performance?
    }

    return $this->settings[$name];
  }

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
  public function getFacetSettingsGlobal(array $facet) {
    // Build the unique name of the configuration and check whether the setting
    // has already be loaded so defaults are processed only once per setting.
    $name = $this->info['name'] . '::' . $facet['name'];
    if (!isset($this->settings[$name])) {

      // Initialize settings and flag whether it is "new" meaning that all
      // setting defaults are used.
      $this->settings[$name] = $this->initSettingsObject($name, $facet['name']);
      $is_new = empty($this->settings[$name]->settings);

      // Ensure the default operator and query type are valid.
      // @see http://drupal.org/node/1443340
      $default_query_type = reset($facet['query types']);
      $allowed_operators = array_filter($facet['allowed operators']);
      $default_operator = key($allowed_operators);

      // Apply defaults common across all configs.
      $this->settings[$name]->settings += array(
        'operator' => $default_operator,
        'hard_limit' => 50,
        'dependencies' => array(),
        'facet_mincount' => 1,
        'facet_missing' => 0,
        'flatten' => 0,
        'query_type' => $default_query_type,
      );

      // Apply the adapter's default settings.
      $this->settings[$name]->settings += $this->getDefaultSettings();

      // Applies each dependency plugin's default settings.
      foreach ($facet['dependency plugins'] as $id) {
        if ($is_new) {
          $this->settings[$name]->settings['dependencies'] = array();
        }
        $class = ctools_plugin_load_class('facetapi', 'dependencies', $id, 'handler');
        $plugin = new $class($id, $this, $facet, $this->settings[$name], array());
        $this->settings[$name]->settings['dependencies'] += $plugin->getDefaultSettings();
      }

      // @todo Save for performance?
    }

    return $this->settings[$name];
  }

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
  public function getEnabledFacets($realm_name = NULL) {
    return facetapi_get_enabled_facets($this->info['name'], $realm_name);
  }

  /**
   * Returns a FacetapiFacet instance for the facet being rendered.
   *
   * @param array $facet
   *   The facet definition as returned by facetapi_facet_load().
   *
   * @return FacetapiFacet
   *   The facet rendering object object.
   */
  public function getFacet(array $facet) {
    if (!isset($this->facets[$facet['name']])) {
      $this->facets[$facet['name']] = new FacetapiFacet($this, $facet);
    }
    return $this->facets[$facet['name']];
  }

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
  public function getFacetQuery($facet) {
    $facet_name = (is_array($facet)) ? $facet['name'] : $facet;
    if (isset($this->queryTypes[$facet_name])) {
      return $this->queryTypes[$facet_name];
    }
  }

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
  public function getMappedValue($facet_name, $value) {
    if (isset($this->processors[$facet_name])) {
      return $this->processors[$facet_name]->getMappedValue($value);
    }
    else {
      return array('#markup' => $value);
    }
  }

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
  public function getProcessor($facet_name) {
    if (isset($this->processors[$facet_name])) {
      return $this->processors[$facet_name];
    }
    else {
      return FALSE;
    }
  }

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
  public function getQueryString(array $facet, array $values, $active) {
    return $this->urlProcessor->getQueryString($facet, $values, $active);
  }

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
  public function getFacetPath(array $facet, array $values, $active) {
    return $this->urlProcessor->getFacetPath($facet, $values, $active);
  }

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
  public function processFacets() {
    if (!$this->processed) {
      $this->processed = TRUE;

      // Initialize each facet's render array. This render array is a common
      // base for all realms and widgets.
      foreach ($this->getEnabledFacets() as $facet) {
        $processor = new FacetapiFacetProcessor($this->getFacet($facet));
        $this->processors[$facet['name']] = $processor;
        $this->processors[$facet['name']]->process();
      }

      // Set the breadcrumb trail if a search was executed.
      if ($this->searchExecuted()) {
        $this->urlProcessor->setBreadcrumb();
      }
    }
  }

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
  public function buildRealm($realm_name) {
    // Bail if realm isn't valid.
    // @todo Call watchdog()?
    if (!$realm = facetapi_realm_load($realm_name)) {
      return array();
    }

    // Make sure facet builds are initialized and breakcrumb trail is set.
    $this->processFacets();

    // Add JavaScript, initializes the realm specific render array.
    $build = array(
      '#adapter' => $this,
      '#realm' => $realm,
    );

    // Iterate over the realm's enabled facets and build their render arrays.
    foreach ($this->getEnabledFacets($realm['name']) as $facet) {
      // Continue to the next facet if this one failed its dependencies.
      if (empty($this->dependenciesPassed[$facet['name']])) {
        continue;
      }

      // Initialize the facet's render array.
      $field_alias = $facet['field alias'];
      $processor = $this->processors[$facet['name']];
      $facet_build = $this->getFacet($facet)->build($realm, $processor);

      // Try to be smart when merging the render arrays. Crazy things happen
      // when merging facets with the same field alias. In these instances we
      // want to merge only the values.
      foreach (element_children($facet_build) as $child) {
        // Bail if there is nothing to render.
        if (!element_children($facet_build[$child])) {
          continue;
        }
        // Our attempt at merging gracefully.
        if (!isset($build[$child])) {
          $build = array_merge_recursive($build, $facet_build);
        }
        else {
          if (isset($build[$child][$field_alias]) && isset($facet_build[$child][$field_alias])) {
            $build[$child][$field_alias] = array_merge_recursive(
              $build[$child][$field_alias],
              $facet_build[$child][$field_alias]
            );
          }
          elseif (isset($build[$child]['#options']) && isset($facet_build[$child]['#options'])) {
            $build[$child]['#options'] = array_merge_recursive(
              $build[$child]['#options'],
              $facet_build[$child]['#options']
            );
          }
          else {
            $build = array_merge_recursive($build, $facet_build);
          }
        }
      }
    }

    return $build;
  }
}
