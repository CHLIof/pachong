<?php
namespace app\pachong\controller;

//import('phpQuery', EXTEND_PATH);
//import('jaeger.querylist',VENDOR_PATH,'.class.php');

use think\Controller;
use think\Db;
use QL\QueryList;

header("Content-Type: text/html; charset=UTF-8");

class Index
{
    public function index()
    {
        
        $number = 550;

        for($i=1;$i<=$number;$i++)
        {
        	$page = 'https://api.123zx.net/?m=vod-index-pg-'.$i.'.html';

        	$this -> getOnePage($page);
        }

		echo "ok";

		//dump($result);
		//dump(1223);
    }

    public function getOnePage($page)
    {
    	//$page = 'https://api.123zx.net/?m=vod-index-pg-1.html';

		$list = getNewList($page);

		$num = count($list);

		for($i=0;$i<$num;$i++)
		{
			$data['link'] = trim($list[$i]['lianjie']);

			$count = Db::name('list_link') ->where('link',$data['link'])->count();

			if($count == 0)
			{
                Db::name('list_link') ->insert($data);
			}

			
		}
    }

    public function insertDB($list)
    {

		$num = count($list);

		for($i=0;$i<$num;$i++)
		{
			$data['link'] = trim($list[$i]['lianjie']);

			$count = Db::name('list_link') ->where('link',$data['link'])->count();

			if($count == 0)
			{
                Db::name('list_link') ->insert($data);
			}

			
		}
    }
    
    //多线程采集内容页
    public function gatherContent()
    {
        $url = "https://api.123zx.net";

        $startrow = 0;
        $length = 100;
        $sign = 1;

        $number = Db::name('list_link')->count();

        while($sign)
        {
            $list = Db::name('list_link') ->limit($startrow,$length)->select();

            $link = array();
            $ids = array();

            foreach($list as $key => $value)
            {
                if($value['is_gather'] == 3)
                {
                   $link[] = $url.$value['link'];
                }

                $ids[] = $value['id'];
             }
              
            //dump($link);
             if($link)
             {
               $this->gatherMulti($link);
             }

            //标记list_link 已采集链接

            Db::name('list_link') ->where('id','in',$ids) ->update(['is_gather'=>4]); 

            if($startrow+$length > $number)
            {
                $sign = 0;
            }

            $startrow += $length;

        }

        dump($number);

    }

    public function newtest()
    {
    	// $data = getNewList('111');
    	// dump($data);

    	$page = 'https://api.123zx.net/?m=vod-detail-id-34393.html';

    	$data = getContent($page);

    	$lianjie = $data['lianjie'];

    	$ids = array();

    	for($i=0;$i<count($lianjie);$i++)
    	{
    		// $link = implode(';', $lianjie[$i]['link']);
    		// $lianjie[$i]['link'] = $link;
    		$links = array();

    		foreach($lianjie[$i]['link'] as $key =>$value)
    		{
    			$link['link'] = $value;
                $links[] = Db::name("link") -> insertGetId($link);

    		}
        
            $lianjie[$i]['link'] = implode(',', $links);
    		

    		$id = Db::name('lianjie') ->insertGetId($lianjie[$i]);

    		$ids[] = $id;
    	}

    	$data['lianjie'] = implode(',', $ids);

    	Db::name('content') -> insert($data);

    	dump($data);
    }

    public function mytest()
    {
    	$list = Db::name('lianjie') -> select();

    	foreach($list as $key => $value)
    	{
    		$list[$key]['link'] = explode(';', $value['link']);

    	}

    	dump($list);
    }
   
   //多线程采集列表页
    public function gatherList()
    {
    	$start = 1;
    	$end = 559;

    	$lt = array();
        for($i=$start;$i<=$end;$i++)
        {
        	$lt[] = 'https://api.123zx.net/?m=vod-index-pg-'.$i.'.html';
        }


		    	//多线程扩展
		QueryList::run('Multi',[
		    //待采集链接集合
		    'list' => $lt
		    ,
		    'curl' => [
		        'opt' => array(
		                    //这里根据自身需求设置curl参数
		                    CURLOPT_SSL_VERIFYPEER => false,
		                    CURLOPT_SSL_VERIFYHOST => false,
		                    CURLOPT_FOLLOWLOCATION => true,
		                    CURLOPT_AUTOREFERER => true,
		                    //........
		                ),
		        //设置线程数
		        'maxThread' => 100,
		        //设置最大尝试数
		        'maxTry' => 3 
		    ],
		    'success' => function($a){
		        //采集规则
		      $reg = array(
  
                    'lianjie' => array('a[target="_blank"]','href'),

                );
		        $rang = '.xing_vb>ul>li';
		       
			    $data = QueryList::Query($a['content'],$reg,$rang)->data;

			    if($data)
			    {
			    	$length = count($data);
			    	unset($data[$length-1]);

			    	if($data[0])
			    	{
			    		unset($data[0]);
			    	}

			    	$data = array_values($data);
			    }

			    $this->insertDB($data);
		        //dump($data);
		    }
		]);
    }

