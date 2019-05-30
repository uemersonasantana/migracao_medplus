<?php
/*
  Autor: Uemerson A. Santana - uemerson@korbantech.com.br
  29/05/2019
*/
  
include("db_inicio_script.php");

define( 'MYSQL_HOST', 'localhost' );
define( 'MYSQL_USER', 'uemerson' );
define( 'MYSQL_PASSWORD', 'uemerson' );
define( 'MYSQL_DB_NAME', 'medplus' );


try
{
    $pConexaoDestino = new PDO( 'mysql:host=' . MYSQL_HOST . ';dbname=' . MYSQL_DB_NAME, MYSQL_USER, MYSQL_PASSWORD);
    //,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
    /*,
    array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_PERSISTENT => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    )*/
}
catch ( PDOException $e )
{
    echo 'Erro ao conectar com o MySQL: ' . $e->getMessage();
}