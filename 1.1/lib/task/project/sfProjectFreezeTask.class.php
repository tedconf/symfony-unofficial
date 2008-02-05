<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Freezes symfony libraries.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfProjectFreezeTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('symfony_data_dir', sfCommandArgument::REQUIRED, 'The symfony data directory'),
    ));

    $this->aliases = array('freeze');
    $this->namespace = 'project';
    $this->name = 'freeze';
    $this->briefDescription = 'Freezes symfony libraries';

    $this->detailedDescription = <<<EOF
The [project:freeze|INFO] task copies all the symfony core files to
the current project:

  [./symfony project:freeze|INFO]

The task also changes [config/config.php|COMMENT] to switch to the
embedded symfony files.
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

    // Check that the symfony librairies are not already freeze for this project
    if (is_readable($sf_lib_dir.DIRECTORY_SEPARATOR.'symfony'))
    {
      throw new sfCommandException(sprintf('You can only freeze when %s/symfony is empty.', $sf_lib_dir));
    }

    if (is_readable($sf_data_dir.DIRECTORY_SEPARATOR.'symfony'))
    {
      throw new sfCommandException(sprintf('You can only freeze when %s/symfony is empty.', $sf_data_dir));
    }

    if (is_readable($sf_web_dir.DIRECTORY_SEPARATOR.'sf'))
    {
      throw new sfCommandException(sprintf('You can only freeze when %s/sf is empty.', $sf_web_dir));
    }

    if (is_link($sf_web_dir.DIRECTORY_SEPARATOR.'sf'))
    {
      $this->filesystem->remove($sf_web_dir.DIRECTORY_SEPARATOR.'sf');
    }

    $symfony_lib_dir  = sfConfig::get('sf_symfony_lib_dir');
    $symfony_data_dir = $arguments['symfony_data_dir'];

    $this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->formatSection('freeze', 'freezing lib found in "'.$symfony_lib_dir.'"'))));
    $this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->formatSection('freeze', 'freezing data found in "'.$symfony_data_dir.'"'))));

    $this->filesystem->mkdirs($sf_lib_dir.DIRECTORY_SEPARATOR.'symfony');
    $this->filesystem->mkdirs($sf_data_dir.DIRECTORY_SEPARATOR.'symfony');

    $finder = sfFinder::type('any')->ignore_version_control();
    $this->filesystem->mirror($symfony_lib_dir, $sf_lib_dir.DIRECTORY_SEPARATOR.'symfony', $finder);
    $this->filesystem->mirror($symfony_data_dir, $sf_data_dir.DIRECTORY_SEPARATOR.'symfony', $finder);

    $this->filesystem->rename($sf_data_dir.DIRECTORY_SEPARATOR.'symfony'.DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.'sf', $sf_web_dir.DIRECTORY_SEPARATOR.'sf');

    // change symfony paths in config/config.php
    file_put_contents('config/config.php.bak', $symfony_lib_dir);
    $this->changeSymfonyDirs("dirname(__FILE__).'/../lib/symfony'");
  }

  protected function changeSymfonyDirs($symfony_lib_dir)
  {
    $sf_config_dir = sfConfig::get('sf_config_dir');

    $content = file_get_contents($sf_config_dir.DIRECTORY_SEPARATOR.'config.php');
    $content = preg_replace("/^(\s*.sf_symfony_lib_dir\s*=\s*).+?;/m", "$1$symfony_lib_dir;", $content);
    file_put_contents($sf_config_dir.DIRECTORY_SEPARATOR.'config.php', $content);
  }
}
