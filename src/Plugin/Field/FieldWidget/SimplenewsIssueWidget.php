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
      'display_label' => FALSE,
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['display_label'] = array(
      '#type' => 'checkbox',
      '#title' => t('Use Multi Option in Newsletters'),
      '#default_value' => $this->getSetting('display_label'),
      '#weight' => -1,
    );
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = array();
    $display_label = $this->getSetting('display_label');
    $summary[] = t('Use field label: @display_label', array('@display_label' => ($display_label ? t('Yes') : 'No')));
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {  
		$element = $element + array(
		  '#type' => 'checkboxes',
		  '#default_value' => !empty($items[0]->value),
          '#options' => simplenews_newsletter_list(),
		); 
    return $element;    
  }
}
