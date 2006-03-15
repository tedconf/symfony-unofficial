<?php

pake_desc('upgrade to a new symfony release');
pake_task('upgrade', 'project_exists');

pake_desc('downgrade to a previous symfony release');
pake_task('downgrade', 'project_exists');

function run_upgrade($task, $args)
{
  if (!isset($args[0]))
  {
    throw new Exception('You must provide the upgrade script to use (0.6 to upgrade to 0.6 for example).');
  }

  $version = $args[0];

  if ($version == '0.6')
  {
    run_upgrade_to_0_6($task, $args);
  }
  else
  {
    throw new Exception('I have no upgrade script for this release.');
  }
}

function run_upgrade_to_0_6($task, $args)
{
  $verbose = pakeApp::get_instance()->get_verbose();

  // find all applications for this project
  $apps = pakeFinder::type('directory')->name(sfConfig::get('sf_app_module_dir_name'))->mindepth(1)->maxdepth(1)->relative()->in(sfConfig::get('sf_apps_dir_name'));

  foreach ($apps as $appModuleDir)
  {
    $app = str_replace(DIRECTORY_SEPARATOR.sfConfig::get('sf_app_module_dir_name'), '', $appModuleDir);
    if ($verbose)
    {
      echo '>> app       '.pakeApp::excerpt('converting "'.$app.'"'.' application')."\n";
    }

    $appDir = sfConfig::get('sf_apps_dir_name').'/'.$app;

    // upgrade config.php script file
    if ($verbose)
    {
      echo '>> app       '.pakeApp::excerpt('upgrading config.php')."\n";
    }
    pake_copy(sfConfig::get('sf_symfony_data_dir').'/skeleton/app/app/config/config.php', $appDir.'/config/config.php', array('override' => true));

    // change all constants to use sfConfig object
    _upgrade_0_6_constants(array($appDir.'/'.sfConfig::get('sf_app_module_dir_name'), $appDir.'/'.sfConfig::get('sf_app_module_dir_name'), $appDir.'/'.sfConfig::get('sf_app_lib_dir_name')));

    // change view shortcuts in global and modules template directories
    $templateDirs   = pakeFinder::type('directory')->name('templates')->mindepth(1)->maxdepth(1)->in($appDir.'/'.sfConfig::get('sf_app_module_dir_name'));
    $templateDirs[] = $appDir.'/'.sfConfig::get('sf_app_template_dir_name');

    _upgrade_0_6_view_shortcuts($templateDirs);
    _upgrade_0_6_mail_to($templateDirs);

    // change comment character in YML files
    _upgrade_0_6_yml_comments($appDir.'/config');

    // change standard_helpers and i18n format
    _upgrade_0_6_settings($appDir);

    // move i18n messages XML file
    _upgrade_0_6_i18n($appDir);

    // rename sfPager to sfPropelPager
    _upgrade_0_6_sfpager($appDir);

    // change disable_web_debug usage
    _upgrade_0_6_disable_web_debug($appDir);

    // rename deprecated methods in actions
    $actionDirs = pakeFinder::type('directory')->name('actions')->mindepth(1)->maxdepth(1)->in($appDir.'/'.sfConfig::get('sf_app_module_dir_name'));

    _upgrade_0_6_action($actionDirs);

    // rename sf_default_culture to sf_i18n_default_culture in all libraries
    _upgrade_0_6_i18n_config($appDir);
  }

  // constants in global libraries
  _upgrade_0_6_constants(sfConfig::get('sf_lib_dir_name'));

  // sfPager in global libraries
  _upgrade_0_6_sfpager(sfConfig::get('sf_lib_dir_name'));

  // location of config/config.php
  _upgrade_0_6_config(array(sfConfig::get('sf_web_dir_name'), sfConfig::get('sf_bin_dir_name')));

  // change propelpropel builder paths
  _upgrade_0_6_propel_builder();

  // rename sf_default_culture to sf_i18n_default_culture in all libraries
  _upgrade_0_6_i18n_config(sfConfig::get('sf_lib_dir_name'));

  // clear cache
  run_clear_cache($task, array());
}

function _upgrade_0_6_propel_builder()
{
  $verbose = pakeApp::get_instance()->get_verbose();

  $propelFile = sfConfig::get('sf_config_dir').DIRECTORY_SEPARATOR.'propel.ini';

  if (is_readable($propelFile))
  {
    $propelIni = file_get_contents($propelFile);

    $propelIni = str_replace('symfony.symfony.addon.propel.builder', 'symfony.addon.propel.builder', $propelIni);

    file_put_contents($propelFile, $propelIni);
  }
}

