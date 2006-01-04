<?php if ($this->getParameterValue('list.actions')): ?>
<ul class="sf_admin_actions">
<?php foreach ($this->getParameterValue('list.actions') as $actionName => $params): ?>
  <?php echo $this->getButtonToAction($actionName, $params, false) ?>
<?php endforeach ?>
</ul>
<?php endif ?>
