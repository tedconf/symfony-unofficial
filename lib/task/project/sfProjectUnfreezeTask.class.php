<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Unfreezes symfony libraries.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfProjectUnfreezeTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->aliases = array('unfreeze');
    $this->namespace = 'project';
    $this->name = 'unfreeze';
    $this->briefDescription = 'Unfreezes symfony libraries';

    $this->detailedDescription = <<<EOF
The [project:unfreeze|INFO] task removes all the symfony core files from
the current project:

  [./symfony project:unfreeze|INFO]

The task also changes [config/config.php|COMMENT] to switch to the
old symfony files used before the [project:freeze|COMMENT] command was used.
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {

    $sf_lib_dir_name = sfConfig::get('sf_lib_dir_name');
    $sf_data_dir_name = sfConfig::get('sf_data_dir_name');
    $sf_web_dir_name = sfConfig::get('sf_web_dir_name');
    $sf_config_dir_name = sfConfig::get('sf_config_dir_name');

    // Remove lib/symfony and data/symfony directories
    if (!is_dir($sf_lib_dir_name.DIRECTORY_SEPARATOR.'symfony'))
    {
      throw new sfCommandException('You can unfreeze only if you froze the symfony libraries before.');
    }

    $dirs = explode('#', file_get_contents($sf_config_dir_name.DIRECTORY_SEPARATOR.'config.php.bak'));
    $this->changeSymfonyDirs('\''.$dirs[0].'\'', '\''.$dirs[1].'\'');

    $finder = sfFinder::type('any');
    $this->filesystem->remove($finder->in($sf_lib_dir_name.DIRECTORY_SEPARATOR.'symfony'));
    $this->filesystem->remove($sf_lib_dir_name.DIRECTORY_SEPARATOR.'symfony');
    $this->filesystem->remove($finder->in($sf_data_dir_name.DIRECTORY_SEPARATOR.'symfony'));
    $this->filesystem->remove($sf_data_dir_name.DIRECTORY_SEPARATOR.'symfony');
    $this->filesystem->remove('symfony.php');
    $this->filesystem->remove($finder->in($sf_web_dir_name.DIRECTORY_SEPARATOR.'sf'));
    $this->filesystem->remove($sf_web_dir_name.DIRECTORY_SEPARATOR.'sf');
   }

  protected function changeSymfonyDirs($symfony_lib_dir, $symfony_data_dir)
  {
    $sf_config_dir_name = sfConfig::get('sf_config_dir_name');

    $content = file_get_contents($sf_config_dir_name.DIRECTORY_SEPARATOR.'config.php');
    $content = preg_replace("/^(\s*.sf_symfony_lib_dir\s*=\s*).+?;/m", "$1$symfony_lib_dir;", $content);
    $content = preg_replace("/^(\s*.sf_symfony_data_dir\s*=\s*).+?;/m", "$1$symfony_data_dir;", $content);
    file_put_contents($sf_config_dir_name.DIRECTORY_SEPARATOR.'config.php', $content);
  }
}
