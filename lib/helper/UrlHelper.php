<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * UrlHelper.
 *
 * @package    symfony
 * @subpackage helper
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */

function url_for($url, $absolute = false)
{
  static $controller;

  if (!isset($controller))
  {
    $controller = sfContext::getInstance()->getController();
  }

  return $controller->genUrl($url, $absolute);
}

/*
    # Creates a link tag of the given +name+ using an URL created by the set of +options+. See the valid options in
    # link:classes/ActionController/Base.html#M000021. It's also possible to pass a string instead of an options hash to
    # get a link tag that just points without consideration. If nil is passed as a name, the link itself will become the name.
    # The html_options have a special feature for creating javascript confirm alerts where if you pass :confirm => 'Are you sure?',
    # the link will be guarded with a JS popup asking that question. If the user accepts, the link is processed, otherwise not.
    #
    # Example:
    #   link_to "Delete this page", { :action => "destroy", :id => @page.id }, :confirm => "Are you sure?"
*/
function link_to($name = '', $options = '', $htmlOptions = array())
{
  $htmlOptions = _parse_attributes($htmlOptions);

  $htmlOptions = _convert_options_to_javascript($htmlOptions);

  $absolute = false;
  if (isset($htmlOptions['absolute_url']))
  {
    unset($htmlOptions['absolute_url']);
    $absolute = true;
  }

  $htmlOptions['href'] = url_for($options, $absolute);

  if (isset($htmlOptions['query_string']))
  {
    $htmlOptions['href'] .= '?'.$htmlOptions['query_string'];
    unset($htmlOptions['query_string']);
  }

  if (is_object($name))
  {
    $name = $name->__toString();
  }

  if (!strlen($name))
  {
    $name = $htmlOptions['href'];
  }

  return content_tag('a', $name, $htmlOptions);
}

function link_to_if($condition, $name = '', $options = '', $htmlOptions = array(), $parametersForMethodReference = array())
{
  if ($condition)
  {
    return link_to($name, $options, $htmlOptions, $parametersForMethodReference);
  }
  else
  {
    if (isset($htmlOptions['tag']))
    {
      $tag = $htmlOptions['tag'];
      unset($htmlOptions['tag']);
    }
    else
    {
      $tag = 'span';
    }

    return content_tag($tag, $name, $htmlOptions);
  }
}

function link_to_unless($condition, $name = '', $options = '', $htmlOptions = array(), $parametersForMethodReference = array())
{
  return link_to_if(!$condition, $name, $options, $htmlOptions, $parametersForMethodReference);
}

function button_to($name, $target, $options = array())
{
  $htmlOptions = _convert_options($options);
  $htmlOptions['value']   = $name;

  if (isset($htmlOptions['post']) && $htmlOptions['post'])
  {
    if (isset($htmlOptions['popup']))
    {
      throw new sfConfigurationException('You can\'t use "popup" and "post" together');
    }
    $htmlOptions['type'] = 'submit';
    unset($htmlOptions['post']);
    $htmlOptions = _convert_options_to_javascript($htmlOptions);

    return form_tag($target, array('method' => 'post', 'class' => 'button_to')).tag('input', $htmlOptions).'</form>';
  }
  elseif (isset($htmlOptions['popup']))
  {
    $htmlOptions['type']    = 'button';
    $htmlOptions = _convert_options_to_javascript($htmlOptions, $target);

    return tag('input', $htmlOptions);
  }
  else
  {
    $htmlOptions['type']    = 'button';
    $htmlOptions['onclick'] = "document.location.href='".url_for($target)."';";
    $htmlOptions = _convert_options_to_javascript($htmlOptions);

    return tag('input', $htmlOptions);
  }
}

function mail_to($email, $name = '', $htmlOptions = array())
{
  $htmlOptions = _parse_attributes($htmlOptions);

  $htmlOptions = _convert_options_to_javascript($htmlOptions);

  if (!$name)
  {
    $name = $email;
  }

  if (isset($htmlOptions['encode']) && $htmlOptions['encode'])
  {
    unset($htmlOptions['encode']);
    $htmlOptions['href'] = _encodeText('mailto:'.$email);
    $name = _encodeText($name);
  }
  else
  {
    $htmlOptions['href'] = 'mailto:'.$email;
  }

  return content_tag('a', $name, $htmlOptions);
}

function _convert_options_to_javascript($htmlOptions, $target = '')
{
  // confirm
  $confirm = isset($htmlOptions['confirm']) ? $htmlOptions['confirm'] : '';
  unset($htmlOptions['confirm']);

  // popup
  $popup = isset($htmlOptions['popup']) ? $htmlOptions['popup'] : '';
  unset($htmlOptions['popup']);

  // post
  $post = isset($htmlOptions['post']) ? $htmlOptions['post'] : '';
  unset($htmlOptions['post']);

  $onclick = isset($htmlOptions['onclick']) ? $htmlOptions['onclick'] : '';

  if ($popup && $post)
  {
    throw new sfConfigurationException('You can\'t use "popup" and "post" in the same link');
  }
  elseif ($confirm && $popup)
  {
    $htmlOptions['onclick'] = $onclick.'if ('._confirm_javascript_function($confirm).') { '._popup_javascript_function($popup, $target).' };return false;';
  }
  elseif ($confirm && $post)
  {
    $htmlOptions['onclick'] = $onclick.'if ('._confirm_javascript_function($confirm).') { '._post_javascript_function().' };return false;';
  }
  elseif ($confirm)
  {
    if ($onclick)
    {
      $htmlOptions['onclick'] = 'if ('._confirm_javascript_function($confirm).') {'.$onclick.'}';
    }
    else
    {
      $htmlOptions['onclick'] = 'return '._confirm_javascript_function($confirm).';';
    }
  }
  elseif ($post)
  {
    $htmlOptions['onclick'] = $onclick._post_javascript_function().'return false;';
  }
  elseif ($popup)
  {
    $htmlOptions['onclick'] = $onclick._popup_javascript_function($popup, $target).'return false;';
  }

  return $htmlOptions;
}

function _confirm_javascript_function($confirm)
{
  return "confirm('".escape_javascript($confirm)."')";
}

function _popup_javascript_function($popup, $target = '')
{
  $url = $target == '' ? 'this.href' : "'".url_for($target)."'";

  if (is_array($popup))
  {
    if (isset($popup[1]))
    {
      return "window.open(".$url.",'".$popup[0]."','".$popup[1]."');";
    }
    else
    {
      return "window.open(".$url.",'".$popup[0]."');";
    }
  }
  else
  {
    return "window.open(".$url.");";
  }
}

function _post_javascript_function()
{
  return "f = document.createElement('form'); document.body.appendChild(f); f.method = 'POST'; f.action = this.href; f.submit();";
}

function _encodeText($text)
{
  $encodedText = '';

  foreach (str_split($text) as $char)
  {
    $r = rand(0, 100);

    # roughly 10% raw, 45% hex, 45% dec
    # '@' *must* be encoded. I insist.
    if ($r > 90 && $char != '@')
    {
      $encodedText .= $char;
    }
    elseif ($r < 45)
    {
      $encodedText .= '&#x'.dechex(ord($char)).';';
    }
    else
    {
      $encodedText .= '&#'.ord($char).';';
    }
  }

  return $encodedText;
}

?>