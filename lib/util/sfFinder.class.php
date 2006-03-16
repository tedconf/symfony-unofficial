<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    symfony
 * @subpackage util
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */

/**
 *
 * Allow to build rules to find files and directories.
 *
 * All rules may be invoked several times, except for ->in() method.
 * Some rules are cumulative (->name() for example) whereas others are destructive
 * (most recent value is used, ->maxdepth() method for example).
 *
 * All methods return the current sfFinder object to allow easy chaining:
 *
 * $files = sfFinder::type('file')->name('*.php')->in(.);
 *
 * Interface loosely based on perl File::Find::Rule module.
 *
 * @package    symfony
 * @subpackage util
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfFinder
{
  private $type       = 'file';
  private $names      = array();
  private $prunes     = array();
  private $discards   = array();
  private $execs      = array();
  private $mindepth   = 0;
  private $sizes      = array();
  private $maxdepth   = 1000000;
  private $relative   = false;
  private $followLink = false;

  /**
   * Sets maximum directory depth.
   *
   * Finder will descend at most $level levels of directories below the starting point.
   *
   * @param  integer level
   * @return object current sfFinder object
   */
  public function maxdepth($level)
  {
    $this->maxdepth = $level;

    return $this;
  }

  /**
   * Sets minimum directory depth.
   *
   * Finder will start applying tests at level $level.
   *
   * @param  integer level
   * @return object current sfFinder object
   */
  public function mindepth($level)
  {
    $this->mindepth = $level;

    return $this;
  }

  public function get_type()
  {
    return $this->type;
  }

  /**
   * Sets the type of elements to returns.
   *
   * @param  string directory or file or any (for both file and directory)
   * @return object new sfFinder object
   */
  public static function type($name)
  {
    $finder = new sfFinder();

    if (strtolower(substr($name, 0, 3)) == 'dir')
    {
      $finder->type = 'directory';
    }
    elseif (strtolower($name) == 'any')
    {
      $finder->type = 'any';
    }
    else
    {
      $finder->type = 'file';
    }

    return $finder;
  }

  /*
   * glob, patterns (must be //) or strings
   */
  private function to_regex($str)
  {
    if ($str{0} == '/' && $str{strlen($str) - 1} == '/')
    {
      return $str;
    }
    else
    {
      return sfGlobto_regex::glob_to_regex($str);
    }
  }

  private function args_to_array($argList, $not = false)
  {
    $list = array();

    foreach ($argList as $arg)
    {
      if (is_array($arg))
      {
        foreach ($arg as $a)
        {
          $list[] = array($not, $this->to_regex($a));
        }
      }
      else
      {
        $list[] = array($not, $this->to_regex($arg));
      }
    }

    return $list;
  }

  /**
   * Adds rules that files must match.
   *
   * You can use patterns (delimited with / sign), globs or simple strings.
   *
   * $finder->name('*.php')
   * $finder->name('/\.php$/') // same as above
   * $finder->name('test.php')
   *
   * @param  list   a list of patterns, globs or strings
   * @return object current sfFinder object
   */
  public function name()
  {
    $args = func_get_args();
    $this->names = array_merge($this->names, $this->args_to_array($args));

    return $this;
  }

  /**
   * Adds rules that files must not match.
   *
   * @see    ->name()
   * @param  list   a list of patterns, globs or strings
   * @return object current sfFinder object
   */
  public function not_name()
  {
    $args = func_get_args();
    $this->names = array_merge($this->names, $this->args_to_array($args, true));

    return $this;
  }

  /**
   * Adds tests for file sizes.
   *
   * $finder->size('> 10K');
   * $finder->size('<= 1Ki');
   * $finder->size(4);
   *
   * @param  list   a list of comparison strings
   * @return object current sfFinder object
   */
  public function size()
  {
    $args = func_get_args();
    foreach ($args as $arg)
    {
      $this->sizes[] = new sfNumberCompare($arg);
    }

    return $this;
  }

  /**
   * Traverses no further.
   *
   * @param  list   a list of patterns, globs to match
   * @return object current sfFinder object
   */
  public function prune()
  {
    $args = func_get_args();
    $this->prunes = array_merge($this->prunes, $this->args_to_array($args));

    return $this;
  }

  /**
   * Discards elements that matches.
   *
   * @param  list   a list of patterns, globs to match
   * @return object current sfFinder object
   */
  public function discard()
  {
    $args = func_get_args();
    $this->discards = array_merge($this->discards, $this->args_to_array($args));

    return $this;
  }

  /**
   * Executes function or method for each element.
   *
   * Element match if functino or method returns true.
   *
   * $finder->exec('myfunction');
   * $finder->exec(array($object, 'mymethod'));
   *
   * @param  mixed  function or method to call
   * @return object current sfFinder object
   */
  public function exec()
  {
    $args = func_get_args();
    foreach ($args as $arg)
    {
      if (is_array($arg) && !method_exists($arg[0], $arg[1]))
      {
        throw new sfException("method {$arg[1]} does not exist for object {$arg[0]}");
      }
      elseif (!is_array($arg) && !function_exists($arg))
      {
        throw new sfException("function {$arg} does not exist");
      }

      $this->execs[] = $arg;
    }

    return $this;
  }

  /**
   * Returns relative paths for all files and directories.
   *
   * @return object current sfFinder object
   */
  public function relative()
  {
    $this->relative = true;

    return $this;
  }

  /**
   * Symlink following.
   *
   * @return object current sfFinder object
   */
  public function follow_link()
  {
    $this->followLink = true;

    return $this;
  }

  /**
   * Searches files and directories which match defined rules.
   *
   * @return array list of files and directories
   */
  public function in()
  {
    $files    = array();
    $hereDir = getcwd();
    $numargs  = func_num_args();
    $argList = func_get_args();

    // first argument is an array?
    if ($numargs == 1 && is_array($argList[0]))
    {
      $argList = $argList[0];
      $numargs  = count($argList);
    }

    foreach ($argList as $startDir)
    {
      $realDir = realpath($startDir);

      // absolute path?
      if (!self::isPathAbsolute($realDir))
      {
        $dir = $hereDir.DIRECTORY_SEPARATOR.$realDir;
      }
      else
      {
        $dir = $realDir;
      }

      if (!is_dir($realDir))
      {
        throw new sfException('directory "'.$startDir.'" does not exist');
      }

      if ($this->relative)
      {
        $files = array_merge($files, str_replace($dir.DIRECTORY_SEPARATOR, '', $this->search_in($dir)));
      }
      else
      {
        $files = array_merge($files, $this->search_in($dir));
      }
    }

    return array_unique($files);
  }

  private function search_in($dir, $depth = 0)
  {
    if ($depth > $this->maxdepth)
    {
      return array();
    }

    if (is_link($dir) && !$this->followLink)
    {
      return array();
    }

    $files = array();

    if (is_dir($dir))
    {
      $currentDir = opendir($dir);
      while ($entryname = readdir($currentDir))
      {
        if ($entryname == '.' || $entryname == '..')
        {
          continue;
        }

        $currentEntry = $dir.DIRECTORY_SEPARATOR.$entryname;
        if (is_link($currentEntry) && !$this->followLink)
        {
          continue;
        }

        if (is_dir($currentEntry))
        {
          if (($this->type == 'directory' || $this->type == 'any') && ($depth >= $this->mindepth) && !$this->is_discarded($dir, $entryname) && $this->match_names($dir, $entryname) && $this->exec_ok($dir, $entryname))
          {
            $files[] = realpath($currentEntry);
          }

          if (!$this->is_pruned($dir, $entryname))
          {
            $files = array_merge($files, $this->search_in($currentEntry, $depth + 1));
          }
        }
        else
        {
          if (($this->type != 'directory' || $this->type == 'any') && ($depth >= $this->mindepth) && !$this->is_discarded($dir, $entryname) && $this->match_names($dir, $entryname) && $this->size_ok($dir, $entryname) && $this->exec_ok($dir, $entryname))
          {
            $files[] = realpath($currentEntry);
          }
        }
      }
      closedir($currentDir);
    }

    return $files;
  }

  private function match_names($dir, $entry)
  {
    if (!count($this->names))
    {
      return true;
    }

    // we must match one "not_name" rules to be ko
    $oneNotNameRule = false;
    foreach ($this->names as $args)
    {
      list($not, $regex) = $args;
      if ($not)
      {
        $oneNotNameRule = true;
        if (preg_match($regex, $entry))
        {
          return false;
        }
      }
    }

    $oneNameRule = false;
    // we must match one "name" rules to be ok
    foreach ($this->names as $args)
    {
      list($not, $regex) = $args;
      if (!$not)
      {
        $oneNameRule = true;
        if (preg_match($regex, $entry))
        {
          return true;
        }
      }
    }

    if ($oneNotNameRule && $oneNameRule)
    {
      return false;
    }
    elseif ($oneNotNameRule)
    {
      return true;
    }
    elseif ($oneNameRule)
    {
      return false;
    }
    else
    {
      return true;
    }
  }

  private function size_ok($dir, $entry)
  {
    if (!count($this->sizes))
    {
      return true;
    }

    if (!is_file($dir.DIRECTORY_SEPARATOR.$entry))
    {
      return true;
    }

    $filesize = filesize($dir.DIRECTORY_SEPARATOR.$entry);
    foreach ($this->sizes as $numberCompare)
    {
      if (!$numberCompare->test($filesize))
      {
        return false;
      }
    }

    return true;
  }

  private function is_pruned($dir, $entry)
  {
    if (!count($this->prunes))
    {
      return false;
    }

    foreach ($this->prunes as $args)
    {
      $regex = $args[1];
      if (preg_match($regex, $entry))
      {
        return true;
      }
    }

    return false;
  }

  private function is_discarded($dir, $entry)
  {
    if (!count($this->discards))
    {
      return false;
    }

    foreach ($this->discards as $args)
    {
      $regex = $args[1];
      if (preg_match($regex, $entry))
      {
        return true;
      }
    }

    return false;
  }

  private function exec_ok($dir, $entry)
  {
    if (!count($this->execs))
    {
      return true;
    }

    foreach ($this->execs as $exec)
    {
      if (!call_user_func_array($exec, array($dir, $entry)))
      {
        return false;
      }
    }

    return true;
  }

  public static function isPathAbsolute($path)
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
}

