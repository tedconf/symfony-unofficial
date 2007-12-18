<?php

/*
 * This file returns part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$sf_dimension = array('culture' => 'fr');

$app = 'dimensions';
if (!include(dirname(__FILE__).'/../bootstrap/functional.php'))
{
  return;
}

require_once(realpath(dirname(__FILE__).'/..').'/../lib/vendor/lime/lime.php');

$path = realpath(dirname(__FILE__).'/..').'/functional/fixtures/project';

$b = new sfTestBrowser();
$b->initialize();

$b->get('/')->
    isStatusCode(200)->
    isRequestParameter('module', 'dimensions')->
    isRequestParameter('action', 'index')->
    checkResponseElement('title', '/[fr]/')->
    checkResponseElement('body', '/France/');

$t = $b->test();

$t->diag('sfLoader::getConfigPaths()');
$t->is(sfLoader::getConfigPaths('config/routing.yml'), array($path.'/apps/'.$app.'/config/routing.yml'), ' returns valid config path for routing.yml');
$t->is(sfLoader::getConfigPaths('config/app.yml'), array($path.'/apps/'.$app.'/config/app.yml'), ' returns valid config path for app.yml');
$t->is(sfLoader::getConfigPaths('config/view.yml'), array($sf_symfony_data_dir.'/config/view.yml',  $path.'/plugins/sfConfigPlugin/config/view.yml', $path.'/config/view.yml', $path.'/apps/'.$app.'/config/view.yml',  $path.'/apps/'.$app.'/config/fr/view.yml'), ' returns valid config dirs for view.yml in culture => fr dimension');

$t->diag('sfLoader::getTemplateDirs()');
$t->is(sfLoader::getTemplateDir('dimensions', 'indexSuccess.php'), $path.'/apps/'.$app.'/modules/dimensions/templates/fr', ' returns valid template directory for dimensions/index in culture => fr dimension');

$t->diag('sfLoader::getControllerDirs()');
$t->is(sfLoader::getControllerDirs('dimensions'), array($path.'/apps/'.$app.'/modules/dimensions/actions/fr' => false,  $path.'/apps/'.$app.'/modules/dimensions/actions' => false,  $sf_symfony_data_dir.'/modules/dimensions/actions' => true), ' returns valid controller dirs for dimensions controller in culture => fr dimension');

$t->diag('::getModelDirs()');
$t->is(sfLoader::getModelDirs(), array($path.'/lib/model'), ' returns valid model directories');
