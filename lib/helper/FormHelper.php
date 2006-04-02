<?php

require_once(sfConfig::get('sf_symfony_lib_dir').'/helper/ValidationHelper.php');

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004 David Heinemeier Hansson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * FormHelper.
 *
 * @package    symfony
 * @subpackage helper
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     David Heinemeier Hansson
 * @version    SVN: $Id$
 */

/*
      # Accepts a container (hash, array, enumerable, your type) and returns a string of option tags. Given a container
      # where the elements respond to first and last (such as a two-element array), the "lasts" serve as option values and
      # the "firsts" as option text. Hashes are turned into this form automatically, so the keys become "firsts" and values
      # become lasts. If +selected+ is specified, the matching "last" or element will get the selected option-tag.  +Selected+
      # may also be an array of values to be selected when using a multiple select.
      #
      # Examples (call, result):
      #   options_for_select([["Dollar", "$"], ["Kroner", "DKK"]])
      #     <option value="$">Dollar</option>\n<option value="DKK">Kroner</option>
      #
      #   options_for_select([ "VISA", "MasterCard" ], "MasterCard")
      #     <option>VISA</option>\n<option selected="selected">MasterCard</option>
      #
      #   options_for_select({ "Basic" => "$20", "Plus" => "$40" }, "$40")
      #     <option value="$20">Basic</option>\n<option value="$40" selected="selected">Plus</option>
      #
      #   options_for_select([ "VISA", "MasterCard", "Discover" ], ["VISA", "Discover"])
      #     <option selected="selected">VISA</option>\n<option>MasterCard</option>\n<option selected="selected">Discover</option>
      #
      # NOTE: Only the option tags are returned, you have to wrap this call in a regular HTML select tag.
*/
function options_for_select($options = array(), $selected = '', $htmlOptions = array())
{
  $htmlOptions = _parse_attributes($htmlOptions);

  if (is_array($selected))
  {
    $valid = array_values($selected);
    $valid = array_map('strval', $valid);
  }

  $html = '';

  if (isset($htmlOptions['include_custom']))
  {
    $html .= content_tag('option', $htmlOptions['include_custom'], array('value' => ''))."\n";
  }
  elseif (isset($htmlOptions['include_blank']))
  {
    $html .= content_tag('option', '', array('value' => ''))."\n";
  }

  foreach ($options as $key => $value)
  {
    $optionOptions = array('value' => $key);
    if (
        isset($selected)
        &&
        (is_array($selected) && in_array(strval($key), $valid, true))
        ||
        (strval($key) == strval($selected))
       )
    {
      $optionOptions['selected'] = 'selected';
    }

    $html .= content_tag('option', $value, $optionOptions)."\n";
  }

  return $html;
}

/*
    # Starts a form tag that points the action to an url configured with <tt>url_for_options</tt> just like
    # ActionController::Base#url_for. The method for the form defaults to POST.
    #
    # Options:
    # * <tt>:multipart</tt> - If set to true, the enctype is set to "multipart/form-data".
*/
function form_tag($urlForOptions = '', $options = array())
{
  $options = _parse_attributes($options);

  $htmlOptions = $options;
  if (!array_key_exists('method', $htmlOptions))
  {
    $htmlOptions['method'] = 'post';
  }

  if (array_key_exists('multipart', $htmlOptions))
  {
    $htmlOptions['enctype'] = 'multipart/form-data';
    unset($htmlOptions['multipart']);
  }

  $htmlOptions['action'] = url_for($urlForOptions);

  return tag('form', $htmlOptions, true);
}

function select_tag($name, $optionTags = null, $options = array())
{
  $options = _convert_options($options);
  if (isset($options['multiple']) && $options['multiple'] && substr($name, -2) !== '[]')
  {
    $name .= '[]';
  }

  return content_tag('select', $optionTags, array_merge(array('name' => $name, 'id' => $name), $options));
}

