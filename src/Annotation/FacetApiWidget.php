<?php

/**
 * @file
 * Contains \Drupal\facetapi\Annotation\FacetApiWidget.
 */

namespace Drupal\facetapi\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Facet API Widget annotation.
 *
 * @see \Drupal\facetapi\Widget\WidgetPluginManager
 * @see plugin_api
 *
 * @ingroup plugin_api
 *
 * @Annotation
 */
class FacetApiWidget extends Plugin {

  /**
   * The widget plugin id.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the widget plugin.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * The widget description.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $description;

  /**
   * The possible query types used by this widget.
   *
   * @var array
   */
  public $queryType = [];

  /**
   * Class used to retrieve derivative definitions of the facet_manager.
   *
   * @var string
   */
  public $derivative = '';

}