function _upgrade_0_6_yml_comments($dir)
{
  $verbose = pakeApp::get_instance()->get_verbose();

  $ymlFiles = pakeFinder::type('file')->name('*.yml')->in($dir);

  $regex = '/^;/m';

  foreach ($ymlFiles as $ymlFile)
  {
    $content = file_get_contents($ymlFile);

    if (!preg_match($regex, $content))
    {
      continue;
    }

    if ($verbose)
    {
      echo '>> file      '.pakeApp::excerpt('change YML comment character for "'.$ymlFile.'"')."\n";
    }

    $content = preg_replace($regex, '#', $content);

    file_put_contents($ymlFile, $content);
  }
}

function _upgrade_0_6_i18n_config($dir)
{
  $verbose = pakeApp::get_instance()->get_verbose();

  $phpFiles = pakeFinder::type('file')->name('*.php')->in($dir);

  $regex = '/sf_default_culture/m';

  foreach ($phpFiles as $phpFile)
  {
    $content = file_get_contents($phpFile);

    if (!preg_match($regex, $content))
    {
      continue;
    }

    if ($verbose)
    {
      echo '>> file      '.pakeApp::excerpt('change i18n constants for "'.$phpFile.'"')."\n";
    }

    $content = preg_replace($regex, 'sf_i18n_default_culture', $content);

    file_put_contents($phpFile, $content);
  }
}

function _upgrade_0_6_action($dir)
{
  $verbose = pakeApp::get_instance()->get_verbose();

  $phpFiles = pakeFinder::type('file')->name('*.php')->in($dir);

  $regex = '(forward404|forward|redirect)_(if|unless)';

  foreach ($phpFiles as $phpFile)
  {
    $content = file_get_contents($phpFile);

    if (!preg_match('/'.$regex.'/', $content))
    {
      continue;
    }

    if ($verbose)
    {
      echo '>> file      '.pakeApp::excerpt('rename deprecated forward/redirect methods for "'.$phpFile.'"')."\n";
    }

    $content = preg_replace('/'.$regex.'/e', "'\\1'.ucfirst('\\2')", $content);

    file_put_contents($phpFile, $content);
  }
}

function _upgrade_0_6_sfpager($dir)
{
  $verbose = pakeApp::get_instance()->get_verbose();

  $phpFiles = pakeFinder::type('file')->name('*.php')->in($dir);

  $regex = '(sfPager)';

  foreach ($phpFiles as $phpFile)
  {
    $content = file_get_contents($phpFile);

    if (!preg_match('/'.$regex.'/', $content))
    {
      continue;
    }

    if ($verbose)
    {
      echo '>> file      '.pakeApp::excerpt('rename sfPager for "'.$phpFile.'"')."\n";
    }

    $content = preg_replace('/'.$regex.'/', 'sfPropelPager', $content);

    file_put_contents($phpFile, $content);
  }
}

function _upgrade_0_6_disable_web_debug($dir)
{
  $verbose = pakeApp::get_instance()->get_verbose();

  $phpFiles = pakeFinder::type('file')->name('*.php')->in($dir);

  foreach ($phpFiles as $phpFile)
  {
    $content = file_get_contents($phpFile);

    if (!preg_match('/disable_web_debug/', $content))
    {
      continue;
    }

    if ($verbose)
    {
      echo '>> file      '.pakeApp::excerpt('converting disable_web_debug for "'.$phpFile.'"')."\n";
    }

    $content = preg_replace("#^(\s*).this\->getRequest\(\)\->setAttribute\s*\(\s*'disable_web_debug'\s*,\s*(true|1)\s*,\s*'debug/web'\s*\)\s*;#mi", '\\1sfConfig::set(\'sf_web_debug\', false);', $content);

    file_put_contents($phpFile, $content);
  }
}

function _upgrade_0_6_i18n($app)
{
  if (is_dir($app.'/i18n/global'))
  {
    // subversion?
    if (is_dir($app.'/i18n/global/.svn'))
    {
      try
      {
        pake_sh('svn move '.$app.'/i18n/global/* '.$app.'/i18n/');
        pake_sh('svn remove '.$app.'/i18n/global');
      }
      catch (Exception $e)
      {
      }
    }
    else
    {
      $finder = pakeFinder::type('any')->prune('.svn')->discard('.svn');
      $sf_app_i18n_dir_name = sfConfig::get('sf_app_i18n_dir_name');
      pake_mirror($finder, $app.'/'.$sf_app_i18n_dir_name.'/global', $app.'/'.$sf_app_i18n_dir_name);
      pake_remove($finder, $app.'/'.$sf_app_i18n_dir_name.'/global');
      pake_remove($app.'/'.$sf_app_i18n_dir_name.'/global', '');
    }
  }
}

