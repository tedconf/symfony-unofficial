[?php use_helpers('I18N', 'Date') ?]

<h1><?php echo $this->getParameterValue('list.title', $this->getModuleName().' list') ?></h1>

<div id="sf_admin_bar">

<?php if ($this->getParameterValue('list.filters')): ?>
<div class="sf_admin_filters">
[?php echo form_tag('<?php echo $this->getModuleName() ?>/list') ?]
<fieldset>
<h2>filters</h2>
<?php foreach ($this->getColumns('list.filters') as $column): $type = $column->getCreoleType() ?>
<div class="form-row">
<label for="<?php echo $column->getName() ?>">[?php echo __('<?php echo $this->getParameterValue('list.fields.'.$column->getName().'.name') ?>:') ?]<?php echo $this->getHelp($column, 'edit') ?></label>
[?php echo <?php echo $this->getColumnFilterTag($column) ?> ?]
</div>
<?php endforeach ?>
</fieldset>
<ul class="sf_admin_actions">
  <li>[?php echo submit_tag(__('filter'), 'name=filter class=sf_admin_filter') ?]</li>
</ul>
</form>
</div>
<?php endif ?>

</div>

<div id="sf_admin_content">

<table cellspacing="0" class="sf_admin_list">
<thead>
<tr>
<?php foreach ($this->getColumns('list.display') as $column): ?>
  <th>
    <?php if ($column->isReal()): ?>
      [?php if ($sf_user->getAttribute('sort', null, 'sf_admin/<?php echo $this->getSingularName() ?>/sort') == '<?php echo $column->getName() ?>'): ?]
      [?php echo link_to(__('<?php echo $this->getParameterValue('list.fields.'.$column->getName().'.name') ?>'), '<?php echo $this->getModuleName() ?>/list?sort=<?php echo $column->getName() ?>&type='.($sf_user->getAttribute('type', 'asc', 'sf_admin/<?php echo $this->getSingularName() ?>/sort') == 'asc' ? 'desc' : 'asc')) ?]
      ([?php echo $sf_user->getAttribute('type', 'asc', 'sf_admin/<?php echo $this->getSingularName() ?>/sort') ?])
      [?php else: ?]
      [?php echo link_to(__('<?php echo $this->getParameterValue('list.fields.'.$column->getName().'.name') ?>'), '<?php echo $this->getModuleName() ?>/list?sort=<?php echo $column->getName() ?>&type=asc') ?]
      [?php endif ?]
    <?php else: ?>
    [?php echo __('<?php echo $this->getParameterValue('list.fields.'.$column->getName().'.name') ?>') ?]
    <?php endif ?>
    <?php echo $this->getHelp($column, 'list') ?>
  </th>
<?php endforeach ?>
</tr>
</thead>
<tbody>
[?php $i = 1; foreach ($pager->getResults() as $<?php echo $this->getSingularName() ?>): $odd = fmod(++$i, 2) ?]
<tr class="sf_admin_row_[?php echo $odd ?]">
<?php foreach ($this->getColumns('list.display') as $column): ?>
  <?php if ($column->isLink()): ?>
  <td>[?php echo link_to($<?php echo $this->getSingularName() ?>->get<?php echo $column->getPhpName() ?>(), '<?php echo $this->getModuleName() ?>/edit?<?php echo $this->getPrimaryKeyUrlParams() ?>) ?]</td>
  <?php else: ?>
  <td>[?php echo <?php echo $this->getColumnListTag($column) ?> ?]</td>
  <?php endif ?>
<?php endforeach ?>
</tr>
[?php endforeach ?]
</tbody>
<tfoot>
<tr><th colspan="<?php echo count($this->getColumns('list.display'))  ?>">
<div class="float-right">
[?php if ($pager->haveToPaginate()): ?]
  [?php echo link_to(image_tag('/sf/images/sf_admin/first.png', 'align=absmiddle'), '<?php echo $this->getModuleName() ?>/list?page=1') ?]
  [?php echo link_to(image_tag('/sf/images/sf_admin/previous.png', 'align=absmiddle'), '<?php echo $this->getModuleName() ?>/list?page='.$pager->getPreviousPage()) ?]

  [?php foreach ($pager->getLinks() as $page): ?]
    [?php echo link_to_unless($page == $pager->getPage(), $page, '<?php echo $this->getModuleName() ?>/list?page='.$page) ?]
  [?php endforeach ?]

  [?php echo link_to(image_tag('/sf/images/sf_admin/next.png', 'align=absmiddle'), '<?php echo $this->getModuleName() ?>/list?page='.$pager->getNextPage()) ?]
  [?php echo link_to(image_tag('/sf/images/sf_admin/last.png', 'align=absmiddle'), '<?php echo $this->getModuleName() ?>/list?page='.$pager->getLastPage()) ?]
[?php endif ?]
</div>
[?php echo format_number_choice('[0] no result|[1] 1 result|(1,+Inf] %1% results', array('%1%' => $pager->getNbResults()), $pager->getNbResults()) ?]
</th></tr>
</tfoot>
</table>

<ul class="sf_admin_actions">
  <li>[?php echo button_to(__('create'), '<?php echo $this->getModuleName() ?>/edit', 'class=sf_admin_create') ?]</li>
</ul>

</div>