function select_country_tag($name, $value, $options = array())
{
  $c = new sfCultureInfo(sfContext::getInstance()->getUser()->getCulture());
  $countries = $c->getCountries();

  if (isset($options['countries']) && is_array($options['countries']))
  {
    $diff = array_diff_key($countries, array_flip($options['countries']));
    foreach ($diff as $key => $v)
    {
      unset($countries[$key]);
    }

    unset($options['countries']);
  }

  asort($countries);

  $optionTags = options_for_select($countries, $value);

  return select_tag($name, $optionTags, $options);
}

function select_language_tag($name, $value, $options = array())
{
  $c = new sfCultureInfo(sfContext::getInstance()->getUser()->getCulture());
  $languages = $c->getLanguages();

  if (isset($options['languages']) && is_array($options['languages']))
  {
    $diff = array_diff_key($languages, array_flip($options['languages']));
    foreach ($diff as $key => $v)
    {
      unset($languages[$key]);
    }

    unset($options['languages']);
  }

  asort($languages);

  $optionTags = options_for_select($languages, $value);

  return select_tag($name, $optionTags, $options);
}

function input_tag($name, $value = null, $options = array())
{
  return tag('input', array_merge(array('type' => 'text', 'name' => $name, 'id' => $name, 'value' => $value), _convert_options($options)));
}

function input_hidden_tag($name, $value = null, $options = array())
{
  $options = _parse_attributes($options);

  $options['type'] = 'hidden';
  return input_tag($name, $value, $options);
}

function input_file_tag($name, $options = array())
{
  $options = _parse_attributes($options);

  $options['type'] = 'file';
  return input_tag($name, null, $options);
}

function input_password_tag($name = 'password', $value = null, $options = array())
{
  $options = _parse_attributes($options);

  $options['type'] = 'password';
  return input_tag($name, $value, $options);
}

/**
 * example user css file
 / * user: foo * / => without spaces. 'foo' is the name in the select box
 .cool {
 color: #f00;
 }
 */
