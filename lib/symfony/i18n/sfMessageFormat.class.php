<?php

require_once "i18n/MessageFormat.php";

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    symfony
 * @subpackage i18n
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfMessageFormat.class.php 432 2005-09-07 12:30:24Z fabien $
 */
class sfMessageFormat extends MessageFormat
{
  public function __construct($culture = null, $dir = 'global')
  {
    $i18n_dir = sfConfig::get('sf_app_i18n_dir');
    $source = MessageSource::factory('XLIFF', $i18n_dir.DIRECTORY_SEPARATOR.$dir);
    $cache_dir = $i18n_dir.DIRECTORY_SEPARATOR.$dir;

    // create cache dir if needed
    if (!is_dir($cache_dir))
    {
      $dirs = explode(DIRECTORY_SEPARATOR, $cache_dir);
      $root = '';
      $current_umask = umask();
      umask(0000);
      foreach($dirs as $dir)
      {
        if ($root == '')
        {
          $root = $dir.DIRECTORY_SEPARATOR;
        }
        else
        {
          $root = $root.DIRECTORY_SEPARATOR.$dir;
        }
        if (!is_dir($root))
        {
          @mkdir($root, 0777);
        }
      }
      umask($current_umask);
    }

    $source->setCache(new MessageCache($cache_dir));

    if ($culture !== null)
    {
      $source->setCulture($culture);
    }

    parent::__construct($source);

//    if (sfConfig::get('sf_debug'))
//      $this->setUntranslatedPS(array('[T]','[/T]'));
  }

  public function _($string, $args = array(), $catalogue = 'messages')
  {
    return $this->format($string, $args, $catalogue);
  }
}

?>