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
      $this->getFilesystem()->remove($sf_web_dir.DIRECTORY_SEPARATOR.'sf');
    }

    $symfonyLibDir  = sfConfig::get('sf_symfony_lib_dir');
    $symfonyDataDir = $arguments['symfony_data_dir'];

    if (!is_readable($symfonyDataDir))
    {
      throw new sfCommandException(sprintf('The symfony data dir does not seem to be located at "%s".', $symfonyDataDir));
    }

    $this->logSection('freeze', sprintf('freezing lib found in "%s', $symfonyLibDir));
    $this->logSection('freeze', sprintf('freezing data found in "%s"', $symfonyDataDir));

    $this->getFilesystem()->mkdirs($sf_lib_dir.DIRECTORY_SEPARATOR.'symfony');
    $this->getFilesystem()->mkdirs($sf_data_dir.DIRECTORY_SEPARATOR.'symfony');

    $finder = sfFinder::type('any')->exec(array($this, 'excludeTests'));
    $this->getFilesystem()->mirror($symfonyLibDir, sfConfig::get('sf_lib_dir').'/symfony', $finder);
    $this->getFilesystem()->mirror($symfonyDataDir, sfConfig::get('sf_data_dir').'/symfony', $finder);

    $this->getFilesystem()->rename($sf_data_dir.DIRECTORY_SEPARATOR.'symfony'.DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.'sf', $sf_web_dir.DIRECTORY_SEPARATOR.'sf');

    // change symfony path in ProjectConfiguration.class.php
    $config = sfConfig::get('sf_config_dir').'/ProjectConfiguration.class.php';
    $content = file_get_contents($config);
    $content = str_replace('<?php', "<?php\n\n# FROZEN_SF_LIB_DIR: $symfonyLibDir", $content);
    $content = preg_replace('#(\'|")'.preg_quote($symfonyLibDir, '#').'#', "dirname(__FILE__).$1/../lib/symfony", $content);
    file_put_contents($config, $content);
  }

  public function excludeTests($dir, $entry)
  {
    return false === strpos($dir.'/'.$entry, 'Plugin/test/');
  }
}
