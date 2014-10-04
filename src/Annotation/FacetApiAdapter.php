<?php

/**
 * @file
 * Contains \Drupal\facet_api\Annotation\FacetApiAdapter.
 */

namespace Drupal\facet_api\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Facet API Adapter annotation object.
 *
 * @see \Drupal\facet_api\FacetApiAdapterManager
 * @see plugin_api
 *
 * @ingroup plugin_api
 *
 * @Annotation
 */
class FacetApiAdapter extends Plugin {

  /**
   * The adapter plugin id
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the adapter plugin
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * The adapter description.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $description;

  /**
   * Class used to retrieve derivative definitions of the block.
   *
   * @var string
   */
  public $derivative = '';

}
