<?php if ($this->getParameterValue('edit.actions')): ?>
<ul class="sf_admin_actions">
<?php foreach ($this->getParameterValue('edit.actions') as $actionName => $params): ?>
  <?php echo $this->getButtonToAction($actionName, $params, true) ?>
<?php endforeach ?>
</ul>
<?php endif ?>
