<ul class="sf_admin_actions">
<?php $editActions = $this->getParameterValue('edit.actions'); if ($editActions): ?>
<?php foreach ($editActions as $actionName => $params): ?>
  <?php if ($actionName == '_delete') continue ?>
  <?php echo $this->getButtonToAction($actionName, $params, true) ?>
<?php endforeach; ?>
<?php else: ?>
  <?php echo $this->getButtonToAction('_list', array(), true) ?>
  <?php echo $this->getButtonToAction('_save', array(), true) ?>
<?php endif; ?>
</ul>
