<?php
/**
 * @file Memcache.php
 * @author wanghe (hihu@qq.com)
 **/

Pi::inc(dirname(__FILE__).DOT.'PiCacheAbstract.php');
class PiMc {
    private static $instance = array();
    
    public static function get($name){
        if(!is_string($name)){
            return null;
        }

        if(isset(self::$instance[$name])){
            return self::$instance[$name];
        }

        self::$instance[$name] = null;
        $conf = self::getConfig($name);
        if($conf == null) return null;
        self::$instance[$name] = new MemInner($conf);
        return self::$instance[$name];
    }

    public static function getConfig($name){
        $conf = Pi::get('cache.'.$name,array());
        if(empty($conf)) return null;
        foreach($conf as $server){
            if(!isset($server['host']) || !isset($server['port']) ||
               !isset($server['port']) || !isset($server['pconnect']
            )){
                return null;
            }
        }
        return $conf;
    }
   
//end of class
}

class MemInner extends PiCacheAbstract{
    public $cache_type = 'memcache'; // memcache 和 memcached
    public function __construct($conf,$type = 'memcache'){
        if(!is_array($conf) || empty($conf)){
            return null;
        }
        if($type == 'memcache'){
            $this->conn = new Memcache();
            foreach ($conf as $s) {
                $this->conn->addServer($s['host'],$s['port'],$s['pconnect']);
            }
        }else{
            $this->conn = new Memcached();
            $this->cache_type = 'memcached';
            foreach ($conf as $s) {
                $this->conn->addServer($s['host'],$s['port']);
            }
        }   
    }

    /**
    * @param string $key
    * @param mixed $value
    * @param int $ttl
    * @return boolean
    */
    public function set($id, $data, $ttl = 86400){
        if($this->cache_type == 'memcache'){
            return $this->conn->set($id, $data,0,$ttl);
        }else{
            return $this->conn->set($id, $data, $ttl);
        }
        
    }

    /**
     * Get Cache Data
     *
     * @param mixed $id
     * @return array
     */
    public function get($id){
        if($this->cache_type == 'memcache'){
            return $this->conn->get($id);
        }else{
            return is_array($id) ? $this->conn->getMulti($id) : $this->conn->get($id);
        }
    }

}



