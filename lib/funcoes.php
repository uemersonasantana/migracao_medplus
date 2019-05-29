<?php
/*
  Autor: Uemerson A. Santana - uemerson@korbantech.com.br
  29/05/2019
*/

    // Funcao para dar Echo dos Logs - retorna o TimeStamp
    //
    // Tipos: 0 = Saida Tela e Arquivo
    //        1 = Saida Somente Tela
    //        2 = Saida Somente Arquivo
    //
    //
    function db_log($sLog="", $sArquivo="", $iTipo=0, $lLogDataHora=true, $lQuebraAntes=true) {
      //
      $aDataHora = getdate();

      $sQuebraAntes = $lQuebraAntes?"\n":"";


      if($lLogDataHora) {
        $sOutputLog = sprintf("%s[%02d/%02d/%04d %02d:%02d:%02d] %s", $sQuebraAntes,
                              $aDataHora["mday"], $aDataHora["mon"], $aDataHora["year"],
                              $aDataHora["hours"], $aDataHora["minutes"], $aDataHora["seconds"],
                              $sLog);
      } else {
        $sOutputLog = sprintf("%s%s", $sQuebraAntes, $sLog);
      }


      // Se habilitado saida na tela...
      if($iTipo==0 or $iTipo==1) {
        echo $sOutputLog;
      }

      // Se habilitado saida para arquivo...
      if($iTipo==0 or $iTipo==2) {
        if(!empty($sArquivo)) {
          $fd=fopen($sArquivo, "a+");
          if($fd) { 
            fwrite($fd, $sOutputLog);
            fclose($fd);
          }
          //system("echo '$sOutputLog' >> $sArquivo");
        }
      }

      return $aDataHora;
    }
?>