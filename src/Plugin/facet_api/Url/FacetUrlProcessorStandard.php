<?php

/**
 * @file
 * Contains Drupal\facet_api\Plugin\Block\FacetUrlProcessor.
 */

namespace Drupal\facet_api\Plugin\Url;

use \Drupal\Component\Utility\UrlHelper;

/**
 * In D7 this was the standard url processor
 * This called FacetApiUrlProcessorStandard but the name would then no longer be consistent
 * with the FacetBlock class. Revisit the naming convention in this module?
 *
 * This was the standard url processor plugin that retrieved facet data from the query string.
 *
 * This plugin retrieved facet data from $_GET, and stored all information in
 * the "f" query string variable by default.
 *
 * All functions and comments in this class have currently been copy/pasted verbatim
 * and then tweaked to provide a skeleton that describes what the D7 version used to handle.
 */
class FacetUrlProcessorStandard extends FacetUrlProcessor {
  /**
   * Stored the "limit_active_items" settings for each facet.
   *
   * @var array
   */
  protected $limitActiveItems = array();

  /**
   * Implemented FacetapiUrlProcessor::fetchParams().
   *
   * Used the $_GET variable as the source for facet data.
   */
  public function fetchParams() {
    return $_GET;
  }

  /**
   * Implemented FacetapiUrlProcessor::normalizeParams().
   *
   * Striped the "q" and "page" variables from the params array.
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
  public function normalizeParams(array $params, $filter_key = 'f') {
    return UrlHelper::filterQueryParameters($params, array('q', 'page'));
  }

  /**
   * Implemented FacetapiUrlProcessor::getQueryString().
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
  public function getQueryString(array $facet, array $values, $active) {
    $query_string = $this->params;
    $active_items = $this->adapter->getActiveItems($facet);

    // Appends to qstring if inactive, removes if active.
    foreach ($values as $value) {
      if ($active && isset($active_items[$value])) {
        unset($query_string[$this->filterKey][$active_items[$value]['pos']]);
      }
      elseif (!$active) {
        $field_alias = rawurlencode($facet['field alias']);

        // Strips all other filters for this facet if limit option is set.
        if ($this->limitActiveItems($facet)) {
          foreach ($query_string[$this->filterKey] as $pos => $filter) {
            // Refactor the if statement to best practises?
            // (strpos($filter, $field_alias) === 0) or (strpos($filter, $field_alias) === FALSE)
            if (0 === strpos($filter, $field_alias)) {
              unset($query_string[$this->filterKey][$pos]);
            }
          }
        }

        // Adds the filter to the query string.
        $query_string[$this->filterKey][] = $field_alias . ':' . $value;
      }
    }

    // Removes duplicates, resets array keys and returns query string.
    // @see http://drupal.org/node/1340528
    $query_string[$this->filterKey] = array_values(array_unique($query_string[$this->filterKey]));
    return array_filter($query_string);
  }

  /**
   * Checked the facet's global "limit_active_items" settings.
   *
   * @param array $facet
   *   The facet definition as returned by facet_api_facet_load().
   *
   * @return int
   *   Whether or not to limit active items to one per facet.
   */
  public function limitActiveItems(array $facet) {
    // If the limit property is not set for this facet
    // retreive the settings from the facet via the adapter
    if (!isset($this->limitActiveItems[$facet['name']])) {
      $settings = $this->adapter->getFacetSettingsGlobal($facet);
      $this->limitActiveItems[$facet['name']] = $settings->settings['limit_active_items'];
    }
    return $this->limitActiveItems[$facet['name']];
  }

  /**
   * Implemented FacetapiUrlProcessor::setBreadcrumb().
   */
  public function setBreadcrumb() {
    // Get the current breadcrumbs
    $breadcrumb = drupal_get_breadcrumb();

    // Gets search keys and active items from the adapter.
    $keys = $this->adapter->getSearchKeys();
    $active_items = $this->adapter->getAllActiveItems();

    // Get the current menu item.
    // Variable is not used however. Remove this line?
    $item = menu_get_item();

    // Initializes base breadcrumb query.
    $query = $this->params;
    unset($query[$this->filterKey]);

    // Adds the current search to the query.
    if ($keys) {
      // The last item should be text, not a link.
      $breadcrumb[] = $active_items ? l($keys, current_path(), array('query' => $query)) : check_plain($keys);
    }

    // Adds filters to the breadcrumb trail.
    $last = end($active_items);
    foreach ($active_items as $item) {
      // Add items in the 'field_alias:value' format
      $query[$this->filterKey][] = rawurlencode($item['field alias']) . ':' . $item['value'];

      // Replaces with the mapped value.
      $value = $this->adapter->getMappedValue($item['facets'][0], $item['value']);

      // The last item should be text, not a link.
      if ($last == $item) {
        $breadcrumb[] = !empty($value['#html']) ? $value['#markup'] : check_plain($value['#markup']);
      }
      else {
        // Appends the filter to the breadcrumb trail.
        $breadcrumb[] = l($value['#markup'], current_path(), array('query' => $query, 'html' => !empty($value['#html'])));
      }
    }

    // Sets the breadcrumb trail with the keys and filters.
    drupal_set_breadcrumb($breadcrumb);
  }
  /**
   * Allowed for processor specific overrides to the settings form.
   */
  public function settingsForm(&$form, &$form_state) {
    $facet = $form['#facet_api']['facet'];
    $settings = $this->adapter->getFacetSettingsGlobal($facet);

    // Add the limit active item field to the form
    $form['global']['limit_active_items'] = array(
      '#type' => 'checkbox',
      '#title' => t('Limit to one active item'),
      '#prefix' => '<div class="facet_api-global-setting">',
      '#suffix' => '</div>',
      '#default_value' => !empty($settings->settings['limit_active_items']),
      '#description' => t('Enabling this option allows only one item to be active at a time.'),
    );
  }

  /**
   * Provided default values for the backend specific settings.
   *
   * @return array
   *   The defaults keyed by setting name to value. Defaults to '0'.
   */
  public function getDefaultSettings() {
    return array(
      'limit_active_items' => 0
    );
  }
}
