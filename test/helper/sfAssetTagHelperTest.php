<?php

require_once 'helper/TagHelper.php';
require_once 'helper/AssetHelper.php';
require_once 'helper/UrlHelper.php';

require_once 'request/sfRequest.class.php';
require_once 'request/sfWebRequest.class.php';

Mock::generate('sfContext');
Mock::generate('sfWebRequest');

class sfAssetTagHelperTest extends UnitTestCase
{
  private
    $context = null,
    $request = null;

  public function SetUp()
  {
    $this->request = new MockSfWebRequest($this);
    $this->request->setReturnValue('getRelativeUrlRoot', '');

    $this->context = new MockSfContext($this);
    $this->context->setReturnValue('getRequest', $this->request);
  }

  public function test_image_tag()
  {
    $this->assertEqual('', image_tag(''));
    $this->assertEqual('<img src="/images/test.png" alt="Test" />', image_tag('test'));
    $this->assertEqual('<img src="/images/test.png" alt="Test" />', image_tag('test.png'));
    $this->assertEqual('<img src="/images/test.png" alt="Test" />', image_tag('/images/test.png'));
    $this->assertEqual('<img src="/images/test.png" alt="Test" />', image_tag('/images/test'));
    $this->assertEqual('<img src="/images/test.jpg" alt="Test" />', image_tag('test.jpg'));

    $this->assertEqual('<img src="/anotherpath/path/test.jpg" alt="Test" />',
      image_tag('/anotherpath/path/test.jpg'));

    $this->assertEqual('<img alt="Foo" src="/images/test.png" />',
      image_tag('test', array('alt' => 'Foo')));

    $this->assertEqual('<img src="/images/test.png" alt="Test" height="10" width="10" />',
      image_tag('test', array('size' => '10x10')));

    $this->assertEqual('<img class="bar" src="/images/test.png" alt="Test" />',
      image_tag('test', array('class' => 'bar')));
  }

  public function test_stylesheet_tag()
  {
    $this->assertEqual('<link rel="stylesheet" type="text/css" media="screen" href="/css/style.css" />'."\n",
      stylesheet_tag('style'));

    $this->assertEqual(
      '<link rel="stylesheet" type="text/css" media="screen" href="/css/random.styles" />'."\n".
      '<link rel="stylesheet" type="text/css" media="screen" href="/css/stylish.css" />'."\n",
      stylesheet_tag('random.styles', '/css/stylish')
    );
  }

  public function test_javascript_include_tag()
  {
    $this->assertEqual('<script type="text/javascript" src="/js/xmlhr.js"></script>'."\n",
      javascript_include_tag('xmlhr'));

    $this->assertEqual(
      '<script type="text/javascript" src="/js/common.javascript"></script>'."\n".
      '<script type="text/javascript" src="/elsewhere/cools.js"></script>'."\n",
      javascript_include_tag('common.javascript', '/elsewhere/cools')
    );
  }

  public function test_asset_javascript_path()
  {
    $this->assertEqual('/js/xmlhr.js', javascript_path('xmlhr'));
  }

  public function test_asset_style_path()
  {
    $this->assertEqual('/css/style.css', stylesheet_path('style'));
  }

  public function test_asset_style_link()
  {
    $this->assertEqual("<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"/css/style.css\" />\n",
      stylesheet_tag('style'));

    $this->assertEqual(
      "<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"/css/random.styles\" />\n".
      "<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"/css/stylish.css\" />\n",
      stylesheet_tag('random.styles', '/css/stylish')
    );
  }

  public function test_asset_image_path()
  {
    $this->assertEqual('/images/xml.png', image_path('xml'));
  }

