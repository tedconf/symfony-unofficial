<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004 David Heinemeier Hansson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * JavascriptHelper.
 *
 * @package    symfony
 * @subpackage helper
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     John Christopher <john.christopher@symfony-project.com>
 * @author     David Heinemeier Hansson
 * @version    SVN: $Id$
 */

/*
 * Provides a set of helpers for calling JavaScript functions and, most importantly,
 * to call remote methods using what has been labelled AJAX[http://www.adaptivepath.com/publications/essays/archives/000385.php].
 * This means that you can call actions in your controllers without reloading the page,
 * but still update certain parts of it using injections into the DOM.
 * The common use case is having a form that adds a new element to a list without reloading the page.
 *
 * To be able to use the JavaScript helpers, you must include the Prototype JavaScript Framework
 * and for some functions script.aculo.us (which both come with symfony) on your pages.
 * Choose one of these options:
 *
 * * Use <tt><?php echo javascript_include_tag :defaults ?></tt> in the HEAD section of your page (recommended):
 *   The function will return references to the JavaScript files created by the +rails+ command in your
 *   <tt>public/javascripts</tt> directory. Using it is recommended as the browser can then cache the libraries
 *   instead of fetching all the functions anew on every request.
 * * Use <tt><?php echo javascript_include_tag 'prototype' ?></tt>: As above, but will only include the Prototype core library,
 *   which means you are able to use all basic AJAX functionality. For the script.aculo.us-based JavaScript helpers,
 *   like visual effects, autocompletion, drag and drop and so on, you should use the method described above.
 * * Use <tt><?php echo define_javascript_functions ?></tt>: this will copy all the JavaScript support functions within a single
 *   script block.
 *
 * For documentation on +javascript_include_tag+ see ActionView::Helpers::AssetTagHelper.
 *
 * If you're the visual type, there's an AJAX movie[http://www.rubyonrails.com/media/video/rails-ajax.mov] demonstrating
 * the use of form_remote_tag.
 */

  function get_callbacks()
  {
    static $callbacks;
    if (!$callbacks)
    {
      $callbacks = array_merge(array(
        'uninitialized', 'loading', 'loaded', 'interactive', 'complete', 'failure', 'success'
        ), range(100, 599));
    }

    return $callbacks;
  }

  function get_ajax_options()
  {
    static $ajaxOptions;
    if (!$ajaxOptions)
    {
      $ajaxOptions = array_merge(array(
        'before', 'after', 'condition', 'url', 'asynchronous', 'method',
        'insertion', 'position', 'form', 'with', 'update', 'script'
        ), get_callbacks());
    }

    return $ajaxOptions;
  }

  /**
   * Returns a link that'll trigger a javascript function using the
   * onclick handler and return false after the fact.
   *
   * Examples:
   *   <?php echo link_to_function('Greeting', "alert('Hello world!')") ?>
   *   <?php echo link_to_function(image_tag('delete'), "if confirm('Really?'){ do_delete(); }") ?>
   */
  function link_to_function($name, $function, $htmlOptions = array())
  {
    $htmlOptions = _parse_attributes($htmlOptions);

    $htmlOptions['href'] = '#';
    $htmlOptions['onclick'] = $function.'; return false;';

    return content_tag('a', $name, $htmlOptions);
  }

  /**
   * Returns a button that'll trigger a javascript function using the
   * onclick handler and return false after the fact.
   *
   * Examples:
   *   <?php echo button_to_function('Greeting', "alert('Hello world!')") ?>
   */
  function button_to_function($name, $function, $htmlOptions = array())
  {
    $htmlOptions = _parse_attributes($htmlOptions);

    $htmlOptions['onclick'] = $function.'; return false;';
    $htmlOptions['type']    = 'button';
    $htmlOptions['value']   = $name;

    return content_tag('input', '', $htmlOptions);
  }

  /**
   * Returns a link to a remote action defined by 'url'
   * (using the 'url_for()' format) that's called in the background using
   * XMLHttpRequest. The result of that request can then be inserted into a
   * DOM object whose id can be specified with 'update'.
   * Usually, the result would be a partial prepared by the controller with
   * either 'render_partial()'.
   *
   * Examples:
   *  <?php echo link_to_remote('Delete this post'), array(
   *    'update' => 'posts',
   *    'url'    => 'destroy?id='.$post.id,
   *  )) ?>
   *  <?php echo link_to_remote(image_tag('refresh'), array(
   *    'update' => 'emails',
   *    'url'    => '@list_emails',
   *  )) ?>
   *
   * You can also specify a hash for 'update' to allow for
   * easy redirection of output to an other DOM element if a server-side error occurs:
   *
   * Example:
   *  <?php echo link_to_remote('Delete this post', array(
   *      'update' => array('success' => 'posts', 'failure' => 'error'),
   *      'url'    => 'destroy?id='.$post.id,
   *  )) ?>
   *
   * Optionally, you can use the 'position' parameter to influence
   * how the target DOM element is updated. It must be one of
   * 'before', 'top', 'bottom', or 'after'.
   *
   * By default, these remote requests are processed asynchronous during
   * which various JavaScript callbacks can be triggered (for progress indicators and
   * the likes). All callbacks get access to the 'request' object,
   * which holds the underlying XMLHttpRequest.
   *
   * To access the server response, use 'request.responseText', to
   * find out the HTTP status, use 'request.status'.
   *
   * Example:
   *  <?php echo link_to_remote($word, array(
   *    'url'      => '@undo?n='.$wordCounter,
   *    'complete' => 'undoRequestCompleted(request)'
   *  )) ?>
   *
   * The callbacks that may be specified are (in order):
   *
   * 'loading'                 Called when the remote document is being
   *                           loaded with data by the browser.
   * 'loaded'                  Called when the browser has finished loading
   *                           the remote document.
   * 'interactive'             Called when the user can interact with the
   *                           remote document, even though it has not
   *                           finished loading.
   * 'success'                 Called when the XMLHttpRequest is completed,
   *                           and the HTTP status code is in the 2XX range.
   * 'failure'                 Called when the XMLHttpRequest is completed,
   *                           and the HTTP status code is not in the 2XX
   *                           range.
   * 'complete'                Called when the XMLHttpRequest is complete
   *                           (fires after success/failure if they are present).,
   *
   * You can further refine 'success' and 'failure' by adding additional
   * callbacks for specific status codes:
   *
   * Example:
   *  <?php echo link_to_remote($word, array(
   *       'url'     => '@rule',
   *       '404'     => "alert('Not found...? Wrong URL...?')",
   *       'failure' => "alert('HTTP Error ' + request.status + '!')",
   *  )) ?>
   *
   * A status code callback overrides the success/failure handlers if present.
   *
   * If you for some reason or another need synchronous processing (that'll
   * block the browser while the request is happening), you can specify
   * 'type' => 'synchronous'.
   *
   * You can customize further browser side call logic by passing
   * in JavaScript code snippets via some optional parameters. In
   * their order of use these are:
   *
   * 'confirm'             Adds confirmation dialog.
   * 'condition'           Perform remote request conditionally
   *                       by this expression. Use this to
   *                       describe browser-side conditions when
   *                       request should not be initiated.
   * 'before'              Called before request is initiated.
   * 'after'               Called immediately after request was
   *                       initiated and before 'loading'.
   * 'submit'              Specifies the DOM element ID that's used
   *                       as the parent of the form elements. By
   *                       default this is the current form, but
   *                       it could just as well be the ID of a
   *                       table row or any other DOM element.
   */
  function link_to_remote($name, $options = array(), $htmlOptions = array())
  {
    return link_to_function($name, remote_function($options), $htmlOptions);
  }

  /**
   * Periodically calls the specified url ('url') every 'frequency' seconds (default is 10).
   * Usually used to update a specified div ('update') with the results of the remote call.
   * The options for specifying the target with 'url' and defining callbacks is the same as 'link_to_remote()'.
   */
  function periodically_call_remote($options = array())
  {
    $frequency = isset($options['frequency']) ? $options['frequency'] : 10; // every ten seconds by default
    $code = 'new PeriodicalExecuter(function() {'.remote_function($options).'}, '.$frequency.')';

    return javascript_tag($code);
  }

  /**
   * Returns a form tag that will submit using XMLHttpRequest in the background instead of the regular
   * reloading POST arrangement. Even though it's using JavaScript to serialize the form elements, the form submission
   * will work just like a regular submission as viewed by the receiving side (all elements available in 'params').
   * The options for specifying the target with 'url' and defining callbacks are the same as 'link_to_remote()'.
   *
   * A "fall-through" target for browsers that don't do JavaScript can be specified
   * with the 'action'/'method' options on '$optionsHtml'
   *
   * Example:
   *  <?php echo form_remote_tag(array(
   *    'url'      => '@tag_add',
   *    'update'   => 'question_tags',
   *    'loading'  => "Element.show('indicator'); \$('tag').value = ''",
   *    'complete' => "Element.hide('indicator');".visual_effect('highlight', 'question_tags'),
   *  )) ?>
   *
   * The hash passed as a second argument is equivalent to the options (2nd) argument in the form_tag() helper.
   *
   * By default the fall-through action is the same as the one specified in the 'url'
   * (and the default method is 'post').
   */
  function form_remote_tag($options = array(), $optionsHtml = array())
  {
    $options = _parse_attributes($options);
    $optionsHtml = _parse_attributes($optionsHtml);

    $options['form'] = true;

    $optionsHtml['onsubmit'] = remote_function($options).'; return false;';
    $optionsHtml['action'] = isset($optionsHtml['action']) ? $optionsHtml['action'] : url_for($options['url']);
    $optionsHtml['method'] = isset($optionsHtml['method']) ? $optionsHtml['method'] : 'post';

    return tag('form', $optionsHtml, true);
  }

  /**
   *  Returns a button input tag that will submit form using XMLHttpRequest in the background instead of regular
   *  reloading POST arrangement. The '$options' argument is the same as in 'form_remote_tag()'.
   */
  function submit_to_remote($name, $value, $options = array())
  {
    if (!isset($options['with']))
    {
      $options['with'] = 'Form.serialize(this.form)';
    }

    if (!isset($options['html']))
    {
      $options['html'] = array();
    }
    $options['html']['type'] = 'button';
    $options['html']['onclick'] = remote_function($options).'; return false;';
    $options['html']['name'] = $name;
    $options['html']['value'] = $value;

    return tag('input', $options['html'], false);
  }

  /**
   * Returns a Javascript function (or expression) that will update a DOM element '$elementId'
   * according to the '$options' passed.
   *
   * Possible '$options' are:
   * 'content'            The content to use for updating. Can be left out if using block, see example.
   * 'action'             Valid options are 'update' (assumed by default), 'empty', 'remove'
   * 'position'           If the 'action' is 'update', you can optionally specify one of the following positions:
   *                      'before', 'top', 'bottom', 'after'.
   *
   * Example:
   *   <?php echo javascript_tag(
   *      update_element_function('products', array(
   *            'position' => 'bottom',
   *            'content'  => "<p>New product!</p>",
   *      ))
   *   ) ?>
   *
   *
   * This method can also be used in combination with remote method call
   * where the result is evaluated afterwards to cause multiple updates on a page.
   *
   * Example:
   *
   *  # Calling view
   *  <?php echo form_remote_tag(array(
   *      'url'      => '@buy',
   *      'complete' => evaluate_remote_response()
   *  )) ?>
   *  all the inputs here...
   *
   *  # Target action
   *  public function executeBuy()
   *  {
   *     $this->product = ProductPeer::retrieveByPk(1);
   *  }
   *
   *  # Returning view
   *  <php echo update_element_function('cart', array(
   *      'action'   => 'update',
   *      'position' => 'bottom',
   *      'content'  => '<p>New Product: '.$product->getName().'</p>',
   *  )) ?>
   */
  function update_element_function($elementId, $options = array())
  {
    sfContext::getInstance()->getResponse()->addJavascript('/sf/js/prototype/prototype');

    $content = escape_javascript(isset($options['content']) ? $options['content'] : '');

    $value = isset($options['action']) ? $options['action'] : 'update';
    switch ($value)
    {
      case 'update':
        if (isset($options['position']) && $options['position'])
        {
          $javascriptFunction = "new Insertion.".sfInflector::camelize($options['position'])."('$elementId','$content')";
        }
        else
        {
          $javascriptFunction = "\$('$elementId').innerHTML = '$content'";
        }
        break;

      case 'empty':
        $javascriptFunction = "\$('$elementId').innerHTML = ''";
        break;

      case 'remove':
        $javascriptFunction = "Element.remove('$elementId')";
        break;

      default:
        throw new sfException('Invalid action, choose one of update, remove, empty');
    }

    $javascriptFunction .= ";\n";

    return (isset($options['binding']) ? $javascriptFunction.$options['binding'] : $javascriptFunction);
  }

  /**
   * Returns 'eval(request.responseText)', which is the Javascript function that
   * 'form_remote_tag()' can call in 'complete' to evaluate a multiple update return document
   * using 'update_element_function()' calls.
   */
  function evaluate_remote_response()
  {
    return 'eval(request.responseText)';
  }

  /**
   * Returns the javascript needed for a remote function.
   * Takes the same arguments as 'link_to_remote()'.
   *
   * Example:
   *   <select id="options" onchange="<?= remote_function(array('update' => 'options', 'url' => '@update_options')) ?>">
   *     <option value="0">Hello</option>
   *     <option value="1">World</option>
   *   </select>
   */
  function remote_function($options)
  {
    sfContext::getInstance()->getResponse()->addJavascript('/sf/js/prototype/prototype');

    $javascriptOptions = _options_for_ajax($options);

    $update = '';
    if (isset($options['update']) && is_array($options['update']))
    {
      $update = array();
      if (isset($options['update']['success']))
      {
        $update[] = "success:'".$options['update']['success']."'";
      }
      if (isset($options['update']['failure']))
      {
        $update[] = "failure:'".$options['update']['failure']."'";
      }
      $update = '{'.join(',', $update).'}';
    }
    elseif (isset($options['update']))
    {
      $update .= "'".$options['update']."'";
    }

    $function = !$update ?  "new Ajax.Request(" : "new Ajax.Updater($update, ";

    $function .= '\''.url_for($options['url']).'\'';
    $function .= ', '.$javascriptOptions.')';

    if (isset($options['before']))
    {
      $function = $options['before'].'; '.$function;
    }
    if (isset($options['after']))
    {
      $function = $function.'; '.$options['after'];
    }
    if (isset($options['condition']))
    {
      $function = 'if ('.$options['condition'].') { '.$function.'; }';
    }
    if (isset($options['confirm']))
    {
      $function = "if (confirm('".escape_javascript($options['confirm'])."')) { $function; }";
      if (isset($options['cancel']))
      {
        $function = $function.' else { '.$options['cancel'].' }';
      }
    }

    return $function;
  }

  /**
   * Observes the field with the DOM ID specified by '$fieldId' and makes
   * an AJAX call when its contents have changed.
   *
   * Required '$options' are:
   * 'url'                 'url_for()'-style options for the action to call
   *                       when the field has changed.
   *
   * Additional options are:
   * 'frequency'           The frequency (in seconds) at which changes to
   *                       this field will be detected. Not setting this
   *                       option at all or to a value equal to or less than
   *                       zero will use event based observation instead of
   *                       time based observation.
   * 'update'              Specifies the DOM ID of the element whose
   *                       innerHTML should be updated with the
   *                       XMLHttpRequest response text.
   * 'with'                A JavaScript expression specifying the
   *                       parameters for the XMLHttpRequest. This defaults
   *                       to 'value', which in the evaluated context
   *                       refers to the new field value.
   *
   * Additionally, you may specify any of the options documented in
   * link_to_remote().
   */
  function observe_field($fieldId, $options = array())
  {
    sfContext::getInstance()->getResponse()->addJavascript('/sf/js/prototype/prototype');

    if (isset($options['frequency']) && $options['frequency'] > 0)
    {
      return _build_observer('Form.Element.Observer', $fieldId, $options);
    }
    else
    {
      return _build_observer('Form.Element.EventObserver', $fieldId, $options);
    }
  }

  /**
   * Like 'observe_field()', but operates on an entire form identified by the
   * DOM ID '$formId'. '$options' are the same as 'observe_field()', except
   * the default value of the 'with' option evaluates to the
   * serialized (request string) value of the form.
   */
  function observe_form($formId, $options = array())
  {
    sfContext::getInstance()->getResponse()->addJavascript('/sf/js/prototype/prototype');

    if (isset($options['frequency']) && $options['frequency'] > 0)
    {
      return _build_observer('Form.Observer', $formId, $options);
    }
    else
    {
      return _build_observer('Form.EventObserver', $formId, $options);
    }
  }

  /**
   * Returns a JavaScript snippet to be used on the AJAX callbacks for starting
   * visual effects.
   *
   * Example:
   *  <?php echo link_to_remote('Reload', array(
   *        'update'  => 'posts',
   *        'url'     => '@reload',
   *        'complete => visual_effect('highlight', 'posts', array('duration' => 0.5 )),
   *  )) ?>
   *
   * If no '$elementId' is given, it assumes "element" which should be a local
   * variable in the generated JavaScript execution context. This can be used
   * for example with drop_receiving_element():
   *
   *  <?php echo drop_receving_element( ..., array(
   *        ...
   *        'loading' => visual_effect('fade'),
   *  )) ?>
   *
   * This would fade the element that was dropped on the drop receiving element.
   *
   * You can change the behaviour with various options, see
   * http://script.aculo.us for more documentation.
   */
  function visual_effect($name, $elementId = false, $jsOptions = array())
  {
    $response = sfContext::getInstance()->getResponse();
    $response->addJavascript('/sf/js/prototype/prototype');
    $response->addJavascript('/sf/js/prototype/builder');
    $response->addJavascript('/sf/js/prototype/effects');

    $element = $elementId ? "'$elementId'" : 'element';

    if (in_array($name, array('toggle_appear', 'toggle_blind', 'toggle_slide')))
    {
      return "new Effect.toggle($element, '".substr($name, 7)."', "._options_for_javascript($jsOptions).");";
    }
    else
    {
      return "new Effect.".sfInflector::camelize($name)."($element, "._options_for_javascript($jsOptions).");";
    }
  }

  /**
   * Makes the elements with the DOM ID specified by '$elementId' sortable
   * by drag-and-drop and make an AJAX call whenever the sort order has
   * changed. By default, the action called gets the serialized sortable
   * element as parameters.
   *
   * Example:
   *   <php echo sortable_element($myList, array(
   *      'url' => '@order',
   *   )) ?>
   *
   * In the example, the action gets a '$myList' array parameter
   * containing the values of the ids of elements the sortable consists
   * of, in the current order.
   *
   * You can change the behaviour with various options, see
   * http://script.aculo.us for more documentation.
   */
  function sortable_element($elementId, $options = array())
  {
    $response = sfContext::getInstance()->getResponse();
    $response->addJavascript('/sf/js/prototype/prototype');
    $response->addJavascript('/sf/js/prototype/builder');
    $response->addJavascript('/sf/js/prototype/effects');
    $response->addJavascript('/sf/js/prototype/dragdrop');

    if (!isset($options['with']))
    {
      $options['with'] = "Sortable.serialize('$elementId')";
    }

    if (!isset($options['onUpdate']))
    {
      $options['onUpdate'] = "function(){".remote_function($options)."}";
    }

    foreach (get_ajax_options() as $key)
    {
      unset($options[$key]);
    }

    foreach (array('tag', 'overlap', 'constraint', 'handle') as $option)
    {
      if (isset($options[$option]))
      {
        $options[$option] = "'{$options[$option]}'";
      }
    }

    if (isset($options['containment']))
    {
      $options['containment'] = _array_or_string_for_javascript($options['containment']);
    }

    if (isset($options['hoverclass']))
    {
      $options['hoverclass'] = "'{$options['hoverclass']}'";
    }

    if (isset($options['only']))
    {
      $options['only'] = _array_or_string_for_javascript($options['only']);
    }

    return javascript_tag("Sortable.create('$elementId', "._options_for_javascript($options).")");
  }

  /**
   * Makes the element with the DOM ID specified by '$elementId' draggable.
   *
   * Example:
   *   <?php echo draggable_element('my_image', array(
   *      'revert' => true,
   *   )) ?>
   *
   * You can change the behaviour with various options, see
   * http://script.aculo.us for more documentation.
   */
  function draggable_element($elementId, $options = array())
  {
    $response = sfContext::getInstance()->getResponse();
    $response->addJavascript('/sf/js/prototype/prototype');
    $response->addJavascript('/sf/js/prototype/builder');
    $response->addJavascript('/sf/js/prototype/effects');
    $response->addJavascript('/sf/js/prototype/dragdrop');

    return javascript_tag("new Draggable('$elementId', "._options_for_javascript($options).")");
  }

  /**
   * Makes the element with the DOM ID specified by '$elementId' receive
   * dropped draggable elements (created by 'draggable_element()') and make an AJAX call.
   * By default, the action called gets the DOM ID of the element as parameter.
   *
   * Example:
   *   <?php drop_receiving_element('my_cart', array(
   *      'url' => 'cart/add',
   *   )) ?>
   *
   * You can change the behaviour with various options, see
   * http://script.aculo.us for more documentation.
   */
  function drop_receiving_element($elementId, $options = array())
  {
    $response = sfContext::getInstance()->getResponse();
    $response->addJavascript('/sf/js/prototype/prototype');
    $response->addJavascript('/sf/js/prototype/builder');
    $response->addJavascript('/sf/js/prototype/effects');
    $response->addJavascript('/sf/js/prototype/dragdrop');

    if (!isset($options['with']))
    {
      $options['with'] = "'id=' + encodeURIComponent(element.id)";
    }
    if (!isset($options['onDrop']))
    {
      $options['onDrop'] = "function(element){".remote_function($options)."}";
    }

    foreach (get_ajax_options() as $key)
    {
      unset($options[$key]);
    }

    if (isset($options['accept']))
    {
      $options['accept'] = _array_or_string_for_javascript($options['accept']);
    }

    if (isset($options['hoverclass']))
    {
      $options['hoverclass'] = "'{$options['hoverclass']}'";
    }

    return javascript_tag("Droppables.add('$elementId', "._options_for_javascript($options).")");
  }

  /**
   * Returns a JavaScript tag with the '$content' inside.
   * Example:
   *   <?php echo javascript_tag("alert('All is good')") ?>
   *   => <script type="text/javascript">alert('All is good')</script>
   */
  function javascript_tag($content)
  {
    return content_tag('script', javascript_cdata_section($content), array('type' => 'text/javascript'));
  }

  function javascript_cdata_section($content)
  {
    return "\n//".cdata_section("\n$content\n//")."\n";
  }

  /**
   * wrapper for script.aculo.us/prototype Ajax.Autocompleter.
   * @param string name value of input field
   * @param string default value for input field
   * @param array input tag options. (size, autocomplete, etc...)
   * @param array completion options. (use_style, etc...)
   *
   * @return string input field tag, div for completion results, and
   *                 auto complete javascript tags
   */
  function input_auto_complete_tag($name, $value, $url, $tagOptions = array(), $completionOptions = array())
  {
    $context = sfContext::getInstance();

    $response = $context->getResponse();
    $response->addJavascript('/sf/js/prototype/prototype');
    $response->addJavascript('/sf/js/prototype/controls');
    $response->addJavascript('/sf/js/prototype/effects');

    $compOptions = _convert_options($completionOptions);
    if (isset($compOptions['use_style']) && $compOptions['use_style'] == true)
    {
      $response->addStylesheet('/sf/css/sf_helpers/input_auto_complete_tag');
    }

    $javascript  = input_tag($name, $value, $tagOptions);
    $javascript .= content_tag('div', '' , array('id' => "{$name}_auto_complete", 'class' => 'auto_complete'));
    $javascript .= _auto_complete_field($name, $url, $compOptions);

    return $javascript;
  }

  /**
   * wrapper for script.aculo.us/prototype Ajax.Autocompleter.
   * @param string name id of field that can be edited
   * @param string url of module/action to be called when ok is clicked
   * @param array editor tag options. (rows, cols, highlightcolor, highlightendcolor, etc...)
   *
   * @return string javascript to manipulate the id field to allow click and edit functionality
   */
  function input_in_place_editor_tag($name, $url, $editorOptions = array())
  {
    $response = sfContext::getInstance()->getResponse();
    $response->addJavascript('/sf/js/prototype/prototype');
    $response->addJavascript('/sf/js/prototype/controls');
    $response->addJavascript('/sf/js/prototype/effects');

    $editorOptions = _convert_options($editorOptions);
    $defaultOptions = array('tag' => 'span', 'id' => '\''.$name.'_in_place_editor', 'class' => 'in_place_editor_field');

    return _in_place_editor($name, $url, array_merge($defaultOptions, $editorOptions));
  }

  /*
   * Makes an HTML element specified by the DOM ID '$fieldId' become an in-place
   * editor of a property.
   *
   * A form is automatically created and displayed when the user clicks the element,
   * something like this:
   * <form id="myElement-in-place-edit-form" target="specified url">
   *   <input name="value" text="The content of myElement"/>
   *   <input type="submit" value="ok"/>
   *   <a onclick="javascript to cancel the editing">cancel</a>
   * </form>
   *
   * The form is serialized and sent to the server using an AJAX call, the action on
   * the server should process the value and return the updated value in the body of
   * the reponse. The element will automatically be updated with the changed value
   * (as returned from the server).
   *
   * Required '$options' are:
   * 'url'                 Specifies the url where the updated value should
   *                       be sent after the user presses "ok".
   *
   * Addtional '$options' are:
   * 'rows'                Number of rows (more than 1 will use a TEXTAREA)
   * 'cancel_text'         The text on the cancel link. (default: "cancel")
   * 'save_text'           The text on the save link. (default: "ok")
   * 'external_control'    The id of an external control used to enter edit mode.
   * 'options'             Pass through options to the AJAX call (see prototype's Ajax.Updater)
   * 'with'                JavaScript snippet that should return what is to be sent
   *                       in the AJAX call, 'form' is an implicit parameter
   */
    function _in_place_editor($fieldId, $url, $options = array())
    {
      $javascript = "new Ajax.InPlaceEditor(";

      $javascript .= "'$fieldId', ";
      $javascript .= "'" . url_for($url) . "'";

      $jsOptions = array();

      if (isset($options['tokens']))
      {
        $jsOptions['tokens'] = _array_or_string_for_javascript($options['tokens']);
      }

      if (isset($options['cancel_text']))
      {
        $jsOptions['cancelText'] = "'".$options['cancel_text']."'";
      }
      if (isset($options['save_text']))
      {
        $jsOptions['okText'] = "'".$options['save_text']."'";
      }
      if (isset($options['cols']))
      {
        $jsOptions['cols'] = $options['cols'];
      }
      if (isset($options['rows']))
      {
        $jsOptions['rows'] = $options['rows'];
      }
      if (isset($options['external_control']))
      {
        $jsOptions['externalControl'] = $options['external_control'];
      }
      if (isset($options['options']))
      {
        $jsOptions['ajaxOptions'] = $options['options'];
      }
      if (isset($options['with']))
      {
        $jsOptions['callback'] = "function(form) { return".$options['with']."}";
      }
      if (isset($options['highlightcolor']))
      {
        $jsOptions['highlightcolor'] = "'".$options['highlightcolor']."'";
      }
      if (isset($options['highlightendcolor']))
      {
        $jsOptions['highlightendcolor'] = "'".$options['highlightendcolor']."'";
      }
      if(isset($options['loadTextURL']))
      {
        $jsOptions['loadTextURL'] =  "'".$options['loadTextURL']."'";
      }

      $javascript .= ', '._options_for_javascript($jsOptions);
      $javascript .= ');';

      return javascript_tag($javascript);
    }

  /**
   * wrapper for script.aculo.us/prototype Ajax.Autocompleter.
   * @param string id value of input field
   * @param string url of module/action to execute for autocompletion
   * @param array completion options
   * @return string javascript tag for Ajax.Autocompleter
   */
  function _auto_complete_field($fieldId, $url, $options = array())
  {
    $javascript = "new Ajax.Autocompleter(";

    $javascript .= "'$fieldId', ";
    if (isset($options['update']))
    {
      $javascript .= "'".$options['update']."', ";
    }
    else
    {
      $javascript .= "'{$fieldId}_auto_complete', ";
    }

    $javascript .= "'".url_for($url)."'";

    $jsOptions = array();
    if (isset($options['tokens']))
    {
      $jsOptions['tokens'] = _array_or_string_for_javascript($options['tokens']);
    }
    if (isset ($options['with']))
    {
      $jsOptions['callback'] = "function(element, value) { return".$options['with']."}";
    }
    if (isset($options['indicator']))
    {
      $jsOptions['indicator']  = "'".$options['indicator']."'";
    }
    if (isset($options['on_show']))
    {
      $jsOptions['onShow'] = $options['on_show'];
    }
    if (isset($options['on_hide']))
    {
      $jsOptions['onHide'] = $options['on_hide'];
    }
    if (isset($options['min_chars']))
    {
      $jsOptions['min_chars'] = $options['min_chars'];
    }

    $javascript .= ', '._options_for_javascript($jsOptions).');';

    return javascript_tag($javascript);
  }

  function _options_for_javascript($options)
  {
    $opts = array();
    foreach ($options as $key => $value)
    {
      $opts[] = "$key:$value";
    }
    sort($opts);

    return '{'.join(', ', $opts).'}';
  }

  function _array_or_string_for_javascript($option)
  {
    if (is_array($option))
    {
      return "['".join('\',\'', $option)."']";
    }
    elseif ($option)
    {
      return "'$option'";
    }
  }

  function _options_for_ajax($options)
  {
    $jsOptions = _build_callbacks($options);

    $jsOptions['asynchronous'] = (isset($options['type'])) ? ($options['type'] != 'synchronous') : 'true';
    if (isset($options['method']))
    {
      $jsOptions['method'] = _method_option_to_s($options['method']);
    }
    if (isset($options['position']))
    {
      $jsOptions['insertion'] = "Insertion.".sfInflector::camelize($options['position']);
    }
    $jsOptions['evalScripts'] = (!isset($options['script']) || $options['script'] == '0' || $options['script'] == false) ? 'false' : 'true';

    if (isset($options['form']))
    {
      $jsOptions['parameters'] = 'Form.serialize(this)';
    }
    elseif (isset($options['submit']))
    {
      $jsOptions['parameters'] = "Form.serialize(document.getElementById('{$options['submit']}'))";
    }
    elseif (isset($options['with']))
    {
      $jsOptions['parameters'] = $options['with'];
    }

    return _options_for_javascript($jsOptions);
  }

  function _method_option_to_s($method)
  {
    return (is_string($method) && $method[0] != "'") ? $method : "'$method'";
  }

  function _build_observer($klass, $name, $options = array())
  {
    if (!isset($options['with']) && $options['update'])
    {
      $options['with'] = 'value';
    }

    $callback = remote_function($options);

    $javascript  = 'new '.$klass.'("'.$name.'", ';
    if (isset($options['frequency']))
    {
      $javascript .= $options['frequency'].", ";
    }
    $javascript .= "function(element, value) {";
    $javascript .= $callback.'});';

    return javascript_tag($javascript);
  }

  function _build_callbacks($options)
  {
    $callbacks = array();
    foreach (get_callbacks() as $callback)
    {
      if (isset($options[$callback]))
      {
        $name = 'on'.ucfirst($callback);
        $code = $options[$callback];
        $callbacks[$name] = 'function(request, json){'.$code.'}';
      }
    }

    return $callbacks;
  }
?>