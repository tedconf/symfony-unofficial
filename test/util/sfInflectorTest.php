<?php

class sfInflectorTest extends UnitTestCase
{
  public function test_camelize()
  {
    $this->assertEqual('Product', sfInflector::camelize('product'));
    $this->assertEqual('SpecialGuest', sfInflector::camelize('special_guest'));
    $this->assertEqual('ApplicationController', sfInflector::camelize('application_controller'));

    $this->assertEqual('HtmlTidyGenerator', sfInflector::camelize('html_tidy_generator'));
    $this->assertEqual('Phone2Ext', sfInflector::camelize('phone2_ext'));
  }

  public function test_underscore()
  {
    $this->assertEqual('product', sfInflector::underscore('Product'));
    $this->assertEqual( 'special_guest', sfInflector::underscore('SpecialGuest'));
    $this->assertEqual('application_controller', sfInflector::underscore('ApplicationController'));

    $this->assertEqual('html_tidy', sfInflector::underscore('HTMLTidy'));
    $this->assertEqual('html_tidy_generator', sfInflector::underscore('HTMLTidyGenerator'));
    $this->assertEqual('phone2_ext', sfInflector::underscore('Phone2Ext'));
  }

  public function test_camelize_with_module()
  {
    $this->assertEqual('Admin::Product', sfInflector::camelize('admin/product'));
    $this->assertEqual('Users::Commission::Department',
      sfInflector::camelize('users/commission/department'));
    $this->assertEqual('UsersSection::CommissionDepartment',
      sfInflector::camelize('users_section/commission_department'));
  }

  public function test_underscore_with_slashes()
  {
    $this->assertEqual('admin/product', sfInflector::underscore('Admin::Product'));
    $this->assertEqual('users/commission/department',
      sfInflector::underscore('Users::Commission::Department'));
    $this->assertEqual('users_section/commission_department',
      sfInflector::underscore('UsersSection::CommissionDepartment'));
  }

  public function test_demodulize()
  {
    $this->assertEqual('Account', sfInflector::demodulize('MyApplication::Billing::Account'));
  }

  public function test_foreign_key()
  {
    $this->assertEqual('person_id', sfInflector::foreign_key('Person'));
    $this->assertEqual('account_id', sfInflector::foreign_key('MyApplication::Billing::Account'));

    $this->assertEqual('personid', sfInflector::foreign_key('Person', false));
    $this->assertEqual('accountid', sfInflector::foreign_key('MyApplication::Billing::Account', false));
  }

  public function test_tableize()
  {
    $this->assertEqual('primary_spokesman', sfInflector::tableize('PrimarySpokesman'));
    $this->assertEqual('node_child', sfInflector::tableize('NodeChild'));
  }

  public function test_classify()
  {
    $this->assertEqual('PrimarySpokesman', sfInflector::classify('primary_spokesman'));
    $this->assertEqual('NodeChild', sfInflector::classify('node_child'));
  }

  public function test_humanize()
  {
    $this->assertEqual('Employee salary', sfInflector::humanize('employee_salary'));
    $this->assertEqual('Underground', sfInflector::humanize('underground'));
  }
}

?>
