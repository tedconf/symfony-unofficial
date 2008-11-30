<?php

require_once(sfConfig::get('sf_lib_dir').'/filter/doctrine/base/BaseFormFilterDoctrine.class.php');

/**
 * Subscription filter form base class.
 *
 * @package    filters
 * @subpackage Subscription *
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */
class BaseSubscriptionFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'     => new sfWidgetFormFilterInput(),
      'name'   => new sfWidgetFormFilterInput(),
      'status' => new sfWidgetFormChoice(array('choices' => array('New' => 'New', 'Active' => 'Active', 'Pending' => 'Pending', 'Expired' => 'Expired'))),
    ));

    $this->setValidators(array(
      'id'     => new sfValidatorDoctrineChoice(array('required' => false, 'model' => 'Subscription', 'column' => 'id')),
      'name'   => new sfValidatorPass(array('required' => false)),
      'status' => new sfValidatorChoice(array('required' => false, 'choices' => array('New' => 'New', 'Active' => 'Active', 'Pending' => 'Pending', 'Expired' => 'Expired'))),
    ));

    $this->widgetSchema->setNameFormat('subscription[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Subscription';
  }

  public function getFields()
  {
    return array(
      'id'     => 'integer',
      'name'   => 'string',
      'status' => 'enum',
    );
  }
}