<?php

/**
 * @file
 * Contains \Drupal\facetapi\Config\FacetSourceConfigOverrideEvents.
 */

namespace Drupal\facetapi\Config;

/**
 * Defines events for facet source configuration overrides.
 *
 * @see \Drupal\Core\Config\ConfigCrudEvent
 */
final class FacetSourceConfigOverrideEvents {

  /**
   * The name of the event fired when saving the configuration override.
   *
   * This event allows you to perform custom actions whenever a facet source
   * config override is saved. The event listener method receives a
   * \Drupal\facetapi\Config\FacetSourceConfigOverrideCrudEvent instance.
   *
   * @Event
   *
   * @see \Drupal\facetapi\Config\FacetSourceConfigOverrideCrudEvent
   * @see \Drupal\facetapi\Config\FacetSourceConfigOverride::save()
   */
  const SAVE_OVERRIDE = 'facet_source.save_override';

  /**
   * The name of the event fired when deleting the configuration override.
   *
   * This event allows you to perform custom actions whenever a facet source
   * config override is deleted. The event listener method receives a
   * \Drupal\facetapi\Config\FacetSourceConfigOverrideCrudEvent instance.
   *
   * @Event
   *
   * @see \Drupal\facetapi\Config\FacetSourceConfigOverrideCrudEvent
   * @see \Drupal\facetapi\Config\FacetSourceConfigOverride::delete()
   */
  const DELETE_OVERRIDE = 'facet_source.delete_override';

}
