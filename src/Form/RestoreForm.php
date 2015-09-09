<?php
/**
 * @file
 * Contains \Drupal\trash\Form\PurgeForm.
 */

namespace Drupal\trash\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Implements an example form.
 */
class RestoreForm extends ConfirmFormBase {

  protected $entity;

  /**
   * {@inheritdoc}.
   */
  public function getFormId() {
    return 'restore_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to restore "@label"?', ['@label' => $this->entity->label()]);
  }

  /**
    * {@inheritdoc}
    */
   public function getDescription() {
     return $this->t('The @entity "@label" will be restored.', ['@entity' => $this->entity->getEntityType()->get('label'), '@label' => $this->entity->label()]);
   }

  /**
     * {@inheritdoc}
     */
    public function getConfirmText() {
      return $this->t('Restore');
    }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('trash.entity_list', ['entity_type_id' => $this->entity->getEntityTypeId()]);
  }

  /**
   * {@inheritdoc}.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $entity = '', $id = '') {
    if (!$this->entity = entity_load_deleted($entity, $id, true)) {
      drupal_set_message(t('Unable to load deleted entity.'), 'error');
      return '';
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $entity->_deleted = FALSE;
    if ($entity->save()) {
      drupal_set_message(t('The @entity "@label" has been restored.', ['@entity' => $this->entity->getEntityType()->get('label'), '@label' => $this->entity->label()]));
      $form_state->setRedirect('trash.entity_list', ['entity_type_id' => $this->entity->getEntityTypeId()]);
    }
  }

}