function textarea_tag($name, $content = null, $options = array())
{
  $options = _parse_attributes($options);

  if (array_key_exists('size', $options))
  {
    list($options['cols'], $options['rows']) = split('x', $options['size'], 2);
    unset($options['size']);
  }

  // rich control?
  $rich = false;
  if (isset($options['rich']))
  {
    $rich = $options['rich'];
    if ($rich === true)
    {
      $rich = 'tinymce';
    }
    unset($options['rich']);
  }

  // we need to know the id for things the rich text editor
  // in advance of building the tag
  if (isset($options['id']))
  {
    $id = $options['id'];
    unset($options['id']);
  }
  else
  {
    $id = $name;
  }

  if ($rich == 'tinymce')
  {
    // tinymce installed?
    $jsPath = sfConfig::get('sf_rich_text_js_dir') ? '/'.sfConfig::get('sf_rich_text_js_dir').'/tiny_mce.js' : '/sf/js/tinymce/tiny_mce.js';
    if (!is_readable(sfConfig::get('sf_web_dir').$jsPath))
    {
      throw new sfConfigurationException('You must install TinyMCE to use this helper (see rich_text_js_dir settings).');
    }

    sfContext::getInstance()->getResponse()->addJavascript($jsPath);

    require_once(sfConfig::get('sf_symfony_lib_dir').'/helper/JavascriptHelper.php');

    $tinymceOptions = '';
    $styleSelector  = '';

    // custom CSS file?
    if (isset($options['css']))
    {
      $cssFile = $options['css'];
      unset($options['css']);

      $cssPath = stylesheet_path($cssFile);

      sfContext::getInstance()->getResponse()->addStylesheet($cssPath);

      $css    = file_get_contents(sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.$cssPath);
      $styles = array();
      preg_match_all('#^/\*\s*user:\s*(.+?)\s*\*/\s*\015?\012\s*\.([^\s]+)#Smi', $css, $matches, PREG_SET_ORDER);
      foreach ($matches as $match)
      {
        $styles[] = $match[1].'='.$match[2];
      }

      $tinymceOptions .= '  content_css: "'.$cssPath.'",'."\n";
      $tinymceOptions .= '  theme_advanced_styles: "'.implode(';', $styles).'"'."\n";
      $styleSelector   = 'styleselect,separator,';
    }

    $tinymceJs = '
tinyMCE.init({
  mode: "exact",
  language: "en",
  elements: "'.$id.'",
  plugins: "table,advimage,advlink,flash",
  theme: "advanced",
  theme_advanced_toolbar_location: "top",
  theme_advanced_toolbar_align: "left",
  theme_advanced_path_location: "bottom",
  theme_advanced_buttons1: "'.$styleSelector.'justifyleft,justifycenter,justifyright,justifyfull,separator,bold,italic,strikethrough,separator,sub,sup,separator,charmap",
  theme_advanced_buttons2: "bullist,numlist,separator,outdent,indent,separator,undo,redo,separator,link,unlink,image,flash,separator,cleanup,removeformat,separator,code",
  theme_advanced_buttons3: "tablecontrols",
  extended_valid_elements: "img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name]",
  relative_urls: false,
  debug: false
  '.($tinymceOptions ? ','.$tinymceOptions : '').'
  '.(isset($options['tinymce_options']) ? ','.$options['tinymce_options'] : '').'
});';

    return
      content_tag('script', javascript_cdata_section($tinymceJs), array('type' => 'text/javascript')).
      content_tag('textarea', $content, array_merge(array('name' => $name, 'id' => $id), _convert_options($options)));
  }
  elseif ($rich === 'fck')
  {
    $phpFile = sfConfig::get('sf_rich_text_fck_js_dir').DIRECTORY_SEPARATOR.'fckeditor.php';

    if (!is_readable(sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.$phpFile))
    {
      throw new sfConfigurationException('You must install FCKEditor to use this helper (see rich_text_fck_js_dir settings).');
    }

    // FCKEditor.php class is written with backward compatibility of PHP4.
    // This reportings are to turn off errors with public properties and already declared constructor
    $errorReporting = ini_get('error_reporting');
    error_reporting(E_ALL);

    require_once(sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.$phpFile);

    // turn error reporting back to your settings
    error_reporting($errorReporting);

    $fckeditor           = new FCKeditor($name);
    $fckeditor->BasePath = DIRECTORY_SEPARATOR.sfConfig::get('sf_rich_text_fck_js_dir').DIRECTORY_SEPARATOR;
    $fckeditor->Value    = $content;

    if (isset($options['width']))
    {
      $fckeditor->Width = $options['width'];
    }
    elseif (isset($options['cols']))
    {
      $fckeditor->Width = (string)((int) $options['cols'] * 10).'px';
    }

    if (isset($options['height']))
    {
      $fckeditor->Height = $options['height'];
    }
    elseif (isset($options['rows']))
    {
      $fckeditor->Height = (string)((int) $options['rows'] * 10).'px';
    }

    if (isset($options['tool']))
    {
      $fckeditor->ToolbarSet = $options['tool'];
    }

    $content = $fckeditor->CreateHtml();

    return $content;
  }
  else
  {
    return content_tag('textarea', htmlspecialchars((is_object($content)) ? $content->__toString() : $content), array_merge(array('name' => $name, 'id' => $id), _convert_options($options)));
  }
}

function checkbox_tag($name, $value = '1', $checked = false, $options = array())
{
  $htmlOptions = array_merge(array('type' => 'checkbox', 'name' => $name, 'id' => $name, 'value' => $value), _convert_options($options));
  if ($checked)
  {
    $htmlOptions['checked'] = 'checked';
  }

  return tag('input', $htmlOptions);
}

