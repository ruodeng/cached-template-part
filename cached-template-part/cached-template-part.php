<?php
/*
Plugin Name: Cached template part
Plugin URI: https://dengruo.com/
Description: Modify the get_template_part function, to cached some not always changed content into memcached.
Version: 1
Author: Ruo
Author URI: https://dengruo.com
*/

/**
 * the idea is copy from https://gist.github.com/bcole808/9371883
 * Retrieves a template part and caches the output to speed up site
 */

if (!function_exists('delete_cached_template_part_function')) {
    /**
     * @param $name
     * @param string $id
     */
    function delete_cached_template_part_function($name, $id=''){
        if(!class_exists('Memcache')){
            return false;
        }
        $transient_id = $id?$name.'-'.$id:$name;
        $memcache_obj = new Memcache();
        $memcache_obj->addServer('127.0.0.1', 11211);
        $memcache_obj->delete(md5($transient_id));
    }
}
if (!function_exists('get_cached_template_part_function')) {
    function get_cached_template_part_function($transient_id){
        if(!class_exists('Memcache')){
            return false;
        }

        $memcache_obj = new Memcache();
        $memcache_obj->addServer('127.0.0.1', 11211);
        $var = $memcache_obj->get(md5($transient_id));
        return $var ;
    }
}



if (!function_exists('set_cached_template_part_function')) {
    /**
     * @param $cached_template_part
     * @param $transient_id
     * @param int $ttl
     */
    function set_cached_template_part_function($cached_template_part,$transient_id,$ttl = 3600 ){
        if(!class_exists('Memcache')){
            return false;
        }
        $memcache_obj = new Memcache();
        $memcache_obj->addServer('127.0.0.1', 11211);
        $memcache_obj->set(md5($transient_id), $cached_template_part,0,$ttl);
    }

}

if (!function_exists('get_cached_template_part')) {
    /**
     * @param string $name    it's the $slug+$name of the template
     * @param int $ttl      The expire time, default is 3600
     * @param bool $debug    Output debug info, also disable the cache.
     * @param bool $nocache  if set nocache, the script will not try to read the memcache cache.
     * @return bool
     */
    function get_cached_template_part($name = '', $ttl = 3600, $debug = false,$nocache=false) {
        return  get_cached_template_part_id($name,'',$ttl, $debug);
    }
}

if (!function_exists('get_cached_template_part_id')) {
    /**
     * @param string $name    The $slug+$name of the template
     * @param string $id    The different id for the template. for example: home page should has different language code for EN/FR/ES etc.
     * @param int $ttl      The expire time, default is 3600
     * @param bool $debug    Output debug info, also disable the cache.
     * @param bool $nocache  if set nocache, the script will not try to read the memcache cache.
     * @return bool
     */
    function get_cached_template_part_id($name = '', $id='',$ttl = 3600, $debug = false,$nocache=false) {
//        echo get_template_part($name);
//        return true;

        if($_GET['w3tc_note']=='flush_all'|| $nocache){

            $id=str_replace('/?w3tc_note=flush_all','',$id);
            $id=str_replace('&w3tc_note=flush_all','',$id);

            delete_cached_template_part_id($name,$id);
            echo get_template_part($name);
            return true;
        }
        $transient_id = $id?$name.'-'.$id:$name;

        if($debug){
            echo '<xmp>';
            echo 'Name:';
            var_dump($name);
            echo '$id:';var_dump($id);
            echo '$ttl:';var_dump($ttl);
            echo 'Memcache Key:'; var_dump($transient_id);
            var_dump(time());
            echo '</xmp>';
        }

        $cached_template_part =  get_cached_template_part_function($transient_id);
        if ( false === $cached_template_part ) {
            ob_start();
            get_template_part($name);
            $cached_template_part = ob_get_contents();
            ob_end_clean();
            set_cached_template_part_function($cached_template_part,$transient_id,$ttl);
        }
        echo $cached_template_part;
        return true;
    }
}


/**
 * Delete the cache
 * @param $name  The $slug+$name of the template
 */
function delete_cached_template_part($name){
    delete_cached_template_part_function($name);
}

/**
 * Delete the cache
 * @param string $name    The $slug+$name of the template
 * @param string $id    The different id for the template. for example: home page should has different language code for EN/FR/ES etc.
 */
if (!function_exists('delete_cached_template_part_id')) {
    function delete_cached_template_part_id($name, $id = '')
    {
        delete_cached_template_part_function($name, $id);
    }
}

