<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Creates the symfony cache.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfCacheCreateTask.class.php 4855 2007-08-10 07:36:48Z dwhittle $
 */
class sfCacheCreateTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->checkProjectExists();

    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name'),
      new sfCommandArgument('environment', sfCommandArgument::OPTIONAL, 'The environment name', 'prod'),
    ));

    $this->aliases = array('create-cache');
    $this->namespace = 'cache';
    $this->name = 'create';
    $this->briefDescription = 'Creates the symfony cache for an application and environment';

    $this->detailedDescription = <<<EOF
The [cache:create|INFO] task creates the symfony cache.

If it's called with an application name and environment.

So, to create the frontend application configuration for production environment:

  [./symfony cache:create frontend prod|INFO]

EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    if(!isset($this->commandApplication))
    {
      throw new sfCommandException('You can only launch this task from the command line.');
    }

    if (count($arguments) < 2)
    {
      throw new sfCommandException('You must provide an application and an environment.');
    }

    if(!in_array($arguments['environment'], array('prod', 'dev', 'test')))
    {
      throw new sfCommandException('You must provide a valid environment (prod, dev, test).');
    }

    $application = $arguments['application'];
    $environment = $arguments['environment'];
    $uris        = array('/');

    $this->checkAppExists($application);

    // simulate a request to populate config cache files
    // and get the current configuration
    define('SF_ROOT_DIR',    realpath('./'));
    define('SF_APP',         $application);
    define('SF_ENVIRONMENT', $environment);
    define('SF_DEBUG',       true);

    require_once(SF_ROOT_DIR.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php');

    $browser = new sfBrowser();
    $browser->initialize(array());
    foreach($uris as $uri)
    {
      $browser->get($uri);
    }

    $this->log($this->formatSection('cache', sprintf('cache created for application "%s" in environment "%s"', $application, $environment)));
  }
}
