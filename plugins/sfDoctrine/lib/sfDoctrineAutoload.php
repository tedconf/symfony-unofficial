<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    symfony.runtime.addon
 * @author     Maarten den Braber <mdb@twister.cx>
 * @version    SVN: $Id$
 */

// locate doctrine root directory
if (isset($sfConfig['doctrine_path']))
{
  include_once($sfConfig['doctrine_path']);
}
else
{
  // directories to look for doctrine librairies
  $dirs = array(
    sfConfig::get('sf_lib_dir').'/doctrine',
    sfConfig::get('sf_symfony_lib_dir').'/vendor/doctrine',
  );

  $loaded = false;
  foreach ($dirs as $dir)
  {
    if (is_readable($dir.'/Doctrine.class.php'))
    {
      require_once($dir.'/Doctrine.class.php');
      $loaded = true;
      
      break;
    }
  }

  if (!$loaded)
  {
    $error = 'Unable to find doctrine librairies. Please set "doctrine_path" in your databases.yml.';

    throw new sfConfigurationException($error);
  }
}

?>