<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
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

function __($text, $args = array(), $culture = null, $catalogue = 'messages')
{
  if (sfConfig::get('sf_i18n'))
  {
    return sfConfig::get('sf_i18n_instance')->__($text, $args, $catalogue);
  }
  else
  {
    // replace object with strings
    foreach ($args as $key => $value)
    {
      if (is_object($value) && method_exists($value, '__toString'))
      {
        $args[$key] = $value->__toString();
      }
    }

    return strtr($text, $args);
  }
}

function format_number_choice($text, $args = array(), $number, $culture = null, $catalogue = 'messages')
{
  $translated = __($text, $args, $culture, $catalogue);

  $choice = new sfChoiceFormat();

  $retval = $choice->format($translated, $number);

  if ($retval === false)
  {
    $error = sprintf('Unable to parse your choice "%s"', $translated);
    throw new sfException($error);
  }

  return $retval;
}

function format_country($country_iso, $culture = null)
{
  $c = new sfCultureInfo($culture === null ? sfContext::getInstance()->getUser()->getCulture() : $culture);
  $countries = $c->getCountries();

  return isset($countries[$country_iso]) ? $countries[$country_iso] : '';
}

function format_language($language_iso, $culture = null)
{
  $c = new sfCultureInfo($culture === null ? sfContext::getInstance()->getUser()->getCulture() : $culture);
  $languages = $c->getLanguages();

  return isset($languages[$language_iso]) ? $languages[$language_iso] : '';
}
