<?php
/**
 * @file
 * Contains \Drupal\facetapi\Plugin\facetapi\UidToUserNameCallbackProcessor.
 */

namespace Drupal\facetapi\Plugin\facetapi\processor;

use Drupal\Core\Entity\Entity;
use Drupal\facetapi\FacetInterface;
use Drupal\facetapi\Processor\BuildProcessorInterface;
use Drupal\facetapi\Processor\ProcessorPluginBase;
use Drupal\user\Entity\User;

/**
 * Provides a processor that transforms the results to show the user's name
 *
 * @FacetApiProcessor(
 *   id = "uid_to_username_callback",
 *   label = @Translation("Transform uid to username"),
 *   description = @Translation("Show the username instead, when the source field is a user id."),
 *   stages = {
 *     "build" = 5
 *   }
 * )
 */
class UidToUserNameCallbackProcessor extends ProcessorPluginBase implements BuildProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet, array $results) {

    /** @var \Drupal\facetapi\Result\ResultInterface $result */
    foreach ($results as &$result) {
      /** @var \Drupal\user\Entity\User $user */
      $user = User::load($result->getRawValue());

      $result->setDisplayValue($user->getDisplayName());
    }

    return $results;
  }

}
