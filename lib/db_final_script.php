<?php
/*
  Autor: Uemerson A. Santana - uemerson@korbantech.com.br
  29/05/2019
*/

db_log("Inicio: $sHoraInicio", $sArquivoLog);
db_log("Final.: " . date( "H:i:s"), $sArquivoLog);

db_log("", $sArquivoLog);
db_log("*** FINAL Script ".$sNomeScript." ***", $sArquivoLog);
db_log("", $sArquivoLog);

db_log("\n\n");

if (getenv('ARQLOGCONV') != "") {
  system("echo    " . ($bErro == false?"ok":"e r r o") . "... $sNomeScript >> " . getenv('ARQLOGCONV'));
}
?>
