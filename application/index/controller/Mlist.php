<?php
namespace app\index\controller;

//import('phpQuery', EXTEND_PATH);
//import('jaeger.querylist',VENDOR_PATH,'.class.php');

use think\Controller;
use think\Db;
use QL\QueryList;

header("Content-Type: text/html; charset=UTF-8");

class Mlist extends Controller
{
    public function index()
    {
      $type = input('type');

     //s $type = 0;

      $records = Db::name('content')
      ->where('type',$type)
      ->count();

      $list = Db::name('content')
      ->where('type',$type)
      ->order('id desc')
      ->paginate(config('paginate.list_rows'),$records);

      $page = $list->render();
      $list = $list->items();

      $type = getBType($type);

      $this->assign(['list'=>$list,'page'=>$page,'records'=>$records,'type'=>$type]);

      return view();
         
    }

    
}
