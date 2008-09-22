<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebResponse class.
 *
 * This class manages web reponses. It supports cookies and headers management.
 *
 * @package    symfony
 * @subpackage response
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfWebResponse extends sfResponse
{
  protected
    $cookies     = array(),
    $statusCode  = 200,
    $statusText  = 'OK',
    $headerOnly  = false,
    $headers     = array(),
    $metas       = array(),
    $httpMetas   = array(),
    $title       = '',
    $positions   = array('first', '', 'last'),
    $stylesheets = array(),
    $javascripts = array(),
    $slots       = array();

  static protected $statusTexts = array(
    '100' => 'Continue',
    '101' => 'Switching Protocols',
    '200' => 'OK',
    '201' => 'Created',
    '202' => 'Accepted',
    '203' => 'Non-Authoritative Information',
    '204' => 'No Content',
    '205' => 'Reset Content',
    '206' => 'Partial Content',
    '300' => 'Multiple Choices',
    '301' => 'Moved Permanently',
    '302' => 'Found',
    '303' => 'See Other',
    '304' => 'Not Modified',
    '305' => 'Use Proxy',
    '306' => '(Unused)',
    '307' => 'Temporary Redirect',
    '400' => 'Bad Request',
    '401' => 'Unauthorized',
    '402' => 'Payment Required',
    '403' => 'Forbidden',
    '404' => 'Not Found',
    '405' => 'Method Not Allowed',
    '406' => 'Not Acceptable',
    '407' => 'Proxy Authentication Required',
    '408' => 'Request Timeout',
    '409' => 'Conflict',
    '410' => 'Gone',
    '411' => 'Length Required',
    '412' => 'Precondition Failed',
    '413' => 'Request Entity Too Large',
    '414' => 'Request-URI Too Long',
    '415' => 'Unsupported Media Type',
    '416' => 'Requested Range Not Satisfiable',
    '417' => 'Expectation Failed',
    '500' => 'Internal Server Error',
    '501' => 'Not Implemented',
    '502' => 'Bad Gateway',
    '503' => 'Service Unavailable',
    '504' => 'Gateway Timeout',
    '505' => 'HTTP Version Not Supported',
  );

  /**
   * Initializes this sfWebResponse.
   *
   * Available options:
   *
   *  * charset:      The charset to use (utf-8 by default)
   *  * content_type: The content type (text/html by default)
   *
   * @param  sfEventDispatcher $dispatcher  An sfEventDispatcher instance
   * @param  array             $options     An array of options
   *
   * @return bool true, if initialization completes successfully, otherwise false
   *
   * @throws <b>sfInitializationException</b> If an error occurs while initializing this sfResponse
   *
   * @see sfResponse
   */
  public function initialize(sfEventDispatcher $dispatcher, $options = array())
  {
    parent::initialize($dispatcher, $options);

    $this->javascripts = array_combine($this->positions, array_fill(0, count($this->positions), array()));
    $this->stylesheets = array_combine($this->positions, array_fill(0, count($this->positions), array()));

    if (!isset($this->options['charset']))
    {
      $this->options['charset'] = 'utf-8';
    }

    $this->options['content_type'] = $this->fixContentType(isset($this->options['content_type']) ? $this->options['content_type'] : 'text/html');
  }

  /**
   * Sets if the response consist of just HTTP headers.
   *
   * @param bool $value
   */
  public function setHeaderOnly($value = true)
  {
    $this->headerOnly = (boolean) $value;
  }

  /**
   * Returns if the response must only consist of HTTP headers.
   *
   * @return bool returns true if, false otherwise
   */
  public function isHeaderOnly()
  {
    return $this->headerOnly;
  }

  /**
   * Sets a cookie.
   *
   * @param  string  $name      HTTP header name
   * @param  string  $value     Value for the cookie
   * @param  string  $expire    Cookie expiration period
   * @param  string  $path      Path
   * @param  string  $domain    Domain name
   * @param  bool    $secure    If secure
   * @param  bool    $httpOnly  If uses only HTTP
   *
   * @throws <b>sfException</b> If fails to set the cookie
   */
  public function setCookie($name, $value, $expire = null, $path = '/', $domain = '', $secure = false, $httpOnly = false)
  {
    if ($expire !== null)
    {
      if (is_numeric($expire))
      {
        $expire = (int) $expire;
      }
      else
      {
        $expire = strtotime($expire);
        if ($expire === false || $expire == -1)
        {
          throw new sfException(sprintf('Your expire parameter "%s" is not valid must be convertable in strtotime.', $expire));
        }
      }
    }

    $this->cookies[] = array(
      'name'     => $name,
      'value'    => $value,
      'expire'   => $expire,
      'path'     => $path,
      'domain'   => $domain,
      'secure'   => $secure ? true : false,
      'httpOnly' => $httpOnly,
    );
  }

  /**
   * Sets response status code.
   *
   * @param string $code  HTTP status code
   * @param string $name  HTTP status text
   *
   */
  public function setStatusCode($code, $name = null)
  {
    $this->statusCode = $code;
    $this->statusText = null !== $name ? $name : self::$statusTexts[$code];
  }

  /**
   * Retrieves status code for the current web response.
   *
   * @return string Status code
   */
  public function getStatusCode()
  {
    return $this->statusCode;
  }

  /**
   * Sets a HTTP header.
   *
   * @param string  $name     HTTP header name
   * @param string  $value    Value (if null, remove the HTTP header)
   * @param bool    $replace  Replace for the value
   *
   */
  public function setHttpHeader($name, $value, $replace = true)
  {
    $name = $this->normalizeHeaderName($name);

    if (is_null($value))
    {
      unset($this->headers[$name]);

      return;
    }

    if ('Content-Type' == $name)
    {
      if ($replace || !$this->getHttpHeader('Content-Type', null))
      {
        $this->setContentType($value);
      }

      return;
    }

    if (!$replace)
    {
      $current = isset($this->headers[$name]) ? $this->headers[$name] : '';
      $value = ($current ? $current.', ' : '').$value;
    }

    $this->headers[$name] = $value;
  }

  /**
   * Gets HTTP header current value.
   *
   * @param  string $name     HTTP header name
   * @param  string $default  Default value returned if named HTTP header is not found
   *
   * @return array
   */
  public function getHttpHeader($name, $default = null)
  {
    $name = $this->normalizeHeaderName($name);

    return isset($this->headers[$name]) ? $this->headers[$name] : $default;
  }

  /**
   * Checks if response has given HTTP header.
   *
   * @param  string $name  HTTP header name
   *
   * @return bool
   */
  public function hasHttpHeader($name)
  {
    return array_key_exists($this->normalizeHeaderName($name), $this->headers);
  }

  /**
   * Sets response content type.
   *
   * @param string $value  Content type
   *
   */
  public function setContentType($value)
  {
    $this->headers['Content-Type'] = $this->fixContentType($value);
  }

  /**
   * Gets response content type.
   *
   * @return array
   */
  public function getContentType()
  {
    return $this->getHttpHeader('Content-Type', $this->options['content_type']);
  }

  /**
   * Sends HTTP headers and cookies.
   *
   */
  public function sendHttpHeaders()
  {
    if (sfConfig::get('sf_test'))
    {
      return;
    }

    // status
    $status = 'HTTP/1.1 '.$this->statusCode.' '.$this->statusText;
    header($status);

    if ($this->options['logging'])
    {
      $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Sent status "%s"', $status))));
    }

    // headers
    if (!$this->getHttpHeader('Content-Type'))
    {
      $this->setContentType($this->options['content_type']);
    }
    foreach ($this->headers as $name => $value)
    {
      header($name.': '.$value);

      if ($value != '' && $this->options['logging'])
      {
        $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Sent header "%s": "%s"', $name, $value))));
      }
    }

    // cookies
    foreach ($this->cookies as $cookie)
    {
      setcookie($cookie['name'], $cookie['value'], $cookie['expire'], $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httpOnly']);

      if ($this->options['logging'])
      {
        $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Sent cookie "%s"', $cookie['name']))));
      }
    }
  }

  /**
   * Send content for the current web response.
   *
   */
  public function sendContent()
  {
    if (!$this->headerOnly)
    {
      parent::sendContent();
    }
  }

  /**
   * Sends the HTTP headers and the content.
   */
  public function send()
  {
    $this->sendHttpHeaders();
    $this->sendContent();
  }

  /**
   * Retrieves a normalized Header.
   *
   * @param  string $name  Header name
   *
   * @return string Normalized header
   */
  protected function normalizeHeaderName($name)
  {
    return preg_replace('/\-(.)/e', "'-'.strtoupper('\\1')", strtr(ucfirst(strtolower($name)), '_', '-'));
  }

  /**
   * Retrieves a formated date.
   *
   * @param  string $timetamp  Timestamp
   * @param  string $type      Format type
   *
   * @return string Formatted date
   */
  public function getDate($timestamp, $type = 'rfc1123')
  {
    $type = strtolower($type);

    if ($type == 'rfc1123')
    {
      return substr(gmdate('r', $timestamp), 0, -5).'GMT';
    }
    else if ($type == 'rfc1036')
    {
      return gmdate('l, d-M-y H:i:s ', $timestamp).'GMT';
    }
    else if ($type == 'asctime')
    {
      return gmdate('D M j H:i:s', $timestamp);
    }
    else
    {
      throw new InvalidArgumentException('The second getDate() method parameter must be one of: rfc1123, rfc1036 or asctime.');
    }
  }

  /**
   * Adds vary to a http header.
   *
   * @param string $header  HTTP header
   */
  public function addVaryHttpHeader($header)
  {
    $vary = $this->getHttpHeader('Vary');
    $currentHeaders = array();
    if ($vary)
    {
      $currentHeaders = split('/\s*,\s*/', $vary);
    }
    $header = $this->normalizeHeaderName($header);

    if (!in_array($header, $currentHeaders))
    {
      $currentHeaders[] = $header;
      $this->setHttpHeader('Vary', implode(', ', $currentHeaders));
    }
  }

  /**
   * Adds an control cache http header.
   *
   * @param string $name   HTTP header
   * @param string $value  Value for the http header
   */
  public function addCacheControlHttpHeader($name, $value = null)
  {
    $cacheControl = $this->getHttpHeader('Cache-Control');
    $currentHeaders = array();
    if ($cacheControl)
    {
      foreach (split('/\s*,\s*/', $cacheControl) as $tmp)
      {
        $tmp = explode('=', $tmp);
        $currentHeaders[$tmp[0]] = isset($tmp[1]) ? $tmp[1] : null;
      }
    }
    $currentHeaders[strtr(strtolower($name), '_', '-')] = $value;

    $headers = array();
    foreach ($currentHeaders as $key => $value)
    {
      $headers[] = $key.(null !== $value ? '='.$value : '');
    }

    $this->setHttpHeader('Cache-Control', implode(', ', $headers));
  }

  /**
   * Retrieves meta headers for the current web response.
   *
   * @return string Meta headers
   */
  public function getHttpMetas()
  {
    return $this->httpMetas;
  }

  /**
   * Adds a HTTP meta header.
   *
   * @param string  $key      Key to replace
   * @param string  $value    HTTP meta header value (if null, remove the HTTP meta)
   * @param bool    $replace  Replace or not
   */
  public function addHttpMeta($key, $value, $replace = true)
  {
    $key = $this->normalizeHeaderName($key);

    // set HTTP header
    $this->setHttpHeader($key, $value, $replace);

    if (is_null($value))
    {
      unset($this->httpMetas[$key]);

      return;
    }

    if ('Content-Type' == $key)
    {
      $value = $this->getContentType();
    }
    elseif (!$replace)
    {
      $current = isset($this->httpMetas[$key]) ? $this->httpMetas[$key] : '';
      $value = ($current ? $current.', ' : '').$value;
    }

    $this->httpMetas[$key] = $value;
  }

  /**
   * Retrieves all meta headers.
   *
   * @return array List of meta headers
   */
  public function getMetas()
  {
    return $this->metas;
  }

  /**
   * Adds a meta header.
   *
   * @param string  $key      Name of the header
   * @param string  $value    Meta header value (if null, remove the meta)
   * @param bool    $replace  true if it's replaceable
   * @param bool    $escape   true for escaping the header
   */
  public function addMeta($key, $value, $replace = true, $escape = true)
  {
    $key = strtolower($key);

    if (is_null($value))
    {
      unset($this->metas[$key]);

      return;
    }

    if ($escape)
    {
      $value = htmlspecialchars($value, ENT_QUOTES, $this->options['charset']);
    }

    $current = isset($this->metas[$key]) ? $this->metas[$key] : null;
    if ($replace || !$current)
    {
      $this->metas[$key] = $value;
    }
  }

  /**
   * Retrieves title for the current web response.
   *
   * @return string Title
   */
  public function getTitle()
  {
    return empty($this->title) ? '' : $this->title;
  }

  /**
   * Sets title for the current web response.
   *
   * @param string  $title   Title name
   * @param bool    $escape  true, for escaping the title
   */
  public function setTitle($title, $escape = true)
  {
    if ($escape)
    {
      $title = htmlspecialchars($title, ENT_QUOTES, $this->options['charset']);
    }

    $this->title = $title;
  }

  /**
   * Returns the available position names for stylesheets and javascripts in order.
   *
   * @return array An array of position names
   */
  public function getPositions()
  {
    return $this->positions;
  }

  /**
   * Retrieves stylesheets for the current web response.
   *
   * @param  string  $position
   *
   * @return string Stylesheets
   */
  public function getStylesheets($position = '')
  {
    if ($position == 'ALL')
    {
      return $this->stylesheets;
    }

    $this->validatePosition($position);

    return isset($this->stylesheets[$position]) ? $this->stylesheets[$position] : array();
  }

  /**
   * Adds a stylesheet to the current web response.
   *
   * @param string $css       Stylesheet
   * @param string $position  Position
   * @param string $options   Stylesheet options
   */
  public function addStylesheet($css, $position = '', $options = array())
  {
    $this->validatePosition($position);

    $this->stylesheets[$position][$css] = $options;
  }

  /**
   * Removes a stylesheet from the current web response.
   *
   * @param string $css       Stylesheet
   * @param string $position  Position
   */
  public function removeStylesheet($css, $position = '')
  {
    $this->validatePosition($position);

    unset($this->stylesheets[$position][$css]);
  }

  /**
   * Retrieves javascript code from the current web response.
   *
   * @param  string $position  Position
   *
   * @return string Javascript code
   */
  public function getJavascripts($position = '')
  {
    if ($position == 'ALL')
    {
      return $this->javascripts;
    }

    $this->validatePosition($position);

    return isset($this->javascripts[$position]) ? $this->javascripts[$position] : array();
  }

  /**
   * Adds javascript code to the current web response.
   *
   * @param string $js        Javascript code
   * @param string $position  Position
   * @param string $options   Javascript options
   */
  public function addJavascript($js, $position = '', $options = array())
  {
    $this->validatePosition($position);

    $this->javascripts[$position][$js] = $options;
  }

  /**
   * Removes javascript code from the current web response.
   *
   * @param string $js        Javascript code
   * @param string $position  Position
   */
  public function removeJavascript($js, $position = '')
  {
    $this->validatePosition($position);

    unset($this->javascripts[$position][$js]);
  }

  /**
   * Retrieves slots from the current web response.
   *
   * @return string Javascript code
   */
  public function getSlots()
  {
    return $this->slots;
  }

  /**
   * Sets a slot content.
   *
   * @param string $name     Slot name
   * @param string $content  Content
   */
  public function setSlot($name, $content)
  {
    if (is_null($content))
    {
      unset($this->slots[$name]);

      return;
    }
    $this->slots[$name] = $content;
  }

  /**
   * Retrieves cookies from the current web response.
   *
   * @return array Cookies
   */
  public function getCookies()
  {
    $cookies = array();
    foreach ($this->cookies as $cookie)
    {
      $cookies[$cookie['name']] = $cookie;
    }

    return $cookies;
  }

  /**
   * Retrieves HTTP headers from the current web response.
   *
   * @return string HTTP headers
   */
  public function getHttpHeaders()
  {
    return $this->headers;
  }

  /**
   * Cleans HTTP headers from the current web response.
   */
  public function clearHttpHeaders()
  {
    $this->headers = array();
  }

  /**
   * Copies all properties from a given sfWebResponse object to the current one.
   *
   * @param sfWebResponse $response  An sfWebResponse instance
   */
  public function copyProperties(sfWebResponse $response)
  {
    $this->options      = $response->getOptions();
    $this->headers      = $response->getHttpHeaders();
    $this->metas        = $response->getMetas();
    $this->title        = $response->getTitle();
    $this->httpMetas    = $response->getHttpMetas();
    $this->stylesheets  = $response->getStylesheets('ALL');
    $this->javascripts  = $response->getJavascripts('ALL');
    $this->slots        = $response->getSlots();
  }

  /**
   * Merges all properties from a given sfWebResponse object to the current one.
   *
   * @param sfWebResponse $response  An sfWebResponse instance
   */
  public function merge(sfWebResponse $response)
  {
    foreach ($this->getPositions() as $position)
    {
      $this->javascripts[$position] = array_merge($this->getJavascripts($position), $response->getJavascripts($position));
      $this->stylesheets[$position] = array_merge($this->getStylesheets($position), $response->getStylesheets($position));
    }

    $this->slots = array_merge($this->getSlots(), $response->getSlots());
  }

  /**
   * @see sfResponse
   */
  public function serialize()
  {
    return serialize(array($this->content, $this->statusCode, $this->statusText, $this->options, $this->cookies, $this->headerOnly, $this->headers, $this->metas, $this->title, $this->httpMetas, $this->stylesheets, $this->javascripts, $this->slots));
  }

  /**
   * @see sfResponse
   */
  public function unserialize($serialized)
  {
    list($this->content, $this->statusCode, $this->statusText, $this->options, $this->cookies, $this->headerOnly, $this->headers, $this->metas, $this->title, $this->httpMetas, $this->stylesheets, $this->javascripts, $this->slots) = unserialize($serialized);
  }

  /**
   * Validate a position name.
   *
   * @param  string $position
   *
   * @throws InvalidArgumentException if the position is not available
   */
  protected function validatePosition($position)
  {
    if (!in_array($position, $this->positions, true))
    {
      throw new InvalidArgumentException(sprintf('The position "%s" does not exist (available positions: %s).', $position, implode(', ', $this->positions)));
    }
  }

  /**
   * Fixes the content type by adding the charset for text content types.
   *
   * @param  string $content  The content type
   *
   * @return string The content type with the charset if needed
   */
  protected function fixContentType($contentType)
  {
    // add charset if needed (only on text content)
    if (false === stripos($contentType, 'charset') && (0 === stripos($contentType, 'text/') || strlen($contentType) - 3 === strripos($contentType, 'xml')))
    {
      $contentType .= '; charset='.$this->options['charset'];
    }

    return $contentType;
  }
}