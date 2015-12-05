<?php

/**
 * @file
 * Contains \Drupal\facets\Processor\ProcessorInterface.
 */

namespace Drupal\facets\Processor;

use Drupal\Core\Form\FormStateInterface;
use Drupal\facets\FacetInterface;
use Drupal\Component\Plugin\ConfigurablePluginInterface;

/**
 * Describes a processor.
 */
interface ProcessorInterface extends ConfigurablePluginInterface {

  /**
   * Processing stage: pre_query.
   */
  const STAGE_PRE_QUERY = 'pre_query';

  /**
   * Processing stage: post_query.
   */
  const STAGE_POST_QUERY = 'post query';

  /**
   * Processing stage: build.
   */
  const STAGE_BUILD = 'build';

  /**
   * Adds a configuration form for this processor.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param \Drupal\facets\FacetInterface $facet
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet);

  /**
   * Validates a configuration form for this processor.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param \Drupal\facets\FacetInterface $facet
   */
  public function validateConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet);

  /**
   * Checks whether this processor implements a particular stage.
   *
   * @param string $stage_identifier
   *   The stage to check: self::STAGE_PRE_QUERY,
   *   self::STAGE_POST_QUERY
   *   or self::STAGE_BUILD.
   *
   * @return bool
   *   TRUE if the processor runs on a particular stage; FALSE otherwise.
   */
  public function supportsStage($stage_identifier);

  /**
   * Returns the default weight for a specific processing stage.
   *
   * Some processors should ensure they run earlier or later in a particular
   * stage. Processors with lower weights are run earlier. The default value is
   * used when the processor is first enabled. It can then be changed through
   * reordering by the user.
   *
   * @param string $stage
   *   The stage whose default weight should be returned. See
   *   \Drupal\facets\Processor\ProcessorPluginManager::getProcessingStages()
   *   for the valid values.
   *
   * @return int
   *   The default weight for the given stage.
   */
  public function getDefaultWeight($stage);

  /**
   * Determines whether this processor should always be enabled.
   *
   * @return bool
   *   TRUE if this processor should be forced enabled; FALSE otherwise.
   */
  public function isLocked();

  /**
   * Determines whether this processor should be hidden from the user.
   *
   * @return bool
   *   TRUE if this processor should be hidden from the user; FALSE otherwise.
   */
  public function isHidden();

  /**
   * Retrieves the processor description.
   *
   * @return string
   *   The description of this processor.
   */
  public function getDescription();

}
