<?php

/**
 * @file
 * Contains \Drupal\facetapi\Form\FacetDeleteConfirmForm.
 */

namespace Drupal\facetapi\Form;

use Drupal\block\Entity\Block;
use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Defines a confirm form for deleting a facet.
 */
class FacetDeleteConfirmForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the facet %name?', array('%name' => $this->entity->label()));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.facetapi_facet.canonical', array('facetapi_facet' => $this->entity->id()));
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }


  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $is_facet_used_by_block = FALSE;

    // if the block module is not installed, we should automatically return
    // false and go ahead with deletion.
    if (!\Drupal::moduleHandler()->moduleExists('block')) {
      $blocks = [];
    }
    else {
      $blocks = Block::loadMultiple();
    }

    // Check if any blocks are currently using this facet.
    foreach ($blocks as $block) {
      if ($block->getPlugin() instanceof \Drupal\facetapi\Plugin\Block\FacetBlock) {
        $facet_context_mapping = $block->getPlugin()->getConfiguration()['context_mapping']['facet'];
        list(, $block_facet_uuid) = explode(':', $facet_context_mapping);

        if ($this->entity->uuid() === $block_facet_uuid) {
          $is_facet_used_by_block = TRUE;
        }
      }
    }
    if ($is_facet_used_by_block) {
      $caption = '<p>' . $this->t("The facet is currently used in a block and thus can't be removed. Remove the block first.");
      $form['#title'] = $this->getQuestion();
      $form['description'] = array('#markup' => $caption);
      return $form;
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();
    drupal_set_message($this->t('The facet %name has been deleted.', array('%name' => $this->entity->label())));
    $form_state->setRedirect('facetapi.overview');
  }

}
