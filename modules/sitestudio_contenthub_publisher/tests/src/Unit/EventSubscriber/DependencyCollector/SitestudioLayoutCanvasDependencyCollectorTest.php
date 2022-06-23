<?php

namespace Drupal\Tests\sitestudio_contenthub_publisher\Unit\EventSubscriber\DependencyCollector;

use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\sitestudio_contenthub_publisher\EventSubscriber\DependencyCollector\SitestudioLayoutCanvasDependencyCollector;
use Drupal\cohesion_elements\Entity\CohesionLayout;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\depcalc\Cache\DepcalcCacheBackend;
use Drupal\depcalc\DependencyCalculator;
use Drupal\depcalc\DependencyStack;
use Drupal\depcalc\DependentEntityWrapper;
use Drupal\depcalc\Event\CalculateEntityDependenciesEvent;
use Drupal\node\NodeInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Tests for CohesionLayout entity dependency collection.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_contenthub_publisher\Unit\EventSubscriber\DependencyCollector
 *
 * @covers \Drupal\sitestudio_contenthub_publisher\EventSubscriber\DependencyCollector\SitestudioLayoutCanvasDependencyCollector::onCalculateDependencies
 */
class SitestudioLayoutCanvasDependencyCollectorTest extends UnitTestCase {

  const JSON_VALUES = '{"canvas":[{"uid":"cpt_test_component","type":"component","title":"Test Component","enabled":true,"category":"category-10","componentId":"cpt_test_component","componentType":"link","uuid":"d23a59d5-c8d7-49b8-9cda-61fa01aadf55","parentUid":"root","children":[]},{"uid":"cpt_test_component","type":"component","title":"Test Component","enabled":true,"category":"category-10","componentId":"cpt_test_component","componentType":"link","uuid":"19c96fe4-db43-4f2f-8c91-8081b7a948fb","parentUid":"root","children":[]},{"uid":"cpt_test_component","type":"component","title":"Test Component","enabled":true,"category":"category-10","componentId":"cpt_test_component","componentType":"link","uuid":"ebafa6a9-b0b8-49cb-a481-d8c123c52e1c","parentUid":"root","children":[]},{"uid":"cpt_links_pattern_repeater","type":"component","title":"Links pattern repeater","enabled":true,"category":"category-4","componentId":"cpt_links_pattern_repeater","componentType":"component-pattern-repeater","uuid":"e7d1a1ac-abef-4971-9273-9d25bcc8db79","parentUid":"root","children":[]}],"mapper":{},"model":{"d23a59d5-c8d7-49b8-9cda-61fa01aadf55":{"settings":{"title":"Test Component"},"4793e582-fe13-490c-99d2-badcce843df7":"node::64"},"19c96fe4-db43-4f2f-8c91-8081b7a948fb":{"settings":{"title":"Test Component"},"4793e582-fe13-490c-99d2-badcce843df7":"node::56"},"ebafa6a9-b0b8-49cb-a481-d8c123c52e1c":{"settings":{"title":"Test Component"},"4793e582-fe13-490c-99d2-badcce843df7":"https:\/\/www.drupal.org\/"},"e7d1a1ac-abef-4971-9273-9d25bcc8db79":{"settings":{"title":"Links pattern repeater"},"d1653d6c-68ef-4b15-aabb-fdf3daf5064e":[{"f1a9f274-f030-47d5-a831-79a23da8a59e":"External","abdc6059-1b0d-498e-ac6a-daa58cbb85fb":"https:\/\/www.drupal.org\/"},{"f1a9f274-f030-47d5-a831-79a23da8a59e":"Node 1","abdc6059-1b0d-498e-ac6a-daa58cbb85fb":"node::4"},{"f1a9f274-f030-47d5-a831-79a23da8a59e":"Node 2","abdc6059-1b0d-498e-ac6a-daa58cbb85fb":"node::3"}]}},"previewModel":{"d23a59d5-c8d7-49b8-9cda-61fa01aadf55":{},"19c96fe4-db43-4f2f-8c91-8081b7a948fb":{},"ebafa6a9-b0b8-49cb-a481-d8c123c52e1c":{},"e7d1a1ac-abef-4971-9273-9d25bcc8db79":{}},"variableFields":{"d23a59d5-c8d7-49b8-9cda-61fa01aadf55":[],"19c96fe4-db43-4f2f-8c91-8081b7a948fb":[],"ebafa6a9-b0b8-49cb-a481-d8c123c52e1c":[],"e7d1a1ac-abef-4971-9273-9d25bcc8db79":[]},"meta":{"fieldHistory":[]}}';
  const NODE_UUIDS = [
    '3' => 'a48e537a-6ec9-4191-b518-ed5e6d559a12',
    '4' => 'f8c3512b-aa9e-4008-8bf3-c03193ca3afa',
    '56' => 'ae85e706-809b-4c6f-9ef5-0a0f6779d6c8',
    '64' => 'dd871855-28df-475a-9d93-6cb4cba62d27',
  ];

