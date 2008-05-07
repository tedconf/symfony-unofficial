<?php

$root_dir = realpath(dirname(__FILE__).'/../..');
require_once($root_dir.'/lib/vendor/lime/lime.php');
require_once($root_dir.'/lib/util/sfFinder.class.php');

require_once($root_dir.'/lib/autoload/sfCoreAutoload.class.php');
$version = SYMFONY_VERSION;

$o = new lime_output_color();
$o->comment(sprintf('symfony LOC (%s)', $version));

// symfony core LOC
$total_loc = 0;
$files = sfFinder::type('file')->name('*.php')->prune('vendor', 'plugins')->in($root_dir.'/lib');
foreach ($files as $file)
{
  $total_loc += count(lime_coverage::get_php_lines($file));
}
$files = sfFinder::type('file')->name('*.php')->prune('vendor')->in($root_dir.'/lib/plugins/*/lib');
foreach ($files as $file)
{
  $total_loc += count(lime_coverage::get_php_lines($file));
}

// symfony tests LOC
$total_tests_loc = 0;
$files = sfFinder::type('file')->name('*Test.php')->in(array(
  $root_dir.'/lib/plugins/sfCompat10Plugin/test/unit',
  $root_dir.'/lib/plugins/sfCompat10Plugin/test/functional',
  $root_dir.'/lib/plugins/sfPropelPlugin/test/unit',
  $root_dir.'/lib/plugins/sfPropelPlugin/test/functional',
  $root_dir.'/test/unit',
  $root_dir.'/test/functional',
  $root_dir.'/test/other',
));
foreach ($files as $file)
{
  $total_tests_loc += count(lime_coverage::get_php_lines($file));
}

$o->echoln(sprintf('core libraries:            %7d', $total_loc));
$o->echoln(sprintf('unit and functional tests: %6d', $total_tests_loc));
$o->echoln(sprintf('ratio tests/libraries:    %5d%%', $total_tests_loc / $total_loc * 100));
