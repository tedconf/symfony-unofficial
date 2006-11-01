<?php
require_once('simpletest/web_tester.php');

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebTestCase is somewhat like SimpleTest::WebTestCase but the
 * browser is kept separate and we include some data debugging functions.
 *
 * @package    symfony
 * @subpackage test
 * @author     Mike Salisbury <salisbur@yahoo-inc.com>
 * @version    SVN: $Id: sfWebTestCase.class.php,v 1.1 2006/05/05 20:25:37 salisbur Exp $
 */
class sfWebTestCase extends UnitTestCase
{
  protected $browser;
  
  public function sfWebTestCase($label = false)
  {
    $this->UnitTestCase($label);
    $this->browser = new SimpleBrowser();
  }

  // returns named section of debug data
  // (or all data if no section named)
  public function getDebugData($section = '', $message = "%s")
  {
    // note: we're not caching this between requests.  could be
    // more efficient if we did, but invalidation is tricky.
    $content = $this->browser->getContent();
    $debugdata = sfTestUtils::extractDebugData($content);

    if (empty($section))
    {
      return $debugdata;
    }

    if (isset($debugdata[$section]))
    {
      return $debugdata[$section];
    }
    else
    {
      $this->fail(sprintf($message, "Debug section [$section] should exist"));
      return false;
    }
  }

  // copies of some WebTestCase assert functions
  function assertLink($label, $message = "%s") {
    return $this->assertTrue(
            $this->browser->isLink($label),
            sprintf($message, "Link [$label] should exist"));
  }
  function assertLinkById($id, $message = "%s") {
    return $this->assertTrue(
            $this->browser->isLinkById($id),
            sprintf($message, "Link ID [$id] should exist"));
  }
  function assertField($name, $expected = true, $message = "%s") {
    $value = $this->browser->getField($name);
    if ($expected === true) {
        return $this->assertTrue(
                isset($value),
                sprintf($message, "Field [$name] should exist"));
    } else {
        return $this->assertExpectation(
                new FieldExpectation($expected),
                $value,
                sprintf($message, "Field [$name] should match with [%s]"));
    }
  }
  function assertFieldById($id, $expected = true, $message = "%s") {
    $value = $this->browser->getFieldById($id);
    if ($expected === true) {
      return $this->assertTrue(
              isset($value),
              sprintf($message, "Field of ID [$id] should exist"));
    } else {
      return $this->assertExpectation(
              new FieldExpectation($expected),
              $value,
              sprintf($message, "Field of ID [$id] should match with [%s]"));
    }
  }




}

?>
