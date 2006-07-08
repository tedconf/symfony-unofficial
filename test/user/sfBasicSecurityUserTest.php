<?php

Mock::generate('sfContext');

class sfBasicSecurityUserTest extends UnitTestCase
{
  private
    $context,
    $storage;

  public function SetUp()
  {
    $this->context = new MockSfContext($this);
    $this->storage = sfStorage::newInstance('sfSessionTestStorage');
    $this->storage->initialize($this->context);

    // mock $this->getContext()->getStorage()
    $this->context->setReturnValue('getStorage', $this->storage);

    $this->user = new sfBasicSecurityUser();
    $this->user->initialize($this->context);
  }

  public function test_credentials()
  {
    $this->assertFalse($this->user->hasCredential('admin'));

    $this->user->addCredential('admin');

    $this->assertTrue($this->user->hasCredential('admin'));

    // admin and user
    $this->assertFalse($this->user->hasCredential(array('admin', 'user')));

    // admin or user
    $this->assertTrue($this->user->hasCredential(array(array('admin', 'user'))));

    $this->user->addCredential('user');
    $this->assertTrue($this->user->hasCredential('admin'));
    $this->assertTrue($this->user->hasCredential('user'));

    $this->user->addCredentials('superadmin', 'subscriber');
    $this->assertTrue($this->user->hasCredential('subscriber'));
    $this->assertTrue($this->user->hasCredential('superadmin'));

    // admin and (user or subscriber)
    $this->assertTrue($this->user->hasCredential(array(
      array('admin', array('user', 'subscriber'))))
    );

    $this->user->addCredentials(array('superadmin1', 'subscriber1'));
    $this->assertTrue($this->user->hasCredential('subscriber1'));
    $this->assertTrue($this->user->hasCredential('superadmin1'));

    // admin and (user or subscriber) and (superadmin1 or subscriber1)
    $this->assertTrue($this->user->hasCredential(array(
      array('admin', array('user', 'subscriber'), array('superadmin1', 'subscriber1'))))
    );

    $this->user->removeCredential('user');
    $this->assertFalse($this->user->hasCredential('user'));

    $this->user->clearCredentials();
    $this->assertFalse($this->user->hasCredential('subscriber'));
    $this->assertFalse($this->user->hasCredential('superadmin'));
  }
}