function radiobutton_tag($name, $value, $checked = false, $options = array())
{
  $htmlOptions = array_merge(array('type' => 'radio', 'name' => $name, 'value' => $value), _convert_options($options));
  if ($checked)
  {
    $htmlOptions['checked'] = 'checked';
  }

  return tag('input', $htmlOptions);
}

function input_upload_tag($name, $options = array())
{
  $options = _parse_attributes($options);

  $options['type'] = 'file';

  return input_tag($name, '', $options);
}

function input_date_tag($name, $value, $options = array())
{
  $options = _parse_attributes($options);

  $context = sfContext::getInstance();
  if (isset($options['culture']))
  {
    $culture = $options['culture'];
    unset($options['culture']);
  }
  else
  {
    $culture = $context->getUser()->getCulture();
  }

  // rich control?
  $rich = false;
  if (isset($options['rich']))
  {
    $rich = $options['rich'];
    unset($options['rich']);
  }

  if (!$rich)
  {
    throw new sfException('input_date_tag (rich=off) is not yet implemented');
  }

  // parse date
  if (($value !== null) && ($value != '') && (!is_int($value)))
  {
    $value = strtotime($value);
    if ($value === -1)
    {
      $value = 0;
//      throw new Exception("Unable to parse value of date as date/time value");
    }
    else
    {
      $dateFormat = new sfDateFormat($culture);
      $value = $dateFormat->format($value, 'd');
    }
  }

  // register our javascripts and stylesheets
  $jss = array(
    '/sf/js/calendar/calendar',
//  '/sf/js/calendar/lang/calendar-'.substr($culture, 0, 2),
    '/sf/js/calendar/lang/calendar-en',
    '/sf/js/calendar/calendar-setup',
  );
  foreach ($jss as $js)
  {
    $context->getResponse()->addJavascript($js);
  }
  $context->getResponse()->addStylesheet('/sf/js/calendar/skins/aqua/theme');

  // date format
  $dateFormatInfo = sfDateTimeFormatInfo::getInstance($culture);
  $dateFormat = strtolower($dateFormatInfo->getShortDatePattern());

  // calendar date format
  $calendarDateFormat = $dateFormat;
  $calendarDateFormat = strtr($calendarDateFormat, array('M' => 'm', 'y' => 'Y'));
  $calendarDateFormat = preg_replace('/([mdy])+/i', '%\\1', $calendarDateFormat);

  $js = '
    document.getElementById("trigger_'.$name.'").disabled = false;
    Calendar.setup({
      inputField : "'.$name.'",
      ifFormat : "'.$calendarDateFormat.'",
      button : "trigger_'.$name.'"
    });
  ';

  // construct html
  $html = input_tag($name, $value);

  // calendar button
  $calendarButton = '...';
  $calendarButtonType = 'txt';
  if (isset($options['calendar_button_img']))
  {
    $calendarButton = $options['calendar_button_img'];
    $calendarButtonType = 'img';
    unset($options['calendar_button']);
  }
  elseif (isset($options['calendar_button_txt']))
  {
    $calendarButton = $options['calendar_button_txt'];
    $calendarButtonType = 'txt';
    unset($options['calendar_button']);
  }

  if ($calendarButtonType == 'img')
  {
    $html .= image_tag($calendarButton, array('id' => 'trigger_'.$name, 'style' => 'cursor: pointer', 'align' => 'absmiddle'));
  }
  else
  {
    $html .= content_tag('button', $calendarButton, array('disabled' => 'disabled', 'onclick' => 'return false', 'id' => 'trigger_'.$name));
  }

  if (isset($options['with_format']))
  {
    $html .= '('.$dateFormat.')';
    unset($options['with_format']);
  }

  // add javascript
  $html .= content_tag('script', $js, array('type' => 'text/javascript'));

  return $html;
}

function submit_tag($value = 'Save changes', $options = array())
{
  return tag('input', array_merge(array('type' => 'submit', 'name' => 'commit', 'value' => $value), _convert_options($options)));
}

