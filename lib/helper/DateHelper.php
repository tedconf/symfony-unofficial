<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * DateHelper.
 *
 * @package    symfony
 * @subpackage helper
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */

function format_daterange($startDate, $endDate, $format = 'd', $fullText, $startText, $endText, $culture = null)
{
  if (!$culture)
  {
    $culture = sfContext::getInstance()->getUser()->getCulture();
  }

  $dateFormat = new sfDateFormat($culture);

  if ($startDate != '' && $endDate != '')
  {
    return sprintf($fullText, $dateFormat->format($startDate, $format), $dateFormat->format($endDate, $format));
  }
  elseif ($startDate != '')
  {
    return sprintf($startText, $dateFormat->format($startDate, $format));
  }
  elseif ($endDate != '')
  {
    return sprintf($endText, $dateFormat->format($endDate, $format));
  }
}

function format_date($date, $format = 'd', $culture = null)
{
  if (!$culture)
  {
    $culture = sfContext::getInstance()->getUser()->getCulture();
  }

  $dateFormat = new sfDateFormat($culture);
  return $dateFormat->format($date, $format);
}

function format_datetime($date, $format = 'F', $culture = null)
{
  if (!$culture)
  {
    $culture = sfContext::getInstance()->getUser()->getCulture();
  }

  $dateFormat = new sfDateFormat($culture);
  return $dateFormat->format($date, $format);
}

function distance_of_time_in_words($fromTime, $toTime = null, $includeSeconds = false)
{
  $toTime = $toTime? $toTime: time();

  $distanceInMinutes = abs(round(($toTime - $fromTime) / 60));
  $distanceInSeconds = abs(round(($toTime - $fromTime)));

  if ($distanceInMinutes <= 1)
  {
    if (!$includeSeconds)
    {
      return ($distanceInMinutes == 0) ? "less than a minute" : "1 minute";
    }

    if ($distanceInSeconds <= 5)
    {
      return "less than 5 seconds";
    }
    elseif ($distanceInSeconds >= 6 && $distanceInSeconds <= 10)
    {
      return "less than 10 seconds";
    }
    elseif ($distanceInSeconds >= 11 && $distanceInSeconds <= 20)
    {
      return "less than 20 seconds";
    }
    elseif ($distanceInSeconds >= 21 && $distanceInSeconds <= 40)
    {
      return "half a minute";
    }
    elseif ($distanceInSeconds >= 41 && $distanceInSeconds <= 59)
    {
      return "less than a minute";
    }
    else
    {
      return "1 minute";
    }
  }
  elseif ($distanceInMinutes >= 2 && $distanceInMinutes <= 45)
  {
    return $distanceInMinutes." minutes";
  }
  elseif ($distanceInMinutes >= 46 && $distanceInMinutes <= 90)
  {
    return "about 1 hour";
  }
  elseif ($distanceInMinutes >= 90 && $distanceInMinutes <= 1440)
  {
    return "about ".round($distanceInMinutes / 60)." hours";
  }
  elseif ($distanceInMinutes >= 1441 && $distanceInMinutes <= 2880)
  {
    return "1 day";
  }
  else
  {
    return round($distanceInMinutes / 1440)." days";
  }
}

# Like distance_of_time_in_words, but where <tt>to_time</tt> is fixed to <tt>Time.now</tt>.
function time_ago_in_words($fromTime, $includeSeconds = false)
{
  return distance_of_time_in_words($fromTime, time(), $includeSeconds);
}

?>