<?php

namespace Drupal\Tests\sitestudio_contenthub_publisher\Unit\EventSubscriber\PrunePublishCdfEntities;

use Acquia\ContentHubClient\CDF\CDFObject;
use Acquia\ContentHubClient\CDFDocument;
use Acquia\ContentHubClient\ContentHubClient;
use Drupal\acquia_contenthub\Event\PrunePublishCdfEntitiesEvent;
use Drupal\sitestudio_contenthub_publisher\EventSubscriber\PrunePublishCdfEntities\RemoveConfigEntities;
use Drupal\Tests\UnitTestCase;

/**
 * Tests for removing Site Studio Config entities from Cdf on publishing.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_contenthub_publisher\Unit\EventSubscriber\PrunePublishCdfEntities
 *
 * @covers \Drupal\sitestudio_contenthub_publisher\EventSubscriber\PrunePublishCdfEntities\RemoveConfigEntities::onPrunePublishCdfEntities
 */
class RemoveConfigEntitiesTest extends UnitTestCase {

  const THIS_SITE_ORIGIN = '9f459fd7-cab9-4b63-bf53-de00ac2a0ea5';
  const OTHER_SITE_ORIGIN = '9f459fd7-cab9-4b63-bf53-de00ac2a0ea6';
  const ENTITY_WITH_DEPENDENCY = '9f459fd7-cab9-4b63-bf53-de00ac2a0ea7';

  protected $entities;

  /**
   * @param $entity_type
   *
   * @dataProvider onPrunePublishCdfEntitiesProvider
   */
  public function testOnPrunePublishCdfEntities($entity_type) {

    $client_mock = $this->getMockBuilder(ContentHubClient::class)
      ->disableOriginalConstructor()
      ->getMock();

    $time = time();

    $origin = self::THIS_SITE_ORIGIN;
    $uuid = self::OTHER_SITE_ORIGIN;

    $this->entities = [
      '9f459fd7-cab9-4b63-bf53-de00ac2a0ea6' => CDFObject::fromArray([
        'type' => $entity_type,
        'uuid' => $uuid,
        'created' => $time,
        'modified' => $time,
        'origin' => $origin,
        'attributes' => [
          'entity_type' => [
            'id' => "entity_type",
            'type' => "keyword",
            'value' => [
              "und" => $entity_type,
            ],
          ],
        ],
        'metadata' => [],
      ]),
      self::ENTITY_WITH_DEPENDENCY => CDFObject::fromArray([
        'type' => 'node',
        'uuid' => self::ENTITY_WITH_DEPENDENCY,
        'created' => $time,
        'modified' => $time,
        'origin' => $origin,
        'attributes' => [
          'entity_type' => [
            'id' => "entity_type",
            'type' => "keyword",
            'value' => [
              "und" => 'node',
            ],
          ],
        ],
        'metadata' => [
          'dependencies' => [
            'entity' => [
              '9f459fd7-cab9-4b63-bf53-de00ac2a0ea6' => '9f459fd7-cab9-4b63-bf53-de00ac2a0ea6',
              '8f459fd7-cab9-4b63-bf53-de00ac2a0ea5' => '8f459fd7-cab9-4b63-bf53-de00ac2a0ea5',
              '7f459fd7-cab9-4b63-bf53-de00ac2a0ea4' => '7f459fd7-cab9-4b63-bf53-de00ac2a0ea4',
            ],
          ],
        ],
      ]),
    ];

    $cdf_document = $this->getMockBuilder(CDFDocument::class)
      ->disableOriginalConstructor()
      ->getMock();
    $cdf_document->expects($this->any())
      ->method('getEntities')
      ->willReturn($this->entities);
    $cdf_document->method('removeCdfEntity')
      ->willReturnCallback(function ($argument) {
        unset($this->entities[$argument]);
      });
    $cdf_document->method('hasEntity')
      ->willReturnCallback(function ($argument) {
        return array_key_exists($argument, $this->entities);
      });
    $cdf_document->method('getCdfEntity')
      ->willReturnCallback(function ($argument) {

        return $this->entities[$argument] ?? NULL;
      });

    $subscriber = new RemoveConfigEntities();
    $event = new PrunePublishCdfEntitiesEvent($client_mock, $cdf_document, self::THIS_SITE_ORIGIN);
    $this->assertNotEmpty($event->getDocument()->hasEntity($uuid));
    $this->assertEquals(
      $event->getDocument()->getCdfEntity($uuid),
      $this->entities[$uuid]
    );
    $this->assertArrayHasKey($uuid, $event->getDocument()->getCdfEntity(self::ENTITY_WITH_DEPENDENCY)->getDependencies());

    $subscriber->onPrunePublishCdfEntities($event);
    $this->assertNull($event->getDocument()->getCdfEntity($uuid));

    // Tests that dependencies in Cdf document are cleaned of removed entity.
    $this->assertNotEmpty($event->getDocument()->hasEntity(self::ENTITY_WITH_DEPENDENCY));
    $this->assertNotEmpty($event->getDocument()->getCdfEntity(self::ENTITY_WITH_DEPENDENCY)->getDependencies());
    $this->assertArrayNotHasKey($uuid, $event->getDocument()->getCdfEntity(self::ENTITY_WITH_DEPENDENCY)->getDependencies());
  }

  /**
   * Provides data for testOnPrunePublishCdfEntities.
   *
   * @return string[]
   *   Array of arrays of entity types.
   */
  public function onPrunePublishCdfEntitiesProvider() {
    $entity_types = RemoveConfigEntities::SITESTUDIO_CONFIG_ENTITY_TYPES;
    array_walk($entity_types, function (&$type) {
      $type = [$type];
    });

    return $entity_types;
  }


}
