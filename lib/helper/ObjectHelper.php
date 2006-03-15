<?php

require_once(sfConfig::get('sf_symfony_lib_dir').'/helper/FormHelper.php');

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * ObjectHelper.
 *
 * @package    symfony
 * @subpackage helper
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */

/**
 * Returns a html date control.
 *
 * @param object An object.
 * @param string An object column.
 * @param array Date options.
 * @param bool Date default value.
 *
 * @return string An html string which represents a date control.
 *
 */
function object_input_date_tag($object, $method, $options = array(), $defaultValue = null)
{
  $options = _parse_attributes($options);

  $value = _get_object_value($object, $method, $defaultValue);

  return input_date_tag(_convert_method_to_name($method, $options), $value, $options);
}

/**
 * Returns a textarea html tag.
 *
 * @param object An object.
 * @param string An object column.
 * @param array Textarea options.
 * @param bool Textarea default value.
 *
 * @return string An html string which represents a textarea tag.
 *
 */
function object_textarea_tag($object, $method, $options = array(), $defaultValue = null)
{
  $options = _parse_attributes($options);

  $value = _get_object_value($object, $method, $defaultValue);

  return textarea_tag(_convert_method_to_name($method, $options), $value, $options);
}

/**
 * Accepts a container of objects, the method name to use for the value,
 * and the method name to use for the display.
 * It returns a string of option tags.
 *
 * NOTE: Only the option tags are returned, you have to wrap this call in a regular HTML select tag.
 */
function objects_for_select($options = array(), $valueMethod, $textMethod = null, $selected = null, $htmlOptions = array())
{
  $selectOptions = array();
  foreach($options as $option)
  {
    // text method exists?
    if ($textMethod && !method_exists($option, $textMethod))
    {
      $error = sprintf('Method "%s" doesn\'t exist for object of class "%s"', $textMethod, get_class($option));
      throw new sfViewException($error);
    }

    // value method exists?
    if (!method_exists($option, $valueMethod))
    {
      $error = sprintf('Method "%s" doesn\'t exist for object of class "%s"', $valueMethod, get_class($option));
      throw new sfViewException($error);
    }

    $value = $option->$valueMethod();
    $key = ($textMethod != null) ? $option->$textMethod() : $value;

    $selectOptions[$value] = $key;
  }

  return options_for_select($selectOptions, $selected, $htmlOptions);
}

/**
 * Returns a list html tag.
 *
 * @param object An object.
 * @param string An object column.
 * @param array Input options (related_class option is mandatory).
 * @param bool Input default value.
 *
 * @return string A list string which represents an input tag.
 *
 */
function object_select_tag($object, $method, $options = array(), $defaultValue = null)
{
  $options = _parse_attributes($options);
  $relatedClass = isset($options['related_class']) ? $options['related_class'] : '';
  if (!isset($options['related_class']) && preg_match('/^get(.+?)Id$/', $method, $match))
  {
    $relatedClass = $match[1];
  }
  unset($options['related_class']);

  $selectOptions = _get_values_for_object_select_tag($object, $relatedClass);

  if (isset($options['include_custom']))
  {
    array_unshift($selectOptions, $options['include_custom']);
    unset($options['include_custom']);
  }
  elseif (isset($options['include_title']))
  {
    array_unshift($selectOptions, '-- '._convert_method_to_name($method, $options).' --');
    unset($options['include_title']);
  }
  elseif (isset($options['include_blank']))
  {
    array_unshift($selectOptions, '');
    unset($options['include_blank']);
  }

  $value = _get_object_value($object, $method, $defaultValue);
  $optionTags = options_for_select($selectOptions, $value, $options);

  return select_tag(_convert_method_to_name($method, $options), $optionTags, $options);
}

function _get_values_for_object_select_tag($object, $class)
{
  // FIXME: drop Propel dependency

  $selectOptions = array();

  require_once(sfConfig::get('sf_model_lib_dir').'/'.$class.'Peer.php');
  $objects = call_user_func(array($class.'Peer', 'doSelect'), new Criteria());
  if ($objects)
  {
    // multi primary keys handling
    $multiPrimaryKeys = is_array($objects[0]->getPrimaryKey()) ? true : false;

    // which method to call?
    $methodToCall = '';
    foreach (array('toString', '__toString', 'getPrimaryKey') as $method)
    {
      if (method_exists($objects[0], $method))
      {
        $methodToCall = $method;
        break;
      }
    }

    // construct select option list
    foreach ($objects as $tmpObject)
    {
      $key   = $multiPrimaryKeys ? implode('/', $tmpObject->getPrimaryKey()) : $tmpObject->getPrimaryKey();
      $value = $tmpObject->$methodToCall();

      $selectOptions[$key] = $value;
    }
  }

  return $selectOptions;
}

function object_select_country_tag($object, $method, $options = array(), $defaultValue = null)
{
  $options = _parse_attributes($options);

  $value = _get_object_value($object, $method, $defaultValue);

  return select_country_tag(_convert_method_to_name($method, $options), $value, $options);
}

function object_select_language_tag($object, $method, $options = array(), $defaultValue = null)
{
  $options = _parse_attributes($options);

  $value = _get_object_value($object, $method, $defaultValue);

  return select_language_tag(_convert_method_to_name($method, $options), $value, $options);
}

/**
 * Returns a hidden input html tag.
 *
 * @param object An object.
 * @param string An object column.
 * @param array Input options.
 * @param bool Input default value.
 *
 * @return string An html string which represents a hidden input tag.
 *
 */
function object_input_hidden_tag($object, $method, $options = array(), $defaultValue = null)
{
  $options = _parse_attributes($options);

  $value = _get_object_value($object, $method, $defaultValue);

  return input_hidden_tag(_convert_method_to_name($method, $options), $value, $options);
}

/**
 * Returns a input html tag.
 *
 * @param object An object.
 * @param string An object column.
 * @param array Input options.
 * @param bool Input default value.
 *
 * @return string An html string which represents an input tag.
 *
 */
function object_input_tag($object, $method, $options = array(), $defaultValue = null)
{
  $options = _parse_attributes($options);

  $value = _get_object_value($object, $method, $defaultValue);

  return input_tag(_convert_method_to_name($method, $options), $value, $options);
}

/**
 * Returns a checkbox html tag.
 *
 * @param object An object.
 * @param string An object column.
 * @param array Checkbox options.
 * @param bool Checkbox value.
 *
 * @return string An html string which represents a checkbox tag.
 *
 */
function object_checkbox_tag($object, $method, $options = array(), $defaultValue = null)
{
  $options = _parse_attributes($options);

  $value = _get_object_value($object, $method, $defaultValue);
  $value = in_array($value, array(true, 1, 'on', 'true', 't', 'yes', 'y'), true);

  return checkbox_tag(_convert_method_to_name($method, $options), 1, $value, $options);
}

function _convert_method_to_name ($method, &$options)
{
  $name = _get_option($options, 'control_name');

  if (!$name)
  {
    $name = sfInflector::underscore($method);
    $name = preg_replace('/^get_?/', '', $name);
  }

  return $name;
}

// returns default_value if object value is null
function _get_object_value ($object, $method, $defaultValue = null)
{
  // method exists?
  if (!method_exists($object, $method))
  {
    $error = 'Method "%s" doesn\'t exist for object of class "%s"';
    $error = sprintf($error, $method, get_class($object));

    throw new sfViewException($error);
  }

  $objectValue = $object->$method();

  return ($defaultValue !== null && $objectValue === null) ? $defaultValue : $objectValue;
}

?>