<?php

/**
 * @file
 * Contains
 *   \Drupal\field_formatter\Plugin\Field\FieldFormatter\FieldFormatterWithInlineSettings.
 */

namespace Drupal\field_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\FormatterInterface;
use Drupal\Core\Field\FormatterPluginManager;
use Drupal\Core\Form\FormHelper;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'link' formatter.
 *
 * @FieldFormatter(
 *   id = "field_formatter_with_inline_settings",
 *   label = @Translation("Field formatter with inline settings"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class FieldFormatterWithInlineSettings extends FieldFormatterBase implements ContainerFactoryPluginInterface{

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The formatter plugin manager.
   *
   * @var \Drupal\Core\Field\FormatterPluginManager
   */
  protected $formatterPluginManager;

  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, EntityFieldManagerInterface $entity_field_manager, FormatterPluginManager $formatter_plugin_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->entityFieldManager = $entity_field_manager;
    $this->formatterPluginManager = $formatter_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('entity_field.manager'),
      $container->get('plugin.manager.field.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'field_name' => '',
      'type' => '',
      'settings' => [],
    ];
  }

  /**
   * At that point in time
   */
  protected function getFieldDefinition(FieldStorageDefinitionInterface $field_storage_definition) {
    return BaseFieldDefinition::createFromFieldStorageDefinition($field_storage_definition);
  }

  /**
   * Get all available formatters by loading available ones and filtering out.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $field_storage_definition
   *   The field storage definition.
   *
   * @return string[]
   *   The field formatter labels keys by plugin ID.
   */
  protected function getAvailableFormatterOptions(FieldStorageDefinitionInterface $field_storage_definition) {
    $field_definition = $this->getFieldDefinition($field_storage_definition);
    $formatters = $this->formatterPluginManager->getOptions($field_storage_definition->getType());
    $formatter_instances = array_map(function($formatter_id) use ($field_definition) {
      $configuration = [
        'field_definition' => $field_definition,
        'settings' => [],
        'label' => '',
        'view_mode' => '',
        'third_party_settings' => [],
      ];
      return $this->formatterPluginManager->createInstance($formatter_id, $configuration);
    }, array_combine(array_keys($formatters), array_keys($formatters)));
    $filtered_formatter_instances = array_filter($formatter_instances, function (FormatterInterface $formatter) use ($field_definition) {
      return $formatter->isApplicable($field_definition);
    });
    $options = array_map(function (FormatterInterface $formatter) {
      return $formatter->getPluginDefinition()['label'];
    }, $filtered_formatter_instances);
    return $options;
  }

  /**
   * Ajax submit callback for field name change.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The replaced form substructure.
   */
  public static function onFieldNameChange(array $form, FormStateInterface $form_state) {
    return $form['fields'][$form_state->getValue('refresh_rows')]['plugin']['settings_edit_form']['settings'];
  }

  /**
   * Ajax submit callback for formatter type change.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The replaced form substructure.
   */
  public static function onFormatterTypeChange(array $form, FormStateInterface $form_state) {
    return $form['fields'][$form_state->getValue('refresh_rows')]['plugin']['settings_edit_form']['settings']['settings'];
  }

  /**
   * Rebuilds the form on select submit.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public static function rebuildSubmit($form, FormStateInterface $form_state) {
    $form_state->setRebuild(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    // This points to the field which formatter is currently configured.
    $current_refresh_rows = $form_state->getValue('refresh_rows');
    $triggered_element = $form_state->getTriggeringElement();

    // Form state is not updated as long just select elements are triggered.
    $field_name = $this->getSetting('field_name');
    if ($triggered_element['#name'] == "fields[$current_refresh_rows][settings_edit_form][settings][field_name]") {
      $field_name = $triggered_element['#value'];
    }

    $form['#prefix'] = '<div id="field-formatter-ajax">';
    $form['#suffix'] = '</div>';
    $form['field_name'] = [
      '#type' => 'select',
      '#title' => $this->t('Field name'),
      '#default_value' => $field_name,
      '#options' => $this->getAvailableFieldNames(),
      // Note: We cannot use ::foo syntax, because the form is the entity form
      // display.
      '#ajax' => [
        'callback' => [get_called_class(), 'onFieldNameChange'],
        'wrapper' => 'field-formatter-ajax',
        'method' => 'replace',
      ],
      '#submit' => [[get_called_class(), 'rebuildSubmit']],
      '#executes_submit_callback' => TRUE,
    ];

    if ($field_name) {
      $target_entity_type_id = $this->fieldDefinition->getSetting('target_type');
      $field_storage_definitions = $this->entityFieldManager->getFieldStorageDefinitions($target_entity_type_id);
      $field_storage = $field_storage_definitions[$field_name];

      // Form state is not updated as long just select elements are triggered.
      $formatter_type = $this->getSetting('type');
      if ($triggered_element['#name'] == "fields[$current_refresh_rows][settings_edit_form][settings][type]") {
        $formatter_type = $triggered_element['#value'];
      }
      $form['type'] = [
        '#type' => 'select',
        '#title' => $this->t('Formatter'),
        '#options' => $this->getAvailableFormatterOptions($field_storage),
        '#default_value' => $formatter_type,
        // Note: We cannot use ::foo syntax, because the form is the entity form
        // display.
        '#ajax' => [
          'callback' => [get_called_class(), 'onFormatterTypeChange'],
          'wrapper' => 'field-formatter-settings-ajax',
          'method' => 'replace',
        ],
        '#submit' => [[get_called_class(), 'rebuildSubmit']],
        '#executes_submit_callback' => TRUE,
      ];

      $options = [
        'field_definition' => $this->getFieldDefinition($field_storage),
        'configuration' => [
          'type' => $formatter_type,
          'settings' => $this->getSetting('settings'),
          'label' => '',
          'weight' => 0,
        ],
        'view_mode' => '_custom',
      ];

      // Get the formatter settings form.
      $settings_form = ['#value' => []];
      if ($formatter = $this->formatterPluginManager->getInstance($options)) {
        $settings_form = $formatter->settingsForm($form, $form_state);
      }
      $form['settings'] = $settings_form;
      $form['settings']['#prefix'] = '<div id="field-formatter-settings-ajax">';
      $form['settings']['#suffix'] = '</div>';
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function getViewDisplay($bundle_id) {
    if (!isset($this->viewDisplay[$bundle_id])) {

      $display = EntityViewDisplay::create([
        'targetEntityType' => $this->fieldDefinition->getSetting('target_type'),
        'bundle' => $bundle_id,
        'status' => TRUE,
      ]);
      $display->setComponent($this->getSetting('field_name'), [
        'type' => $this->getSetting('type'),
        'settings' => $this->getSetting('settings'),
      ]);
      $this->viewDisplay[$bundle_id] = $display;
    }
    return $this->viewDisplay[$bundle_id];
  }

}
