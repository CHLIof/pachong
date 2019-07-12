<?php
namespace app\index\controller;

//import('phpQuery', EXTEND_PATH);
//import('jaeger.querylist',VENDOR_PATH,'.class.php');

use think\Controller;
use think\Db;
use QL\QueryList;

header("Content-Type: text/html; charset=UTF-8");

class Index extends Controller
{
    public function index()
    {
     
       //电影
       $dy = Db::name('content')
       ->where('type',0)
       ->order('id desc')
       ->limit(12)
       ->select();

       //电视剧
       $dsj = Db::name('content')
       ->where('type',1)
       ->order('id desc')
       ->limit(12)
       ->select();

       //动漫
       $dm = Db::name('content')
       ->where('type',2)
       ->order('id desc')
       ->limit(12)
       ->select();

       //综艺
       $zy = Db::name('content')
       ->where('type',3)
       ->order('id desc')
       ->limit(12)
       ->select();


        $this->assign(['dy'=>$dy,'dsj'=>$dsj,'dm'=>$dm,'zy'=>$zy]);
       
        return view();
        
    }

    public function article_movie()
    {
        return view();
    }
}
