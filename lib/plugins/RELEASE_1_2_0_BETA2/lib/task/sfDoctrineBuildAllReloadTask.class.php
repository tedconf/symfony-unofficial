<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/sfDoctrineBaseTask.class.php');

/**
 * Drops Databases, Creates Databases, Generates Doctrine model, SQL, initializes database, and load data.
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id$
 */
class sfDoctrineBuildAllReloadTask extends sfDoctrineBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', null),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      new sfCommandOption('no-confirmation', null, sfCommandOption::PARAMETER_NONE, 'Do not ask for confirmation'),
      new sfCommandOption('skip-forms', 'F', sfCommandOption::PARAMETER_NONE, 'Skip generating forms')
    ));

    $this->aliases = array('doctrine-build-all-reload');
    $this->namespace = 'doctrine';
    $this->name = 'build-all-reload';
    $this->briefDescription = 'Generates Doctrine model, SQL, initializes database, and load data';

    $this->detailedDescription = <<<EOF
The [doctrine:build-all-reload|INFO] task is a shortcut for four other tasks:

  [./symfony doctrine:build-all-reload frontend|INFO]

The task is equivalent to:
  
  [./symfony doctrine:drop-db|INFO]
  [./symfony doctrine:build-db|INFO]
  [./symfony doctrine:build-model|INFO]
  [./symfony doctrine:insert-sql|INFO]
  [./symfony doctrine:data-load frontend|INFO]

The task takes an application argument because of the [doctrine:data-load|COMMENT]
task. See [doctrine:data-load|COMMENT] help page for more information.
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $dropDb = new sfDoctrineDropDbTask($this->dispatcher, $this->formatter);
    $dropDb->setCommandApplication($this->commandApplication);

    $dropDbOptions = array();
    $dropDbOptions[] = '--env='.$options['env'];
    if (isset($options['no-confirmation']) && $options['no-confirmation'])
    {
      $dropDbOptions[] = '--no-confirmation';
    }
    if (isset($options['application']) && $options['application'])
    {
      $dropDbOptions[] = '--application=' . $options['application'];
    }
    $dropDb->run(array(), $dropDbOptions);
    
    $buildAllLoad = new sfDoctrineBuildAllLoadTask($this->dispatcher, $this->formatter);
    $buildAllLoad->setCommandApplication($this->commandApplication);

    $loadDataOptions = array();
    $loadDataOptions[] = '--env='.$options['env'];
    if (!empty($options['dir']))
    {
      $loadDataOptions[] = '--dir=' . implode(' --dir=', $options['dir']);
    }
    if (isset($options['append']) && $options['append'])
    {
      $loadDataOptions[] = '--append';
    }
    if (isset($options['application']) && $options['application'])
    {
      $loadDataOptions[] = '--application=' . $options['application'];
    }

    $buildAllLoad->run(array(), $loadDataOptions);
  }
}