    public function gatherMulti($list)
    {
        //多线程扩展
        QueryList::run('Multi',[
            //待采集链接集合
            'list' => $list
            ,
            'curl' => [
                'opt' => array(
                            //这里根据自身需求设置curl参数
                            CURLOPT_SSL_VERIFYPEER => false,
                            CURLOPT_SSL_VERIFYHOST => false,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_AUTOREFERER => true,
                            //........
                        ),
                //设置线程数
                'maxThread' => 100,
                //设置最大尝试数
                'maxTry' => 3 
            ],
            'success' => function($a){
                $data = getContent($a['content']);
                
                //获取采集链接id---start
                $url = $a['info']['url'];

                $wid = explode('-',$url);

                $wid = explode('.',$wid[3]);

                $wid = $wid[0];
                //获取采集链接id---end

                //插入数据库---start

                $lianjie = $data['lianjie'];

                $ids = array();

                for($i=0;$i<count($lianjie);$i++)
                {
                    
                    $links = array();

                    foreach($lianjie[$i]['link'] as $key =>$value)
                    {
                        $link['link'] = $value;
                        $links[] = Db::name("link") -> insertGetId($link);

                    }
                
                    $lianjie[$i]['link'] = implode(',', $links);
                    

                    $id = Db::name('lianjie') ->insertGetId($lianjie[$i]);

                    $ids[] = $id;
                }

                $data['lianjie'] = implode(',', $ids);
                $data['wid'] = $wid; 

                Db::name('content') -> insert($data);
                //插入数据库---end

            }
        ]);
    }

    public function gatherImgByMulti($list)
    {
        //多线程扩展
        QueryList::run('Multi',[
            //待采集链接集合
            'list' => $list
            ,
            'curl' => [
                'opt' => array(
                            //这里根据自身需求设置curl参数
                            CURLOPT_SSL_VERIFYPEER => false,
                            CURLOPT_SSL_VERIFYHOST => false,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_AUTOREFERER => true,
                            //........
                        ),
                //设置线程数
                'maxThread' => 100,
                //设置最大尝试数
                'maxTry' => 3 
            ],
            'success' => function($a){
                $data = getImg($a['content']);

                //插入数据库---start

                $title = $data[0]['title'];
                $img = $data[0]['img'];

                $content = Db::name('content')
                ->where('title',$title)
                ->field('id')
                ->find();

                if($content)
                {
                    Db::name('content')
                    ->where('id',$content['id'])
                    ->update(['img'=>$img]);
                }
                //插入数据库---end

            }
        ]);
    }

    //多线程采集图片
    public function gatherImg()
    {
        $url = "https://api.123zx.net";

        $startrow = 0;
        $length = 100;
        $sign = 1;

        $number = Db::name('list_link')->count();

        while($sign)
        {
            $list = Db::name('list_link') ->limit($startrow,$length)->select();

            $link = array();
            $ids = array();

            foreach($list as $key => $value)
            {
                if($value['is_gather'] == 1)
                {
                   $link[] = $url.$value['link'];
                }

                $ids[] = $value['id'];
             }
              
            //dump($link);
             if($link)
             {
               $this->gatherImgByMulti($link);
             }

            //标记list_link 已采集链接

            Db::name('list_link') ->where('id','in',$ids) ->update(['is_gather'=>2]); 

            echo '第'.$startrow.'行----'.($startrow+$length).'行';
            echo '<br\>';

            if($startrow+$length > $number)
            {
                $sign = 0;
            }

            $startrow += $length;

        }

        dump($number);
    }


    //更新采集
    public function updateData()
    {
    

        $start = 1;
        $end = 10;

        $lt = array();
        for($i=$start;$i<=$end;$i++)
        {
            $lt[] = 'https://api.123zx.net/?m=vod-index-pg-'.$i.'.html';
        }

        //Db::name('config') ->where('id',1)->update(['last_update'=>strtotime('today')]);

        QueryList::run('Multi',[
            //待采集链接集合
            'list' => $lt
            ,
            'curl' => [
                'opt' => array(
                            //这里根据自身需求设置curl参数
                            CURLOPT_SSL_VERIFYPEER => false,
                            CURLOPT_SSL_VERIFYHOST => false,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_AUTOREFERER => true,
                            //........
                        ),
                //设置线程数
                'maxThread' => 100,
                //设置最大尝试数
                'maxTry' => 3 
            ],
            'success' => function($a){
                //采集规则
              $reg = array(
  
                    'lianjie' => array('a[target="_blank"]','href'),
                    'update7'  => array('.xing_vb7','text'),
                    'update6'  => array('.xing_vb6','text'),


                );
                $rang = '.xing_vb>ul>li';
               
                $data = QueryList::Query($a['content'],$reg,$rang)->data;

                if($data)
                {
                    $length = count($data);
                    unset($data[$length-1]);

                    if($data[0])
                    {
                        unset($data[0]);
                    }

                    $data = array_values($data);
                }

                foreach($data as $key =>$value)
                {
                    $insert['link'] = $value['lianjie'];
                    if($value['update7'] != '')
                    {
                        $insert['update'] = strtotime($value['update7']);
                    }elseif($$value['update6'] != '')
                    {
                        $insert['update'] = strtotime($value['update6']);
                    }else{
                        $insert['update'] = time();
                    }

                    Db::name('update') -> insert($insert);
                }

                dump($data);

                //$this->insertDB($data);
                //dump($data);
                Db::name('config') ->where('id',1)->update(['this_update'=>strtotime('today')]);
            }
        ]);

    }

