[?php use_helpers('I18N', 'Date') ?]

<h1><?php echo $this->getParameterValue('list.title', $this->getModuleName().' list') ?></h1>

<div id="sf_admin_bar">

[?php echo include_partial('filters', array('filters' => $filters)) ?]

</div>

<div id="sf_admin_content">

<table cellspacing="0" class="sf_admin_list">
<thead>
<tr>
[?php echo include_partial('list_th_<?php echo $this->getParameterValue('list.display.layout') ?>') ?]
  <th>[?php echo __('Actions') ?]</th>
</tr>
</thead>
<tbody>
[?php $i = 1; foreach ($pager->getResults() as $<?php echo $this->getSingularName() ?>): $odd = fmod(++$i, 2) ?]
<tr class="sf_admin_row_[?php echo $odd ?]">
[?php echo include_partial('list_td_<?php echo $this->getParameterValue('list.display.layout') ?>', array('<?php echo $this->getSingularName() ?>' => $<?php echo $this->getSingularName() ?>)) ?]
  <td>
    [?php echo link_to(image_tag('/sf/images/sf_admin/edit.png', array('alt' => __('edit'), 'title' => __('edit'))), '<?php echo $this->getModuleName() ?>/edit?<?php echo $this->getPrimaryKeyUrlParams() ?>) ?]
    [?php echo link_to(image_tag('/sf/images/sf_admin/delete.png', array('alt' => __('delete'), 'title' => __('delete'))), '<?php echo $this->getModuleName() ?>/delete?<?php echo $this->getPrimaryKeyUrlParams() ?>, 'post=true confirm=Are you sure?') ?]
  </td>
</tr>
[?php endforeach ?]
</tbody>
<tfoot>
<tr><th colspan="<?php echo count($this->getColumns('list.display.fields')) + 1 ?>">
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
