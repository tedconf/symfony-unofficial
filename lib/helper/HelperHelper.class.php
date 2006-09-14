<?php
class HelperHelper
{
	/**
	 * <b>DEPRECATED:</b> use use_helper() instead with the same syntax. 
	 */ 
	static function use_helpers()
	{
	  if (sfConfig::get('sf_logging_active')) sfContext::getInstance()->getLogger()->err('The function "use_helpers()" is deprecated. Please use "use_helper()"'); 

	  foreach (func_get_args() as $helperName)
	  {
		use_helper($helperName);
	  }
	}

	static function use_helper()
	{
	  sfLoader::loadHelpers(func_get_args(), sfContext::getInstance()->getModuleName());
	}
}
