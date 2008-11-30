[?php

require_once(sfConfig::get('sf_lib_dir').'/filter/doctrine/base/BaseFormFilterDoctrine.class.php');

/**
 * <?php echo $this->table->getOption('name') ?> filter form base class.
 *
 * @package    filters
 * @subpackage <?php echo $this->table->getOption('name') ?>
 *
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 11675 2008-09-19 15:21:38Z fabien $
 */
class Base<?php echo $this->table->getOption('name') ?>FormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
<?php foreach ($this->table->getColumns() as $name => $column): ?>
      '<?php echo $this->table->getFieldName($name) ?>'<?php echo str_repeat(' ', $this->getColumnNameMaxLength() - strlen($name)) ?> => new <?php echo $this->getWidgetClassForColumn($name) ?>(<?php echo $this->getWidgetOptionsForColumn($name) ?>),
<?php endforeach; ?>
<?php foreach ($this->getManyToManyRelations() as $relation): ?>
      '<?php echo $this->underscore($relation['refTable']->getOption('name')) ?>_list'<?php echo str_repeat(' ', $this->getColumnNameMaxLength() - strlen($this->underscore($relation['refTable']->getOption('name')).'_list')) ?> => new sfWidgetFormDoctrineSelectMany(array('model' => '<?php echo $relation['table']->getOption('name') ?>')),
<?php endforeach; ?>
    ));

    $this->setValidators(array(
<?php foreach ($this->table->getColumns() as $name => $column): ?>
      '<?php echo $this->table->getFieldName($name) ?>'<?php echo str_repeat(' ', $this->getColumnNameMaxLength() - strlen($name)) ?> => new <?php echo $this->getValidatorClassForColumn($name) ?>(<?php echo $this->getValidatorOptionsForColumn($name) ?>),
<?php endforeach; ?>
<?php foreach ($this->getManyToManyRelations() as $relation): ?>
      '<?php echo $this->underscore($relation['refTable']->getOption('name')) ?>_list'<?php echo str_repeat(' ', $this->getColumnNameMaxLength() - strlen($this->underscore($relation['refTable']->getOption('name')).'_list')) ?> => new sfValidatorDoctrineChoiceMany(array('model' => '<?php echo $relation['table']->getOption('name') ?>', 'required' => false)),
<?php endforeach; ?>
    ));

    $this->widgetSchema->setNameFormat('<?php echo $this->underscore($this->modelName) ?>[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

<?php foreach ($this->getManyToManyRelations() as $relation): ?>
  public function add<?php echo $this->underscore($relation['refTable']->getOption('name')) ?>ListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query->leftJoin('r.<?php echo $relation['refTable']->getOption('name') ?> <?php echo $relation['refTable']->getOption('name') ?>')
          ->orWhereIn('<?php echo $relation['refTable']->getOption('name') ?>.<?php $relation->getLocalFieldName() ?>', $values);
  }

<?php endforeach; ?>
  public function getModelName()
  {
    return '<?php echo $this->modelName ?>';
  }

  public function getFields()
  {
    return array(
<?php foreach ($this->table->getColumns() as $name => $column): ?>
      '<?php echo $this->table->getFieldName($name) ?>'<?php echo str_repeat(' ', $this->getColumnNameMaxLength() - strlen($name)) ?> => '<?php echo $this->table->getTypeOf($name) ?>',
<?php endforeach; ?>
<?php foreach ($this->getManyToManyRelations() as $relation): ?>
      '<?php echo $this->underscore($relation['refTable']->getOption('name')) ?>_list'<?php echo str_repeat(' ', $this->getColumnNameMaxLength() - strlen($this->underscore($relation['refTable']->getOption('name')).'_list')) ?> => 'ManyKey',
<?php endforeach; ?>
    );
  }
}