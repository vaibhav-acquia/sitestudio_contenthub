<?php

namespace Drupal\Tests\sitestudio_contenthub_publisher\Unit\EventSubscriber\EnqueueEligibility;

use Drupal\acquia_contenthub_publisher\Event\ContentHubEntityEligibilityEvent;
use Drupal\sitestudio_contenthub_publisher\EventSubscriber\EnqueueEligibility\IsNotCohesionLayout;
use Drupal\cohesion_elements\Entity\CohesionLayout;
use Drupal\Tests\UnitTestCase;

/**
 * Tests for directly enqueing CohesionLayout entities.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\acquia_contenthub_publisher\Unit\EventSubscriber\EntityEligibility
 *
 * @covers \Drupal\sitestudio_contenthub_publisher\EventSubscriber\EnqueueEligibility\IsNotCohesionLayout::onEnqueueCandidateEntity
 */
class IsNotCohesionLayoutTest extends UnitTestCase {

  public function testCohesionConfigEligibility() {
    // Setup our files for testing.
    $config = $this->prophesize(CohesionLayout::class);

    // This is the thing we're actually going to test.
    $subscriber = new IsNotCohesionLayout();

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
