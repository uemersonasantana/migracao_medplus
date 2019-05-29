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
	TRUNCATE migra_consultorio;
	TRUNCATE migra_con_pro_esp;
	TRUNCATE migra_parceiro;
	TRUNCATE migra_procedimento;');
db_log("Limpando tabelas", $sArquivoLog, 1, true, true);

// Início do script no banco.
$pConexaoDestino->query( 'BEGIN' );

$bErro = false;

$t_consultorio 		= 0;
$t_profissional 	= 0;
$t_procedimento 	= 0;
$t_profissional_repetido	= 0;
$t_procedimento_repetido	= 0;

// Busca o arquivo csv.
$file = fopen('/var/www/html/migracao_medplus/scripts/csv/arquivo.csv', 'r');

// Lista os dados do arquivo csv.
while (($line = fgetcsv($file)) !== false ) {

  // Tratamento para consultório.
  if ( $line[0] == '1consultorio' ) {
	
	// Verifico se o registro existe, se sim ele pegará o ID no banco, se não ele irá cadastrar.
  	$SqlConsultorio = $pConexaoDestino->prepare("SELECT id FROM migra_consultorio WHERE nome = '".utf8_decode($line[1])."'");
	$SqlConsultorio->execute();

	if ( $SqlConsultorio->rowCount() == 0 ) { 
		$SqlConsultorio = "INSERT INTO migra_consultorio (nome) VALUES (?)";
		$sql = $pConexaoDestino->prepare($SqlConsultorio);
		if (!$sql) {
			$bErro = true;
			print_r($pConexaoDestino->errorInfo());
			db_log("Erro SQL migra_consultorio", $sArquivoLog, 1, true, true);
		}
		$sql->execute([utf8_decode($line[1])]);

		$id_consultorio = $pConexaoDestino->lastInsertId();

		$t_consultorio++;
	} else {
		$SqlConsultorio = $SqlConsultorio->fetch();
		$id_consultorio = $SqlConsultorio['id'];
	}

	db_log("Processando->Consultorio: $id_consultorio - ".$line[1], $sArquivoLog, 1, true, true);
	
  }

  // Tratamento para profissional.
  if ( $line[0] == '2profissional' ) {
  	
  	// Verifico se o registro existe, se sim ele pegará o ID no banco, se não ele irá cadastrar.
  	$SqlProfissional = $pConexaoDestino->prepare("SELECT id FROM migra_parceiro WHERE nome = '".utf8_decode($line[1])."'");
	$SqlProfissional->execute();

	if ( $SqlProfissional->rowCount() == 0 ) {
		$SqlProfissional = "INSERT INTO migra_parceiro (nome) VALUES (?)";
		$sql = $pConexaoDestino->prepare($SqlProfissional);
		if (!$sql) {
			$bErro = true;
			print_r($pConexaoDestino->errorInfo());
			db_log("Erro SQL migra_parceiro", $sArquivoLog, 1, true, true);
		}
		$sql->execute([utf8_decode($line[1])]);

		$id_profissional = $pConexaoDestino->lastInsertId();

		$t_profissional++;
	} else {
		$SqlProfissional = $SqlProfissional->fetch();
		$id_profissional = $SqlProfissional['id'];

		$t_profissional_repetido++;
	}

	db_log("Processando->Profissional: $id_profissional - ".$line[1], $sArquivoLog, 1, true, true);
	
  }

  
  // Tratamento para procedimento e vículos entre consultório, profissional e procedimentos.
  if ( $line[0] == '3procedimento' and $line[1] != 'Procedimento' ) {
  	
  	// Separo o id e o nome do Procedimento
  	$id_nome = explode(":", $line[1]);
  	if ( empty($id_nome[1]) ) {
  		$id_nome[1] = $id_nome[0];
  		$semID = true;
  	} else {
  		$semID = false;
  	}

  	// Verifico se o registro existe, se sim ele pegará o ID no banco, se não ele irá cadastrar.
  	$SqlProcedimento = $pConexaoDestino->prepare("SELECT id FROM migra_procedimento WHERE nome = '".utf8_decode($id_nome[1])."'");
	$SqlProcedimento->execute();


	// Se procedimento tiver ID no arquivo csv o script irá cadastrar esse ID no banco, caso não haja o script irá usar o auto-increment.
	if ( $SqlProcedimento->rowCount() == 0 and $semID == false ) {
		$SqlProcedimento = "INSERT INTO migra_procedimento (id, nome) VALUES (?,?)";
		
		$sql = $pConexaoDestino->prepare($SqlProcedimento);
		if (!$sql) {
			$bErro = true;
			print_r($pConexaoDestino->errorInfo());
			db_log("Erro SQL migra_procedimento", $sArquivoLog, 1, true, true);
		}
		
		$sql->execute([$id_nome[0],utf8_decode($id_nome[1])]);


		$id_procedimento = $id_nome[0];

		$t_procedimento++;
	} else if ( $SqlProcedimento->rowCount() == 0 ) {
		$SqlProcedimento = "INSERT INTO migra_procedimento (nome) VALUES (?)";
		$sql = $pConexaoDestino->prepare($SqlProcedimento);
		if (!$sql) {
			$bErro = true;
			print_r($pConexaoDestino->errorInfo());
			db_log("Erro SQL migra_procedimento", $sArquivoLog, 1, true, true);
		}

		$sql->execute([utf8_decode($id_nome[1])]);
		$id_procedimento = $pConexaoDestino->lastInsertId();

		$t_procedimento++;
	} else {
		$SqlProcedimento = $SqlProcedimento->fetch();
		$id_procedimento = $SqlProcedimento['id'];

		$t_procedimento_repetido++;
	}

	db_log("Processando->Procedimento: $id_procedimento - ".$id_nome[1], $sArquivoLog, 1, true, true);

	$SqlConProEsp = $pConexaoDestino->prepare("SELECT id_consultorio FROM migra_con_pro_esp WHERE 
		id_consultorio 	= '".$id_consultorio."'
		,id_profissional = '".$id_profissional."'
		,id_procedimento = '".$id_procedimento."'
		");
	$SqlConProEsp->execute();

	if ( $SqlConProEsp->rowCount() == 0 ) {
		$SqlConProEsp = "INSERT INTO migra_con_pro_esp (id_consultorio,id_profissional,id_procedimento,repasse,final) VALUES (?,?,?,?,?)";
		
		$sql = $pConexaoDestino->prepare($SqlConProEsp);
		if (!$sql) {
			$bErro = true;
			print_r($pConexaoDestino->errorInfo());
			db_log("Erro SQL migra_con_pro_esp", $sArquivoLog, 1, true, true);
		}
		$sql->execute([$id_consultorio,$id_profissional,$id_procedimento,$line[2],$line[3]]);

	}
  }
}

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

db_log("Finalizando transacao [{$sOperFim}] - Total Consultórios: $t_consultorio | Total Profissionais: $t_profissional | Total Profissionais Repetidos: $t_profissional_repetido | Total Procedimentos: $t_procedimento | Total Procedimentos Repetidos: $t_procedimento_repetido", $sArquivoLog, 1, true, true);
$pConexaoDestino->query( "{$sOperFim};" );

// Final do Script
include("../lib/db_final_script.php");
?>