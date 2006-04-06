<?php

class sfMemcacheCache extends sfCache
{
  const DEFAULT_NAMESPACE = '';
  
  private $memcache;
  
  public function __construct() {
    $this->memcache = new Memcache();
    $this->memcache->connect('localhost', 11211);
  }

  private function getCacheId($id, $namespace) {
    return $id . '-' . $namespace;
  }
  
  public function get($id, $namespace = self::DEFAULT_NAMESPACE, $doNotTestCacheValidity = false) {
    $cacheId = $this->getCacheId($id, $namespace);
    return $this->memcache->get($cacheId);
  }

  public function has($id, $namespace = self::DEFAULT_NAMESPACE, $doNotTestCacheValidity = false) {
    $cacheId = $this->getCacheId($id, $namespace);
  }

  public function set($id, $namespace = self::DEFAULT_NAMESPACE, $data) {
    $cacheId = $this->getCacheId($id, $namespace);
    $this->memcache->set($cacheId, $data, 0, $this->lifeTime);
    return true;
  }

  public function remove($id, $namespace = self::DEFAULT_NAMESPACE) {
    $cacheId = $this->getCacheId($id, $namespace);
    $this->memcache->delete($cacheId);
  }

  public function clean($namespace = null, $mode = 'all') {
    $this->memcache->flush();
  }
  
  /**
  * Return the cache last modification time
  *
  * @return int last modification time
  */
  public function lastModified($id, $namespace = self::DEFAULT_NAMESPACE) {
  }

}

?>
