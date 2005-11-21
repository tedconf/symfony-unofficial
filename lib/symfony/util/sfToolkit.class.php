<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004, 2005 Sean Kerr.
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfToolkit provides basic utility methods.
 *
 * @package    symfony
 * @subpackage util
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id: sfView.class.php 422 2005-09-03 16:11:31Z fabien $
 */
class sfToolkit
{
  /**
   * Extract the class or interface name from filename.
   *
   * @param string A filename.
   *
   * @return string A class or interface name, if one can be extracted, otherwise null.
   */
  public static function extractClassName ($filename)
  {
    $retval = null;

    if (self::isPathAbsolute($filename))
      $filename = basename($filename);

    $pattern = '/(.*?)\.(class|interface)\.php/i';

    if (preg_match($pattern, $filename, $match))
      $retval = $match[1];

    return $retval;
  }

  /**
   * Clear all files in a given directory.
   *
   * @param  string An absolute filesystem path to a directory.
   *
   * @return void
   */
  public static function clearDirectory ($directory)
  {
    if (!is_dir($directory))
    {
      return;
    }

    // open a file point to the cache dir
    $fp = opendir($directory);

    // ignore names
    $ignore = array('.', '..', 'CVS', '.svn');

    while (($file = readdir($fp)) !== false)
    {
      if (!in_array($file, $ignore))
      {
        if (is_link($directory.'/'.$file))
        {
          // delete symlink
          unlink($directory.'/'.$file);
        }
        else if (is_dir($directory.'/'.$file))
        {
          // recurse through directory
          self::clearDirectory($directory.'/'.$file);

          // delete the directory
          rmdir($directory.'/'.$file);
        }
        else
        {
          // delete the file
          unlink($directory.'/'.$file);
        }
      }
    }

    // close file pointer
    fclose($fp);
  }

  /**
   * Determine if a filesystem path is absolute.
   *
   * @param path A filesystem path.
   *
   * @return bool true, if the path is absolute, otherwise false.
   */
  public static function isPathAbsolute ($path)
  {
    if ($path{0} == '/' || $path{0} == '\\' ||
        (strlen($path) > 3 && ctype_alpha($path{0}) &&
         $path{1} == ':' &&
         ($path{2} == '\\' || $path{2} == '/')
        )
       )
    {
      return true;
    }

    return false;
  }

  /**
   * Determine if a lock file is present.
   *
   * @param integer A max amount of life time for the lock file.
   *
   * @return bool true, if the lock file is present, otherwise false.
   */
  public static function hasLockFile($lockFile, $maxLockFileLifeTime)
  {
    $isLocked = false;
    if (is_readable($lockFile) && ($last_access = fileatime($lockFile)))
    {
      $now = time();
      $timeDiff = $now - $last_access;

      if ($timeDiff < $maxLockFileLifeTime)
      {
        $isLocked = true;
      }
      else
      {
        unlink($lockFile);
      }
    }

    return $isLocked;
  }

  public static function stripComments ($source)
  {
    /* T_ML_COMMENT does not exist in PHP 5.
     * The following three lines define it in order to
     * preserve backwards compatibility.
     *
     * The next two lines define the PHP 5-only T_DOC_COMMENT,
     * which we will mask as T_ML_COMMENT for PHP 4.
     */
    if (!defined('T_ML_COMMENT'))
    {
      define('T_ML_COMMENT', T_COMMENT);
    }
    else
    {
      if (!defined('T_DOC_COMMENT')) define('T_DOC_COMMENT', T_ML_COMMENT);
    }

    $output = '';

    $tokens = token_get_all($source);
    foreach ($tokens as $token)
    {
      if (is_string($token))
      {
        // simple 1-character token
        $output .= $token;
      }
      else
      {
        // token array
        list($id, $text) = $token;
        switch ($id)
        {
          case T_COMMENT:
          case T_ML_COMMENT:  // we've defined this
          case T_DOC_COMMENT: // and this
            // no action on comments
            break;
          default:
            // anything else -> output "as is"
            $output .= $text;
            break;
        }
      }
    }

    return $output;
  }

  public static function array_deep_merge()
  {
    switch (func_num_args())
    {
      case 0:
        return false;
        break;
      case 1:
        return func_get_arg(0);
        break;
      case 2:
        $args = func_get_args();
        $args[2] = array();
        if( is_array($args[0]) && is_array($args[1]))
        {
          foreach (array_unique(array_merge(array_keys($args[0]),array_keys($args[1]))) as $key)
          {
            if( is_string($key) && isset($args[0][$key]) && isset($args[1][$key]) && is_array($args[0][$key]) && is_array($args[1][$key]) )
            {
              $args[2][$key] = sfToolkit::array_deep_merge( $args[0][$key], $args[1][$key] );
            }
            else if( is_string($key) && isset($args[0][$key]) && isset($args[1][$key]) )
            {
              $args[2][$key] = $args[1][$key];
            }
            else if( is_integer($key) && isset($args[0][$key]) && isset($args[1][$key]) )
            {
              $args[2][] = $args[0][$key];
              $args[2][] = $args[1][$key];
            }
            else if( is_integer($key) && isset($args[0][$key]) )
            {
              $args[2][] = $args[0][$key];
            }
            else if( is_integer($key) && isset($args[1][$key]) )
            {
              $args[2][] = $args[1][$key];
            }
            else if( ! isset($args[1][$key]) )
            {
              $args[2][$key] = $args[0][$key];
            }
            else if( ! isset($args[0][$key]) )
            {
              $args[2][$key] = $args[1][$key];
            }
          }
          return $args[2];
        }
        else
        {
          return $args[1];
        }
        break;
      default :
        $args = func_get_args();
        $args[1] = self::array_deep_merge($args[0], $args[1]);
        array_shift($args);
//        return call_user_func_array(array(self, 'array_deep_merge'), $args);
        return $args;
        break;
    }
  }
}

?>