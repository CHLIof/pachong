<?php
namespace app\index\controller;

//import('phpQuery', EXTEND_PATH);
//import('jaeger.querylist',VENDOR_PATH,'.class.php');

use think\Controller;
use think\Db;
use QL\QueryList;

header("Content-Type: text/html; charset=UTF-8");

class Single extends Controller
{
    public function index()
    {
     
       $id = input('id');

       //$id = 41333;

       if(empty($id))
       {
        $content = Db::name('content')
        ->order('id desc')
        ->find();
       }else{
        $content = Db::name('content')
       ->where('id',$id)
       ->find();
       }

       // $content = Db::name('content')
       // ->where('id',$id)
       // ->find();

       $lianjie = explode(',',$content['lianjie']);

       $ljArray = Db::name('lianjie')
       ->where('id','in',$lianjie)
       ->select();

       foreach($ljArray as $key =>$value)
       {
        $link = explode(',',$value['link']);

        $ljArray[$key]['link'] = Db::name('link')
        ->where('id','in',$link)
        ->select();
       }

       $this->assign(['content'=>$content,'ljArray'=>$ljArray]);

       //dump($ljArray);
       
        return view();
        
    }

}