/**
 * Match globbing patterns against text.
 *
 *   if match_glob("foo.*", "foo.bar") echo "matched\n";
 *
 * // prints foo.bar and foo.baz
 * $regex = glob_to_regex("foo.*");
 * for (array('foo.bar', 'foo.baz', 'foo', 'bar') as $t)
 * {
 *   if (/$regex/) echo "matched: $char\n";
 * }
 *
 * sfGlobto_regex implements glob(3) style matching that can be used to match
 * against text, rather than fetching names from a filesystem.
 *
 * based on perl Text::Glob module.
 *
 * @package    symfony
 * @subpackage util
 * @author     Fabien Potencier <fabien.potencier@gmail.com> php port
 * @author     Richard Clamp <richardc@unixbeard.net> perl version
 * @copyright  2004-2005 Fabien Potencier <fabien.potencier@gmail.com>
 * @copyright  2002 Richard Clamp <richardc@unixbeard.net>
 * @version    SVN: $Id$
 */
class sfGlobto_regex
{
  private static $strictLeadingDot = true;
  private static $strictWildcardSlash = true;

  public static function setStrictLeadingDot($boolean)
  {
    self::$strictLeadingDot = $boolean;
  }

  public static function setStrictWildcardSlash($boolean)
  {
    self::$strictWildcardSlash = $boolean;
  }

