<?php

require_once(sfConfig::get('sf_lib_dir').'/filter/doctrine/base/BaseFormFilterDoctrine.class.php');

/**
 * Author filter form base class.
 *
 * @package    filters
 * @subpackage Author *
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */
class BaseAuthorFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'   => new sfWidgetFormFilterInput(),
      'name' => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'id'   => new sfValidatorDoctrineChoice(array('required' => false, 'model' => 'Author', 'column' => 'id')),
      'name' => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('author[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'Author';
  }

  public function getFields()
  {
    return array(
      'id'   => 'integer',
      'name' => 'string',
    );
  }
}