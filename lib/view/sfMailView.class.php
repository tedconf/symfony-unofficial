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
 * @subpackage view
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfMailView extends sfPHPView
{
  /**
   * Retrieve the template engine associated with this view.
   *
   * Note: This will return null because PHP itself has no engine reference.
   *
   * @return null
   */
  public function &getEngine()
  {
    return 'sfMail';
  }

  public function configure()
  {
    // view.yml configure
    parent::configure();

    // require our configuration
    $moduleName = $this->getContext()->getActionStack()->getLastEntry()->getActionInstance()->getModuleName();
    $viewConfigFile = $this->moduleName.'/'.sfConfig::get('sf_app_module_config_dir_name').'/mailer.yml';
    require(sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_app_module_dir_name').'/'.$viewConfigFile));
  }

  /**
   * Render the presentation and send the email to the client.
   *
   */
  public function &render()
  {
    $template         = $this->getDirectory().'/'.$this->getTemplate();
    $actionStackEntry = $this->getContext()->getActionStack()->getLastEntry();
    $actionInstance   = $actionStackEntry->getActionInstance();

    $moduleName = $actionInstance->getModuleName();
    $actionName = $actionInstance->getActionName();

    $retval = null;

    // execute pre-render check
    $this->preRenderCheck();

    if ($sf_logging_active = sfConfig::get('sf_logging_active'))
    {
      $this->getContext()->getLogger()->info('{sfMailView} render "'.$template.'"');
    }

    // get sfMail object from action
    $mail = $actionInstance->getVarHolder()->get('mail');
    if (!$mail)
    {
      $error = 'You must define a sfMail object named $action in your action to be able to use a sfMailView.';
      throw new sfActionException($error);
    }

    // render main template
    $retval = $this->renderFile($template);

    // render main and alternate templates
    $allTemplateDir  = dirname($template);
    $allTemplateRegex = preg_replace('/\\.php$/', '\..+\.php', basename($template));
    $allTemplates = sfFinder::type('file')->name('/^'.$allTemplateRegex.'$/')->in($allTemplateDir);
    $allRetvals = array();
    foreach ($allTemplates as $templateFile)
    {
      if (preg_match('/\.(.+?)\.php$/', $templateFile, $matches))
      {
        $allRetvals[$matches[1]] = $this->renderFile($templateFile);
      }
    }

    // send email
    if ($sf_logging_active)
    {
      $this->getContext()->getLogger()->info('{sfMailView} send email to client');
    }

    // configuration prefix
    $configPrefix = 'sf_mailer_'.strtolower($this->moduleName).'_';

    $vars = array(
      'mailer',
      'priority', 'content_type', 'charset', 'encoding', 'wordwrap',
      'hostname', 'port', 'domain', 'username', 'password'
    );
    foreach ($vars as $var)
    {
      $setter = 'set'.sfInflector::camelize($var);
      $getter = 'get'.sfInflector::camelize($var);
      $value  = $mail->$getter() ? $mail->$getter() : sfConfig::get($configPrefix.strtolower($var));
      $mail->$setter($value);
    }

    $mail->setBody($retval);

    // alternate bodies
    $i = 0;
    foreach ($allRetvals as $type => $retval)
    {
      if ($type == 'altbody' && !$mail->getAltBody())
      {
        $mail->setAltBody($retval);
      }
      else
      {
        ++$i;
        $mail->addStringAttachment($retval, 'file'.$i, 'base64', 'text/'.$type);
      }
    }

    // preparing email to be sent
    $mail->prepare();

    // send e-mail
    if (sfConfig::get($configPrefix.'deliver'))
    {
      $mail->send();
    }

    $header = $mail->getRawHeader();
    $body   = $mail->getRawBody();
    $retval = $header.$body;

    return $retval;
  }
}

?>