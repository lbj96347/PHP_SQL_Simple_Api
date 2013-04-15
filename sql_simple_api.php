<?php

/*
 * author : CashLee
 * date : 2013/04/15
 *
 * 简单介绍：PHP_SQL_Simple_API 是根据微博美女项目的服务端进行提炼而推出的
 *
 * 应用场景：适用于简单的数据库提取，写入。进而和手机或者Web App进行交互时，提供JSON数据输出。
 *
 * 原理：封装了简单的数据库类，编写SQL，以及传入需要输出的字段即可。可拓展性以及维护性高
 *
 * 封装数据库类
 * 使用方法
 *
 * $example_object = new database;
 * $example_object->sql = 'SELCET ... ';//SQL语句
 * $example_object->data_item_structure = array('num1','num2');//需要查询的字段
 * $example_object->output_array;//如果设置为true的话，返回的结果将会以数组的形式输出，对于合并，多表联查数组有重要意义
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
  var $output_array;
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
      if( $this->output_array == true ){
        return $data_group;
      }
      else{
        return json_encode( $data_group ) ;
      }
      mysql_close($con);
    }
  }
}
