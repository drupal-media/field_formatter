<?php

namespace Drupal\Tests\field_formatter\Kernel;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;

/**
 * @coversDefaultClass \Drupal\field_formatter\Plugin\Field\FieldFormatter\FieldFormatterFromViewDisplay
 * @group field_formatter
 */
class FieldFormatterFromViewDisplayTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['entity_test', 'user', 'field', 'field_formatter'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('entity_test');
    $this->installEntitySchema('user');
  }

  public function testRender() {
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'test_er_field',
      'entity_type' => 'entity_test',
      'type' => 'entity_reference',
      'settings' => [
        'target_type' => 'entity_test',
      ]
    ]);
    $field_storage->save();

    $field_config = FieldConfig::create([
      'field_name' => 'test_er_field',
      'entity_type' => 'entity_test',
      'bundle' => 'entity_test',
    ]);
    $field_config->save();

    $parent_entity_view_display = EntityViewDisplay::create([
      'targetEntityType' => 'entity_test',
      'bundle' => 'entity_test',
      'mode' => 'default',
      'content' => [],
    ]);
    $parent_entity_view_display->setComponent('test_er_field', [
      'type' => 'field_formatter_from_view_display',
      'settings' => [
        'view_display_id' => 'entity_test.child',
        'field_name' => 'name',
      ]
    ]);
    $parent_entity_view_display->save();

    $child_view_mode = EntityViewMode::create([
      'targetEntityType' => 'entity_test',
      'id' => 'entity_test.child',
    ]);
    $child_view_mode->save();
    $child_entity_view_display = EntityViewDisplay::create([
      'id' =>'entity_test.entity_test.child',
      'targetEntityType' => 'entity_test',
      'bundle' => 'entity_test',
      'mode' => 'child',
    ]);
    $child_entity_view_display->setComponent('name', [
      'type' => 'string',
    ]);
    $child_entity_view_display->save();


    $child_entity = EntityTest::create([
      'name' => ['child name'],
    ]);
    $child_entity->save();

    $entity = EntityTest::create([
      'test_er_field' => [[
        'target_id' => $child_entity->id(),
      ]],
    ]);
    $entity->save();

    $build = $parent_entity_view_display->build($entity);

    \Drupal::service('renderer')->renderRoot($build);

    $this->assertEquals('child name', $build['test_er_field'][0]['#markup']);
  }

}
