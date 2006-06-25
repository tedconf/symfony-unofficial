<?php
/**
 * ##BATCH_NAME## batch script
 *
 * Here goes a brief description of the purpose of the batch script
 *
 * @package ##PROJECT_NAME##
 * @subpackage batch
 * @author your name here
 * @version $Id$
 */

define('SF_ROOT_DIR',      realpath(dirname(__file__).'/..'));
define('SF_APP',           '##APP_NAME##');
define('SF_ENVIRONMENT',   '##ENV_NAME##');
define('SF_DEBUG',         ##DEBUG##);

require_once(SF_ROOT_DIR.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php');

sfContext::getInstance();

// batch process here

?>