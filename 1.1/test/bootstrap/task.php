<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/unit.php');

require_once(sfConfig::get('sf_symfony_lib_dir').'/command/sfCommandApplication.class.php');
require_once(sfConfig::get('sf_symfony_lib_dir').'/command/sfSymfonyCommandApplication.class.php');

$tmpDir = sfToolkit::getTmpDir().DIRECTORY_SEPARATOR.'sf_'.rand(11111, 99999);
mkdir($tmpDir, 0777, true);
chdir($tmpDir);

$application = new sfSymfonyCommandApplication(new sfEventDispatcher(), new sfFormatter(), array(
  'symfony_lib_dir' => sfConfig::get('sf_symfony_lib_dir'),
));

register_shutdown_function('sf_shutdown_task_test');

function sf_shutdown_task_test()
{
  global $tmpDir;

  sfToolkit::clearDirectory($tmpDir);
  rmdir($tmpDir);

  $sf_root_dir = sfToolkit::getTmpDir().'/sf_test_project';
  if(is_dir($sf_root_dir))
  {
    sfToolkit::clearDirectory($sf_root_dir);
    rmdir($sf_root_dir);
  }

  foreach (array_merge(glob(sfToolkit::getTmpDir().'/sessions*'), glob(sfToolkit::getTmpDir().'/sf*')) as $file)
  {
    if(is_dir($sf_root_dir))
    {
      sfToolkit::clearDirectory($sf_root_dir);
      rmdir($sf_root_dir);
    }
    else
    {
      unlink($file);
    }
  }
}
