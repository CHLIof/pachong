<?php
namespace app\index\controller;

//import('phpQuery', EXTEND_PATH);
//import('jaeger.querylist',VENDOR_PATH,'.class.php');

use think\Controller;
use think\Db;
use QL\QueryList;

header("Content-Type: text/html; charset=UTF-8");

class Test
{

   public function printOne()
   {
   	 $list = Db::name('content')
   	       ->where('leixing','类型：动作片')
   	       ->limit(1)
   	       ->select();

   	 foreach($list as $key => $value)
   	 {
   	 	$lianjie = explode(',',$value['lianjie']);

   	 	$list[$key]['lianjie'] = Db::name('lianjie')
   	 	               ->where('id','in',$lianjie)
   	 	               ->select();
        foreach($list[$key]['lianjie'] as $k => $v)
        {
        	$link = explode(',',$v['link']);

        	$list[$key]['lianjie'][$k]['link'] = Db::name('link')
        	        ->where('id','in',$link)
        	        ->select();
        }
   	 }

   	 dump($list);
   }

   public function test()
   {
	   	$data = getImg($page);

	    $title = $data[0]['title'];
	    $img = $data[0]['img'];
	    dump($title);

	    $content = Db::name('content')
	    ->where('title',$title)
	    ->field('id')
	    ->find();

	    if($content)
	    {
	        Db::name('content')
	        ->where('id',$content['id'])
	        ->update(['img'=>$img]);
	    }else{
	       echo "没有找到";
	    }
   }
}