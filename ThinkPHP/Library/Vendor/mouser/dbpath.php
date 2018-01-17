<?php
$mysql_host='db1.ichunt.com';     //host name
$mysql_pwd='123456';        //password
$mysql_user='root';     //login name
$mysql_db='liexin';     //name of database
$pdo = new PDO("mysql:host={$mysql_host};dbname={$mysql_db}","{$mysql_user}","{$mysql_pwd}");
$pdo->query('set names utf8;');
?>