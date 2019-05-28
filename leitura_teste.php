<?php

$meuArray = Array();
$file = fopen('arquivo.csv', 'r');
$i=0;
while (($line = fgetcsv($file)) !== false)
{

  if ( $line[0] == '1consultorio' ) {
  	echo '<br /><br /><br />';	
  	echo 'Consultório: '.$line[1];
  }


  if ( $line[0] == '2profissional' ) {
  	unset($profissional);
  	echo '<br />.....';	
  	echo 'Profissional: '.$line[1];
  	$profissional = $line[1];
  }

  if ( $line[0] == '3procedimento' ) {
  	echo '<br />..........';	
  	echo 'Procedimento: '.$line[1];
  }
  
  



  
 // print_r($meuArray[0][0]);
  
  
}
fclose($file);
print_r($meuArray);