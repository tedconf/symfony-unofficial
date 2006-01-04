<td>
  [?php echo link_to(image_tag('/sf/images/sf_admin/edit_icon.png', array('alt' => __('edit'), 'title' => __('edit'))), '<?php echo $this->getModuleName() ?>/edit?<?php echo $this->getPrimaryKeyUrlParams() ?>) ?]
  [?php echo link_to(image_tag('/sf/images/sf_admin/delete_icon.png', array('alt' => __('delete'), 'title' => __('delete'))), '<?php echo $this->getModuleName() ?>/delete?<?php echo $this->getPrimaryKeyUrlParams() ?>, 'post=true confirm=Are you sure?') ?]
<?php foreach ($this->getParameterValue('list.actions') as $actionName => $params): ?>
  <?php
    $name   = isset($params['name']) ? $params['name'] : $actionName;
    $icon   = isset($params['icon']) ? $params['icon'] : '/sf/images/sf_admin/default_icon.png';
    $action = isset($params['action']) ? $params['action'] : 'List'.sfInflector::camelize($actionName);
  ?>
  [?php echo link_to(image_tag('<?php echo $icon ?>', array('alt' => __('<?php echo $name ?>'), 'title' => __('<?php echo $name ?>'))), '<?php echo $this->getModuleName() ?>/<?php echo $action ?>?<?php echo $this->getPrimaryKeyUrlParams() ?>) ?]
<?php endforeach ?>
</td>
