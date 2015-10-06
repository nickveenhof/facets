<?php

namespace Drupal\facetapi\Plugin\facetapi\Widget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\facetapi\Widget\WidgetInterface;

/**
 * @FacetApiWidget(
 *   id = "links",
 *   label = @Translation("List of links"),
 *   description = @Translation("A simple widget that shows a list of links"),
 * )
 *
 * Class LinksWidget
 */
class LinksWidget implements WidgetInterface {

  /**
   * {@inheritdoc}
   */
  public function execute() {
    // Execute all the things.
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // @TODO actually build the correct render array.
    return ['#markup' => 'links widget'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    return false;
  }

}
