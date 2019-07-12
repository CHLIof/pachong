<?php
namespace app\pachong\controller;

//import('phpQuery', EXTEND_PATH);
//import('jaeger.querylist',VENDOR_PATH,'.class.php');

use think\Controller;
use think\Db;
use QL\QueryList;

header("Content-Type: text/html; charset=UTF-8");

class Myset 
{

    public function index()
    {
       echo "hello world";
    }

    public function exchangeType()
    {
       $startrow = 1;
       $length = 1000;

       $sign = 1;
       $count = Db::name('content') ->count();

       while($sign)
       {
         $list = Db::name('content')
         ->field('id,leixing')
         ->limit($startrow,$length)
         ->select();

         foreach($list as $key => $value)
         {
          $type = $this->getType($value['leixing']);

          Db::name('content') -> where('id',$value['id'])->update(['type'=>$type]);
         }

         if($startrow+$length >= $count)
         {
           $sign = 0;
         }

         $startrow += $length;
       }

       echo "ok";
      
    }

    public function test()
    {
      $startrow = 1;
      $length = 100;


      $list = Db::name('content')
      ->field('id,leixing')
      ->limit($startrow,$length)
      ->select();

      foreach($list as $key => $value)
      {
        $list[$key]['type'] = $this->getType($value['leixing']);
      }

      dump($list);
    }

    public function out()
    {
      $list = Db::name('content')->where('leixing','类型：视讯美女')->select();

      foreach()
    }

    public function getType($leixing)
    {
       
       $re = getTypeid($leixing);

       if($re>=1 && $re<=9)
       {
        return 0;
       }

       if($re>=10 && $re<=16)
       {
        return 1;
       }

       if($re == 17)
       {
        return 2;
       }

       if($re == 18)
       {
        return 3;
       }

       if($re == 19 || $re == 20)
       {
        return 3;
       }

       return 3;
    }
}