<?php

/**
 * @file
 * Simplenews send test functions multiple.
 *
 * @ingroup simplenews
 */

namespace Drupal\simplenews\Tests;

use Drupal\node\Entity\Node;
use Drupal\simplenews\Tests\SimplenewsTestBase;

/**
 * Test cases for creating and sending newsletters to multiple.
 *
 * @group simplenews
 */
class SimplenewsMultipleNewsletters extends SimplenewsTestBase {
  /**
   * Initialized the test SimplenewsMultipleNewsletters.
   */
  public function setUp() {
    parent::setUp();

    $admin_user = $this->drupalCreateUser(array(
      'administer newsletters',
      'send newsletter',
      'administer nodes',
      'administer simplenews subscriptions',
      'create simplenews_issue content',
      'edit any simplenews_issue content',
      'view own unpublished content',
      'delete any simplenews_issue content',
    ));
    $this->drupalLogin($admin_user);
  }

  /**
   * Creates and sends a node using the API to Multiple.
   */
  public function testProgrammaticNewsletterMultiple() {
    // Create a new newsletter.
    $this->drupalGet('admin/config/services/simplenews');
    $this->clickLink(t('Add newsletter'));
    $edit = array(
      'name' => $this->randomString(10),
      'id' => strtolower($this->randomMachineName(10)),
      'description' => $this->randomString(20),
    );
    $this->drupalPostForm(NULL, $edit, t('Save'));

    // Checking if node creating successful with correct name.
    $this->assertText(t('Newsletter @name has been added', array('@name' => $edit['name'])));

    // Subscription setupSimplenewsMultipleNewsletters.
    $this->setUpSubscribersWithMultiNewsletters();

    // Create a very basic node.
    $node = Node::create(array(
      'type' => 'simplenews_issue',
      'title' => $this->randomString(10),
      'uid' => 0,
      'status' => 1,
    ));
    $node->simplenews_issue = $this->getRandomNewsletters(2);
    $node->simplenews_issue->handler = 'simplenews_all';
    $node->save();

    // Add the subscribers of the node to the spool storage queue.
    \Drupal::service('simplenews.spool_storage')->addFromEntity($node);
    $node->save();

    // Subsciber Count check.
    $this->assertEqual(3, count($this->subscribers), t('all subscribers have been received a mail'));

    // Make sure that they have been added.
    $this->assertEqual(\Drupal::service('simplenews.spool_storage')->countMails(), 3);

    // Mark them as pending, fake a currently running send process.
    $this->assertEqual(count(\Drupal::service('simplenews.spool_storage')->getMails(2)), 2);

    // Those two should be excluded from the count now.
    $this->assertEqual(\Drupal::service('simplenews.spool_storage')->countMails(), 1);

    // Get one additional spool entry.
    $this->assertEqual(count(\Drupal::service('simplenews.spool_storage')->getMails(1)), 1);

    // Now only none should be returned by the count.
    $this->assertEqual(\Drupal::service('simplenews.spool_storage')->countMails(), 0);
  }

}