  /**
   * Returns a compiled regex which is the equiavlent of the globbing pattern.
   *
   * @param  string glob pattern
   * @return string regex
   */
  public static function glob_to_regex($glob)
  {
    $firstByte = true;
    $escaping = false;
    $inCurlies = 0;
    $regex = '';
    foreach (str_split($glob) as $char)
    {
      if ($firstByte)
      {
        if (self::$strictLeadingDot && $char != '.')
        {
          $regex .= '(?=[^\.])';
        }

        $firstByte = false;
      }

      if ($char == '/')
      {
        $firstByte = true;
      }

      if ($char == '.' || $char == '(' || $char == ')' || $char == '|' || $char == '+' || $char == '^' || $char == '$')
      {
        $regex .= "\\$char";
      }
      elseif ($char == '*')
      {
        $regex .= ($escaping ? "\\*" : (self::$strictWildcardSlash ? "[^\/]*" : ".*"));
      }
      elseif ($char == '?')
      {
        $regex .= ($escaping ? "\\?" : (self::$strictWildcardSlash ? "[^\/]" : "."));
      }
      elseif ($char == '{')
      {
        $regex .= ($escaping ? "\\{" : "(");
        if (!$escaping)
        {
          ++$inCurlies;
        }
      }
      elseif ($char == '}' && $inCurlies)
      {
        $regex .= ($escaping ? "}" : ")");
        if (!$escaping)
        {
          --$inCurlies;
        }
      }
      elseif ($char == ',' && $inCurlies)
      {
        $regex .= ($escaping ? "," : "|");
      }
      elseif ($char == "\\")
      {
        if ($escaping)
        {
          $regex .= "\\\\";
          $escaping = false;
        }
        else
        {
          $escaping = true;
        }

        continue;
      }
      else
      {
        $regex .= $char;
        $escaping = false;
      }
      $escaping = false;
    }

    return "/^$regex$/";
  }
}

