<?php
namespace app\store\controller;

//import('phpQuery', EXTEND_PATH);
//import('jaeger.querylist',VENDOR_PATH,'.class.php');

use think\Controller;
use think\Db;
//use QL\QueryList;

//header("Content-Type: text/html; charset=UTF-8");

class Index
{
    public function index()
    {
        $re = Db::name('admin')->select();

        dump($re);
    }
 

    //数据库信息转储
    public function store()
    { 
      $startrow = 1;
      $length = 100;

      $db2 = Db::connect('db2');

      $number = Db::name('content')->count();
      $sign = 1;

      while($sign)
      {

      $list = Db::name('content')
      ->limit($startrow,$length)
      //->where('id',3426)
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

        //开始插入到 电影网 数据库----start
        $info = $list[$key];

        $id = $db2 -> name('archives') 
        -> order('id desc')
        -> field('id')
        -> limit(1)
        ->select();

        $id = $id[0]['id'];

        $id++;

        $typeid = $this->getTypeid($info['leixing']);

        $data_archives = array(
            'id' => $id,
            'typeid'=>$typeid,
            'sortrank'=>time(),
            'ismake'=>0,
            'channel'=>17,
            'title' => $info['title'],
            'writer' => 'admin',
            'source' => '未知',
            'litpic' => $info['img'],
            'pubdate'=>time(),
            'senddate'=>time(),
            'mid'=>1,
            'dutyadmin'=>1,
            'weight'=>3380,
            'status'=>1

        );

        $re = $db2->name('archives')->insert($data_archives);

        if($re)
        {
            $uu = array();
            $uuck = array();
            if($info['lianjie'][0]['link'])
            {
               $link_33uu = $info['lianjie'][0]['link'];
               
               foreach($link_33uu as $k => $v)
               {
                $uu[] = $v['link'];
               }
            }

            if($info['lianjie'][1]['link'])
            {
                $link_33uuck = $info['lianjie'][1]['link'];

                foreach($link_33uuck as $k => $v)
                {
                    $uuck[] = $v['link'];
                }
            }

            $uu = implode(';',$uu);
            $uuck = implode(';',$uuck);

            
            $data_add = array(
             'aid' => $id,
             'typeid'=> $typeid,    
             'authors'=> $info['zhuyan'],
             'areas' => $info['diqv'],
             '33uu' => $uu,
             '33uuck'=> $uuck,
             'shangying'=>$info['shangying']

            );

            $db2->name('addonmovie')->insert($data_add);

            $data_arc = array(
                'id' => $id,
                'typeid'=> $typeid,
                'channel'=>17,
                'senddate'=>time(),
                'sortrank'=>time(),
                'mid'=>1
            );

            $db2->name('arctiny') ->insert($data_arc);
        }
        //开始插入到 电影网 数据库----end

      }

      if($startrow+$length>$number)
      {
        $sign = 0;
      }

      $startrow += $length;

     }

      dump($info);

    }


    //获取电影网记录的typeid
    //电影      1
    //动作片    2
    //喜剧片    3
    //爱情片    4
    //科幻片    5
    //剧情片    6
    //恐怖片    7
    //文艺片    8
    //战争片    9
    //电视剧    10
    //国产电视剧  11
    //日韩电视剧  12
    //港台电视剧  13
    //欧美电视剧  15
    //新马泰电视剧 16
    //卡通动漫    17
    //综艺片      18
    //影视预告    19
    public function getTypeid($leixing)
    {
           $leixing = explode('：',$leixing);

           $lx = trim($leixing[1]);

           $lx = explode(' ',$lx);

           $lx = $lx[0];

           //dump($lx);

           if($lx == '综艺')
           {
             return 18;
           }

           if($lx == '韩剧')
           {
            return 12;
           }

           if($lx == '伦理类')
           {
            return 6;
           }

           if($lx == '动作片')
           {
            return 2;
           }

           if($lx == '剧情片')
           {
            return 6;
           }

           if($lx == '泰剧')
           {
            return 16;
           }

           if($lx == '欧美剧')
           {
            return 15;
           }


           if($lx == '喜剧片')
           {
            return 3;
           }

           if($lx == '动漫')
           {
            return 17;
           }

           if($lx == '纪录片')
           {
            return 18;
           }

           if($lx == '台剧')
           {
            return 13;
           }

           if($lx == '国产剧')
           {
            return 11;
           }

           if($lx == '日剧')
           {
            return 12;
           }

           if($lx == '港剧')
           {
            return 13;
           }

           if($lx == '恐怖片')
           {
            return 7;
           }

           if($lx == '爱情')
           {
            return 4;
           }

           if($lx == '战争片')
           {
            return 9;
           }

           if($lx == '科幻片')
           {
            return 5;
           }

           if($lx == '电影')
           {
            return 1;
           }

           if($lx == '连续剧')
           {
            return 10;
           }

           if($lx == '越南剧')
           {
            return 16;
           }





           return 20;
    }
    

    public function test()
    {
          $list = Db::name('link')
         ->limit(1)
         ->select();


         $db2 = Db::connect('db2');

         $result = $db2->name('admin')->select();


         dump($result);
         dump($list);
    }

}
