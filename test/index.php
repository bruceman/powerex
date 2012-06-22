<?php
    require_once '../Powerex.php';
    
    $pstl = new Powerex();
    $pstl->cache_dir = "./cached/";
    $pstl->tpl_dir = "./";
    
    $pstl->assign("name", "bruce");
    $pstl->assign("age", "100");
    $pstl->assign("books", array("Java in action","Thinking in java"));
    $pstl->assign("songs", array("a"=> "beyond", "zj" => array("zj-1","zj-2")));
    
    $pstl->assign("link1", "http://google.com");
    $pstl->assign("link2", "http://bing.com");
    
    //$pstl->display("view");
    echo $pstl->fetch("view");
?>
