[?php use_helpers('Object', 'Validation', 'ObjectAdmin', 'I18N') ?]

<h1><?php echo $this->getI18NString('edit.title', 'edit '.$this->getModuleName()) ?></h1>

<div id="sf_admin_content">

[?php if ($sf_params->get('save') == 'ok'): ?]
<div class="save-ok">
<h2>[?php echo __('Your modifications has been saved') ?]</h2>
</div>
[?php elseif ($sf_request->hasErrors()): ?]
<div class="form-errors">
<h2>[?php echo __('There are some errors that prevent the form to validate') ?]</h2>
<ul>
[?php foreach ($sf_request->getErrorNames() as $name): ?]
  <li>[?php echo $sf_request->getError($name) ?]</li>
[?php endforeach ?]
</ul>
</div>
[?php endif ?]

[?php echo form_tag('<?php echo $this->getModuleName() ?>/edit', 'id=sf_admin_edit_form') ?]

<?php foreach ($this->getPrimaryKey() as $pk): ?>
[?php echo object_input_hidden_tag($<?php echo $this->getSingularName() ?>, 'get<?php echo $pk->getPhpName() ?>') ?]
<?php endforeach ?>

<?php foreach ($this->getColumnCategories('edit.display') as $category): ?>
<?php
  if ($category[0] == '-')
  {
    $category_name = substr($category, 1);
    $collapse = true;
  }
  else
  {
    $category_name = $category;
    $collapse = false;
  }
?>
<fieldset class="<?php if ($collapse): ?> collapse<?php endif ?>">
<?php if ($category != 'NONE'): ?><h2>[?php echo __('<?php echo $category_name ?>') ?]</h2>

<?php endif ?>
<?php foreach ($this->getColumns('edit.display', $category) as $name => $column): ?>
<?php if ($column->isPrimaryKey()) continue ?>
<div class="form-row">
  <label <?php if ($column->isNotNull()): ?>class="required" <?php endif ?>for="<?php echo $column->getName() ?>">[?php echo __('<?php echo $this->getParameterValue('edit.fields.'.$column->getName().'.name') ?>:') ?]<?php echo $this->getHelp($column, 'edit') ?></label>
  <div[?php if ($sf_request->hasError('<?php echo $column->getName() ?>')): ?] class="form-error"[?php endif ?]>
  [?php if ($sf_request->hasError('<?php echo $column->getName() ?>')): ?]<div class="form-error-msg">&darr;&nbsp;[?php echo $sf_request->getError('<?php echo $column->getName() ?>') ?]&nbsp;&darr;</div>[?php endif ?]

  [?php echo <?php echo $this->getColumnEditTag($column) ?> ?]
  </div>
</div>

<?php endforeach ?>
</fieldset>
<?php endforeach ?>

<ul class="sf_admin_actions">
[?php if (<?php echo $this->getPrimaryKeyIsSet() ?>): ?]
  <li class="float-left">[?php echo button_to(__('delete'), '<?php echo $this->getModuleName() ?>/delete?<?php echo $this->getPrimaryKeyUrlParams() ?>, 'class=sf_admin_delete post=true confirm=Are you sure?') ?]</li>
[?php endif ?]
  <li>[?php echo button_to(__('cancel'), '<?php echo $this->getModuleName() ?>/list', 'class=sf_admin_cancel') ?]</li>
  <li>[?php echo submit_tag(__('save'), 'class=sf_admin_default_action sf_admin_save') ?]</li>
</ul>

</form>

</div>
