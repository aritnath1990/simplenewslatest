<?php

/**
 * @file
 * Simplenews send test functions multiple.
 *
 * @ingroup simplenews
 */

namespace Drupal\simplenews\Tests;

use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\simplenews\Tests\SimplenewsTestBase;

/**
 * Test cases for creating and sending newsletters to multiple.
 *
 * @group simplenews
 */
class SimplenewsMultipleNewsletters extends SimplenewsTestBase {

  function setUp() {
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
    
    //$this->setUpSubscribers(5);
  }

  /**
   * Creates and sends a node using the API to Multiple.
   */
  function testProgrammaticNewsletterMultiple() {

    // Add a new newsletter
    $this->drupalGet('admin/config/services/simplenews');
    $this->clickLink(t('Add newsletter'));
    $edit = array(
      'name' => $this->randomString(10),
      'id' => strtolower($this->randomMachineName(10)),
      'description' => $this->randomString(20),
    );
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertText(t('Newsletter @name has been added', array('@name' => $edit['name'])));
	  
    // Subscribe a few users.	  
    $this->setUpSubscribersWithMultiNewsletters(3);

    // Create a very basic node.
    $node = Node::create(array(
      'type' => 'simplenews_issue',
      'title' => $this->randomString(10),
      'uid' => 0,
      'status' => 1
    ));
    $node->simplenews_issue = $this->getRandomNewsletters(2);
    $node->simplenews_issue->handler = 'simplenews_all';
    $node->save();

    // Send the node.
    \Drupal::service('simplenews.spool_storage')->addFromEntity($node);
    $node->save();

    // Make sure that they have been added.
    $this->assertEqual(\Drupal::service('simplenews.spool_storage')->countMails(), 3);

    // Mark them as pending, fake a currently running send process.
    $this->assertEqual(count(\Drupal::service('simplenews.spool_storage')->getMails(2)), 2);

    // Those two should be excluded from the count now.
    $this->assertEqual(\Drupal::service('simplenews.spool_storage')->countMails(), 1);

    // Get two additional spool entries.
    $this->assertEqual(count(\Drupal::service('simplenews.spool_storage')->getMails(1)), 1);

    // Now only one should be returned by the count.
    $this->assertEqual(\Drupal::service('simplenews.spool_storage')->countMails(), 0);
  }
}