function _upgrade_0_6_settings($app)
{
  $verbose = pakeApp::get_instance()->get_verbose();

  $sf_app_config_dir_name = sfConfig::get('sf_app_config_dir_name');

  $content = file_get_contents($app.'/'.$sf_app_config_dir_name.'/settings.yml');

  if ($verbose)
  {
    echo '>> file      '.pakeApp::excerpt('converting settings.yml')."\n";
  }

  if (!preg_match('/(standard_helpers|standard_helpers):\s*\[/', $content))
  {
    $content = preg_replace('/^([;\s]+)(helper_standard|standard_helpers)\:(\s+)(.+)$/me', "'$1standard_helpers:$3['.implode(', ', explode(',', '$4')).']'", $content);
  }

  // i18n
  $content = preg_replace('/^([;\s]+)is_i18n\:(\s+)(.+)$/m', '$1i18n:   $2$3', $content);

  $defaultCulture = 'en';
  if (preg_match('/^.+default_culture\:\s*(.+)$/m', $content, $match))
  {
    $defaultCulture = $match[1];
    $content = preg_replace('/^.+default_culture\:\s*.+$/m', '', $content);

    // create the new i18n configuration file
    if (!is_readable($app.'/'.$sf_app_config_dir_name.'/i18n.yml'))
    {
      if ($verbose) echo '>> file+     '.pakeApp::excerpt('new i18n.yml configuration file')."\n";
      $i18n = "all:\n  default_culture: $defaultCulture\n";
      file_put_contents($app.'/'.$sf_app_config_dir_name.'/i18n.yml', $i18n);
    }
  }

  file_put_contents($app.'/'.$sf_app_config_dir_name.'/settings.yml', $content);
}

function _upgrade_0_6_view_shortcuts($dirs)
{
  $verbose = pakeApp::get_instance()->get_verbose();

  $phpFiles = pakeFinder::type('file')->name('*.php')->in($dirs);

  $regex = '(context\-|params\-|request\-|user\-|view\-|last_module|last_action|first_module|first_action)';

  foreach ($phpFiles as $phpFile)
  {
    $content = file_get_contents($phpFile);

    if (!preg_match('/\$'.$regex.'/', $content))
    {
      continue;
    }

    if ($verbose)
    {
      echo '>> file      '.pakeApp::excerpt('converting view shortcuts for "'.$phpFile.'"')."\n";
    }

    $content = preg_replace('/\$'.$regex.'/', '$sf_\\1', $content);

    file_put_contents($phpFile, $content);
  }
}

function _upgrade_0_6_mail_to($dirs)
{
  $verbose = pakeApp::get_instance()->get_verbose();

  $phpFiles = pakeFinder::type('file')->name('*.php')->in($dirs);

  $regex = 'mail_to\s*\(\s*([^,\)]+?), ([^,\)]+?)\)';

  foreach ($phpFiles as $phpFile)
  {
    $content = file_get_contents($phpFile);

    if (!preg_match('/'.$regex.'/', $content))
    {
      continue;
    }

    if ($verbose)
    {
      echo '>> file      '.pakeApp::excerpt('converting mail_to for "'.$phpFile.'"')."\n";
    }

    $content = preg_replace('/'.$regex.'/', 'mail_to($1, $1, \'encode=$2\')', $content);

    file_put_contents($phpFile, $content);
  }
}

function _upgrade_0_6_constants($dirs)
{
  $verbose = pakeApp::get_instance()->get_verbose();

  $phpFiles = pakeFinder::type('file')->name('*.php')->in($dirs);

  $regex = '((SF|APP|MOD)_[A-Z0-9_]+)';

  foreach ($phpFiles as $phpFile)
  {
    $content = file_get_contents($phpFile);

    if (!preg_match('/'.$regex.'/', $content))
    {
      continue;
    }

    if ($verbose)
    {
      echo '>> file      '.pakeApp::excerpt('converting constants for "'.$phpFile.'"')."\n";
    }

    $content = preg_replace('/defined\(\''.$regex.'\'\)/e', "'sfConfig::get(\''.strtolower('\\1').'\')'", $content);
    $content = preg_replace('/define\(\''.$regex.'\',\s*(.+?)\)/e', "'sfConfig::set(\''.strtolower('\\1').'\', \\3)'", $content);
    $content = preg_replace('/'.$regex.'/e', "'sfConfig::get(\''.strtolower('\\1').'\')'", $content);

    file_put_contents($phpFile, $content);
  }
}

function _upgrade_0_6_config($dirs)
{
  $verbose = pakeApp::get_instance()->get_verbose();

  $phpFiles = pakeFinder::type('file')->name('*.php')->in($dirs);

  $search = "SF_ROOT_DIR.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.'config'.";

  foreach ($phpFiles as $phpFile)
  {
    $content = file_get_contents($phpFile);

    if (strpos($content, $search) === false)
    {
      continue;
    }

    if ($verbose)
    {
      echo '>> file      '.pakeApp::excerpt('updating location of config.php for "'.$phpFile.'"')."\n";
    }

    $content = str_replace($search, "SF_ROOT_DIR.DIRECTORY_SEPARATOR.'".sfConfig::get('sf_apps_dir_name')."'.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.'config'.", $content);

    file_put_contents($phpFile, $content);
  }
}

?>