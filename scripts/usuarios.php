<?php
#!/usr/bin/php

/*
  Autor: Uemerson A. Santana - uemerson@korbantech.com.br
  29/05/2019
*/

// Seta Nome do Script
$sNomeScript = basename(__FILE__);

include("../lib/db_conecta.php");

// Limpando tabelas
$pConexaoDestino->query('
	TRUNCATE customer;');
db_log("Limpando tabelas", $sArquivoLog, 1, true, true);

// Início do script no banco.
$pConexaoDestino->query( 'BEGIN' );

$bErro = false;

$i = 0;
// Busca o arquivo csv.
$file = fopen('/var/www/html/migracao_medplus/scripts/csv/usuarios.csv', 'r');

// Lista os dados do arquivo csv.
while (($line = fgetcsv($file)) !== false ) {
	/* Legenda 
		USUÁRIO(INÍCIO DA LINHA)
			-$line[0]
		CARTEIRA  
			-$line[1].$line[2].','. $line[3]
		NOME
			-$line[4]
		CPF
			-$line[5]
		RG
			-$line[6]
		DATA CADASTRO
			-$line[7]
		DATA NASCIMENTO
			-$line[8]
		LOGIN
			-$line[9]
		TEL. RESIDENCIAL
			-$line[10]
		TEL. CELULAR
			-$line[11]
		ENDERECO
			-$line[12]
		END. NÚMERO
			-$line[13]
		BAIRRO
			-$line[14]
		CIDADE
			-$line[15]
		ESTADO
			-$line[16]
		TEL. COMERCIAL
			-$line[17]
		E-MAIL
			-$line[18]
		ULT. UTILIZAÇÃO
			-$line[19]
		TEL. RECADO
			-$line[20]
	*/

	/* Tratamento de variáveis */
	
	// Preparando valor da cateira para DECIMAL
	$line[3] 	= floatval($line[1].$line[2].$line[3]);
	// REMOVENDO TÍTULO DOS CAMPOS	
	$line[10]	= str_replace('RESIDENCIAL:', '', $line[10]);
	$line[11]	= str_replace('CELULAR:', '', $line[11]);
	$line[17]	= str_replace('COMERCIAL:', '', $line[17]);
	$line[20]	= str_replace('RECADO:', '', $line[20]);
	$line[18]	= str_replace('Email:', '', $line[18]);
	$line[12]	= str_replace('Endereço:', '', $line[12]);

	// RETIRANDO CARACTERES
	$line[5]	= retira_caracteres($line[5]);
	$line[10]	= retira_caracteres($line[10]);
	$line[11]	= retira_caracteres($line[11]);
	$line[17]	= retira_caracteres($line[17]);
	$line[20]	= retira_caracteres($line[20]);

	// AJUSTE DATA
	$line[7]	= str_replace('/', '-', $line[7]);
	$line[8]	= str_replace('/', '-', $line[8]);

	$date 		= strtotime($line[7]);
	$line[7] 	= date('Y-m-d H:i:s', $date);

	$date 		= strtotime($line[8]);
	$line[8] 	= date('Y-m-d', $date);

	// ENVIAR ERRO DE CARACTERES
	$line[4] 	= utf8_decode($line[4]);
	$line[12]	= utf8_decode($line[12]);
    $line[14] 	= utf8_decode($line[14]);
	$line[15] 	= utf8_decode($line[15]);
	/*---*/



	$SqlUsuario = "INSERT INTO customer (
			name
			,email
			,carteira
			,cpf
			,rg
			,data_cadastro
			,data_nascimento
			,tel_residencial
			,tel_comercial
			,tel_celular
			,tel_recado
			,endereco
			,end_numero
			,bairro
			,cidade
			,estado
		) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
	$sql = $pConexaoDestino->prepare($SqlUsuario);
	if (!$sql) {
		$bErro = true;
		print_r($pConexaoDestino->errorInfo());
		db_log("Erro SQL migra_consultorio", $sArquivoLog, 1, true, true);
	}
	$sql->execute([
		$line[4]
		,$line[18]
		,$line[3]
		,$line[5]
		,$line[6]
		,$line[7]
		,$line[8]
		,$line[10]
		,$line[17]
		,$line[11]
		,$line[20]
		,$line[12]
		,$line[13]
		,$line[14]
		,$line[15]
		,$line[16]
	]);

	$id_consultorio = $pConexaoDestino->lastInsertId();

	db_log("Processando->Usuário: ".$line[4], $sArquivoLog, 1, true, true);

	$i++;

	if ( $id_consultorio == 0 ) {
		$bErro = true;
		db_log("ERRO!!!! ".$line[4], $sArquivoLog, 1, true, true);
		break;
	}

}

//$i += 8199;
//$i += 8199;
//$i += 8190;
//$i += 1234;

echo '<b>TOTAL: '.$i.'</b>';

// Encerra a comunicação com o arquivo csv.
fclose($file);


// Caso haja erro durante o processo, o script irá cancelar os cadastros, caso contrário ele irá confirmar as alterações.
if ($bErro) {
  	$pConexaoDestino->query( 'ROLLBACK' );
  	$sOperFim = "rollback";
} else { 
	$pConexaoDestino->query( 'commit' );
  	$sOperFim = "commit";
}

db_log("Finalizando transacao [{$sOperFim}] - Total Usuários: $i", $sArquivoLog, 1, true, true);
$pConexaoDestino->query( "{$sOperFim};" );


// Final do Script
include("../lib/db_final_script.php");
?>