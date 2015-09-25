<?php
/**
 * @file
 * Contains \Drupal\facetapi\Annotation\FaceApiContext.
 */

namespace Drupal\facetapi\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Facet API FaceApiContext annotation object.
 *
 * @see plugin_api
 *
 * @ingroup plugin_api
 *
 * @Annotation
 */
class FacetApiContext extends Plugin {

  /**
   * The adapter plugin id
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the context plugin
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * The context description.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $description;

  /**
   * Class used to retrieve derivative definitions of the context.
   *
   * @var string
   */
  public $derivative = '';

}
