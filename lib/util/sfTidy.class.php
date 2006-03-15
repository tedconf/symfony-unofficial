<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfTidy is a wrapper for the tidy library.
 *
 * @package    symfony
 * @subpackage util
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
class sfTidy
{
  public static function tidy($html, $name)
  {

    if (!extension_loaded('tidy'))
    {
      return $html;
    }

    if ($sf_logging_active = sfConfig::get('sf_logging_active'))
    {
      $log = sfLogger::getInstance();
      $log->info('{sfView} tidy output for "'.$name.'"');
    }

    $tidy = new tidy();
    $tidy->parseString($html, sfConfig::get('sf_app_config_dir').DIRECTORY_SEPARATOR.'tidy.conf');
    $tidy->cleanRepair();

    // warnings and errors
    if ($sf_logging_active)
    {
      $tidy->diagnose();

      $errorMsgs = array(
        'access' => array(),
        'warning' => array(),
        'error' => array(),
      );
      if ($tidy->errorBuffer)
      {
        foreach (split("\n", htmlspecialchars($tidy->errorBuffer)) as $line)
        {
          if (trim($line) == '' || preg_match('/were found\!/', $line))
          {
            continue;
          }

          $line = '{sfView} '.$line;
          if (preg_match('/Error\:/i', $line))
          {
            $errorMsgs['error'][] = $line;
          }
          elseif (preg_match('/Access\:/i', $line))
          {
            $errorMsgs['access'][] = $line;
          }
          elseif (preg_match('/Warning\:/i', $line))
          {
            $errorMsgs['warning'][] = $line;
          }
          elseif (preg_match('/Info/i', $line))
          {
            $log->info($line);
          }
          else
          {
            $log->info($line);
          }
        }
      }

      if (tidy_error_count($tidy))
      {
        $msg = '{sfView} '.tidy_error_count($tidy).' error(s) for "'.$name.'"';
        if (count($errorMsgs['error']))
        {
          $msg .= '[BEGIN_COMMENT] [n] '.implode('[n]', $errorMsgs['error']).' [END_COMMENT]';
        }
        $log->err($msg);
      }
      if (tidy_warning_count($tidy))
      {
        $msg = '{sfView} '.tidy_warning_count($tidy).' warning(s) for "'.$name.'"';
        if (count($errorMsgs['warning']))
        {
          $msg .= '[BEGIN_COMMENT] [n] '.implode('[n]', $errorMsgs['warning']).' [END_COMMENT]';
        }
        $log->warning($msg);
      }
      if (tidy_access_count($tidy))
      {
        $msg = '{sfView} '.tidy_access_count($tidy).' accessibility problem(s) for "'.$name.'"';
        if (count($errorMsgs['access']))
        {
          $msg .= '[BEGIN_COMMENT] [n] '.implode('[n]', $errorMsgs['access']).' [END_COMMENT]';
        }
        $log->warning($msg);
      }
    }

    return (string) $tidy;
  }
}

?>