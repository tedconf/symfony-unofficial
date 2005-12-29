<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * I18NHelper.
 *
 * @package    symfony
 * @subpackage helper
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */

if (!sfConfig::get('sf_message_format'))
{
  sfConfig::set('sf_message_format', new sfMessageFormat(sfContext::getInstance()->getUser()->getCulture()));
}

function __($text, $args = array(), $culture = null)
{
  return sfConfig::get('sf_message_format')->_($text, $args);
}

function format_country($country_iso)
{
  require_once('i18n/CultureInfo.php');

  $c = new CultureInfo(sfContext::getInstance()->getUser()->getCulture());
  $countries = $c->getCountries();

  return $countries[$country_iso];
}

function format_language($language_iso)
{
  require_once('i18n/CultureInfo.php');

  $c = new CultureInfo(sfContext::getInstance()->getUser()->getCulture());
  $languages = $c->getLanguages();

  return $languages[$language_iso];
}

?>