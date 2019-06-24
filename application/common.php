<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
use QL\QueryList;

function getList($page)
{
    $reg = array(

        'lianjie' => array('.xing_vb','html'),

    );


    $rang = 'body';
    $ql = QueryList::Query($page,$reg,$rang);
    $data = $ql->getData();

    $str = $data[0]['lianjie'];
    $doc = phpQuery::newDocumentHTML($str);
    $li = pq($doc)->find('a[target="_blank"]');

    $liArray = get_object_vars($li);

    $li_num = count($liArray['elements']);

    $list = array();
    for($i=0;$i<$li_num;$i++)
    {
        $condition = 'a[target="_blank"]:eq('.$i.')';
        $a = pq($condition,$doc)->attr('href');
        $list[] = $a;
    }

    return $list;
}

function getContent($page)
{

    $reg = array(
        //采集文章标题
         'title' => array('h2','text'),//片名
         'bieming' => array('.vodinfobox li:eq(0)','text'),
         'daoyan'  => array('.vodinfobox li:eq(1)','text'),
         'zhuyan'  => array('.vodinfobox li:eq(2)','text'),
         'leixing'  => array('.vodinfobox li:eq(3)','text'),
         'diqv'  => array('.vodinfobox li:eq(4)','text'),
         'yuyan'  => array('.vodinfobox li:eq(5)','text'),
         'shangying'  => array('.vodinfobox li:eq(6)','text'),
         'genxin'  => array('.vodinfobox li:eq(7)','text'),
        'lianjie' => array('.vodplayinfo div','html','',function($content)
        {
            $doc = phpQuery::newDocumentHTML($content);
            $h3 = pq($doc)->find('h3');

            $h3Array = get_object_vars($h3);

            $h3_num = count($h3Array['elements']);

            $lj = array();

            for($i=0;$i<$h3_num;$i++)
            {
                $lj[$i]['resource'] = $h3Array['elements'][$i]->textContent;

                $find = 'ul:eq('.$i.') li';
                $size = pq($find,$doc)->size();

                $link = array();
                for($j=0;$j<$size;$j++)
                {
                    $condition = 'ul:eq('.$i.') li:eq('.$j.')';
                    $link[] = pq($condition,$doc)->text();
                }

                $lj[$i]['link'] = $link;
            }

            return  $lj;
        }),

    );

    $rang = '.warp';
    $ql = QueryList::Query($page,$reg,$rang);
    $data = $ql->getData();


    return  $data[0];
}

function getNewList($page)
{
	$page = 'https://api.123zx.net/?m=vod-index-pg-1.html';

	$reg = array(

        'lianjie' => array('a[target="_blank"]','href'),

    );

    $range = '.xing_vb>ul>li';

    $data = QueryList::Query($page,$reg,$range)->data;

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

    return $data;
}