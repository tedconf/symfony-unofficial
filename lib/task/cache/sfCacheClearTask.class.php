<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Clears the symfony cache.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfCacheClearTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::OPTIONAL, 'The application name'),
      new sfCommandArgument('type', sfCommandArgument::OPTIONAL, 'The cache type to clear'),
    ));

    $this->aliases = array('cc', 'clear-cache');
    $this->namespace = 'cache';
    $this->name = 'clear';
    $this->briefDescription = 'Clears the cache';

    $this->detailedDescription = <<<EOF
The [cache:clear|INFO] task clears the symfony cache.

It removes all the files found in the [sf_cache_dir|COMMENT] directory
([cache/|COMMENT] by default). It does not remove directories.

If it's called with an application name, it only clears the cache
for the given application.

For example, to clear the frontend application cache:

  [./symfony cache:clear frontend|INFO]

If it's called with an application name and a type, it will only
clears the cache for the given application and type.
The symfony built-in types are: [config|COMMENT], [i18n|COMMENT] and [template|COMMENT].

So, to clear the frontend application template cache:

  [./symfony cache:clear frontend template|INFO]

EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $cacheDir = sfConfig::get('sf_cache_dir');
    if (!$cacheDir || !is_dir($cacheDir))
    {
      throw new sfException(sprintf('Cache directory "%s" does not exist.', $cacheDir));
    }

    // app
    $mainApp = '';
    if (isset($arguments['application']))
    {
      $mainApp = $arguments['application'];
    }

    // type (template, i18n or config)
    $mainType = '';
    if (isset($arguments['type']))
    {
      $mainType = $arguments['type'];
    }

    // declare type that must be cleaned safely (with a lock file during cleaning)
    $safeTypes = array(sfConfig::get('sf_app_config_dir_name'), sfConfig::get('sf_app_i18n_dir_name'));

    // finder to remove all files in a cache directory
    $finder = sfFinder::type('file')->ignore_version_control()->discard('.sf');

    // finder to find directories (1 level) in a directory
    $dirFinder = sfFinder::type('dir')->ignore_version_control()->discard('.sf')->maxdepth(0)->relative();

    // clear global cache
    if (!$mainApp)
    {
      $this->filesystem->remove($finder->in(sfConfig::get('sf_cache_dir')));
    }

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
      if (!is_dir($cacheDir.DIRECTORY_SEPARATOR.$app))
      {
        continue;
      }

      // remove cache for all environments
      foreach ($dirFinder->in($cacheDir.DIRECTORY_SEPARATOR.$app) as $env)
      {
        // which types?
        $types = array();
        if ($mainType)
        {
          $types[] = $mainType;
        }
        else
        {
          $types = $dirFinder->in($cacheDir.DIRECTORY_SEPARATOR.$app.DIRECTORY_SEPARATOR.$env);
        }

        foreach ($types as $type)
        {
          $subDir = $cacheDir.DIRECTORY_SEPARATOR.$app.DIRECTORY_SEPARATOR.$env.DIRECTORY_SEPARATOR.$type;

          if (!is_dir($subDir))
          {
            continue;
          }

          // remove cache files
          if (in_array($type, $safeTypes))
          {
            $this->safeCacheRemove($finder, $subDir, $app.'_'.$env);
          }
          else
          {
            $this->filesystem->remove($finder->in(sfConfig::get('sf_root_dir').DIRECTORY_SEPARATOR.$subDir));
          }
        }
      }
    }

    // clear apc cache
    $cache = new sfAPCCache(array('prefix' => ''));
    $cache->clean(sfAPCCache::ALL);

    $this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->formatSection('apc', 'cache removed'))));

    // clear autoload cache
    foreach(sfFinder::type('file')->name('sf_autoload*')->maxdepth(0)->in(sfToolkit::getTmpDir()) as $file)
    {
      @unlink($file);
    }
    $this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->formatSection('autoload', 'cache removed'))));
  }

  /**
   * Removes a directory safely.
   *
   * @param object $finder
   * @param string $subDir
   * @param string $lockName
   */
  protected function safeCacheRemove($finder, $subDir, $lockName)
  {
    // create a lock file
    $this->filesystem->touch(sfConfig::get('sf_root_dir').DIRECTORY_SEPARATOR.$lockName.'.lck');

    // change mode so the web user can remove it if we die
    $this->filesystem->chmod(sfConfig::get('sf_root_dir').DIRECTORY_SEPARATOR.$lockName.'.lck', 0777);

    // remove cache files
    $this->filesystem->remove($finder->in(sfConfig::get('sf_root_dir').DIRECTORY_SEPARATOR.$subDir));

    // release lock
    $this->filesystem->remove(sfConfig::get('sf_root_dir').DIRECTORY_SEPARATOR.$lockName.'.lck');
  }
}
