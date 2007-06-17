<div id="test1"><?php echo pjs_path('test/index') ?></div>
<div id="test2">
  <?php echo pjs_include_tag('test/index') ?>
</div>
<div id="test3">
  <?php use_pjs('test/index') ?>
</div>
<div id="test4"><?php echo pjs_path('test/index?foo=bar') ?></div>
<div id="test5"><?php echo pjs_path('test/index', null, array('query_string' => 'foo=bar')) ?></div>
<div id="test6"><?php echo pjs_path('test/index', true) ?></div>
