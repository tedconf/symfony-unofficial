<?php

require_once(sfConfig::get('sf_lib_dir').'/filter/doctrine/base/BaseFormFilterDoctrine.class.php');

/**
 * myDoctrineRecord filter form base class.
 *
 * @package    filters
 * @subpackage myDoctrineRecord *
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */
class BasemyDoctrineRecordFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id' => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'id' => new sfValidatorDoctrineChoice(array('required' => false, 'model' => 'myDoctrineRecord', 'column' => 'id')),
    ));

    $this->widgetSchema->setNameFormat('my_doctrine_record[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'myDoctrineRecord';
  }

  public function getFields()
  {
    return array(
      'id' => 'integer',
    );
  }
}