<?php
class DebugHelper extends sfHelper
{

static function debug_message($message)
{
  if (sfConfig::get('sf_web_debug'))
  {
    sfWebDebug::getInstance()->logShortMessage($message);
  }
}

static function log_message($message, $priority = 'info')
{
  if (sfConfig::get('sf_logging_active'))
  {
    sfContext::getInstance()->getLogger()->log($message, constant('SF_PEAR_LOG_'.strtoupper($priority)));
  }
}

}