function reset_tag($value = 'Reset', $options = array())
{
  return tag('input', array_merge(array('type' => 'reset', 'name' => 'reset', 'value' => $value), _convert_options($options)));
}

function submit_image_tag($source, $options = array())
{
  return tag('input', array_merge(array('type' => 'image', 'name' => 'commit', 'src' => image_path($source)), _convert_options($options)));
}

function select_day_tag($name, $value, $options = array(), $htmlOptions = array())
{
  $selectOptions = array();

  if (_get_option($options, 'include_blank'))
  {
    $selectOptions[''] = '';
  }
  elseif ($includeCustom = _get_option($options, 'include_custom'))
  {
    $selectOptions[''] = $includeCustom;
  }

  for ($x = 1; $x <= 31; ++$x)
  {
    $selectOptions[$x] = _add_zeros($x, 2);
  }

  $optionTags = options_for_select($selectOptions, $value);

  return select_tag($name, $optionTags, $htmlOptions);
}

function select_month_tag($name, $value, $options = array(), $htmlOptions = array())
{

  $culture = _get_option($options, 'culture', sfContext::getInstance()->getUser()->getCulture());

  $I18n_arr = _get_I18n_date_locales($culture);

  $selectOptions = array();

  if (_get_option($options, 'include_blank'))
  {
    $selectOptions[''] = '';
  }
  elseif ($includeCustom = _get_option($options, 'include_custom'))
  {
    $selectOptions[''] = $includeCustom;
  }

  if (_get_option($options, 'use_month_numbers'))
  {
    for ($k = 1; $k <= 12; ++$k)
    {
      $selectOptions[$k] = _add_zeros($k, 2);
    }
  }
  else
  {
    if (_get_option($options, 'use_short_month'))
    {
      $monthNames = $I18n_arr['dateFormatInfo']->getAbbreviatedMonthNames();
    }
    else
    {
      $monthNames = $I18n_arr['dateFormatInfo']->getMonthNames();
    }

    $addMonthNumbers = _get_option($options, 'add_month_numbers');
    foreach ($monthNames as $k => $v)
    {
      $selectOptions[$k + 1] = ($addMonthNumbers) ? ($k + 1 . ' - ' . $v) : $v;
    }
  }

  $optionTags = options_for_select($selectOptions, $value);

  return select_tag($name, $optionTags, $htmlOptions);
}

function select_year_tag($name, $value, $options = array(), $htmlOptions = array())
{
  $selectOptions = array();

  if (_get_option($options, 'include_blank'))
  {
    $selectOptions[''] = '';
  }
  elseif ($includeCustom = _get_option($options, 'include_custom'))
  {
    $selectOptions[''] = $includeCustom;
  }

  if (strlen($value) > 0 && is_numeric($value))
  {
    $yearOrigin = $value;
  }
  else
  {
    $yearOrigin = date('Y');
  }

  $yearStart = _get_option($options, 'year_start', $yearOrigin - 5);
  $yearEnd = _get_option($options, 'year_end', $yearOrigin + 5);

  $ascending = ($yearStart < $yearEnd);
  $untilYear = ($ascending) ? $yearEnd + 1 : $yearEnd - 1;

  $inc = ($ascending)? 1: -1;
  for ($x = $yearStart; $x != $untilYear; $x += $inc)
  {
    $selectOptions[$x] = $x;
  }

  $optionTags = options_for_select($selectOptions, $value);

  return select_tag($name, $optionTags, $htmlOptions);
}

/**
 * Enter description here...
 *
 * @param string $name
 * @param string $value (proper date format: array('year'=>2005, 'month'=>1, 'day'=1) or timestamp or english date text)
 * @param array $options
 * @return string
 */
