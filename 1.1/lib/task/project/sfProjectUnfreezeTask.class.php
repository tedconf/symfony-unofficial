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
 * @subpackage task
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
    $sf_lib_dir = sfConfig::get('sf_lib_dir');
    $sf_data_dir = sfConfig::get('sf_data_dir');
    $sf_web_dir = sfConfig::get('sf_web_dir');
    $sf_config_dir = sfConfig::get('sf_config_dir');

    // remove lib/symfony and data/symfony directories
    if (!is_dir($sf_lib_dir.DIRECTORY_SEPARATOR.'symfony'))
    {
      throw new sfCommandException('You can unfreeze only if you froze the symfony libraries before.');
    }

    $this->changeSymfonyDirs("'".file_get_contents('config/config.php.bak')."'");

    $finder = sfFinder::type('any');
    $this->getFilesystem()->remove($finder->in($sf_lib_dir.DIRECTORY_SEPARATOR.'symfony'));
    $this->getFilesystem()->remove($sf_lib_dir.DIRECTORY_SEPARATOR.'symfony');
    $this->getFilesystem()->remove($finder->in($sf_data_dir.DIRECTORY_SEPARATOR.'symfony'));
    $this->getFilesystem()->remove($sf_data_dir.DIRECTORY_SEPARATOR.'symfony');
    $this->getFilesystem()->remove($finder->in($sf_web_dir.DIRECTORY_SEPARATOR.'sf'));
    $this->getFilesystem()->remove($sf_web_dir.DIRECTORY_SEPARATOR.'sf');
   }

  protected function changeSymfonyDirs($symfony_lib_dir)
  {
    $sf_config_dir = sfConfig::get('sf_config_dir');

    $content = file_get_contents($sf_config_dir.DIRECTORY_SEPARATOR.'config.php');
    $content = preg_replace("/^(\s*.sf_symfony_lib_dir\s*=\s*).+?;/m", "$1$symfony_lib_dir;", $content);
    file_put_contents($sf_config_dir.DIRECTORY_SEPARATOR.'config.php', $content);
  }
}
