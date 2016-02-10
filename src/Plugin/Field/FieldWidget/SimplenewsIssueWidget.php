<?php

/**
 * @file
 * Contains \Drupal\simplenews\Plugin\Field\FieldWidget\SimplenewsIssueWidget.
 */

namespace Drupal\simplenews\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Plugin implementation of the 'simplenews_issue_select' widget.
 *
 * @FieldWidget(
 *   id = "simplenews_issue_select",
 *   label = @Translation("Simplenews Issue"),
 *   field_types = {
 *     "simplenews_issue"
 *   },
 *   multiple_values = TRUE
 * )
 */
class SimplenewsIssueWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'allow_multiple' => FALSE,
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $field_storage = FieldStorageConfig::loadByName('node', 'simplenews_issue');
    if($field_storage->getCardinality()!=1){
      $element['allow_multiple'] = array(
        '#type' => 'checkbox',
        '#title' => t('Allow multiple newsletters to be selected'),
        '#default_value' => $this->getSetting('allow_multiple'),
        '#weight' => -1,
      );
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = array();
    $display_label = $this->getSetting('allow_multiple');
    $summary[] = t('Use field label: @allow_multiple', array('@allow_multiple' => ($display_label ? t('Yes') : 'No')));
    return $summary;
  }

 /**
   * Form validation handler for widget elements.
   *
   * @param array $element
   *   The form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public static function validateElement(array $element, FormStateInterface $form_state) {
    if ($element['#required'] && $element['#value'] == '_none') {
      $form_state->setError($element, t('@name field is required.', array('@name' => $element['#title'])));
    }

    // Massage submitted form values.
    // Drupal\Core\Field\WidgetBase::submit() expects values as
    // an array of values keyed by delta first, then by column, while our
    // widgets return the opposite.
    if (is_array($element['#value'])) {
      $values = array_values($element['#value']);
    }else {
      $values = array($element['#value']);
    }

    // Filter out the 'none' option. Use a strict comparison, because
    // 0 == 'any string'.
    $index = array_search('_none', $values, TRUE);
    if ($index !== FALSE) {
      unset($values[$index]);
    }

    // Transpose selections from field => delta to delta => field.
    $items = array();
    foreach ($values as $value) {
      $items[] = array('target_id' => $value);
    }
    $form_state->setValueForElement($element, $items);
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    // To get the Cardinality value.
    $field_storage = FieldStorageConfig::loadByName('node', 'simplenews_issue');
    $options = array();
    foreach($items as $key => $val){
      $options[] = $val->target_id;
    }

    // Determining the type of the field.
    $element_type="select";

   // Cheking if it's UNLIMITED or not.
   if($field_storage->getCardinality()!=1){
     // Determining the field type from the issue setings if it is UNLIMITED
     if($this->getSetting('allow_multiple')=="1"){
       $element_type='checkboxes';
     }
   }
    // Setting the field.
    $element += array(
      '#type' => $element_type,
     '#default_value' => $options,
     '#options' => $this->getOptions(simplenews_newsletter_list()),
   );
    // Add our custom validator.
    $element['#element_validate'][] = array(get_class($this), 'validateElement');
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions(array $optionArr){
    $optionReturn =  array();
    foreach($optionArr as $key =>$optionData){
      $optionReturn[$key] = $optionData->__toString();
    }
    return $optionReturn;
  }
}