function select_date_tag($name, $value, $options = array(), $htmlOptions = array())
{
  $htmlOptions = _parse_attributes($htmlOptions);
  $options = _parse_attributes($options);

  $culture = _get_option($options, 'culture', sfContext::getInstance()->getUser()->getCulture());
  //set it back for month tag
  $option['culture'] = $culture;

  $I18n_arr = _get_I18n_date_locales($culture);

  $dateSeperator = _get_option($options, 'date_seperator', $I18n_arr['date_seperator']);

  $discardMonth = _get_option($options, 'discard_month');
  $discardDay = _get_option($options, 'discard_day');
  $discardYear = _get_option($options, 'discard_year');

  //discarding month automatically discards day
  if ($discardMonth)
  {
    $discardDay = true;
  }

  $order = _get_option($options, 'order');

  $tags = array();

  if (is_array($order) && count($order) == 3)
  {
    foreach ($order as $k => $v)
    {
      $tags[] = $v[0]; //'day' => 'd' | 'month' => 'm'
    }
  }
  else
  {
    $tags = $I18n_arr['date_order'];
  }

  if ($includeCustom = _get_option($options, 'include_custom'))
  {
    $includeCustomMonth = (is_array($includeCustom))
        ? ((isset($includeCustom['month'])) ? array('include_custom'=>$includeCustom['month']) : array())
        : array('include_custom'=>$includeCustom);

    $includeCustomDay = (is_array($includeCustom))
        ? ((isset($includeCustom['day'])) ? array('include_custom'=>$includeCustom['day']) : array())
        : array('include_custom'=>$includeCustom);

    $includeCustomYear = (is_array($includeCustom))
        ? ((isset($includeCustom['year'])) ? array('include_custom'=>$includeCustom['year']) : array())
        : array('include_custom'=>$includeCustom);
  }
  else
  {
    $includeCustomMonth = array();
    $includeCustomDay = array();
    $includeCustomYear = array();
  }

  $htmlOptions['id'] = $name . '_month';
  $m = ($discardMonth != true) ? select_month_tag($name . '[month]', _parse_value_for_date($value, 'month', 'm'), $options + $includeCustomMonth, $htmlOptions) : '';

  $htmlOptions['id'] = $name . '_day';
  $d = ($discardDay != true) ? select_day_tag($name . '[day]', _parse_value_for_date($value, 'day', 'd'), $options + $includeCustomDay, $htmlOptions) : '';

  $htmlOptions['id'] = $name . '_year';
  $y = ($discardYear != true) ? select_year_tag($name . '[year]', _parse_value_for_date($value, 'year', 'Y'), $options + $includeCustomYear, $htmlOptions) : '';

  //we have $tags = array ('m','d','y')
  foreach ($tags as $k => $v)
  {
    $tags[$k] = $$v;
  }

  return implode($dateSeperator, $tags);
}

function select_second_tag($name, $value, $options = array(), $htmlOptions = array())
{
  $selectOptions = array();

  if (_get_option($options, 'include_blank'))
  {
    $selectOptions[''] = '';
  }
  elseif ($includeCustom = _get_option($options, 'include_custom'))
  {
    $selectOptions[''] = $includeCustom;
  }

  $secondStep = _get_option($options, 'second_step', 1);
  for ($x = 0; $x < 60; $x += $secondStep)
  {
    $selectOptions[$x] = _add_zeros($x, 2);
  }

  $optionTags = options_for_select($selectOptions, $value);

  return select_tag($name, $optionTags, $htmlOptions);
}

function select_minute_tag($name, $value, $options = array(), $htmlOptions = array())
{
  $selectOptions = array();

  if (_get_option($options, 'include_blank'))
  {
    $selectOptions[''] = '';
  }
  elseif ($includeCustom = _get_option($options, 'include_custom'))
  {
    $selectOptions[''] = $includeCustom;
  }

  $minuteStep = _get_option($options, 'minute_step', 1);
  for ($x = 0; $x < 60; $x += $minuteStep)
  {
    $selectOptions[$x] = _add_zeros($x, 2);
  }

  $optionTags = options_for_select($selectOptions, $value);

  return select_tag($name, $optionTags, $htmlOptions);
}

