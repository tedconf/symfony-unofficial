[?php if (<?php echo $this->getPrimaryKeyIsSet() ?>): ?]
  <li class="float-left">[?php echo button_to(__('delete'), '<?php echo $this->getModuleName() ?>/delete?<?php echo $this->getPrimaryKeyUrlParams() ?>, 'class=sf_admin_delete post=true confirm=Are you sure?') ?]</li>
[?php endif ?]
<?php foreach ($this->getParameterValue('edit.actions') as $actionName => $params): ?>
  <?php
    $name    = isset($params['name']) ? $params['name'] : $actionName;
    $icon    = isset($params['icon']) ? $params['icon'] : '/sf/images/sf_admin/default_icon.png';
    $action  = isset($params['action']) ? $params['action'] : 'List'.sfInflector::camelize($actionName);
    $options = isset($params['class']) ? 'class='.$params['class'] : 'style=background: #ffc url('.$icon.') no-repeat 3px 2px';
  ?>
  <li>[?php echo button_to(__('<?php echo $name ?>'), '<?php echo $this->getModuleName() ?>/<?php echo $action ?>?<?php echo $this->getPrimaryKeyUrlParams() ?>, '<?php echo $options ?>') ?]</li>
<?php endforeach ?>
  <li>[?php echo button_to(__('cancel'), '<?php echo $this->getModuleName() ?>/list', 'class=sf_admin_cancel') ?]</li>
  <li>[?php echo submit_tag(__('save'), 'class=sf_admin_default_action sf_admin_save') ?]</li>