    //采集更新

    public function executeUpdate()
    {
        $url = "https://api.123zx.net";
        $last_update = Db::name('config') -> where('id',1)->value('last_update');

        $updateList = Db::name('update') 
        ->where('update','>=',$last_update)
        ->select();

        $list = array();

        foreach($updateList as $key => $value)
        {
            $count = Db::name('list_link')->where('link',$value['link'])->count();

            if($count == 0)
            {
                Db::name('list_link') -> insert(['link'=>$value['link']]);
            }

           $list[] = $url.$value['link'];
        }

         //多线程扩展
        QueryList::run('Multi',[
            //待采集链接集合
            'list' => $list
            ,
            'curl' => [
                'opt' => array(
                            //这里根据自身需求设置curl参数
                            CURLOPT_SSL_VERIFYPEER => false,
                            CURLOPT_SSL_VERIFYHOST => false,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_AUTOREFERER => true,
                            //........
                        ),
                //设置线程数
                'maxThread' => 100,
                //设置最大尝试数
                'maxTry' => 3 
            ],
            'success' => function($a){
                $data = getContent($a['content']);
                
                //获取采集链接id---start
                $url = $a['info']['url'];

                $wid = explode('-',$url);

                $wid = explode('.',$wid[3]);

                $wid = $wid[0];
                //获取采集链接id---end

                //插入数据库---start

                $widNum = Db::name('content')->where('wid',$wid)->count();
                if($widNum == 0)
                {

                    $lianjie = $data['lianjie'];

                    $ids = array();

                    for($i=0;$i<count($lianjie);$i++)
                    {
                        
                        $links = array();

                        foreach($lianjie[$i]['link'] as $key =>$value)
                        {
                            $link['link'] = $value;
                            $links[] = Db::name("link") -> insertGetId($link);

                        }
                    
                        $lianjie[$i]['link'] = implode(',', $links);
                        

                        $id = Db::name('lianjie') ->insertGetId($lianjie[$i]);

                        $ids[] = $id;
                    }

                    $data['lianjie'] = implode(',', $ids);
                    $data['wid'] = $wid; 

                    Db::name('content') -> insert($data);
            }else{

               $content = Db::name('content') ->where('wid',$wid)->find();

               $lj = explode(',',$content['lianjie']);

               $ljArray = Db::name('lianjie')->where('id','in',$lj)->select();

               foreach ($ljArray as $key => $value) {
                   $lk = explode(',',$value['link']);

                   Db::name('link')->where('id','in',$lk)->delete();

                   Db::name('lianjie') ->where('id',$value['id'])->delete();
               }

               // foreach($lianjie as $key => $value)
               // {
               //   $lj = Db::name('lianjie')->where('id',$value)->find();


               // }

               ////////
               $lianjie = $data['lianjie'];

                $ids = array();

                for($i=0;$i<count($lianjie);$i++)
                {
                    
                    $links = array();

                    foreach($lianjie[$i]['link'] as $key =>$value)
                    {
                        $link['link'] = $value;
                        $links[] = Db::name("link") -> insertGetId($link);

                    }
                
                    $lianjie[$i]['link'] = implode(',', $links);
                    

                    $id = Db::name('lianjie') ->insertGetId($lianjie[$i]);

                    $ids[] = $id;
                }

                $data['lianjie'] = implode(',', $ids);
                $data['wid'] = $wid; 

                Db::name('content') ->where('wid',$wid)->update(['lianjie'=>$data['lianjie']]);
            }
             //插入数据库---end
            }
        ]);


        $thisUpdate = Db::name('config')->where('id',1)->value('this_update');

        Db::name('config') ->where('id',1)->update(['last_update'=>$thisUpdate]);
    }

    public function test626()
    {
        $list = [
            "https://api.123zx.net/?m=vod-detail-id-32892.html"
        ];
        QueryList::run('Multi',[
            //待采集链接集合
            'list' => $list
            ,
            'curl' => [
                'opt' => array(
                            //这里根据自身需求设置curl参数
                            CURLOPT_SSL_VERIFYPEER => false,
                            CURLOPT_SSL_VERIFYHOST => false,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_AUTOREFERER => true,
                            //........
                        ),
                //设置线程数
                'maxThread' => 100,
                //设置最大尝试数
                'maxTry' => 3 
            ],
            'success' => function($a){
                $url = $a['info']['url'];

                $wid = explode('-',$url);

                $wid = explode('.',$wid[3]);

                $wid = $wid[0];
                dump($wid);

            }
        ]);
    }


    public function test0704()
    {
        $date = strtotime('2019-07-04');

        dump($date);
    }
}
