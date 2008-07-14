<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$sf_ez_lib_dir = sfConfig::get('sf_ez_lib_dir') ? sfConfig::get('sf_ez_lib_dir').'/' : '';

if(file_exists($sf_ez_lib_dir.'Base/src/base.php'))
{
  require_once($sf_ez_lib_dir.'Base/src/base.php');
}
else if(file_exists($sf_ez_lib_dir.'Base/base.php'))
{
  require_once($sf_ez_lib_dir.'Base/base.php');
}
else
{
  throw new sfAutoloadException('Invalid eZ component library path.');
}

/**
 * This class makes easy to use ez components classes within symfony
 *
 * WARNING: This class is deprecated and will be removed in symfony 1.2.
 *
 * @package    symfony
 * @subpackage addon
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 * @deprecated Deprecated since symfony 1.1
 */
class sfEzComponentsBridge
{
  public static function autoload($class)
  {
    return ezcBase::autoload($class);
  }
}
