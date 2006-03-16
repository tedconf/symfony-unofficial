<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Propel admin generator.
 *
 * This class executes all the logic for the current request.
 *
 * @package    symfony
 * @subpackage generator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfPropelAdminGenerator extends sfPropelCrudGenerator
{
  private
    $params = array(),
    $fields = array();

  public function initialize($generatorManager)
  {
    parent::initialize($generatorManager);

    $this->setGeneratorClass('sfPropelAdmin');
  }

  public function generate($params = array())
  {
    $this->params = $params;

    $requiredParameters = array('model_class', 'moduleName');
    foreach ($requiredParameters as $entry)
    {
      if (!isset($this->params[$entry]))
      {
        $error = 'You must specify a "%s"';
        $error = sprintf($error, $entry);

        throw new sfParseException($error);
      }
    }

    $modelClass = $this->params['model_class'];

    if (!class_exists($modelClass))
    {
      $error = 'Unable to scaffold unexistant model "%s"';
      $error = sprintf($error, $modelClass);

      throw new sfInitializationException($error);
    }

    $this->setScaffoldingClassName($modelClass);

    // generated module name
    $this->setGeneratedModuleName('auto'.ucfirst($params['moduleName']));
    $this->setModuleName($params['moduleName']);

    // get some model metadata
    $this->loadMapBuilderClasses();

    // load all primary keys
    $this->loadPrimaryKeys();

    // theme exists?
    $theme = isset($this->params['theme']) ? $this->params['theme'] : 'default';
    if (!is_dir(sfConfig::get('sf_symfony_data_dir').'/generator/sfPropelAdmin/'.$theme.'/template'))
    {
      $error = 'The theme "%s" does not exist.';
      $error = sprintf($error, $theme);
      throw new sfConfigurationException($error);
    }

    $this->setTheme($theme);
    $templateFiles = array(
      'listSuccess', 'editSuccess', '_filters',
      '_list_th_'.$this->getParameterValue('list.layout', 'tabular'), '_list_td_'.$this->getParameterValue('list.layout', 'tabular'),
      '_list_th_tabular',
      '_list_header', '_edit_header', '_list_footer', '_edit_footer',
      '_list_td_actions', '_list_actions', '_edit_actions',
    );
    $this->generatePhpFiles($this->generatedModuleName, $templateFiles);

    // require generated action class
    $data = "require_once(sfConfig::get('sf_module_cache_dir').'/".$this->generatedModuleName."/actions/actions.class.php')\n";

    return $data;
  }

  public function getHelpAsIcon($column, $type = '')
  {
    $help = $this->getParameterValue($type.'.fields.'.$column->getName().'.help');
    if ($help)
    {
      return "[?php echo image_tag('/sf/images/sf_admin/help.png', array('align' => 'absmiddle', 'alt' => __('".$this->escapeString($help)."'), 'title' => __('".$this->escapeString($help)."'))) ?]";
    }

    return '';
  }

  public function getHelp($column, $type = '')
  {
    $help = $this->getParameterValue($type.'.fields.'.$column->getName().'.help');
    if ($help)
    {
      return "<div class=\"sf_admin_edit_help\">[?php echo __('".$this->escapeString($help)."') ?]</div>";
    }

    return '';
  }

  public function getButtonToAction($actionName, $params, $pkLink = false)
  {
    $options = isset($params['params']) ? sfToolkit::stringToArray($params['params']) : array();
    $method  = 'button_to';
    $liClass  = '';
    $onlyIfId = false;

    // default values
    if ($actionName[0] == '_')
    {
      $actionName    = substr($actionName, 1);
      $defaultName   = strtr($actionName, '_', ' ');
      $defaultIcon   = '/sf/images/sf_admin/'.$actionName.'_icon.png';
      $defaultAction = $actionName;
      $defaultClass  = 'sf_admin_action_'.$actionName;

      if ($actionName == 'save' || $actionName == 'save_and_add')
      {
        $method = 'submit_tag';
        $options['name'] = $actionName;
      }

      if ($actionName == 'delete')
      {
        $options['post'] = true;
        if (!isset($options['confirm']))
        {
          $options['confirm'] = 'Are you sure?';
        }

        $liClass = 'float-left';

        $onlyIfId = true;
      }
    }
    else
    {
      $defaultName   = strtr($actionName, '_', ' ');
      $defaultIcon   = '/sf/images/sf_admin/default_icon.png';
      $defaultAction = 'List'.sfInflector::camelize($actionName);
      $defaultClass  = '';
    }

    $name   = isset($params['name']) ? $params['name'] : $defaultName;
    $icon   = isset($params['icon']) ? $params['icon'] : $defaultIcon;
    $action = isset($params['action']) ? $params['action'] : $defaultAction;
    $urlParams = $pkLink ? '?'.$this->getPrimaryKeyUrlParams() : '\'';

    if (!isset($options['class']) && $defaultClass)
    {
      $options['class'] = $defaultClass;
    }
    else
    {
      $options['style'] = 'background: #ffc url('.$icon.') no-repeat 3px 2px';
    }

    $liClass = $liClass ? ' class='.$liClass : '';

    $html = '<li'.$liClass.'>';

    if ($onlyIfId)
    {
      $html .= '[?php if ('.$this->getPrimaryKeyIsSet().'): ?]'."\n";
    }

    if ($method == 'submit_tag')
    {
      $html .= '[?php echo submit_tag(__(\''.$name.'\'), '.var_export($options, true).') ?]';
    }
    else
    {
      $html .= '[?php echo button_to(__(\''.$name.'\'), \''.$this->getModuleName().'/'.$action.$urlParams.', '.var_export($options, true).') ?]';
    }

    if ($onlyIfId)
    {
      $html .= '[?php endif ?]'."\n";
    }

    $html .= '</li>';

    return $html;
  }

  public function getLinkToAction($actionName, $params, $pkLink = false)
  {
    // default values
    if ($actionName[0] == '_')
    {
      $actionName = substr($actionName, 1);
      $name       = $actionName;
      $icon       = '/sf/images/sf_admin/'.$actionName.'_icon.png';
      $action     = $actionName;
    }
    else
    {
      $name   = isset($params['name']) ? $params['name'] : $actionName;
      $icon   = isset($params['icon']) ? $params['icon'] : '/sf/images/sf_admin/default_icon.png';
      $action = isset($params['action']) ? $params['action'] : 'List'.sfInflector::camelize($actionName);
    }

    $urlParams = $pkLink ? '?'.$this->getPrimaryKeyUrlParams() : '\'';

    return '<li>[?php echo link_to(image_tag(\''.$icon.'\', array(\'alt\' => __(\''.$name.'\'), \'title\' => __(\''.$name.'\'))), \''.$this->getModuleName().'/'.$action.$urlParams.') ?]</li>';
  }

  public function getColumnEditTag($column, $params = array())
  {
    // user defined parameters
    $userParams = $this->getParameterValue('edit.fields.'.$column->getName().'.params');
    $userParams = is_array($userParams) ? $userParams : sfToolkit::stringToArray($userParams);
    $params     = $userParams ? array_merge($params, $userParams) : $params;

    if ($column->isPartial())
    {
      return "include_partial('".$column->getName()."', array('{$this->getSingularName()}' => \${$this->getSingularName()}))";
    }

    // default control name
    $params = array_merge(array('control_name' => $this->getSingularName().'['.$column->getName().']'), $params);

    // default parameter values
    $type = $column->getCreoleType();
    if ($type == CreoleTypes::DATE)
    {
      $params = array_merge(array('rich' => true, 'calendar_button_img' => '/sf/images/sf_admin/date.png'), $params);
    }
    elseif ($type == CreoleTypes::TIMESTAMP)
    {
      $params = array_merge(array('rich' => true, 'withtime' => true, 'calendar_button_img' => '/sf/images/sf_admin/date.png'), $params);
    }

    // user sets a specific tag to use
    if ($inputType = $this->getParameterValue('edit.fields.'.$column->getName().'.type'))
    {
      if ($inputType == 'plain')
      {
        return $this->getColumnListTag($column, $params);
      }
      else
      {
        $params = $this->getObjectTagParams($params);

        return "object_$inputType(\${$this->getSingularName()}, 'get{$column->getPhpName()}', $params)";
      }
    }

    // guess the best tag to use with column type
    return parent::getColumnEditTag($column, $params);
  }

  public function getColumnCategories($paramName)
  {
    if (is_array($this->getParameterValue($paramName)))
    {
      $fields = $this->getParameterValue($paramName);

      // do we have categories?
      if (!isset($fields[0]))
      {
        return array_keys($fields);
      }

    }

    return array('NONE');
  }

  public function addCredentialCondition($content, $params = array())
  {
    if (isset($params['credentials']))
    {
      $credentials = str_replace("\n", ' ', var_export($params['credentials'], true));

      return <<<EOF
[?php if (\$sf_user->hasCredential($credentials)): ?]
$content
[?php endif ?]
EOF;
    }
    else
    {
      return $content;
    }
  }

  /**
   * returns an array of sfAdminColumn objects
   * from the $paramName list or the list of all columns (in table) if it does not exist
   */
  public function getColumns($paramName, $category = 'NONE')
  {
    $phpNames = array();

    // user has set a personnalized list of fields?
    $fields = $this->getParameterValue($paramName);
    if (is_array($fields))
    {
      // categories?
      if (isset($fields[0]))
      {
        // simulate a default one
        $fields = array('NONE' => $fields);
      }

      if (!$fields)
      {
        return array();
      }

      foreach ($fields[$category] as $field)
      {
        $found = false;

        list($field, $flag) = $this->splitFlag($field);
        $phpName = sfInflector::camelize($field);

        // search the matching column for this column name
        foreach ($this->getTableMap()->getColumns() as $column)
        {
          if ($column->getPhpName() == $phpName)
          {
            $found = true;
            $phpNames[] = new sfAdminColumn($column->getPhpName(), $column, $flag);
            break;
          }
        }

        // not a "real" column, so we simulate one
        if (!$found)
        {
          $phpNames[] = new sfAdminColumn($phpName, null, $flag);
        }
      }
    }
    else
    {
      // no, just return the full list of columns in table
      foreach ($this->getTableMap()->getColumns() as $column)
      {
        $phpNames[] = new sfAdminColumn($column->getPhpName(), $column);
      }
    }

    return $phpNames;
  }

  public function splitFlag($text)
  {
    $flag = '';
    if (in_array($text[0], array('=', '-', '+', '_')))
    {
      $flag = $text[0];
      $text = substr($text, 1);
    }

    return array($text, $flag);
  }

  // $name example: list.display
  // special default behaviour for fields. keys
  public function getParameterValue($key, $default = null)
  {
    if (preg_match('/^([^\.]+)\.fields\.(.+)$/', $key, $matches))
    {
      return $this->getFieldParameterValue($matches[2], $matches[1], $default);
    }
    else
    {
      return $this->getValueFromKey($key, $default);
    }
  }

  private function getFieldParameterValue($key, $type = '', $default = null)
  {
    $retval = $this->getValueFromKey($type.'.fields.'.$key, $default);
    if ($retval)
    {
      return $retval;
    }

    $retval = $this->getValueFromKey('fields.'.$key, $default);
    if ($retval)
    {
      return $retval;
    }

    if (preg_match('/\.name$/', $key))
    {
      // default field.name
      return sfInflector::humanize(($pos = strpos($key, '.')) ? substr($key, 0, $pos) : $key);
    }
    else
    {
      return null;
    }
  }

  private function getValueFromKey($key, $default = null)
  {
    $ref   =& $this->params;
    $parts =  explode('.', $key);
    $count =  count($parts);
    for ($i = 0; $i < $count; ++$i)
    {
      $partKey = $parts[$i];
      if (!isset($ref[$partKey]))
      {
        return $default;
      }

      if ($count == $i + 1)
      {
        return $ref[$partKey];
      }
      else
      {
        $ref =& $ref[$partKey];
      }
    }

    return $default;
  }

  public function getI18NString($key, $default = null)
  {
    $value = $this->escapeString($this->getParameterValue($key, $default));

    // find %%xx%% strings
    preg_match_all('/%%([^%]+)%%/', $value, $matches, PREG_PATTERN_ORDER);
    $this->params['tmp']['display'] = array();
    foreach ($matches[1] as $name)
    {
      $this->params['tmp']['display'][] = $name;
    }

    $vars = array();
    foreach ($this->getColumns('tmp.display') as $column)
    {
      if ($column->isLink())
      {
        $vars[] = '\'%%'.$column->getName().'%%\' => link_to('.$this->getColumnListTag($column).', \''.$this->getModuleName().'/edit?'.$this->getPrimaryKeyUrlParams().')';
      }
      else
      {
        $vars[] = '\'%%'.$column->getName().'%%\' => '.$this->getColumnListTag($column);
      }
    }

    // strip all = signs
    $value = preg_replace('/%%=([^%]+)%%/', '%%$1%%', $value);

    return '[?php echo __(\''.$value.'\', '."\n".'array('.implode(",\n", $vars).')) ?]';
  }

  public function getColumnListTag($column, $params = array())
  {
    $userParams = $this->getParameterValue('list.fields.'.$column->getName().'.params');
    $userParams = is_array($userParams) ? $userParams : sfToolkit::stringToArray($userParams);
    $params     = $userParams ? array_merge($params, $userParams) : $params;

    $type = $column->getCreoleType();

    if ($column->isPartial())
    {
      return "include_partial('".$column->getName()."', array('{$this->getSingularName()}' => \${$this->getSingularName()}))";
    }
    elseif ($type == CreoleTypes::DATE || $type == CreoleTypes::TIMESTAMP)
    {
      $format = isset($params['date_format']) ? $params['date_format'] : 'f';
      return "format_date(\${$this->getSingularName()}->get{$column->getPhpName()}(), \"$format\")";
    }
    elseif ($type == CreoleTypes::BOOLEAN)
    {
      return "\${$this->getSingularName()}->get{$column->getPhpName()}() ? image_tag('/sf/images/sf_admin/ok.png') : '&nbsp;'";
    }
    else
    {
      return "\${$this->getSingularName()}->get{$column->getPhpName()}()";
    }
  }

  public function getColumnFilterTag($column, $params = array())
  {
    $userParams = $this->getParameterValue('list.fields.'.$column->getName().'.params');
    $userParams = is_array($userParams) ? $userParams : sfToolkit::stringToArray($userParams);
    $params     = $userParams ? array_merge($params, $userParams) : $params;

    $type = $column->getCreoleType();

    $defaultValue = "isset(\$filters['".$column->getName()."']) ? \$filters['".$column->getName()."'] : null";
    $name = '\'filters['.$column->getName().']\'';

    if ($column->isForeignKey())
    {
      $relatedTable = $this->getMap()->getDatabaseMap()->getTable($column->getRelatedTableName());
      $params = $this->getObjectTagParams($params, array('include_blank' => true));

      $options = "objects_for_select(".$relatedTable->getPhpName()."Peer::doSelect(new Criteria()), 'getPrimaryKey', '__toString', $defaultValue, $params)";

      return "select_tag($name, $options, $params)";
    }
    elseif ($type == CreoleTypes::DATE)
    {
      // rich=false not yet implemented
      $params = $this->getObjectTagParams($params, array('rich' => true, 'calendar_button_img' => '/sf/images/sf_admin/date.png'));
      return "input_date_tag($name, $defaultValue, $params)";
    }
    elseif ($type == CreoleTypes::TIMESTAMP)
    {
      // rich=false not yet implemented
      $params = $this->getObjectTagParams($params, array('rich' => true, 'withtime' => true, 'calendar_button_img' => '/sf/images/sf_admin/date.png'));
      return "input_date_tag($name, $defaultValue, $params)";
    }
    elseif ($type == CreoleTypes::BOOLEAN)
    {
      $defaultIncludeCustom = '__("yes or no")';
      $params = $this->getObjectTagParams($params, array('include_custom' => $defaultIncludeCustom));

      // little hack
      $params = preg_replace("/'".preg_quote($defaultIncludeCustom)."'/", $defaultIncludeCustom, $params);

      $options = "options_for_select(array(1 => __('yes'), 0 => __('no')), $defaultValue, $params)";

      return "select_tag($name, $options, $params)";
    }
    elseif ($type == CreoleTypes::CHAR || $type == CreoleTypes::VARCHAR)
    {
      $size = ($column->getSize() < 15 ? $column->getSize() : 15);
      $params = $this->getObjectTagParams($params, array('size' => $size));
      return "input_tag($name, $defaultValue, $params)";
    }
    elseif ($type == CreoleTypes::INTEGER || $type == CreoleTypes::TINYINT || $type == CreoleTypes::SMALLINT || $type == CreoleTypes::BIGINT)
    {
      $params = $this->getObjectTagParams($params, array('size' => 7));
      return "input_tag($name, $defaultValue, $params)";
    }
    elseif ($type == CreoleTypes::FLOAT || $type == CreoleTypes::DOUBLE || $type == CreoleTypes::DECIMAL || $type == CreoleTypes::NUMERIC || $type == CreoleTypes::REAL)
    {
      $params = $this->getObjectTagParams($params, array('size' => 7));
      return "input_tag($name, $defaultValue, $params)";
    }
    elseif ($type == CreoleTypes::TEXT || $type == CreoleTypes::LONGVARCHAR)
    {
      $params = $this->getObjectTagParams($params, array('size' => '15x2'));
      return "textarea_tag($name, $defaultValue, $params)";
    }
    else
    {
      $params = $this->getObjectTagParams($params, array('disabled' => true));
      return "input_tag($name, $defaultValue, $params)";
    }
  }

  private function escapeString($string)
  {
    return preg_replace('/\'/', '\\\'', $string);
  }
}

/**
 * Propel admin generator column.
 *
 * @package    symfony
 * @subpackage generator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfAdminColumn
{
  private
    $phpName    = '',
    $column     = null,
    $flag       = '';

  public function __construct($phpName, $column = null, $flag = '')
  {
    $this->phpName = $phpName;
    $this->column  = $column;
    $this->flag    = $flag;
  }

  public function __call ($name, $arguments)
  {
    return $this->column ? $this->column->$name() : null;
  }

  public function isReal()
  {
    return $this->column ? true : false;
  }

  public function getPhpName()
  {
    return $this->phpName;
  }

  public function getName()
  {
    return sfInflector::underscore($this->phpName);
  }

  public function isPartial()
  {
    return (($this->flag == '_') ? true : false);
  }

  public function isLink()
  {
    return (($this->flag == '=' || $this->isPrimaryKey()) ? true : false);
  }
}

?>