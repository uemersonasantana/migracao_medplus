<?php
/*
  Autor: Uemerson A. Santana - uemerson@korbantech.com.br
  29/05/2019
*/

// Desabilita tempo máximo de execução
set_time_limit(0);

// Hora de Inicio do Script
$sHoraInicio = date( "H:i:s" );

// Bibliotecas
include_once("funcoes.php");

// Timestamp para data/Hora
$sTimeStampInicio = date("Ymd_His");

// Verifica se nao foi setado o nome do script
if(!isset($sNomeScript)) {
  $sNomeScript = basename(__FILE__);
} 

$dDataConversao = date('Y-m-d H:i:s');


// Seta nome do arquivo de Log, caso já não exista
if(!defined("DB_ARQUIVO_LOG")) {
  $sArquivoLog = "log/".$sNomeScript."_".$sTimeStampInicio.".log";
  define("DB_ARQUIVO_LOG", $sArquivoLog);
}

// Logs...
db_log("", $sArquivoLog);
db_log("*** INICIO Script ".$sNomeScript." ***", $sArquivoLog);
db_log("", $sArquivoLog);

db_log("Arquivo de Log: $sArquivoLog", $sArquivoLog);
db_log("    Script PHP: ".$sNomeScript, $sArquivoLog);
db_log("", $sArquivoLog);
?>