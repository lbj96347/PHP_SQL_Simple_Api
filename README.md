PHP_SQL_Simple_Api
==================

 * author : CashLee

 * date : 2013/03/29

 * 简单介绍：PHP_SQL_Simple_API 是根据微博美女项目的服务端进行提炼而推出的

 * 应用场景：适用于简单的数据库提取，写入。进而和手机或者Web App进行交互时，提供JSON数据输出。

 * 原理：封装了简单的数据库类，编写SQL，以及传入需要输出的字段即可。可拓展性以及维护性高
 
Usage
=====

  1.switch用于指定请求路径，对应处理函数；

  2.数据库类使用办法

     include('sql_simple_api.php');

     $example_object = new database;

     $example_object->sql = 'SELCET ... ';//SQL语句

     $example_object->output_array = true;//设置为true的时候会在结果返回数组，这个时候对于处理一对多，多表联查的时候有作用

     $example_object->data_item_structure = array('num1','num2');//需要查询的字段

     $example_object->connect_db()//最后输出结果，以json形式输出

