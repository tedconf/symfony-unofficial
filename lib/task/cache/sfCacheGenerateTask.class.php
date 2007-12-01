<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Generates the symfony cache by simulating a web browser and requesting a list of URIs.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Dustin Whittle <dustin.whittle@symfony-project.com>
 * @version    SVN: $Id: sfCacheGenerateTask.class.php 4855 2007-08-10 07:36:48Z dwhittle $
 */
class sfCacheGenerateTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name'),
      new sfCommandArgument('environment', sfCommandArgument::OPTIONAL, 'The environment name', 'prod'),
    ));

    $this->aliases = array('cache-generate', 'prefetch');
    $this->namespace = 'cache';
    $this->name = 'generate';
    $this->briefDescription = 'Generates the symfony cache for an application and environment';

    $this->detailedDescription = <<<EOF
The [cache:generate|INFO] task generates the symfony cache by simulating a web browser and requesting a list of URIs.

It can also be called with an application name and environment.

So, to generate the frontend application configuration for production environment:

  [./symfony cache:generate frontend prod|INFO]

EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
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

    $uris = sfConfig::get('sf_prefetch_uris', array('/', '/not-found'));

    $this->checkAppExists($application);

    // simulate a request to populate configuration cache files for the current application and environment
    // only works for one application / environment per execution
    define('SF_ROOT_DIR',    realpath('./'));
    define('SF_APP',         $application);
    define('SF_ENVIRONMENT', $environment);
    define('SF_DEBUG',       true);

    require_once(SF_ROOT_DIR.DIRECTORY_SEPARATOR.sfConfig::get('sf_apps_dir_name', 'apps').DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.sfConfig::get('sf_config_dir_name', 'config').DIRECTORY_SEPARATOR.'config.php');

    $browser = new sfBrowser();
    $browser->initialize(array());
    foreach($uris as $uri)
    {
      $browser->get($uri);
    }

    $this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->format(sprintf('cache generated for application "%s" in environment "%s"', $application, $environment), 'COMMENT'))));
  }
}
