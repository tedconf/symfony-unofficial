<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/sfGeneratorBaseTask.class.php');

/**
 * Generates a new module.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfGenerateModuleTask extends sfGeneratorBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name'),
      new sfCommandArgument('module', sfCommandArgument::REQUIRED, 'The module name'),
    ));

    $this->aliases = array('init-module');
    $this->namespace = 'generate';
    $this->name = 'module';

    $this->briefDescription = 'Generates a new module';

    $this->detailedDescription = <<<EOF
The [generate:module|INFO] task creates the basic directory structure
for a new module in an existing application:

  [./symfony generate:module frontend article|INFO]

The task can also change the author name found in the [actions.class.php|COMMENT]
if you have configure it in [config/properties.ini|COMMENT]:

  [symfony]
    name=blog
    author=Fabien Potencier <fabien.potencier@sensio.com>

You can customize the default skeleton used by the task by creating a
[%sf_data_dir%/skeleton/module|COMMENT] directory.

The task also creates a functional test stub named
[%sf_test_dir%/functional/%application%/%module%ActionsTest.class.php|COMMENT]
that does not pass by default.

If a module with the same name already exists in the application,
it throws a [sfCommandException|COMMENT].
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $app       = $arguments['application'];
    $module    = $arguments['module'];

    $moduleDir = sfConfig::get('sf_root_dir').DIRECTORY_SEPARATOR.sfConfig::get('sf_apps_dir_name').DIRECTORY_SEPARATOR.$app.DIRECTORY_SEPARATOR.sfConfig::get('sf_app_module_dir_name').DIRECTORY_SEPARATOR.$module;

    if (is_dir($moduleDir))
    {
      throw new sfCommandException(sprintf('The module "%s" already exists in the "%s" application.', $moduleDir, $app));
    }

    $properties = parse_ini_file(sfConfig::get('sf_config_dir').DIRECTORY_SEPARATOR.'properties.ini', true);

    $constants = array(
      'PROJECT_NAME' => isset($properties['symfony']['name']) ? $properties['symfony']['name'] : 'symfony',
      'APP_NAME'     => $app,
      'MODULE_NAME'  => $module,
      'AUTHOR_NAME'  => isset($properties['symfony']['author']) ? $properties['symfony']['author'] : 'Your name here',
    );

    if (is_readable(sfConfig::get('sf_data_dir').DIRECTORY_SEPARATOR.'skeleton'.DIRECTORY_SEPARATOR.'module'))
    {
      $skeletonDir = sfConfig::get('sf_data_dir').DIRECTORY_SEPARATOR.'skeleton'.DIRECTORY_SEPARATOR.'module';
    }
    else
    {
      $skeletonDir = dirname(__FILE__).DIRECTORY_SEPARATOR.'skeleton'.DIRECTORY_SEPARATOR.'module';
    }

    // create basic application structure
    $finder = sfFinder::type('any')->ignore_version_control()->discard('.sf');
    $this->filesystem->mirror($skeletonDir.DIRECTORY_SEPARATOR.'module', $moduleDir, $finder);

    // create basic test
    $this->filesystem->copy($skeletonDir.DIRECTORY_SEPARATOR.'test'.DIRECTORY_SEPARATOR.'actionsTest.php', sfConfig::get('sf_test_functional_dir').DIRECTORY_SEPARATOR.$app.DIRECTORY_SEPARATOR.$module.'ActionsTest.php');

    // customize test file
    $this->filesystem->replaceTokens(sfConfig::get('sf_test_functional_dir').DIRECTORY_SEPARATOR.$app.DIRECTORY_SEPARATOR.$module.'ActionsTest.php', '##', '##', $constants);

    // customize php and yml files
    $finder = sfFinder::type('file')->name('*.php', '*.yml');
    $this->filesystem->replaceTokens($finder->in($moduleDir), '##', '##', $constants);
  }
}