  /**
   * EntityTypeManager Mock.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityTypeManager;

  protected function setUp() {
    parent::setUp();

    $cohesion_layout = $this->getMockBuilder(CohesionLayout::class)
      ->disableOriginalConstructor()
      ->getMock();
    $cohesion_layout->method('getLayoutCanvasInstance')
      ->willReturn(new LayoutCanvas(self::JSON_VALUES));
    $cohesion_layout->method('uuid')
      ->willReturn('3a68e76a-d224-4fff-9737-a13cfa481165');
    $cohesion_layout->method('id')
      ->willReturn('113');
    $cohesion_layout->method('getEntityTypeId')
      ->willReturn('cohesion_layout');
    $cohesion_layout->method('toArray')
      ->willReturn([
        'uuid' => [
          [
            'value' => '3a68e76a-d224-4fff-9737-a13cfa481165',
          ],
        ],
        'id' => [
          [
            'value' => '113',
          ],
        ],
        'json_values' => [
          [
            'value' => self::JSON_VALUES,
          ],
        ],
      ]);
    $this->cohesion_layout = $cohesion_layout;

    $entityStorage = $this->getMockBuilder(EntityStorageInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $entityStorage->expects($this->any())
      ->method('load')
      ->willReturnCallback(function ($argument) {
        $nodeMock = $this->getMockBuilder(NodeInterface::class)
          ->disableOriginalConstructor()
          ->getMock();
        $nodeMock->expects($this->any())
          ->method('uuid')
          ->will($this->returnValue(self::NODE_UUIDS[$argument]));

        return $nodeMock;
      });


    $entityTypeManager = $this->getMockBuilder(EntityTypeManagerInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $entityTypeManager->expects($this->any())
      ->method('getStorage')
      ->willReturn($entityStorage);
    $this->entityTypeManager = $entityTypeManager;


    $entity_repository = $this->getMockBuilder(EntityRepository::class)
      ->disableOriginalConstructor()
      ->getMock();
    $entity_repository->method('loadEntityByUuid')
      ->willReturn($cohesion_layout);


    $dependency_calculator = $this->getMockBuilder(DependencyCalculator::class)
      ->disableOriginalConstructor()
      ->getMock();
    $dependency_calculator->method('calculateDependencies')
      ->willReturn([]);

    $cache_depcalc = $this->getMockBuilder(DepcalcCacheBackend::class)
      ->disableOriginalConstructor()
      ->getMock();
    $cache_depcalc->expects($this->any())
      ->method('set')
      ->willReturn([]);

    $container = new ContainerBuilder();
    \Drupal::setContainer($container);
    $container->set('entity.repository', $entity_repository);
    $container->set('entity.dependency.calculator', $dependency_calculator);
    $container->set('cache.depcalc', $cache_depcalc);

  }

  public function testOnCalculateDependencies() {
    $subscriber = new SitestudioLayoutCanvasDependencyCollector($this->entityTypeManager);

    $wrapper = new DependentEntityWrapper($this->cohesion_layout);
    $stack = new DependencyStack();
    $event = new CalculateEntityDependenciesEvent($wrapper, $stack);

    $this->assertArrayEquals([], $event->getDependencies());

    $subscriber->onCalculateDependencies($event);

    $dependencies = $event->getDependencies();
    foreach (self::NODE_UUIDS as $node_uuid) {
      $this->assertArrayHasKey($node_uuid, $dependencies);
      $this->assertInstanceOf(DependentEntityWrapper::class, $dependencies[$node_uuid]);
    }
  }


}
