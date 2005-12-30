<?php

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
 * @version    SVN: $Id$
 */
class sfI18N
{
  private
    $context             = null,
    $globalMessageSource = null,
    $messageSource       = null,
    $messageFormat       = null;

  public function initialize($context)
  {
    $this->context = $context;

    include(sfConfigCache::checkConfig(sfConfig::get('sf_app_config_dir_name').'/i18n.yml'));

    $this->globalMessageSource = $this->createMessageSource(sfConfig::get('sf_app_i18n_dir'));

    $this->globalMessageFormat = $this->createMessageFormat($this->globalMessageSource);
  }

  public function setMessageSourceDir($dir, $culture)
  {
    $this->messageSource = $this->createMessageSource($dir);
    $this->messageSource->setCulture($culture);

    $this->messageFormat = $this->createMessageFormat($this->messageSource);
  }

  public function createMessageSource($dir)
  {
    $messageSource = sfMessageSource::factory(sfConfig::get('sf_i18n_source'), $dir);

    if (sfConfig::get('sf_i18n_cache'))
    {
      $subdir = str_replace(sfConfig::get('sf_root_dir'), '', $dir);

      $cache_dir = sfConfig::get('sf_i18n_cache_dir').DIRECTORY_SEPARATOR.$subdir;

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

      $messageSource->setCache(new sfMessageCache($cache_dir));
    }

    return $messageSource;
  }

  public function createMessageFormat($source)
  {
    $messageFormat = new sfMessageFormat($source);

    if (sfConfig::get('sf_debug') && sfConfig::get('sf_i18n_debug'))
    {
      $messageFormat->setUntranslatedPS(array('[T]','[/T]'));
    }

    return $messageFormat;
  }

  public function setCulture($culture)
  {
    $this->messageSource->setCulture($culture);
    $this->globalMessageSource->setCulture($culture);
  }

  public function getMessageSource()
  {
    return $this->messageSource;
  }

  public function getGlobalMessageSource()
  {
    return $this->globalMessageSource;
  }

  public function getMessageFormat()
  {
    return $this->messageFormat;
  }

  public function getGlobalMessageFormat()
  {
    return $this->globalMessageFormat;
  }

  public function __($string, $args = array(), $catalogue = 'messages')
  {
    $retval = $this->messageFormat->formatExists($string, $args, $catalogue);

    if (!$retval)
    {
      $retval = $this->globalMessageFormat->format($string, $args, $catalogue);
    }

    return $retval;
  }
}

?>