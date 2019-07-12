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

error_reporting(E_ERROR | E_WARNING | E_PARSE);

//获取列表页
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
//获取内容页
function getContent($page)
{

    $reg = array(
        //采集文章标题
         'title' => array('h2','text'),//片名
         'img'   => array('.lazy','src'),
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

 function getImg($page)
{
    //$page = 'https://api.123zx.net/?m=vod-detail-id-33773.html';


    $reg = array(

        'title' => array('h2','text'),//片名
        'img'   => array('.lazy','src'),

    );

    $rang = '.warp';

    //$ql = QueryList::Query($page,$reg,$rang);

    $data = QueryList::Query($page,$reg,$range)->data;

    return $data;

}


//获取列表页---新算法
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

/**
 * CURL请求
 * @param $url 请求url地址
 * @param $method 请求方法 get post
 * @param null $postfields post数据数组
 * @param array $headers 请求header信息
 * @param bool|false $debug  调试开启 默认false
 * @return mixed
 */
function httpRequest($url, $method, $postfields = null, $headers = array(), $debug = false) {
    $method = strtoupper($method);
    $ci = curl_init();
    /* Curl settings */
    curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ci, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64; rv:34.0) Gecko/20100101 Firefox/34.0");
    curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 60); /* 在发起连接前等待的时间，如果设置为0，则无限等待 */
    curl_setopt($ci, CURLOPT_TIMEOUT, 7); /* 设置cURL允许执行的最长秒数 */
    curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
    switch ($method) {
        case "POST":
            curl_setopt($ci, CURLOPT_POST, true);
            if (!empty($postfields)) {
                $tmpdatastr = is_array($postfields) ? http_build_query($postfields) : $postfields;
                curl_setopt($ci, CURLOPT_POSTFIELDS, $tmpdatastr);
            }
            break;
        default:
            curl_setopt($ci, CURLOPT_CUSTOMREQUEST, $method); /* //设置请求方式 */
            break;
    }
    $ssl = preg_match('/^https:\/\//i',$url) ? TRUE : FALSE;
    curl_setopt($ci, CURLOPT_URL, $url);
    if($ssl){
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE); // 不从证书中检查SSL加密算法是否存在
    }
    //curl_setopt($ci, CURLOPT_HEADER, true); /*启用时会将头文件的信息作为数据流输出*/
    curl_setopt($ci, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ci, CURLOPT_MAXREDIRS, 2);/*指定最多的HTTP重定向的数量，这个选项是和CURLOPT_FOLLOWLOCATION一起使用的*/
    curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ci, CURLINFO_HEADER_OUT, true);
    /*curl_setopt($ci, CURLOPT_COOKIE, $Cookiestr); * *COOKIE带过去** */
    $response = curl_exec($ci);
    $requestinfo = curl_getinfo($ci);
    $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
    if ($debug) {
        echo "=====post data======\r\n";
        var_dump($postfields);
        echo "=====info===== \r\n";
        print_r($requestinfo);
        echo "=====response=====\r\n";
        print_r($response);
    }
    curl_close($ci);
    return $response;
}


//从数据获取连接信息获取影视信息
function getInfo($list)
{

}

//获取资源类型

function getBType($type)
{
  if($type==0)
  {
    return '电影';
  }

  if($type==1)
  {
    return '电视剧';
  }

  if($type==2)
  {
    return '动漫';
  }

   if($type==3)
  {
    return '综艺';
  }
}


function subtext($text, $length)
{
 
if(mb_strlen($text,'utf8') > $length)
 
return mb_substr($text,0,$length,'utf8').'…';
 
return $text;
 
}

//获取集数
function getJS($link)
{
  $re = explode('$',$link);

  return $re[0];
}

//获取连接
function getLink($link)
{
  $re = explode('$',$link);

  return $re[1];
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
    //其他   20
 function getTypeid($leixing)
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
