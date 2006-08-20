<?php

	/**
	 * A class to manage ini files
	 *
	 * @package util
	 * @author Joe Simms
	 **/
	class sfIni
	{
		/**
		 * returns an array
		 *
		 * @return void
		 * @author Joe Simms
		 **/
		public static function load($file, $process_sections = false)
		{
			return parse_ini_file($file, $process_sections);
		}

		/**
		 * converts array to ini string
		 *
		 * @return void
		 * @author Joe Simms
		 **/
		public static function dump($array, $process_sections = false)
		{
	    $content = "";
	    if ($process_sections)
			{
	      foreach ($array as $key => $elem)
			  {
	          $content .= "[".$key."]\n";
	          foreach ($elem as $key2 => $elem2)
						{
	              $content .= $key2." = \"".$elem2."\"\n";
	          }
	       }
	    }
	    else
			{
	       foreach ($array as $key => $elem)
				 {
	          $content .= $key." = \"".$elem."\"\n";
	       }
	    }
			return $content;
			var_dump($content);
		}
		
		/**
		 * converts array to ini format and writes to file
		 *
		 * @return void
		 * @author Joe Simms
		 **/
		public static function write($array, $file, $process_sections = false)
		{
			$content = self::dump($array, $process_sections);
			file_put_contents($file, $content);

	    return true;			
		}

	} // END class sfIni