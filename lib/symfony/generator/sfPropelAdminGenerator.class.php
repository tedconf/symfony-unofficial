<?php

/*

symfony init-propeladmin-app app

symfony init-propeladmin app test Test
symfony init-propeladmin-main

symfony install-module sfAuth
symfony install-module sfMedia

- filtres (avec filtres prédéfinis ?) => dates, fk, enums, 
- breadcrumb
- many to many (ajax?)
- edition des tables liées en inline ou tabular (cf. django)
- module d'authentification avec page de login toute faite (paramètre: classes à utiliser pour les utilisateurs)
- module de gestion des images (sfMedia...)
- mettre de la doc phpdoc dans les fichiers générées -> PDF automatique / html OK
- possibilité d'avoir une colonne non existante avec get et set ou que get ou que set (password)
- autocomplete (pour des listes longues -> choix d'un utilisateur par exemple à la place d'un select)
  avec erreur si existe pas en BDD au retour!!! (ou champ caché user_id_real)
- possibilité de choisir les boutons : delete ou pas, ben c'est tout!
- generateur spécifique pour gérer la home page et aggréger les modules générés et les autres
- gestion des types enums en passant un paramètre value
- support des tables i18n

*/

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
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

    $required_parameters = array('model_class', 'moduleName');
    foreach ($required_parameters as $entry)
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
    if (!is_dir(sfConfig::get('sf_symfony_data_dir').'/symfony/generator/sfPropelAdmin/'.$theme.'/template'))
    {
      $error = 'The theme "%s" does not exist.';
      $error = sprintf($error, $theme);
      throw new sfConfigurationException($error);
    }

    $this->setTheme($theme);
    $templateFiles = array('listSuccess', 'editSuccess', '_filters', '_list_th_'.$this->getParameterValue('list.layout'), '_list_td_'.$this->getParameterValue('list.display.layout'));
    $this->generatePhpFiles($this->generatedModuleName, $templateFiles);

    // require generated action class
    $data = "require_once(sfConfig::get('sf_module_cache_dir').'/".$this->generatedModuleName."/actions/actions.class.php')\n";

    return $data;
  }

  public function getHelp($column, $type = '')
  {
    $help = $this->getParameterValue($type.'.fields.'.$column->getName().'.help');
    if ($help)
    {
      return "[?php echo image_tag('/sf/images/sf_admin/help.png', array('align' => 'absmiddle', 'alt' => __('".$this->escapeString($help)."'), 'title' => __('".$this->escapeString($help)."'))) ?]";
    }

    return '';
  }

  public function getColumnEditTag($column, $params = array())
  {
    // user defined parameters
    $user_params = $this->getParameterValue('edit.fields.'.$column->getName().'.params');
    $user_params = is_array($user_params) ? $user_params : sfToolkit::stringToArray($user_params);
    $params = $user_params ? array_merge($params, $user_params) : $params;

    // user sets a specific tag to use
    if ($type = $this->getParameterValue('edit.fields.'.$column->getName().'.type'))
    {
      $params = $this->getObjectTagParams($params, array('control_name' => $this->getSingularName().'['.$column->getName().']'));
      return "object_$type(\${$this->getSingularName()}, 'get{$column->getPhpName()}', $params)";
    }

    // guess the best tag to use with column type
    $params = array_merge($params, array('control_name' => $this->getSingularName().'['.$column->getName().']'));
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
          $phpNames[] = new sfAdminColumn($phpName);
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
    if (in_array($text[0], array('=', '-', '+')))
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
    for ($i = 0; $i < $count; $i++)
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
    $vars = array();
    $columns = $this->getColumns('');
    preg_match_all('/%%([^%]+)%%/', $value, $matches, PREG_PATTERN_ORDER);
    foreach ($matches[1] as $name)
    {
      foreach ($columns as $column)
      {
        $found = false;
        if ($column->getName() == $name)
        {
          $vars[] = '\'%%'.$name.'%%\' => $'.$this->getSingularName().'->get'.$column->getPhpName().'()';
          $found = true;
          break;
        }
      }

      if (!$found)
      {
        $vars[] = '\'%%'.$name.'%%\' => $'.$this->getSingularName().'->get'.sfInflector::camelize($name).'()';
      }
    }

    return '[?php echo __(\''.$value.'\', array('.implode(', ', $vars).')) ?]';
  }

  public function getColumnFilterTag($column, $params = array())
  {
    $type = $column->getCreoleType();

    $default_value = "isset(\$filters['".$column->getName()."']) ? \$filters['".$column->getName()."'] : null";
    $name = '\'filters['.$column->getName().']\'';

    if ($column->isForeignKey())
    {
      $relatedTable = $this->getMap()->getDatabaseMap()->getTable($column->getRelatedTableName());
      $params = $this->getObjectTagParams($params, array('related_class' => $relatedTable->getPhpName()));
      return "select_tag($name, $default_value, $params)";
    }
    else if ($type == CreoleTypes::DATE)
    {
      // rich=false not yet implemented
      $params = $this->getObjectTagParams($params, array('rich' => true, 'calendar_button_img' => '/sf/images/sf_admin/date.png'));
      return "input_date_tag($name, $default_value, $params)";
    }
    else if ($type == CreoleTypes::TIMESTAMP)
    {
      // rich=false not yet implemented
      $params = $this->getObjectTagParams($params, array('rich' => true, 'withtime' => true, 'calendar_button_img' => '/sf/images/sf_admin/date.png'));
      return "input_date_tag($name, $default_value, $params)";
    }
    else if ($type == CreoleTypes::BOOLEAN)
    {
      $params = $this->getObjectTagParams($params);
      return "checkbox_tag($name, $default_value, $params)";
    }
    else if ($type == CreoleTypes::CHAR || $type == CreoleTypes::VARCHAR)
    {
      $size = ($column->getSize() < 15 ? $column->getSize() : 15);
      $params = $this->getObjectTagParams($params, array('size' => $size));
      return "input_tag($name, $default_value, $params)";
    }
    else if ($type == CreoleTypes::INTEGER || $type == CreoleTypes::TINYINT || $type == CreoleTypes::SMALLINT || $type == CreoleTypes::BIGINT)
    {
      $params = $this->getObjectTagParams($params, array('size' => 7));
      return "input_tag($name, $default_value, $params)";
    }
    else if ($type == CreoleTypes::FLOAT || $type == CreoleTypes::DOUBLE || $type == CreoleTypes::DECIMAL || $type == CreoleTypes::NUMERIC || $type == CreoleTypes::REAL)
    {
      $params = $this->getObjectTagParams($params, array('size' => 7));
      return "input_tag($name, $default_value, $params)";
    }
    else if ($type == CreoleTypes::TEXT || $type == CreoleTypes::LONGVARCHAR)
    {
      $params = $this->getObjectTagParams($params, array('size' => '15x2'));
      return "textarea_tag($name, $default_value, $params)";
    }
    else
    {
      $params = $this->getObjectTagParams($params, array('disabled' => true));
      return "input_tag($name, $default_value, $params)";
    }
  }

  private function escapeString($string)
  {
    return preg_replace('/\'/', '\\\'', $string);
  }
}

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

  public function isReal ()
  {
    return $this->column ? true : false;
  }

  public function getPhpName ()
  {
    return $this->phpName;
  }

  public function getName ()
  {
    return sfInflector::underscore($this->phpName);
  }

  public function isLink ()
  {
    return ($this->flag == '=' ? true : false);
  }
}

?>