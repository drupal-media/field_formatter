<?php

namespace Drupal\field_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

abstract class FieldFormatterBase extends FormatterBase {

  /**
   * The entity view display.
   *
   * @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface
   */
  protected $viewDisplay;

  /**
   * @param string $bundle_id
   *   The bundle ID.
   *
   * @return \Drupal\Core\Entity\Display\EntityViewDisplayInterface
   */
  abstract protected function getViewDisplay($bundle_id);

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    /** @var \Drupal\Core\Entity\FieldableEntityInterface $entity */
    $entity = $items->getEntity();

    $build = [];
    foreach ($items as $delta => $item) {
      $build[$delta] = $this->getViewDisplay($entity->bundle())->build($item->entity);
    }

    return $build;
  }

  protected function getAvailableFieldNames() {
    $entity_type_id = $this->fieldDefinition->getSetting('target_type');
    $bundle_id = $this->fieldDefinition->getTargetBundle();
    $field_names = array_map(function (FieldDefinitionInterface $field_definition) {
      return $field_definition->getLabel();
    }, $this->entityFieldManager->getFieldDefinitions($entity_type_id, $bundle_id));
    return $field_names;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $entity_type = \Drupal::entityTypeManager()->getDefinition($field_definition->getTargetEntityTypeId());
    return $entity_type->isSubclassOf(FieldableEntityInterface::class);
  }

}
