<?php

/*
 * author : CashLee
 * date : 2013/03/29
 *
 * 简单介绍：PHP_SQL_Simple_API 是根据微博美女项目的服务端进行提炼而推出的
 *
 * 应用场景：适用于简单的数据库提取，写入。进而和手机或者Web App进行交互时，提供JSON数据输出。
 *
 * 原理：封装了简单的数据库类，编写SQL，以及传入需要输出的字段即可。可拓展性以及维护性高
 */


/*
 *
 * 参数列表
 * socket : square , rank , location , person , album 
 *
 */

$_socket = $_GET['socket'];

switch( $_socket )
{
  case 'square' :  
    square();
    break;
  case 'rank' :  
    //
    break;
  case 'location' : 
    location();
    break;
  case 'person' : 
    person();
    break;
  case 'album' : 
    album();
    break;
  case 'like' :
    like();
    break;
  case 'ad' :
    advertise();
    break;
}

/*
 *
 * 封装数据库类
 * 使用方法
 *
 * $example_object = new database;
 * $example_object->sql = 'SELCET ... ';//SQL语句
 * $example_object->data_item_structure = array('num1','num2');//需要查询的字段
 * $example_object->connect_db()//最后输出结果，以json形式输出
 *
 * (完）
 *
 * 如果使用云计算平台，请根据云计算平台配置数据库方式配置数据库链接
 *
 */

class database {

  var $sql;//查询、插入或者更新数据库的语句
  var $data_item_structure;
  var $fetch_switch;
  var $local_debug = false;//上线时记得把选项改为false，确定目前时发布版本

  function connect_db(){
    header("Content-type: application/json;");
    if( $this->local_debug ){
      $con = mysql_connect("127.0.0.1","root","");//write your loca database configure 
      $con ? : die('Could not connect: ' . mysql_error());
    }
    else{
      $con = mysql_connect( SAE_MYSQL_HOST_M . ':' . SAE_MYSQL_PORT, SAE_MYSQL_USER ,SAE_MYSQL_PASS);
      $con ? : die('Could not connect: ' . mysql_error());
    }
    $this->local_debug ? mysql_select_db("test", $con ) : mysql_select_db(SAE_MYSQL_DB, $con); 
    mysql_query("set names utf8;");
    $result = mysql_query( $this->sql );
    if( $this->fetch_switch == true ){
      /*
       * 看看是否开启fetch
       * 一般insert , update 都不会开启
       */
      mysql_close($con);
      return true;
    }else{
      $data_group = array();
      while($row = mysql_fetch_array($result))
      {
        $field_array = $this->data_item_structure;
        $data_item = array();
        for( $i = 0 ; $i < count($field_array) ; $i++ ){
          $data_item[ $field_array[$i] ] = $row[ $field_array[$i] ];
        }
        array_push( $data_group, $data_item );
      }
      return json_encode( $data_group ) ;//json_encode( $row_array );//查询的结果，并转换成json格式 
      mysql_close($con);
    }
  }
}




/*
 *
 * 链接数据
 *
 * GET接口
 *
 */

function square(){
  //$page = $_GET['page'];
  $square_object = new database;
  $square_object->sql = 'SELECT * FROM `girl_user` LIMIT 5';
  $square_object->data_item_structure = array(
    /* 根据key value */
    'uid',
    'username',
    'userlocation',
    'education'
  );
  print_r( $square_object->connect_db() );
} 

function location(){
  /*
   *
   * 逆向解析用户经纬度
   * default 广州大学 经纬度 = 23.04433 , 113.36721
   * 解析出城市，省份 提供相关的美女出来
   * 随机页数，制造每次刷新美女数据出现不同
   * 用后端数据的userlocation 字段 和百度api返回的city 来进行match
   *
   *
   * 1.0先用城市进行匹配，升级后将加用school
   *
   */

  $longitude = $_GET['lon'];//经度
  $latitude = $_GET['lat'];//纬度

  if( $longitude == null || $latitude == null || (int)$longitude == 0 || (int)$latitude == 0 ){
    $latitude =  23.04433;
    $longitude = 113.36721; 
  }

  $location_object = new database;

  /* request baidu map api for more location information  */
  
  $baidu_location_message = file_get_contents('http://api.map.baidu.com/geocoder?location='. $latitude .','. $longitude .'&output=json&key=2WItBpcOQTRvmBodovRGnY9i');

  $baidu_location_array =  json_decode( $baidu_location_message );

  $city = $baidu_location_array->result->addressComponent->city; 

  $city_last_string = substr($city , strlen( $city ) - 3 , strlen( $city ) );

  if( $city_last_string == '市' ){
    $city_string = substr($city , 0 , strlen( $city ) - 3 );
  }
  else{
    $city_string = $city;
  }

  $location_object->sql = 'SELECT * FROM `girl_user` WHERE  userlocation = "'. $city_string .'" LIMIT 20';//添加一些其他地区，外国，没有location的结果进来
  $location_object->data_item_structure = array(
    /* 根据key value */
    'uid',
    'username',
    'userlocation',
    'education'
  );

  print_r( $location_object->connect_db() );
} 

function person(){
  $uid = $_GET['uid'];
  $person_object = new database;
  $person_object->sql = 'SELECT * FROM `girl_user` WHERE uid = '.$uid;
  $person_object->data_item_structure = array(
    'uid',
    'icon',
    'username',
    'weibo_id',
    'userlocation',
    'shape',
    'like'
  );
  print_r( $person_object->connect_db() );
} 

function album(){
  $uid = $_GET['uid'];
  $album_object = new database;
  $album_object->sql = 'SELECT * FROM `girl_photos` WHERE uid = '.$uid;
  $album_object->data_item_structure = array(
    'uid',
    'photo'
  );
  print_r( $album_object->connect_db() );
}

function rank(){
  $rank_object = new database;
  $rank_object->sql = 'SELECT * FROM girl_user ORDER BY like DESC LIMIT 0,30 OFFSET 0';
  $rank_object->data_item_structure = array(
    /* 根据key value */
    'uid',
    'username',
    'userlocation',
    'education',
    'icon'
  );
  print_r( $rank_object->connect_db() );
} 


/*
 *
 * 链接数据
 *
 * POST接口 
 *
 */

function like(){
  $uid = $_GET['uid'];
  /*
   *
   * ”赞“功能为了防止刷票行为，需要加入一个时间戳
   *
   */
  $like_update_object = new database;
  $like_get_object = new database;

  $like_update_object->sql = 'UPDATE `girl_user` SET `like`=`like`+1  WHERE uid = "'.$uid .'"';
  $like_update_object->fetch_switch = true;
  $like_update_object->connect_db();

  $like_get_object->sql = 'SELECT * FROM `girl_user` WHERE uid = "'.$uid .'"' ;
  $like_get_object->data_item_structure = array(
    'uid',
    'like'
  );

  print_r( $like_get_object->connect_db() );

}


/*
 *
 * 在线广告图片接口 
 * 含跳转链接
 *
 */

function advertise(){
  /* 
   *
   * 接收接口参数，看到底是显示哪个区域的广告图
   *
   */
  $advertise_area = $_GET['area'];
}
