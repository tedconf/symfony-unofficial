<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * Copyright (c) 2006 Yahoo! Inc.  All rights reserved.  
 * The copyrights embodied in the content in this file are licensed under 
 * the MIT open source license
 * 
 * For the full copyright and license information, please view the LICENSE
 * and LICENSE.yahoo file that was distributed with this source code.
 */

/**
 * Helper functions for tests
 *
 * @package    symfony
 * @subpackage test
 * @author     Mike Salisbury <salisbur@yahoo-inc.com>
 * @version    SVN: $Id: sfTestUtils.class.php,v 1.1 2006/05/05 20:25:37 salisbur Exp $
 */
class sfTestUtils
{
  // extracts the debug data into an array keyed by the data labels.
  public static function extractDebugData($content)
  {
    $data = array();

    $inDataSection = false;   // true if in data section of file
    $sectionName = '';        // if in data section, name of data
    $sectionData = null;      // if in data section, serialized data so far

    $datalines = explode("\n", $content);
    foreach ($datalines as $line)
    {
      if ($inDataSection)
      {
        // see if we've hit the end.
        if (preg_match('/^END_DATADUMP$/', $line))
        {
          @eval('$data[$sectionName] = '.implode("\n",$sectionData).';');
          $inDataSection = false;
          $sectionName = '';
          $sectionData = null;
        }
        else
        {
          // not at end; remember line
          $sectionData[] = $line;
        }
      }
      else
      {
        // see if we've hit beginning
        if (preg_match('/^BEGIN_DATADUMP:(.*)$/', $line, $matches))
        {
          $inDataSection = true;
          $sectionName = $matches[1];
          $sectionData = array();
        }
      }
    }
    return $data;
  }
}

?>