/**
 * Numeric comparisons.
 *
 * sfNumberCompare compiles a simple comparison to an anonymous
 * subroutine, which you can call with a value to be tested again.

 * Now this would be very pointless, if sfNumberCompare didn't understand
 * magnitudes.

 * The target value may use magnitudes of kilobytes (C<k>, C<ki>),
 * megabytes (C<m>, C<mi>), or gigabytes (C<g>, C<gi>).  Those suffixed
 * with an C<i> use the appropriate 2**n version in accordance with the
 * IEC standard: http://physics.nist.gov/cuu/Units/binary.html
 *
 * based on perl Number::Compare module.
 *
 * @package    symfony
 * @subpackage util
 * @author     Fabien Potencier <fabien.potencier@gmail.com> php port
 * @author     Richard Clamp <richardc@unixbeard.net> perl version
 * @copyright  2004-2005 Fabien Potencier <fabien.potencier@gmail.com>
 * @copyright  2002 Richard Clamp <richardc@unixbeard.net>
 * @see        http://physics.nist.gov/cuu/Units/binary.html
 * @version    SVN: $Id$
 */
class sfNumberCompare
{
  private $test = '';

  public function __construct($test)
  {
    $this->test = $test;
  }

  public function test($number)
  {
    if (!preg_match('{^([<>]=?)?(.*?)([kmg]i?)?$}i', $this->test, $matches))
    {
      throw new sfException('don\'t understand "'.$this->test.'" as a test');
    }

    $target     = 0;
    $magnitude  = null;
    $comparison = '==';

    if (array_key_exists(2, $matches))
    {
      $target = (float)$matches[2];
    }
    if (array_key_exists(3, $matches))
    {
      $magnitude =  strtolower($matches[3]);
    }

    if ($magnitude == 'k')
    {
      $target *=           1000;
    }
    elseif ($magnitude == 'ki')
    {
      $target *=           1024;
    }
    elseif ($magnitude == 'm')
    {
      $target *=        1000000;
    }
    elseif ($magnitude == 'mi')
    {
      $target *=      1024*1024;
    }
    elseif ($magnitude == 'g')
    {
      $target *=     1000000000;
    }
    elseif ($magnitude == 'gi')
    {
      $target *= 1024*1024*1024;
    }

    $comparison = array_key_exists(1, $matches) ? $matches[1] : '==';
    if ($comparison == '==' || $comparison == '')
    {
      return ($number == $target);
    }
    elseif ($comparison == '>')
    {
      return ($number > $target);
    }
    elseif ($comparison == '>=')
    {
      return ($number >= $target);
    }
    elseif ($comparison == '<')
    {
      return ($number < $target);
    }
    elseif ($comparison == '<=')
    {
      return ($number <= $target);
    }

    return false;
  }
}

?>