  public function test_asset_image_tag()
  {
    $this->assertEqual('<img src="/images/xml.png" alt="Xml" />', image_tag('xml'));

    $this->assertEqual('<img alt="rss syndication" src="/images/rss.png" />',
      image_tag('rss', array('alt' => 'rss syndication')));

    $this->assertEqual('<img src="/images/gold.png" alt="Gold" height="70" width="45" />',
      image_tag('gold', array('size' => '45x70')));
  }

/*
  public function test_auto_discovery_link_tag()
  {
    $this->assertEqual(auto_discovery_link_tag(),
      '<link href="http://www.example.com" rel="alternate" title="RSS" type="application/rss+xml" />');

    $this->assertEqual(auto_discovery_link_tag('atom'),
      '<link href="http://www.example.com" rel="alternate" title="ATOM" type="application/atom+xml" />');

    $this->assertEqual(auto_discovery_link_tag('rss', array('action' => 'feed')),
      '<link href="http://www.example.com" rel="alternate" title="RSS" type="application/rss+xml" />');
  }
*/
}

class sfAssetTagHelperNonVhostTest extends UnitTestCase
{
  private
    $context = null;

  public function SetUp()
  {
    $this->request = new MockSfRequest($this);
    $this->request->setReturnValue('getRelativeUrlRoot', '/mypath');

    $this->context = new MockSfContext($this);
    $this->context->setReturnValue('getRequest', $this->request);
  }
/*
  public function test_auto_discovery()
  {
    $this->assertEqual(auto_discovery_link_tag('rss', array('action' => 'feed')),
      '<link href="http://www.example.com/mypath" rel="alternate" title="RSS" type="application/rss+xml" />');

    $this->assertEqual(auto_discovery_link_tag('atom'),
      '<link href="http://www.example.com/mypath" rel="alternate" title="ATOM" type="application/atom+xml" />');

    $this->assertEqual(auto_discovery_link_tag(),
      '<link href="http://www.example.com/mypath" rel="alternate" title="RSS" type="application/rss+xml" />');
  }
*/
  public function test_javascript_path()
  {
    $this->assertEqual('/mypath/js/xmlhr.js', javascript_path('xmlhr'));
  }

  public function test_javascript_include()
  {
    $this->assertEqual('<script type="text/javascript" src="/mypath/js/xmlhr.js"></script>'."\n",
      javascript_include_tag('xmlhr'));

    $this->assertEqual(
      '<script type="text/javascript" src="/mypath/js/common.javascript"></script>'."\n".
      '<script type="text/javascript" src="/mypath/elsewhere/cools.js"></script>'."\n",
      javascript_include_tag('common.javascript', '/elsewhere/cools')
    );
  }

  public function test_style_path()
  {
    $this->assertEqual('/mypath/css/style.css', stylesheet_path('style'));
  }

  public function test_style_link()
  {
    $this->assertEqual('<link rel="stylesheet" type="text/css" media="screen" href="/mypath/css/style.css" />'."\n",
      stylesheet_tag('style'));

    $this->assertEqual(
      '<link rel="stylesheet" type="text/css" media="screen" href="/mypath/css/random.styles" />'."\n".
      '<link rel="stylesheet" type="text/css" media="screen" href="/mypath/css/stylish.css" />'."\n",
      stylesheet_tag('random.styles', '/css/stylish')
    );
  }

  public function test_image_path()
  {
    $this->assertEqual('/mypath/images/xml.png', image_path('xml'));
  }

  public function test_image_tag()
  {
    $this->assertEqual('<img src="/mypath/images/xml.png" alt="Xml" />', image_tag('xml'));

    $this->assertEqual('<img alt="rss syndication" src="/mypath/images/rss.png" />',
      image_tag('rss', array('alt' => 'rss syndication')));

    $this->assertEqual('<img src="/mypath/images/gold.png" alt="Gold" height="70" width="45" />',
      image_tag('gold', array('size' => '45x70')));

    $this->assertEqual('<img src="http://www.example.com/images/icon.gif" alt="Icon" />',
      image_tag('http://www.example.com/images/icon.gif'));
  }

  public function test_stylesheet_with_asset_host_already_encoded()
  {
    $this->assertEqual('<link rel="stylesheet" type="text/css" media="screen" href="http://bar.example.com/css/style.css" />'."\n",
      stylesheet_tag("http://bar.example.com/css/style.css"));
  }

}
?>