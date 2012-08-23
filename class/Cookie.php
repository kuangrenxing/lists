<?php
define('COOKIE_PREFIX' , 'km_');
define('COOKIE_EXPIRE' , 24*3600*15);
define('COOKIE_PATH' , '/');
define('COOKIE_DOMAIN' , '.work.com');

class Cookie
{
    // 判断Cookie是否存在
    static function is_set($name) {
        return isset($_COOKIE[COOKIE_PREFIX.$name]);
    }

    // 获取某个Cookie值
    static function get($name) {
        $value   = $_COOKIE[COOKIE_PREFIX.$name];
//        $value   =  unserialize(base64_decode($value));
        return $value;
    }

    // 设置某个Cookie值
    static function set($name,$value,$expire='',$path='',$domain='') {
        if($expire=='') {
            $expire =   COOKIE_EXPIRE;
        }
        if(empty($path)) {
            $path = COOKIE_PATH;
        }
        if(empty($domain)) {
            $domain =   COOKIE_DOMAIN;
        }
        $expire =   !empty($expire)?    time()+$expire   :  0;
//        $value   =  base64_encode(serialize($value));
        setcookie(COOKIE_PREFIX.$name, $value,$expire,$path,$domain);
        $_COOKIE[COOKIE_PREFIX.$name]  =   $value;
    }

    // 删除某个Cookie值
    static function delete($name) {
        Cookie::set($name,'',time()-3600);
        unset($_COOKIE[COOKIE_PREFIX.$name]);
    }

    // 清空Cookie值
    static function clear() {
        unset($_COOKIE);
    }
}
?>