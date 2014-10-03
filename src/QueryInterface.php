<?php

/**
 * @file
 * Contains \Drupal\facetapi\QueryInterface.
 */

namespace Drupal\facetapi;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * In D7 this interface did not exist.
 * We are creating this interface so functions can be moved here from the adapter interface.
 */
interface QueryInterface extends ConfigEntityInterface {

}