function select_hour_tag($name, $value, $options = array(), $htmlOptions = array())
{
  $selectOptions = array();

  if (_get_option($options, 'include_blank'))
  {
    $selectOptions[''] = '';
  }
  elseif ($includeCustom = _get_option($options, 'include_custom'))
  {
    $selectOptions[''] = $includeCustom;
  }

  $_12hour_time = _get_option($options, '12hour_time');

  $startHour = ($_12hour_time) ? 1 : 0;
  $endHour = ($_12hour_time) ? 12 : 23;

  for ($x = $startHour; $x <= $endHour; ++$x)
  {
    $selectOptions[$x] = _add_zeros($x, 2);
  }

  $optionTags = options_for_select($selectOptions, $value);

  return select_tag($name, $optionTags, $htmlOptions);
}

function select_ampm_tag($name, $value, $options = array(), $htmlOptions = array())
{
  $selectOptions = array();

  if (_get_option($options, 'include_blank'))
  {
    $selectOptions[''] = '';
  }
  elseif ($includeCustom = _get_option($options, 'include_custom'))
  {
    $selectOptions[''] = $includeCustom;
  }

  $selectOptions['AM'] = 'AM';
  $selectOptions['PM'] = 'PM';

  $optionTags = options_for_select($selectOptions, $value);

  return select_tag($name, $optionTags, $htmlOptions);
}

/**
 * Enter description here...
 *
 * @param string $name
 * @param string $value (proper time format: array('hour'=>0, 'minute'=>0, 'second'=0) or timestamp or english date text)
 * @param array $options
 * @return string
 */
function select_time_tag($name, $value, $options = array(), $htmlOptions = array())
{
  $htmlOptions = _parse_attributes($htmlOptions);
  $options = _parse_attributes($options);

  $timeSeperator = _get_option($options, 'time_seperator', ':');
  $ampmSeperator = _get_option($options, 'ampm_seperator', '');
  $includeSecond = _get_option($options, 'include_second');
  $_12hour_time = _get_option($options, '12hour_time');

  $options['12hour_time'] = $_12hour_time; //set it back. hour tag needs it.

  if ($includeCustom = _get_option($options, 'include_custom'))
  {
    $includeCustomHour = (is_array($includeCustom))
        ? ((isset($includeCustom['hour'])) ? array('include_custom'=>$includeCustom['hour']) : array())
        : array('include_custom'=>$includeCustom);

    $includeCustomMinute = (is_array($includeCustom))
        ? ((isset($includeCustom['minute'])) ? array('include_custom'=>$includeCustom['minute']) : array())
        : array('include_custom'=>$includeCustom);

    $includeCustomSecond = (is_array($includeCustom))
        ? ((isset($includeCustom['second'])) ? array('include_custom'=>$includeCustom['second']) : array())
        : array('include_custom'=>$includeCustom);

    $includeCustomAmpm = (is_array($includeCustom))
        ? ((isset($includeCustom['ampm'])) ? array('include_custom'=>$includeCustom['ampm']) : array())
        : array('include_custom'=>$includeCustom);
  }
  else
  {
    $includeCustomHour = array();
    $includeCustomMinute = array();
    $includeCustomSecond = array();
    $includeCustomAmpm = array();
  }

  $tags = array();

  $htmlOptions['id'] = $name . '_hour';
  $tags[] = select_hour_tag($name . '[hour]', _parse_value_for_date($value, 'hour', ($_12hour_time) ? 'h' : 'H'), $options + $includeCustomHour, $htmlOptions);

  $htmlOptions['id'] = $name . '_minute';
  $tags[] = select_minute_tag($name . '[minute]', _parse_value_for_date($value, 'minute', 'i'), $options + $includeCustomMinute, $htmlOptions);

  if ($includeSecond)
  {
    $htmlOptions['id'] = $name . '_second';
    $tags[] = select_second_tag($name . "[second]" , _parse_value_for_date($value, 'second', 's'), $options + $includeCustomSecond, $htmlOptions);
  }

  $time = implode($timeSeperator, $tags);

  if ($_12hour_time)
  {
    $htmlOptions['id'] = $name . '_ampm';
    $time .=  $ampmSeperator . select_ampm_tag($name . "[ampm]" , _parse_value_for_date($value, 'ampm', 'A'), $options + $includeCustomAmpm, $htmlOptions);
  }

  return $time;
}

