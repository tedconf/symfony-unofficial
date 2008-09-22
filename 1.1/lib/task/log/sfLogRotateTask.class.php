<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Rotates an application log files.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfLogRotateTask extends sfBaseTask
{

  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name'),
      new sfCommandArgument('env', sfCommandArgument::REQUIRED, 'The environment name'),
    ));

    $this->addOptions(array(
      new sfCommandOption('history', null, sfCommandOption::PARAMETER_REQUIRED, 'The maximum number of old log files to keep', sfConfig::get('sf_logging_history', 10)),
      new sfCommandOption('period', null, sfCommandOption::PARAMETER_REQUIRED, 'The period in days', sfConfig::get('sf_logging_period', 7)),
    ));

    $this->aliases = array('log-rotate');
    $this->namespace = 'log';
    $this->name = 'rotate';
    $this->briefDescription = 'Rotates an application log files';

    $this->detailedDescription = <<<EOF
The [log:rotate|INFO] task rotates application log files for a given
environment:

  [./symfony log:rotate frontend dev|INFO]

You can specify a [period|COMMENT] or a [history|COMMENT] option:

  [./symfony --history=10 --period=7 log:rotate frontend dev|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $app = $arguments['application'];
    $env = $arguments['env'];

    $this->rotate($app, $env, $options['period'], $options['history'], true);
  }

  /**
   * Rotates log file.
   *
   * @param  string $app       Application name
   * @param  string $env       Enviroment name
   * @param  string $period    Period
   * @param  string $history   History
   * @param  bool   $override  Override
   *
   * @author Joe Simms
   **/
  public function rotate($app, $env, $period = null, $history = null, $override = false)
  {
    $logfile = $app.'_'.$env;
    $logdir = sfConfig::get('sf_log_dir');

    // set history and period values if not passed to default values
    $period = isset($period) ? $period : sfConfig::get('sf_logging_period', 7);
    $history = isset($history) ? $history : sfConfig::get('sf_logging_history', 10);

    // get todays date
    $today = date('Ymd');

    // check history folder exists
    if (!is_dir($logdir.DIRECTORY_SEPARATOR.'history'))
    {
      mkdir($logdir.DIRECTORY_SEPARATOR.'history', 0777);
    }

    // determine date of last rotation
    $logs = sfFinder::type('file')->maxdepth(1)->name($logfile.'_*.log')->in($logdir.'/history/');
    $recentlog = is_array($logs) ? array_pop($logs) : null;

    if ($recentlog)
    {
      // calculate date to rotate logs on
      $lastRotatedOn = filemtime($recentlog);
      $rotateOn = date('Ymd', strtotime('+ '.$period.' days', $lastRotatedOn));
    }
    else
    {
      // no rotation has occured yet
      $rotateOn = null;
    }

    $srcLog = $logdir.DIRECTORY_SEPARATOR.$logfile.'.log';
    $destLog = $logdir.DIRECTORY_SEPARATOR.'history'.DIRECTORY_SEPARATOR.$logfile.'_'.$today.'.log';

    // if rotate log on date doesn't exist, or that date is today, then rotate the log
    if (!$rotateOn || ($rotateOn == $today) || $override)
    {
      // create a lock file
      $lockFile = sfConfig::get('sf_data_dir').'/'.$app.'_'.$env.'-cli.lck';
      $this->getFilesystem()->touch($lockFile);

      // change mode so the web user can remove it if we die
      $this->getFilesystem()->chmod($lockFile, 0777);

      // if log file exists rotate it
      if (file_exists($srcLog))
      {
        // check if the log file has already been rotated today
        if (file_exists($destLog))
        {
          // append log to existing rotated log
          $handle = fopen($destLog, 'a');
          $append = file_get_contents($srcLog);
          fwrite($handle, $append);
        }
        else
        {
          // copy log
          copy($srcLog, $destLog);
        }

        // remove the log file
        unlink($srcLog);

        // get all log history files for this application and environment
        $newLogs = sfFinder::type('file')->maxdepth(1)->name($logfile.'_*.log')->in($logdir.'/history/');

        // if the number of logs in history exceeds history then remove the oldest log
        if (count($newLogs) > $history)
        {
          unlink($newLogs[0]);
        }
      }

      // release lock
      $this->getFilesystem()->remove($lockFile);
    }
  }
}