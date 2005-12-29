[?php use_helpers('I18N') ?]

<h1><?php echo $this->getParameterValue('list.title', $this->getModuleName().' list') ?></h1>

<?php if ($this->getParameterValue('list.filters')): ?>
[?php echo form_tag('<?php echo $this->getModuleName() ?>/list') ?]
<?php foreach ($this->getColumns('list.filters') as $column): $type = $column->getCreoleType() ?>
<label for="<?php echo $column->getName() ?>">[?php echo __('<?php echo $this->getParameterValue('list.fields.'.$column->getName().'.name') ?>:') ?]<?php echo $this->getHelp($column, 'edit') ?></label>
[?php echo input_tag('filter_<?php echo $column->getName() ?>', isset($filters['<?php echo $column->getName() ?>']) ? $filters['<?php echo $column->getName() ?>'] : '') ?]
<br />
<?php endforeach ?>
[?php echo submit_tag(__('filter'), 'name=filter') ?]
</form>
<?php endif ?>

<div class="module">
<table width="100%">
<thead>
<tr>
<?php foreach ($this->getColumns('list.display') as $column): ?>
  <th>
    <?php if ($column->isReal()): ?>
      [?php if ($user->getAttribute('sort', null, 'sf_admin/<?php echo $this->getSingularName() ?>/sort') == '<?php echo $column->getName() ?>'): ?]
      [?php echo link_to(__('<?php echo $this->getParameterValue('list.fields.'.$column->getName().'.name') ?>'), '<?php echo $this->getModuleName() ?>/list?sort=<?php echo $column->getName() ?>&type='.($user->getAttribute('type', 'asc', 'sf_admin/<?php echo $this->getSingularName() ?>/sort') == 'asc' ? 'desc' : 'asc')) ?]
      ([?php echo $user->getAttribute('type', 'asc', 'sf_admin/<?php echo $this->getSingularName() ?>/sort') ?])
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
[?php foreach ($pager->getResults() as $<?php echo $this->getSingularName() ?>): ?]
<tr>
<?php foreach ($this->getColumns('list.display') as $column): ?>
  <?php if ($column->isLink()): ?>
  <td>[?php echo link_to($<?php echo $this->getSingularName() ?>->get<?php echo $column->getPhpName() ?>(), '<?php echo $this->getModuleName() ?>/edit?<?php echo $this->getPrimaryKeyUrlParams() ?>) ?]</td>
  <?php else: ?>
  <td>[?php echo $<?php echo $this->getSingularName() ?>->get<?php echo $column->getPhpName() ?>() ?]</td>
  <?php endif ?>
<?php endforeach ?>
</tr>
[?php endforeach ?]
</tbody>
</table>
</div>

[?php if ($pager->haveToPaginate()): ?]
  [?php echo link_to('&lt;&lt;', '<?php echo $this->getModuleName() ?>/list?page=1') ?]
  [?php echo link_to('&lt;', '<?php echo $this->getModuleName() ?>/list?page='.$pager->getPreviousPage()) ?]

  [?php foreach ($pager->getLinks() as $page): ?]
    [?php echo link_to_unless($page == $pager->getPage(), $page, '<?php echo $this->getModuleName() ?>/list?page='.$page) ?]
  [?php endforeach ?]

  [?php echo link_to('&gt;', '<?php echo $this->getModuleName() ?>/list?page='.$pager->getNextPage()) ?]
  [?php echo link_to('&gt;&gt;', '<?php echo $this->getModuleName() ?>/list?page='.$pager->getLastPage()) ?]
[?php endif ?]

<ul>
  <li>[?php echo link_to (__('create'), '<?php echo $this->getModuleName() ?>/edit') ?]</li>
</ul>

