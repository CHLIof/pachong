<?php
namespace app\index\controller;

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

    public function gatherContent()
    {
        $startrow = 0;

        $number = Db::name('list_link')->count();

        whiler()
        {
            $list = Db::name('list_link') ->limit($startrow,$length)->select();
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

    public function test()
    {
    	$start = 130;
    	$end = 550;



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
}
