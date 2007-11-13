<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormSchema represents an array of fields.
 *
 * A field is a named validator.
 *
 * @package    symfony
 * @subpackage widget
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfWidgetFormSchema extends sfWidgetForm implements ArrayAccess
{
  const
    FIRST  = 'first',
    LAST   = 'last',
    BEFORE = 'before',
    AFTER  = 'after';

  protected
    $formFormatters = array(),
    $options        = array(),
    $labels         = array(),
    $fields         = array(),
    $positions      = array(),
    $helps          = array();

  /**
   * Constructor.
   *
   * The first argument can be:
   *
   *  * null
   *  * an array of sfWidget instances
   *
   * Available options:
   *
   *  * name_format:    The sprintf pattern to use for input names
   *  * form_formatter: The form formatter name (table and list are bundled)
   *
   * @param mixed Initial fields
   * @param array An array of default HTML attributes
   * @param array An array of options
   * @param array An array of HTML labels
   * @param array An array of help texts
   *
   * @see sfWidgetForm
   */
  public function __construct($fields = null, $options = array(), $attributes = array(), $labels = array(), $helps = array())
  {
    if (is_array($fields))
    {
      foreach ($fields as $name => $widget)
      {
        $this[$name] = $widget;
      }
    }
    else if (!is_null($fields))
    {
      throw new sfException('sfWidgetFormSchema constructor takes an array of sfWidget objects.');
    }

    $this->labels = $labels;

    $this->addOption('name_format', '%s');
    $this->addOption('form_formatter', 'table');

    parent::__construct($options, $attributes);
  }

  /**
   * Adds a form formatter.
   *
   * @param string                      The formatter name
   * @param sfWidgetFormSchemaFormatter A sfWidgetFormSchemaFormatter instance
   */
  public function addFormFormatter($name, sfWidgetFormSchemaFormatter $formatter)
  {
    $this->formFormatters[$name] = $formatter;
  }

  /**
   * Returns all the form formats defined for this form schema.
   *
   * @return array An array of named form formats
   */
  public function getFormFormatters()
  {
    return $this->formFormatters;
  }

  /**
   * Sets the form formatter name to use when rendering the widget schema.
   *
   * @param string The form formatter name
   */
  public function setFormFormatterName($name)
  {
    $this->options['form_formatter'] = $name;
  }

  /**
   * Gets the form formatter name that will be used to render the widget schema.
   *
   * @return string The form formatter name
   */
  public function getFormFormatterName()
  {
    return $this->options['form_formatter'];
  }

  /**
   * Returns the form formatter to use for widget schema rendering
   *
   * @return sfWidgetFormSchemaFormatter sfWidgetFormSchemaFormatter instance
   *
   * @throws sfException
   */
  public function getFormFormatter()
  {
    $name = $this->getFormFormatterName();

    if (isset($this->formFormatters[$name]))
    {
      return $this->formFormatters[$name];
    }

    $class = 'sfWidgetFormSchemaFormatter'.ucfirst($name);
    if (class_exists($class))
    {
      return new $class();
    }

    throw new sfException(sprintf('The form formatter "%s" does not exist.', $name));
  }

  /**
   * Sets the format string for the name HTML attribute.
   *
   * @param string The format string (must contain a %s for the name placeholder)
   */
  public function setNameFormat($format)
  {
    $this->options['name_format'] = $format;
  }

  /**
   * Gets the format string for the name HTML attribute.
   *
   * @return string The format string
   */
  public function getNameFormat()
  {
    return $this->options['name_format'];
  }

  /**
   * Sets the label names to render for each field.
   *
   * @param array An array of label names
   */
  public function setLabels($labels)
  {
    $this->labels = $labels;
  }

  /**
   * Sets the labels.
   *
   * @return array An array of label names
   */
  public function getLabels()
  {
    return $this->labels;
  }

  /**
   * Sets a label.
   *
   * @param string The field name
   * @param string The label name
   */
  public function setLabel($name, $value)
  {
    $this->labels[$name] = $value;
  }

  /**
   * Gets a label by field name.
   *
   * @param  string The field name
   *
   * @return string The label name or an empty string if it is not defined
   */
  public function getLabel($name)
  {
    return array_key_exists($name, $this->labels) ? $this->labels[$name] : '';
  }

  /**
   * Sets the help texts to render for each field.
   *
   * @param array An array of help texts
   */
  public function setHelps($helps)
  {
    $this->helps = $helps;
  }

  /**
   * Sets the help texts.
   *
   * @return array An array of help texts
   */
  public function getHelps()
  {
    return $this->helps;
  }

  /**
   * Sets a help text.
   *
   * @param string The field name
   * @param string The help text
   */
  public function setHelp($name, $help)
  {
    $this->helps[$name] = $help;
  }

  /**
   * Gets a text help by field name.
   *
   * @param  string The field name
   *
   * @return string The help text or an empty string if it is not defined
   */
  public function getHelp($name)
  {
    return array_key_exists($name, $this->helps) ? $this->helps[$name] : '';
  }

  /**
   * Returns true if the widget schema needs a multipart form.
   *
   * @return Boolean true if the widget schema needs a multipart form, false otherwise
   */
  public function needsMultipartForm()
  {
    foreach ($this->fields as $field)
    {
      if ($field->needsMultipartForm())
      {
        return true;
      }
    }

    return false;
  }

  /**
   * Renders a field by name.
   *
   * @param string       The field name
   * @param string       The field value
   *
   * @param string       A HTML string representing the rendered widget
   */
  public function renderField($name, $value = null, $errors = array())
  {
    if (is_null($widget = $this[$name]))
    {
      throw new sfException(sprintf('The field named "%s" does not exist.', $name));
    }

    // we clone the widget because we want to change the id format temporarily
    $clone = clone $widget;
    $clone->setIdFormat($this->options['id_format']);

    return $clone->render($this->generateName($name), $value, array(), $errors);
  }

  /**
   * Renders the widget.
   *
   * @param  string The name of the HTML widget
   * @param  mixed  The value of the widget
   * @param  array  An array of HTML attributes
   * @param  array  An array of errors
   *
   * @return string A HTML representation of the widget
   */
  public function render($name, $values = array(), $attributes = array(), $errors = array())
  {
    if (is_null($values))
    {
      $values = array();
    }

    if (!is_array($values) && !$values instanceof ArrayAccess)
    {
      throw new sfException('You must pass an array of values to render a widget schema');
    }

    $formFormat = $this->getFormFormatter();

    $rows = array();
    $hiddenRows = array();
    $errorRows = array();

    // global errors
    $globalErrors = array();
    if (!is_null($errors))
    {
      foreach ($errors as $name => $error)
      {
        if (!isset($this->fields[$name]))
        {
          $globalErrors[] = $error;
        }
      }
    }

    // render each field
    foreach ($this->positions as $name)
    {
      $widget = $this[$name];
      $value = isset($values[$name]) ? $values[$name] : null;

      if ($widget instanceof sfWidgetForm && $widget->isHidden())
      {
        $hiddenRows[] = $widget->render($this->generateName($name), $value);
        if (isset($errors[$name]))
        {
          $globalErrors[$this->generateLabelName($name)] = $errors[$name];
        }
      }
      else
      {
        $error = isset($errors[$name]) ? $errors[$name] : array();
        $field = $this->renderField($name, $value, $error);

        // don't add a label tag and errors if we embed a form schema
        $label = $widget instanceof sfWidgetFormSchema ? $this->generateLabelName($name) : $this->generateLabel($name);
        $error = $widget instanceof sfWidgetFormSchema ? array() : $error;

        $rows[] = $formFormat->formatRow($label, $field, $error, $this->getHelp($name));
      }
    }

    // insert hidden fields in the last row
    for ($i = 0, $max = count($rows); $i < $max; $i++)
    {
      $rows[$i] = strtr($rows[$i], array('%hidden_fields%' => $i == $max - 1 ? implode("\n", $hiddenRows) : ''));
    }

    return $formFormat->formatErrorRow($globalErrors).implode('', $rows);
  }

  /**
   * Generates a label for the given field name.
   *
   * @param  string The field name
   *
   * @return string The label tag
   */
  public function generateLabel($name)
  {
    $labelName = $this->generateLabelName($name);

    if (false === $labelName)
    {
      return '';
    }

    return $this->renderContentTag('label', $labelName, array('for' => $this->generateId($this->generateName($name))));
  }

  /**
   * Generates the label name for the given field name.
   *
   * @param  string The field name
   *
   * @return string The label name
   */
  public function generateLabelName($name)
  {
    $label = $this->getLabel($name);
    if (!$label && false !== $label)
    {
      $label = str_replace('_', ' ', ucfirst($name));
    }

    return $label;
  }

  /**
   * Generates a name.
   *
   * @param string The name
   *
   * @param string The generated name
   */
  public function generateName($name)
  {
    if (false === $this->options['name_format'])
    {
      return $name;
    }

    if (false !== strpos($this->options['name_format'], '%s'))
    {
      return sprintf($this->options['name_format'], $name);
    }

    return $name;
  }

  /**
   * Returns true if the schema has a field with the given name (implements the ArrayAccess interface).
   *
   * @param  string  The field name
   *
   * @return Boolean true if the schema has a field with the given name, false otherwise
   */
  public function offsetExists($name)
  {
    return isset($this->fields[$name]);
  }

  /**
   * Gets the field associated with the given name (implements the ArrayAccess interface).
   *
   * @param  string   The field name
   *
   * @return sfWidget The sfWidget instance associated with the given name, null if it does not exist
   */
  public function offsetGet($name)
  {
    return isset($this->fields[$name]) ? $this->fields[$name] : null;
  }

  /**
   * Sets a field (implements the ArrayAccess interface).
   *
   * @param string   The field name
   * @param sfWidget A sfWidget instance
   */
  public function offsetSet($name, $widget)
  {
    if (!$widget instanceof sfWidget)
    {
      throw new sfException('A field must be an instance of sfWidget.');
    }

    if (!isset($this->fields[$name]))
    {
      $this->positions[] = $name;
    }

    $this->fields[$name] = $widget;
  }

  /**
   * Removes a field by name (implements the ArrayAccess interface).
   *
   * @param string
   */
  public function offsetUnset($name)
  {
    unset($this->fields[$name]);
    if (false !== $position = array_search($name, $this->positions))
    {
      unset($this->positions[$position]);
    }
  }

  /**
   * Returns an array of fields.
   *
   * @return sfWidget An array of sfWidget instance
   */
  public function getFields()
  {
    return $this->fields;
  }

  /**
   * Gets the positions of the fields.
   *
   * The field positions are only used when rendering the schema with ->render().
   *
   * @return array An ordered array of field names
   */
  public function getPositions()
  {
    return $this->positions;
  }

  /**
   * Sets the positions of the fields.
   *
   * @param array An ordered array of field names
   *
   * @see getPositions()
   */
  public function setPositions($positions)
  {
    $positions = array_values($positions);
    if (array_diff($positions, array_keys($this->fields)) || array_diff(array_keys($this->fields), $positions))
    {
      throw new sfException('Positions must contains all field names.');
    }

    $this->positions = $positions;
  }

  /**
   * Moves a field in a given position
   *
   * Available actions are:
   *
   *  * sfWidgetFormSchema::BEFORE
   *  * sfWidgetFormSchema::AFTER
   *  * sfWidgetFormSchema::LAST
   *  * sfWidgetFormSchema::FIRST
   *
   * @param string   The field name to move
   * @param constant The action (see above for all possible actions)
   * @param string   The field name used for AFTER and BEFORE actions
   */
  public function moveField($field, $action, $pivot = null)
  {
    if (false === $fieldPosition = array_search($field, $this->positions))
    {
      throw new sfException(sprintf('Field "%s" does not exist.', $field));
    }
    unset($this->positions[$fieldPosition]);
    $this->positions = array_values($this->positions);

    if (!is_null($pivot))
    {
      if (false === $pivotPosition = array_search($pivot, $this->positions))
      {
        throw new sfException(sprintf('Field "%s" does not exist.', $pivot));
      }
    }

    switch ($action)
    {
      case sfWidgetFormSchema::FIRST:
        array_unshift($this->positions, $field);
        break;
      case sfWidgetFormSchema::LAST:
        array_push($this->positions, $field);
        break;
      case sfWidgetFormSchema::BEFORE:
        if (is_null($pivot))
        {
          throw new sfException(sprintf('Unable to move field "%s" without a relative field.', $field));
        }
        $this->positions = array_merge(
          array_slice($this->positions, 0, $pivotPosition),
          array($field),
          array_slice($this->positions, $pivotPosition)
        );
        break;
      case sfWidgetFormSchema::AFTER:
        if (is_null($pivot))
        {
          throw new sfException(sprintf('Unable to move field "%s" without a relative field.', $field));
        }
        $this->positions = array_merge(
          array_slice($this->positions, 0, $pivotPosition + 1),
          array($field),
          array_slice($this->positions, $pivotPosition + 1)
        );
        break;
      default:
        throw new sfException(sprintf('Unknown move operation for field "%s".', $field));
    }
  }
}
