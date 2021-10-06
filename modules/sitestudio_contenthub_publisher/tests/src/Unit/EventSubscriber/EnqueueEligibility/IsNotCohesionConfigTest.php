<?php

namespace Drupal\Tests\sitestudio_contenthub_publisher\Unit\EventSubscriber\EnqueueEligibility;

use Drupal\acquia_contenthub_publisher\Event\ContentHubEntityEligibilityEvent;
use Drupal\cohesion\Entity\CohesionConfigEntityBase;
use Drupal\sitestudio_contenthub_publisher\EventSubscriber\EnqueueEligibility\IsNotCohesionConfig;
use Drupal\Tests\UnitTestCase;

/**
 * Tests for directly enqueing CohesionConfig entities.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\acquia_contenthub_publisher\Unit\EventSubscriber\EntityEligibility
 *
 * @covers \Drupal\sitestudio_contenthub_publisher\EventSubscriber\EnqueueEligibility\IsNotCohesionConfig::onEnqueueCandidateEntity
 */
class IsNotCohesionConfigTest extends UnitTestCase {

  public function testCohesionConfigEligibility() {
    // Setup our files for testing.
    $config = $this->prophesize(CohesionConfigEntityBase::class);

    // This is the thing we're actually going to test.
    $subscriber = new IsNotCohesionConfig();

    // Test insert.
    $event = new ContentHubEntityEligibilityEvent($config->reveal(), 'insert');
    $subscriber->onEnqueueCandidateEntity($event);
    $this->assertFalse($event->getEligibility());

    // Test update.
    $event = new ContentHubEntityEligibilityEvent($config->reveal(), 'update');
    $subscriber->onEnqueueCandidateEntity($event);
    $this->assertFalse($event->getEligibility());
  }

}
