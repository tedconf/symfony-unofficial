<?php

require_once(sfConfig::get('sf_lib_dir').'/filter/doctrine/base/BaseFormFilterDoctrine.class.php');

/**
 * ArticleTranslation filter form base class.
 *
 * @package    filters
 * @subpackage ArticleTranslation *
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */
class BaseArticleTranslationFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'    => new sfWidgetFormFilterInput(),
      'title' => new sfWidgetFormFilterInput(),
      'body'  => new sfWidgetFormFilterInput(),
      'lang'  => new sfWidgetFormFilterInput(),
      'slug'  => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'id'    => new sfValidatorDoctrineChoice(array('required' => false, 'model' => 'ArticleTranslation', 'column' => 'id')),
      'title' => new sfValidatorPass(array('required' => false)),
      'body'  => new sfValidatorPass(array('required' => false)),
      'lang'  => new sfValidatorDoctrineChoice(array('required' => false, 'model' => 'ArticleTranslation', 'column' => 'lang')),
      'slug'  => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('article_translation[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'ArticleTranslation';
  }

  public function getFields()
  {
    return array(
      'id'    => 'integer',
      'title' => 'string',
      'body'  => 'string',
      'lang'  => 'string',
      'slug'  => 'string',
    );
  }
}