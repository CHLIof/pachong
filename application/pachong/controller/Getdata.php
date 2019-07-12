<?php
namespace app\pachong\controller;

//import('phpQuery', EXTEND_PATH);
//import('jaeger.querylist',VENDOR_PATH,'.class.php');

use think\Controller;
use think\Db;
use QL\QueryList;

header("Content-Type: text/html; charset=UTF-8");

class Getdata
{
	public function index()
	{

	}

	public function getData()
	{
        $startrow = input('startrow');
        $length = input('length');

		$list = Db::name('content')
		->limit($startrow,$length)
		->select();

		return $list;
	}

	public function  test()
	{
		echo "ok";
	}

}