<?php

/**
 * @file
 * Contains \Drupal\facetapi\Config\FacetSourceConfigOverrideCrudEvent.
 */

namespace Drupal\facetapi\Config;

use Symfony\Component\EventDispatcher\Event;

/**
 * Provides a facet source override event for event listeners.
 *
 * @see \Drupal\Core\Config\ConfigCrudEvent
 */
class FacetSourceConfigOverrideCrudEvent extends Event {

  /**
   * Configuration object.
   *
   * @var \Drupal\facetapi\Config\FacetSourceConfigOverride
   */
  protected $override;

  /**
   * Constructs a configuration event object.
   *
   * @param \Drupal\facetapi\Config\FacetSourceConfigOverride $override
   *   Configuration object.
   */
  public function __construct(FacetSourceConfigOverride $override) {
    $this->override = $override;
  }

  /**
   * Gets configuration object.
   *
   * @return \Drupal\facetapi\Config\FacetSourceConfigOverride
   *   The configuration object that caused the event to fire.
   */
  public function getLanguageConfigOverride() {
    return $this->override;
  }

}