/**
 * Enter description here...
 *
 * @param string $name
 * @param string $value (proper datetime format YYYY-MM-DD HH:MM:SS)
 * @param array $options
 * @return string
 */
function select_datetime_tag($name, $value, $options = array(), $htmlOptions = array())
{
  $options = _parse_attributes($options);
  $datetimeSeperator = _get_option($options, 'datetime_seperator', '');

  $date = select_date_tag($name, $value, $options, $htmlOptions);
  $time = select_time_tag($name, $value, $options, $htmlOptions);

  return $date.$datetimeSeperator.$time;
}

function _add_zeros($string, $strlen)
{
  $len= $strlen - strlen($string);
  if ($len > 0)
  {
    $string = str_repeat('0', $len) . $string;
  }

  return $string;
}

function _get_I18n_date_locales($culture = '')
{
  if (empty($culture))
  {
    $culture = sfContext::getInstance()->getUser()->getCulture();
  }

  $retVal = array();
  $retVal['culture'] = $culture;

  $dateFormatInfo = sfDateTimeFormatInfo::getInstance($culture);
  $dateFormat = strtolower($dateFormatInfo->getShortDatePattern());

  $retVal['dateFormatInfo'] = $dateFormatInfo;

  $matchPattern = "/([dmy]+)(.*?)([dmy]+)(.*?)([dmy]+)/";
  if (!preg_match($matchPattern, $dateFormat, $matchArr))
  {
    //if matching fails use en shortdate
    preg_match($matchPattern, 'm/d/yy', $matchArr);
  }

  $retVal['date_seperator'] = $matchArr[2];

  //unset all but [dmy]+
  unset($matchArr[0], $matchArr[2], $matchArr[4]);

  $cnt = 0;
  foreach ($matchArr as $k => $v)
  {
    $retVal['date_order'][$cnt++] = $v[0]; //$arr[date_order][0] = 'm'; [1] = 'd'; [2] = 'y';
  }

  return $retVal;
}

/**
 * _parse_value_for_date function can parse any date field from $value given as:
 *  - an array('year'=>2000, 'month'=> 1, ..
 *  - a timestamp
 *  - english text presentation of date (i.e '14:23', '03:30 AM', '2005-12-25' Refer to strtotime function in PHP manual)
 */
function _parse_value_for_date($value, $name, $formatChar)
{
  if (is_array($value))
  {
    return (isset($value[$name])) ? $value[$name] : '';
  }
  elseif (is_numeric($value))
  {
    return date($formatChar, $value);
  }
  elseif ($value == '' || ($name == 'ampm' && ($value == 'AM' || $value == 'PM')))
  {
    return $value;
  }

  return date($formatChar, strtotime($value));
}

function _convert_options($options)
{
  $options = _parse_attributes($options);

  foreach (array('disabled', 'readonly', 'multiple') as $attribute)
  {
    $options = _boolean_attribute($options, $attribute);
  }

  return $options;
}

function _boolean_attribute($options, $attribute)
{
  if (array_key_exists($attribute, $options))
  {
    if ($options[$attribute])
    {
      $options[$attribute] = $attribute;
    }
    else
    {
      unset($options[$attribute]);
    }
  }

  return $options;
}

?>