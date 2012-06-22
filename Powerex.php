<?php

/**
 * Powerex is a small and simple PHP template engine based on Regex
 *
 * @author bruceman
 */
class Powerex {
    //the base directory to search template file
    public $tpl_dir = './tpl/';
    //extension of template file
    public $tpl_ext = '.php';
    //the base directory to store compiled template file
    public $cache_dir = './tpl_c/';
    //0 - compile template file for every request
    //-1 - never expired, just compile once if compiled file doesn't exists.
    public $cache_time = 0;
    //all data pass to template file
    public $data = array();
    //extend rules
    public  $ext_rules = array();
    //predefined replacement rules
    public $rep_rules = array(
        //{$name} 
       '~\{\s*(\$[a-z0-9_]+)\s*\}~i'
            => '<?php echo $1 ?>', 
       
         //{$arr[key]} or {$arr[key1][key2]} or {$arr[key1][key2][key3]}
        '~\{\s*(\$[a-z0-9_]+(\[[\"\']?\$?[a-z0-9_]+[[\"\']?\])+)\s*\}~i'
            => '<?php echo $1 ?>', 
        
        //{$arr.key}
        '~\{\s*(\$[a-z0-9_]+)\.([a-z0-9_]+)\s*\}~i'
            => '<?php echo $1[\'$2\'] ?>', 
       
        //{$arr.key1.key2}
        '~\{\s*(\$[a-z0-9_]+)\.([a-z0-9_]+)\.([a-z0-9_]+)\s*\}~i'
            => '<?php echo $1[\'$2\'][\'$3\'] ?>',
       
       //<?=
         '~<\?=\s*~' => '<?php echo ', 
        
        //method return value
        //{= count($arr) }
        //or {= $num1 + num2}
        '~\{=\s*(.+)\s*\}~'
            => '<?php echo $1 ?>',
        
        //method doesn't return value
        //{: echo "hello" }
        '~\{:\s*(.+)\s*\}~'
            => '<?php $1 ?>',
        
        //<p:set var="$i" value="123" />
        '~<p:set\s+var\s*=\s*[\'\"](.+)[\'\"]\s+value\s*=\s*[\'\"](.+)[\'\"]\s*/?>~'
            => '<?php $1 = $2 ?>',
        
        //<p:unset var="$i" />
        '~<p:unset\s+var\s*=\s*[\'\"](.+)[\'\"]\s*/?>~'
            => '<?php unset($1) ?>',
        
        //<p:if test='expr'> 
        '~<p:if\s+test\s*=\s*[\'\"](.+)[\'\"]\s*>~'
            => '<?php if($1) { ?>',
        
        //<p:elseif test="expr">
        '~<p:elseif\s+test\s*=\s*[\'\"](.+)[\'\"]\s*>~'
            => '<?php } elseif($1) { ?>',
         
        //<p:else>
        '~<p:else\s*>~'
            => '<?php } else { ?>',
        
          //</p:if>
        '~</p:if>~'
            => '<?php } ?>',
        
        //<p:while test="expr">
         '~<p:while\s+test\s*=\s*[\'\"](.+)[\'\"]\s*>~'
            => '<?php while($1) { ?>',
        
         //</p:while>
        '~</p:while>~'
            => '<?php } ?>',
        
        //<p:foreach value="$book" in="$books">
        '~<p:foreach\s+value\s*=\s*[\'\"](.+)[\'\"]\s+in\s*=\s*[\'\"](.+)[\'\"]\s*/?>~'
            => '<?php foreach($2 as $1) { ?>',
        
        //<p:foreach key="$name" value="$price" in="$books"> 
        '~<p:foreach\s+key\s*=\s*[\'\"](.+)[\'\"]\s+value\s*=\s*[\'\"](.+)[\'\"]\s+in\s*=\s*[\'\"](.+)[\'\"]\s*/?>~'
            => '<?php foreach($3 as $1 => $2) { ?>',
        
         //</p:foreach>
        '~</p:foreach>~'
            => '<?php } ?>',
        
        
        // ＜?php include('nav.php'); ?＞
        '~<\?php\s+(include_once|require_once|include|require)\s*\(\s*(.+?)\s*\)\s*;?\s*\?>~i'
            => '<?php include \$this->_include($2, __FILE__) ?>'
    );
    
    function __construct($cfg=NULL) {
        if ($cfg) {
            $this->config($cfg);
        }
    }
    
    function config($cfg) {
        if (is_string($cfg)) {
            $cfg = require $cfg;
        }
        if (isset($cfg['tpl_dir'])) {
            $this->tpl_dir = $cfg['tpl_dir'];
        }
        if (isset($cfg['tpl_ext'])) {
            $this->tpl_ext = $cfg['tpl_ext'];
        }
        if (isset($cfg['cache_dir'])) {
            $this->cache_dir = $cfg['cache_dir'];
        }
        if (isset($cfg['cache_time'])) {
            $this->cache_time = $cfg['cache_time'];
        }
        if (isset($cfg['ext_rules'])) {
            $this->ext_rules = $cfg['ext_rules'];
        }
        if (isset($cfg['data'])) {
            $this->data = $cfg['data'];
        }
    }
  
    //set data that access from template file
    function assign($name, $value=NULL) {
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                $this->data[$k] = $v;
            }
        }else{
            $this->data[$name] = &$value;
        }
    }
   
    //output execution result of template file 
    function display($tpl_file) {
        $_cache_path = $this->cache_path($tpl_file);
        if (!$this->is_cached($_cache_path)) {
            $this->compile($this->tpl_path($tpl_file), $_cache_path);
        }
        unset($tpl_file);
        extract($this->data); 
        include $_cache_path;
    }
    
    // just return the ouput as string (doesn't display)
    function fetch($tpl_file) {
        ob_start();
        ob_implicit_flush(0);
        $this->display($tpl_file);
        return ob_get_clean();
    }
    
    //reset template data context
    //it is very useful when reuse this class for render multiple template files.
    public function reset() {
        $this->data = array();
    }

    private function tpl_path($tpl_file) {
        return $this->tpl_dir . $tpl_file . $this->tpl_ext;
    }
 
    private function cache_path($tpl_file) {
        return $this->cache_dir . $tpl_file . $this->tpl_ext;
    }
  
    private function is_cached($cache_path) {
        if (!file_exists($cache_path)) {
            return false;
        }
        if ($this->cache_time<0) {
            return true;
        }
        $cache_time = filemtime($cache_path);
        if ( time()-$cache_time > $this->cache_time ) {
            return false;
        }
        return true;
    }
  
    private function compile($tpl_path, $cache_path) {
        $tpl = @file_get_contents($tpl_path);
        if ($tpl===FALSE) {
            die("$tpl_path doesn't exists.");
        }
        
        $tmp = array_merge($this->rep_rules, $this->ext_rules);
        $cache = preg_replace(array_keys($tmp), $tmp, $tpl);
        
        @mkdir(dirname($cache_path), 0777, true);
        
        $tmp = @file_put_contents($cache_path, $cache, LOCK_EX);
        if ($tmp===FALSE) {
            die("can not write cached file ($cache_path).");
        }
    }
  
    private function _include($inc_file, $cache_path) {
        $inc_path = dirname($cache_path) . '/' . $inc_file;
        if (!$this->is_cached($inc_path)) {
            $tpl_path = str_replace(realpath($this->cache_dir), realpath($this->tpl_dir), $inc_path);
            $this->compile($tpl_path, $inc_path);
        }
        return $inc_path;
    }
}

?>
