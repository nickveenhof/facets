<?php
/**
 * @file
 * Contains \Drupal\facetapi\Annotation\FacetApiUrlProcessor.
 */

namespace Drupal\facetapi\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Facet API UrlProcessor annotation object.
 *
 * @see \Drupal\facetapi\UrlProcessor\UrlProcessorPluginManager
 * @see plugin_api
 *
 * @ingroup plugin_api
 *
 * @Annotation
 */
class FacetApiUrlProcessor extends Plugin {

  /**
   * The facet_manager plugin id
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the url processor plugin
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * The url processor description.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $description;

  /**
   * Class used to retrieve derivative definitions of the url processor.
   *
   * @var string
   */
  public $derivative = '';

}