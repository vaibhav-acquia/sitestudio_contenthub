<?php

namespace Drupal\Tests\sitestudio_contenthub_publisher\Unit\EventSubscriber\DependencyCollector;

use Drupal\depcalc\EventSubscriber\DependencyCollector\EntityReferenceFieldDependencyCollector;
use Drupal\entity_reference_revisions\EntityReferenceRevisionsFieldItemList;
use Drupal\field\Entity\FieldConfig;
use Drupal\node\Entity\Node;
use Drupal\sitestudio_contenthub_publisher\EventSubscriber\DependencyCollector\SitestudioEntityReferenceFieldDependencyCollector;
use Drupal\Tests\UnitTestCase;

/**
 * Tests Sitestudio Entity Reference Field Dependency collector.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_contenthub_publisher\Unit\EventSubscriber\DependencyCollector
 *
 * @covers \Drupal\sitestudio_contenthub_publisher\EventSubscriber\DependencyCollector\SitestudioEntityReferenceFieldDependencyCollector::fieldCondition
 */
class SitestudioEntityReferenceFieldDependencyCollectorTest extends UnitTestCase {

  /**
   * Tests fieldCondition method of SiteStudio dependency collector subscriber.
   */
  public function testFieldContition() {
    $field_config = $this->getMockBuilder(FieldConfig::class)
      ->disableOriginalConstructor()
      ->getMock();
    $field_config->expects($this->any())
      ->method('getType')
      ->willReturn('cohesion_entity_reference_revisions');
    $field_mock = $this->getMockBuilder(EntityReferenceRevisionsFieldItemList::class)
      ->disableOriginalConstructor()
      ->getMock();
    $field_mock->expects($this->any())
      ->method('getFieldDefinition')
      ->willReturn($field_config);

    $node_mock = $this->getMockBuilder(Node::class)
      ->disableOriginalConstructor()
      ->getMock();

    $sitestudio_collector = new SitestudioEntityReferenceFieldDependencyCollector();
    $acquia_contenthub_subscriber = new EntityReferenceFieldDependencyCollector();

    $this->assertFalse($acquia_contenthub_subscriber->fieldCondition($node_mock, '', $field_mock));
    $this->assertTrue($sitestudio_collector->fieldCondition($node_mock, '', $field_mock));
  }

}
