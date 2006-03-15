<?php

pake_desc('clear cached information');
pake_task('clear-cache', 'project_exists');
pake_alias('cc', 'clear-cache');

pake_desc('fix directories permissions');
pake_task('fix-perms', 'project_exists');

function run_fix_perms($task, $args)
{
  $sf_root_dir = sfConfig::get('sf_root_dir');

  $finder = pakeFinder::type('dir')->prune('.svn')->discard('.svn');
  pake_chmod($finder, sfConfig::get('sf_upload_dir'), 0777);

  pake_chmod(sfConfig::get('sf_apps_dir_name'), $sf_root_dir, 0777);
  pake_chmod('cache', $sf_root_dir, 0777);

  $finder = pakeFinder::type('file')->prune('.svn')->discard('.svn');
  pake_chmod($finder, sfConfig::get('sf_upload_dir'), 0666);
  pake_chmod($finder, sfConfig::get('sf_log_dir'), 0666);
}

function run_clear_cache($task, $args)
{
  if (!file_exists('cache'))
  {
    throw new Exception('Cache directory does not exist.');
  }

  $cacheDir = sfConfig::get('sf_cache_dir_name');

  // app
  $mainApp = '';
  if (isset($args[0]))
  {
    $mainApp = $args[0];
  }

  // type (template, i18n or config)
  $mainType = '';
  if (isset($args[1]))
  {
    $mainType = $args[1];
  }

  // declare type that must be cleaned safely (with a lock file during cleaning)
  $safeTypes = array(sfConfig::get('sf_app_config_dir_name'), sfConfig::get('sf_app_i18n_dir_name'));

  // finder to remove all files in a cache directory
  $finder = pakeFinder::type('file')->prune('.svn')->discard('.svn', '.sf');

  // finder to find directories (1 level) in a directory
  $dirFinder = pakeFinder::type('dir')->prune('.svn')->discard('.svn', '.sf')->maxdepth(0)->relative();

  // iterate through applications
  $apps = array();
  if ($mainApp)
  {
    $apps[] = $mainApp;
  }
  else
  {
    $apps = $dirFinder->in($cacheDir);
  }

  foreach ($apps as $app)
  {
    if (!is_dir($cacheDir.'/'.$app))
    {
      continue;
    }

    // remove cache for all environments
    foreach ($dirFinder->in($cacheDir.'/'.$app) as $env)
    {
      // which types?
      $types = array();
      if ($mainType)
      {
        $types[] = $mainType;
      }
      else
      {
        $types = $dirFinder->in($cacheDir.'/'.$app.'/'.$env);
      }

      $sf_root_dir = sfConfig::get('sf_root_dir');
      foreach ($types as $type)
      {
        $subDir = $cacheDir.'/'.$app.'/'.$env.'/'.$type;

        if (!is_dir($subDir))
        {
          continue;
        }

        // remove cache files
        if (in_array($type, $safeTypes))
        {
          $lockName = $app.'_'.$env;
          _safe_cache_remove($finder, $subDir, $lockName);
        }
        else
        {
          pake_remove($finder, $sf_root_dir.'/'.$subDir);
        }
      }
    }
  }
}

function _safe_cache_remove($finder, $subDir, $lockName)
{
  $sf_root_dir = sfConfig::get('sf_root_dir');

  // create a lock file
  pake_touch($sf_root_dir.'/'.$lockName.'.lck', '');

  // change mode so the web user can remove it if we die
  pake_chmod($lockName.'.lck', $sf_root_dir, 0777);

  // remove cache files
  pake_remove($finder, $sf_root_dir.'/'.$subDir);

  // release lock
  pake_remove($sf_root_dir.'/'.$lockName.'.lck', '');
}

?>