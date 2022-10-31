<?php
////////////////////////////////////////////////////////////////////////////////
function php2js($var){
 if (is_array($var)){
  $res = "[";
  $array = array();
  foreach ($var as $a_var){
   $array[] = php2js($a_var);
  }
  return "[" . join(",", $array) . "]";
 }
 elseif (is_bool($var)){
  return $var ? "true" : "false";
 }
 elseif (is_int($var) || is_integer($var) || is_double($var) || is_float($var)){
  return $var;
 }
 elseif (is_string($var)){
  return "\"" . addslashes(stripslashes($var)) . "\"";
 }
 return FALSE;
}
////////////////////////////////////////////////////////////////////////////////

 //hay que configurar la zona horaria para evitar warnings a partir de php 5.0
 date_default_timezone_set('America/Bogota');
 //Variables de configuracion generales del sistema
 //Directorio temporal para ver archivos pdf
 $dirTmpPdf = "tempo";
 //variables para manejo del almacen
 $almacenista = "Diego Pérez";
 //variables para el control diario de maquinaria
 $horasDia = 8; //esta variable permite el calculo de las horas extra diarias
 //----------------------------------------------------------------
 //funcion para el registro de actividades
 //REQUISITO 1: Debe existir una conexion previa a la BDD
 //REQUISITO 2: Debe existir un llamado previo a las varibles de sesion $nombreuser, $tipouser
 //FECHA: Diciembre 27 de 2004
 //CREADO POR: Javier García
// function registrar($servicio,$tabla,$accion,$comentario,$nombreuser,$tipouser,$link){
 function registrar($servicio,$tabla,$accion,$comentario,$link){
  global $nombreuser, $tipouser;
	//preparacion de variables para el query
	$_SESSION['nombreuser'];
  $_SESSION['tipouser'];
	if(isset($_SERVER["HTTP_X_REAL_IP"])){
	$clienteIp = $_SERVER["HTTP_X_REAL_IP"];
	}else{
    $clienteIp = $_SERVER["REMOTE_ADDR"];
    }
//  $clienteHost =  $_SERVER["REMOTE_HOST"];
  $clienteHost =  "n.a.";
  $clienteSystem = $_SERVER["HTTP_USER_AGENT"];
  $script =  $_SERVER["PHP_SELF"];
  $fecha = date("Y-m-d H:i:s");
	$script_split = preg_split("[/]",$script);//Modificado->division de string en archivo
	$script_split_reverse = array_reverse($script_split);
	$script = $script_split_reverse[0];
	switch($accion){
	 case 1: $accionTxt = "[LogIn] - Inici&oacute; Sesi&oacute;n"; break;
	 case 2: $accionTxt = "[LogOut] - Finaliz&oacute; Sesi&oacute;n"; break;
	 case 3: $accionTxt = "[List] - Consult&oacute; Datos"; break;
	 case 4: $accionTxt = "[Add] - Adicion&oacute; Datos"; break;
	 case 5: $accionTxt = "[Del] - Elimin&oacute; Datos"; break;
	 case 6: $accionTxt = "[Edit] - Actualiz&oacute; Datos"; break;
	 case 66: $accionTxt = "[Deta] - Consult&oacute; Detalle"; break;
	 case 7: $accionTxt = "[Error] - Operaci&oacute;n Inv&aacute;lida"; break;
	 case 8: $accionTxt = "[Warning] - Advertencia";
	}
	//preparacion del query
	$query_actividad = "INSERT INTO registro(REGISTRO_USUARIO_LOGIN, REGISTRO_USUARIO_TIPO_ID, ";
  $query_actividad.= "REGISTRO_CLIENTE_IP, REGISTRO_CLIENTE_HOST, ";
  $query_actividad.= "REGISTRO_CLIENTE_SYSTEM, REGISTRO_SCRIPT, ";
  $query_actividad.= "REGISTRO_SERVICIO, REGISTRO_TABLA, ";
  $query_actividad.= "REGISTRO_ACCION, REGISTRO_ACCION_ID, REGISTRO_FECHA, ";
  $query_actividad.= "REGISTRO_COMENTARIOS) VALUES('".$_SESSION['nombreuser']."', '".$_SESSION['tipouser']."', ";
  $query_actividad.= "'$clienteIp', '$clienteHost', ";
  $query_actividad.= "'$clienteSystem', '$script', ";
  $query_actividad.= "'$servicio', '$tabla', '$accionTxt', $accion, ";
  $query_actividad.= "'$fecha', '$comentario')";
	//ejecucion del query (insercion del registro)
  $actividad = nmysql2_query($query_actividad,$link);
 }
 //----------------------------------------------------------------
 //funcion para la conversion de numeros de ordinal a cardinal
 //FECHA: Marzo 10 de 2005
 //CREADO POR: Javier García
 function cardinal($valor){
  $size_valor = strlen($valor);
	if ($size_valor <= 3){ //desde 000 hasta 999
	 $cardinal = cardinal3digitos($valor);
	}
	elseif (($size_valor > 3) && ($size_valor <=6)){ //desde 1.000 hasta 999.999
	 $punto = $size_valor - 3;
	 $parteBaja = substr($valor,$punto);
	 $parteAlta = substr($valor,0,$punto);
	 if ($punto == 1){//PARTE ALTA CON UN SOLO DIGITO
	  //tratamiento de la parte alta
		if ($parteAlta == 1)
		 $parteAltaCardinal = "MIL";
		else{
		 switch($parteAlta){
		  case 2:$parteAltaCardinal = "DOS MIL"; break;
		  case 3:$parteAltaCardinal = "TRES MIL"; break;
		  case 4:$parteAltaCardinal = "CUATRO MIL"; break;
		  case 5:$parteAltaCardinal = "CINCO MIL"; break;
		  case 6:$parteAltaCardinal = "SEIS MIL"; break;
		  case 7:$parteAltaCardinal = "SIETE MIL"; break;
		  case 8:$parteAltaCardinal = "OCHO MIL"; break;
		  case 9:$parteAltaCardinal = "NUEVE MIL";
		 }
		}
		//tratamiento de la parte baja
		$parteBajaCardinal = cardinal3digitos($parteBaja);
		$cardinal = $parteAltaCardinal." ".$parteBajaCardinal;
	 }
	 elseif ($punto == 2){//PARTE ALTA CON 2 DIGITOS
	  //tratamiento de la parte alta
		$parteAltaCardinal = cardinal2digitos($parteAlta)." MIL";
	  //tratamiento de la parte baja
		$parteBajaCardinal = cardinal3digitos($parteBaja);
		$cardinal = $parteAltaCardinal." ".$parteBajaCardinal;
	 }
	 elseif ($punto == 3){//PARTE ALTA CON 3 DIGITOS
	  //tratamiento de la parte alta
		$parteAltaCardinal = cardinal3digitos($parteAlta)." MIL";
	  //tratamiento de la parte baja
		$parteBajaCardinal = cardinal3digitos($parteBaja);
		$cardinal = $parteAltaCardinal." ".$parteBajaCardinal;
	 }
	}
	elseif (($size_valor > 6) && ($size_valor <=9)){ //desde 1.000.000 hasta 999.999.999
	 $punto1 = $size_valor - 3;
	 $punto2 = $size_valor - 6;
	 $parteBaja = substr($valor,$punto1);
	 $parteMedia = substr($valor,$punto2,3);
	 $parteAlta = substr($valor,0,$punto2);
	 if ($punto2 == 1){//PARTE ALTA CON UN SOLO DIGITO
	  //tratamiento de la parte alta
		switch($parteAlta){
		 case 1:$parteAltaCardinal = "UN MILLON"; break;
		 case 2:$parteAltaCardinal = "DOS MILLONES"; break;
		 case 3:$parteAltaCardinal = "TRES MILLONES"; break;
		 case 4:$parteAltaCardinal = "CUATRO MILLONES"; break;
		 case 5:$parteAltaCardinal = "CINCO MILLONES"; break;
		 case 6:$parteAltaCardinal = "SEIS MILLONES"; break;
		 case 7:$parteAltaCardinal = "SIETE MILLONES"; break;
		 case 8:$parteAltaCardinal = "OCHO MILLONES"; break;
		 case 9:$parteAltaCardinal = "NUEVE MILLONES";
		}
		//tratamiento de la parte media
		$parteMediaCardinal = cardinal3digitos($parteMedia)." MIL";
		//tratamiento de la parte baja
		$parteBajaCardinal = cardinal3digitos($parteBaja);
		$cardinal = $parteAltaCardinal." ".$parteMediaCardinal." ".$parteBajaCardinal;
	 }
	 elseif ($punto2 == 2){//PARTE ALTA CON 2 DIGITOS
	  //tratamiento de la parte alta
		$parteAltaCardinal = cardinal2digitos($parteAlta)." MILLONES";
		//tratamiento de la parte media
		$parteMediaCardinal = cardinal3digitos($parteMedia)." MIL";
		//tratamiento de la parte baja
		$parteBajaCardinal = cardinal3digitos($parteBaja);
		$cardinal = $parteAltaCardinal." ".$parteMediaCardinal." ".$parteBajaCardinal;
	 }
	 elseif ($punto2 == 3){//PARTE ALTA CON 3 DIGITOS
	  //tratamiento de la parte alta
		$parteAltaCardinal = cardinal3digitos($parteAlta)." MILLONES";
		//tratamiento de la parte media
		$parteMediaCardinal = cardinal3digitos($parteMedia)." MIL";
		//tratamiento de la parte baja
		$parteBajaCardinal = cardinal3digitos($parteBaja);
		$cardinal = $parteAltaCardinal." ".$parteMediaCardinal." ".$parteBajaCardinal;
	 }
	}
	$size_cardinal = strlen($cardinal);
	$segmento_final = substr($cardinal,$size_cardinal-3,3);
	if ($segmento_final == " UN")
	 $cardinal.= "O PESOS MCTE.";
	else
	 $cardinal.= " PESOS MCTE.";
	return($cardinal);
 }
 //----------------------------------------------------------------
 //funcion para la conversion de numeros de ordinal a cardinal para cuando siempre tiene 3 digitos
 //FECHA: Marzo 10 de 2005
 //CREADO POR: Javier García
 function cardinal3digitos($valor){
  $cardinal = "";
	$digi123 = substr($valor,0,3);
	$digi1 = substr($valor,0,1);
  switch($digi1){
	 case '1': $cardinal = "CIENTO "; break;
	 case '2': $cardinal = "DOCIENTOS "; break;
	 case '3': $cardinal = "TRECIENTOS "; break;
	 case '4': $cardinal = "CUATROCIENTOS "; break;
	 case '5': $cardinal = "QUINIENTOS "; break;
	 case '6': $cardinal = "SEISCIENTOS "; break;
	 case '7': $cardinal = "SETECIENTOS "; break;
	 case '8': $cardinal = "OCHOCIENTOS "; break;
	 case '9': $cardinal = "NOVECIENTOS ";
	}
	$digi23 = substr($valor,1,2);
	if (($digi23<=30)||($digi23==40)||($digi23==50)||($digi23==60)||($digi23==70)||($digi23==80)||($digi23==90)){
	 switch($digi23){
	  case '01': $cardinal.= "UN"; break;
	  case '02': $cardinal.= "DOS"; break;
	  case '03': $cardinal.= "TRES"; break;
	  case '04': $cardinal.= "CUATRO"; break;
	  case '05': $cardinal.= "CINCO"; break;
	  case '06': $cardinal.= "SEIS"; break;
	  case '07': $cardinal.= "SIETE"; break;
	  case '08': $cardinal.= "OCHO"; break;
	  case '09': $cardinal.= "NUEVE"; break;
	  case '10': $cardinal.= "DIEZ"; break;
	  case '11': $cardinal.= "ONCE"; break;
	  case '12': $cardinal.= "DOCE"; break;
	  case '13': $cardinal.= "TRECE"; break;
	  case '14': $cardinal.= "CATORCE"; break;
	  case '15': $cardinal.= "QUINCE"; break;
	  case '16': $cardinal.= "DIECISEIS"; break;
	  case '17': $cardinal.= "DIECISIETE"; break;
	  case '18': $cardinal.= "DIECIOCHO"; break;
	  case '19': $cardinal.= "DIECINUEVE"; break;
	  case '20': $cardinal.= "VEINTE"; break;
	  case '21': $cardinal.= "VEINTIUNO"; break;
	  case '22': $cardinal.= "VEINTIDOS"; break;
	  case '23': $cardinal.= "VEINTITRES"; break;
	  case '24': $cardinal.= "VEINTICUATRO"; break;
	  case '25': $cardinal.= "VEINTICINCO"; break;
	  case '26': $cardinal.= "VEINTISEIS"; break;
	  case '27': $cardinal.= "VEINTISIETE"; break;
	  case '28': $cardinal.= "VEINTIOCHO"; break;
	  case '29': $cardinal.= "VEINTINUEVE"; break;
	  case '30': $cardinal.= "TREINTA"; break;
	  case '40': $cardinal.= "CUARENTA"; break;
	  case '50': $cardinal.= "CINCUENTA"; break;
	  case '60': $cardinal.= "SESENTA"; break;
	  case '70': $cardinal.= "SETENTA"; break;
	  case '80': $cardinal.= "OCHENTA"; break;
	  case '90': $cardinal.= "NOVENTA";
	 }
	}
	else{
	 $digi2 = substr($valor,1,1);
	 switch($digi2){
	  case '3': $cardinal.= "TREINTA Y "; break;
	  case '4': $cardinal.= "CUARENTA Y "; break;
	  case '5': $cardinal.= "CINCUENTA Y "; break;
	  case '6': $cardinal.= "SESENTA Y "; break;
	  case '7': $cardinal.= "SETENTA Y "; break;
	  case '8': $cardinal.= "OCHENTA Y "; break;
	  case '9': $cardinal.= "NOVENTA Y ";
	 }
   $digi3 = substr($valor,2,1);
	 switch($digi3){
	  case '1': $cardinal.= "UN"; break;
	  case '2': $cardinal.= "DOS"; break;
	  case '3': $cardinal.= "TRES"; break;
	  case '4': $cardinal.= "CUATRO"; break;
	  case '5': $cardinal.= "CINCO"; break;
	  case '6': $cardinal.= "SEIS"; break;
	  case '7': $cardinal.= "SIETE"; break;
	  case '8': $cardinal.= "OCHO"; break;
	  case '9': $cardinal.= "NUEVE";
	 }
	}
	switch($digi123){
	 case '100': $cardinal = "CIEN"; break;
	 case '200': $cardinal = "DOSCIENTOS"; break;
	 case '300': $cardinal = "TRESCIENTOS"; break;
	 case '400': $cardinal = "CUATROCIENTOS"; break;
	 case '500': $cardinal = "QUINIENTOS"; break;
	 case '600': $cardinal = "SEISCIENTOS"; break;
	 case '700': $cardinal = "SETECIENTOS"; break;
	 case '800': $cardinal = "OCHOCIENTOS"; break;
	 case '900': $cardinal = "NOVECIENTOS";
	}
	return($cardinal);
 }
 //----------------------------------------------------------------
 //funcion para la conversion de numeros de ordinal a cardinal para cuando siempre tiene 2 digitos
 //FECHA: Marzo 10 de 2005
 //CREADO POR: Javier García
 function cardinal2digitos($valor){
	$cardinal = "";
	$digi12 = substr($valor,0,2);
	if (($digi12<=30)||($digi12==40)||($digi12==50)||($digi12==60)||($digi12==70)||($digi12==80)||($digi12==90)){
	 switch($digi12){
	  case '01': $cardinal.= "UN"; break;
	  case '02': $cardinal.= "DOS"; break;
	  case '03': $cardinal.= "TRES"; break;
	  case '04': $cardinal.= "CUATRO"; break;
	  case '05': $cardinal.= "CINCO"; break;
	  case '06': $cardinal.= "SEIS"; break;
	  case '07': $cardinal.= "SIETE"; break;
	  case '08': $cardinal.= "OCHO"; break;
	  case '09': $cardinal.= "NUEVE"; break;
	  case '10': $cardinal.= "DIEZ"; break;
	  case '11': $cardinal.= "ONCE"; break;
	  case '12': $cardinal.= "DOCE"; break;
	  case '13': $cardinal.= "TRECE"; break;
	  case '14': $cardinal.= "CATORCE"; break;
	  case '15': $cardinal.= "QUINCE"; break;
	  case '16': $cardinal.= "DIECISEIS"; break;
	  case '17': $cardinal.= "DIECISIETE"; break;
	  case '18': $cardinal.= "DIECIOCHO"; break;
	  case '19': $cardinal.= "DIECINUEVE"; break;
	  case '20': $cardinal.= "VEINTE"; break;
	  case '21': $cardinal.= "VEINTIUNO"; break;
	  case '22': $cardinal.= "VEINTIDOS"; break;
	  case '23': $cardinal.= "VEINTITRES"; break;
	  case '24': $cardinal.= "VEINTICUATRO"; break;
	  case '25': $cardinal.= "VEINTICINCO"; break;
	  case '26': $cardinal.= "VEINTISEIS"; break;
	  case '27': $cardinal.= "VEINTISIETE"; break;
	  case '28': $cardinal.= "VEINTIOCHO"; break;
	  case '29': $cardinal.= "VEINTINUEVE"; break;
	  case '30': $cardinal.= "TREINTA"; break;
	  case '40': $cardinal.= "CUARENTA"; break;
	  case '50': $cardinal.= "CINCUENTA"; break;
	  case '60': $cardinal.= "SESENTA"; break;
	  case '70': $cardinal.= "SETENTA"; break;
	  case '80': $cardinal.= "OCHENTA"; break;
	  case '90': $cardinal.= "NOVENTA";
	 }
	}
	else{
	 $digi1 = substr($valor,0,1);
	 switch($digi1){
	  case '3': $cardinal.= "TREINTA Y "; break;
	  case '4': $cardinal.= "CUARENTA Y "; break;
	  case '5': $cardinal.= "CINCUENTA Y "; break;
	  case '6': $cardinal.= "SESENTA Y "; break;
	  case '7': $cardinal.= "SETENTA Y "; break;
	  case '8': $cardinal.= "OCHENTA Y "; break;
	  case '9': $cardinal.= "NOVENTA Y ";
	 }
   $digi2 = substr($valor,1,1);
	 switch($digi2){
	  case '1': $cardinal.= "UN"; break;
	  case '2': $cardinal.= "DOS"; break;
	  case '3': $cardinal.= "TRES"; break;
	  case '4': $cardinal.= "CUATRO"; break;
	  case '5': $cardinal.= "CINCO"; break;
	  case '6': $cardinal.= "SEIS"; break;
	  case '7': $cardinal.= "SIETE"; break;
	  case '8': $cardinal.= "OCHO"; break;
	  case '9': $cardinal.= "NUEVE";
	 }
	}
	return($cardinal);
 }
 //----------------------------------------------------------------
 //funcion para la generacion de mensajes automaticos, a partir de tareas
 //programadas en la agenda, mttos, cheques post, etc.
 //FECHA: Mayo 11 de 2005
 //CREADO POR: Javier García
 function msgAuto($link,$usuario_id){
  $fingreso = date("Y-m-d H:i:s");
  $fechaToday = date("Y-m-d");
	$fechaTomorrow = 	date("Y-m-d",mktime(0,0,0,date("m"),date("d")+1,date("Y")));
	//A G E N D A
	//iniciamos con la revision de las actividades registradas en la agenda
	$query_review_agenda = "select * from agenda where date <= '$fechaTomorrow' ";
	$query_review_agenda.= "and date >= '$fechaToday' ";
	$query_review_agenda.= "and user = $usuario_id";
	$review_agenda = nmysql2_query($query_review_agenda,$link);
  if (nmysql2_num_rows($review_agenda)!=0){
   while($row = nmysql2_fetch_array($review_agenda)) {
	  $agenda_id = $row["id"];
	  $agenda_date = $row["date"];
	  $agenda_text = $row["text"];
	  $agenda_alert1 = $row["alert1"];
	  $agenda_alert2 = $row["alert2"];
		//comparamos fechas y estados de las alertas, para crear mensaje
		if (($agenda_date == $fechaToday) && ($agenda_alert2 == 0)){
		 //se genera mensaje para recordar los eventos en la agenda del dia de HOY
     //query para el ingreso del mensaje
     $query_nuevo_mensaje = "INSERT INTO mensaje(MENSAJE_FROM, MENSAJE_TO, MENSAJE_CC, "; 
     $query_nuevo_mensaje.= "MENSAJE_SUBJECT, MENSAJE_CONTENT, MENSAJE_TIPO, "; 
     $query_nuevo_mensaje.= "MENSAJE_FSEND, MENSAJE_FREAD) "; 
     $query_nuevo_mensaje.= "VALUES(0,$usuario_id,0,'Actividades de Agenda para Hoy',"; 
     $query_nuevo_mensaje.= "'$agenda_text',1,'$fingreso','')"; 
     //ejecucion del query para insertar nueva mensaje
     $nuevo_mensaje = nmysql2_query($query_nuevo_mensaje,$link);
		 //luego actualizamos el campo alert1 para que solo se envie un mensaje
		 $query_update_msg = "update agenda set alert2 = 1 where id = $agenda_id";
		 $update_msg = nmysql2_query($query_update_msg,$link);
		}
		elseif (($agenda_date == $fechaTomorrow) && ($agenda_alert1 == 0)){
		 //se genera mensaje para recordar los eventos en la agenda del dia de MAÑANA
     //query para el ingreso del mensaje
     $query_nuevo_mensaje = "INSERT INTO mensaje(MENSAJE_FROM, MENSAJE_TO, MENSAJE_CC, "; 
     $query_nuevo_mensaje.= "MENSAJE_SUBJECT, MENSAJE_CONTENT, MENSAJE_TIPO, "; 
     $query_nuevo_mensaje.= "MENSAJE_FSEND, MENSAJE_FREAD) "; 
     $query_nuevo_mensaje.= "VALUES(0,$usuario_id,0,'Actividades de Agenda para Ma&ntilde;ana',"; 
     $query_nuevo_mensaje.= "'$agenda_text',1,'$fingreso','')"; 
     //ejecucion del query para insertar nueva mensaje
     $nuevo_mensaje = nmysql2_query($query_nuevo_mensaje,$link);
		 //luego actualizamos el campo alert1 para que solo se envie un mensaje
		 $query_update_msg = "update agenda set alert1 = 1 where id = $agenda_id";
		 $update_msg = nmysql2_query($query_update_msg,$link);
		}
	 }
	}
	nmysql2_free_result($review_agenda);
	//C H E Q U E S
	$query_review_cheque = "select * from cheque where CHEQUE_FCAMBIO <= '$fechaTomorrow' ";
	$query_review_cheque.= "and CHEQUE_FCAMBIO >= '$fechaToday'";
	$review_cheque = nmysql2_query($query_review_cheque,$link);
  if (nmysql2_num_rows($review_cheque)!=0){
   while($row = nmysql2_fetch_array($review_cheque)) {
	  $cheque_id = $row["CHEQUE_ID"];
	  $cheque_valor = $row["CHEQUE_VALOR"];
	  $cheque_numero = $row["CHEQUE_NUMERO"];
	  $cheque_beneficiario = $row["CHEQUE_BENEFICIARIO_NOMBRE"];
	  $cheque_valor = $row["CHEQUE_VALOR"];
	  $cheque_valor_format = "$ ".number_format($cheque_valor,0,',','.');
	  $cheque_cuenta = $row["CHEQUE_CUENTABAN_NUMERO"]." - ".$row["CHEQUE_CUENTABAN_BANCO"];
	  //$cheque_fgiro = $row["CHEQUE_FGIRO"];
	  $cheque_fcambio = $row["CHEQUE_FCAMBIO"];
	  $cheque_cruce = $row["CHEQUE_CRUCE"];
	  $cheque_alert1 = $row["CHEQUE_ALERT1"];
	  $cheque_alert2 = $row["CHEQUE_ALERT2"];
    //preparacion del mensaje
		$cheque_text = "COBRO DE CHEQUE : ".$cheque_numero;
		$cheque_text.= "\nPOR VALOR DE : ".$cheque_valor_format;
		$cheque_text.= "\nCUENTA / BANCO : ".$cheque_cuenta;
		$cheque_text.= "\nA NOMBRE DE : ".$cheque_beneficiario;
		//$cheque_text.= "\nF. ELABORADO : ".$cheque_fgiro;
		$cheque_text.= "\nFECHA DE COBRO : ".$cheque_fcambio;
		$cheque_text.= "\nCRUZADO ? : ".$cheque_cruce;
		//comparamos fechas y estados de las alertas, para crear mensaje
		if (($cheque_fcambio == $fechaToday) && ($cheque_alert2 == 0)){
		 //se genera mensaje para recordar los pagos de cheques del dia de HOY
     //query para el ingreso del mensaje PARA EL ADMINISTRADOR
     $query_nuevo_mensaje = "INSERT INTO mensaje(MENSAJE_FROM, MENSAJE_TO, MENSAJE_CC, "; 
     $query_nuevo_mensaje.= "MENSAJE_SUBJECT, MENSAJE_CONTENT, MENSAJE_TIPO, "; 
     $query_nuevo_mensaje.= "MENSAJE_FSEND, MENSAJE_FREAD) "; 
     $query_nuevo_mensaje.= "VALUES(0,2,0,'Cheque Girado para Hoy',"; 
     $query_nuevo_mensaje.= "'$cheque_text',1,'$fingreso','')"; 
     //ejecucion del query para insertar nueva mensaje
     $nuevo_mensaje = nmysql2_query($query_nuevo_mensaje,$link);
     //query para el ingreso del mensaje PARA LA SECRETARIA
     $query_nuevo_mensaje = "INSERT INTO mensaje(MENSAJE_FROM, MENSAJE_TO, MENSAJE_CC, "; 
     $query_nuevo_mensaje.= "MENSAJE_SUBJECT, MENSAJE_CONTENT, MENSAJE_TIPO, "; 
     $query_nuevo_mensaje.= "MENSAJE_FSEND, MENSAJE_FREAD) "; 
     $query_nuevo_mensaje.= "VALUES(0,3,0,'Cheque Girado para Hoy',"; 
     $query_nuevo_mensaje.= "'$cheque_text',1,'$fingreso','')"; 
     //ejecucion del query para insertar nueva mensaje
     $nuevo_mensaje = nmysql2_query($query_nuevo_mensaje,$link);
     //query para el ingreso del mensaje PARA EL GERENTE
     $query_nuevo_mensaje = "INSERT INTO mensaje(MENSAJE_FROM, MENSAJE_TO, MENSAJE_CC, "; 
     $query_nuevo_mensaje.= "MENSAJE_SUBJECT, MENSAJE_CONTENT, MENSAJE_TIPO, "; 
     $query_nuevo_mensaje.= "MENSAJE_FSEND, MENSAJE_FREAD) "; 
     $query_nuevo_mensaje.= "VALUES(0,5,0,'Cheque Girado para Hoy',"; 
     $query_nuevo_mensaje.= "'$cheque_text',1,'$fingreso','')"; 
     //ejecucion del query para insertar nueva mensaje
     $nuevo_mensaje = nmysql2_query($query_nuevo_mensaje,$link);
		 //luego actualizamos el campo alert1 para que solo se envie un mensaje
		 $query_update_msg = "update cheque set CHEQUE_ALERT2 = 1 where CHEQUE_ID = $cheque_id";
		 $update_msg = nmysql2_query($query_update_msg,$link);
		}
		elseif (($cheque_fcambio == $fechaTomorrow) && ($cheque_alert1 == 0)){
		 //se genera mensaje para recordar los pagos de cheques del dia de MAÑANA
     //query para el ingreso del mensaje PARA EL ADMINISTRADOR
     $query_nuevo_mensaje = "INSERT INTO mensaje(MENSAJE_FROM, MENSAJE_TO, MENSAJE_CC, "; 
     $query_nuevo_mensaje.= "MENSAJE_SUBJECT, MENSAJE_CONTENT, MENSAJE_TIPO, "; 
     $query_nuevo_mensaje.= "MENSAJE_FSEND, MENSAJE_FREAD) "; 
     $query_nuevo_mensaje.= "VALUES(0,2,0,'Cheque Girado para Ma&ntilde;ana',"; 
     $query_nuevo_mensaje.= "'$cheque_text',1,'$fingreso','')"; 
     //ejecucion del query para insertar nueva mensaje
     $nuevo_mensaje = nmysql2_query($query_nuevo_mensaje,$link);
     //query para el ingreso del mensaje PARA LA SECRETARIA
     $query_nuevo_mensaje = "INSERT INTO mensaje(MENSAJE_FROM, MENSAJE_TO, MENSAJE_CC, "; 
     $query_nuevo_mensaje.= "MENSAJE_SUBJECT, MENSAJE_CONTENT, MENSAJE_TIPO, "; 
     $query_nuevo_mensaje.= "MENSAJE_FSEND, MENSAJE_FREAD) "; 
     $query_nuevo_mensaje.= "VALUES(0,3,0,'Cheque Girado para Ma&ntilde;ana',"; 
     $query_nuevo_mensaje.= "'$cheque_text',1,'$fingreso','')"; 
     //ejecucion del query para insertar nueva mensaje
     $nuevo_mensaje = nmysql2_query($query_nuevo_mensaje,$link);
     //query para el ingreso del mensaje PARA EL GERENTE
     $query_nuevo_mensaje = "INSERT INTO mensaje(MENSAJE_FROM, MENSAJE_TO, MENSAJE_CC, "; 
     $query_nuevo_mensaje.= "MENSAJE_SUBJECT, MENSAJE_CONTENT, MENSAJE_TIPO, "; 
     $query_nuevo_mensaje.= "MENSAJE_FSEND, MENSAJE_FREAD) "; 
     $query_nuevo_mensaje.= "VALUES(0,5,0,'Cheque Girado para Ma&ntilde;ana',"; 
     $query_nuevo_mensaje.= "'$cheque_text',1,'$fingreso','')"; 
     //ejecucion del query para insertar nueva mensaje
     $nuevo_mensaje = nmysql2_query($query_nuevo_mensaje,$link);
		 //luego actualizamos el campo alert1 para que solo se envie un mensaje
		 $query_update_msg = "update cheque set CHEQUE_ALERT1 = 1 where CHEQUE_ID = $cheque_id";
		 $update_msg = nmysql2_query($query_update_msg,$link);
		}
	 }
	}
	nmysql2_free_result($review_cheque);
	//M T T O S (Programados por Horas de Trabajo)

  //  MENSAJES POR MANTENIMIENTOS DESHABILITADOS TEMPORALMENTE PORQUE ACTUALMENTE
  //  NO SE LE PRESTA ATANCION. ENTONCES SE HA DESHABILITADO PARA NO SATURAR LA
  //  TABLA DE MENSAJES - AGO-18-2008
  /*
	$query_review_maquina = "select * from maquina";
	$review_maquina = nmysql2_query($query_review_maquina,$link);
	if (nmysql2_num_rows($review_maquina)!=0){
   while($row = nmysql2_fetch_array($review_maquina)) {
	  $maquina_id = $row["MAQUINA_ID"];
	  $maquina_nombre = $row["MAQUINA_NOMBRE"];
	  $maquina_mtto1 = $row["MAQUINA_MTTO1"];
	  $maquina_mtto2 = $row["MAQUINA_MTTO2"];
	  $maquina_mtto3 = $row["MAQUINA_MTTO3"];
	  $maquina_mtto4 = $row["MAQUINA_MTTO4"];
	  $maquina_mtto5 = $row["MAQUINA_MTTO5"];
	  $maquina_mtto6 = $row["MAQUINA_MTTO6"];
	  $maquina_horas1 = $row["MAQUINA_HORAS1"];
	  $maquina_horas2 = $row["MAQUINA_HORAS2"];
	  $maquina_horas3 = $row["MAQUINA_HORAS3"];
	  $maquina_horas4 = $row["MAQUINA_HORAS4"];
	  $maquina_horas5 = $row["MAQUINA_HORAS5"];
	  $maquina_horas6 = $row["MAQUINA_HORAS6"];
	  $maquina_fecha1 = $row["MAQUINA_FECHA_MSG1"];
	  $maquina_fecha2 = $row["MAQUINA_FECHA_MSG2"];
	  $maquina_fecha3 = $row["MAQUINA_FECHA_MSG3"];
	  $maquina_fecha4 = $row["MAQUINA_FECHA_MSG4"];
	  $maquina_fecha5 = $row["MAQUINA_FECHA_MSG5"];
	  $maquina_fecha6 = $row["MAQUINA_FECHA_MSG6"];
    //query para los datos de las horas trabajadas
    $query_horas = "SELECT SUM(CMAQUINA_HORAS) AS SUMA, MIN(CMAQUINA_FECHA) AS FECHA FROM cmaquina "; 
    $query_horas.= "WHERE CMAQUINA_MAQUINA_ID = $maquina_id"; 
    $horas = nmysql2_query($query_horas,$link); 
    $sum_horas = mysql_result($horas, 0, "SUMA");
    $min_fecha = mysql_result($horas, 0, "FECHA");
		if($maquina_horas1 > 0){
		 //busqueda del ultimo mantenimiento del mismo tipo
		 $query_mtto = "select MAX(MTTO_HORAS) as HORAS_MTTO from mtto ";
		 $query_mtto.= "where MTTO_TIPO = '$maquina_mtto1' and MTTO_MAQUINA_ID = $maquina_id";
     $mtto = nmysql2_query($query_mtto,$link); 
     $max_horas = mysql_result($mtto, 0, "HORAS_MTTO");
		 $fechaActual = date("Y-m-d");
		 //echo "$sum_horas -- $max_horas -- $maquina_horas1 -- $fechaActual -- $maquina_fecha1<br>";
		 if ((($sum_horas - $max_horas) > ($maquina_horas1)) && ($fechaActual != $maquina_fecha1)){
      //preparacion del mensaje
		  $mtto_text = "MENSAJE PARA MANTENIMIENTO PREVENTIVO";
		  $mtto_text.= "\n\nLA MAQUINA : ".$maquina_nombre;
		  $mtto_text.= "\nREQUIERE : ".$maquina_mtto1;
		  $mtto_text.= "\nPROGRAMADO CADA : ".$maquina_horas1." Horas";
		  $mtto_text.= "\nHORAS DE TRABAJO ACTUALES : ".ceil($sum_horas). " Horas";
		  $mtto_text.= "\nULTIMO MTTO REALIZADO A LAS : ".$max_horas." Horas";
		  //se genera mensaje para recordar el mtto
      //query para el ingreso del mensaje PARA EL ADMINISTRADOR
      $query_nuevo_mensaje = "INSERT INTO mensaje(MENSAJE_FROM, MENSAJE_TO, MENSAJE_CC, "; 
      $query_nuevo_mensaje.= "MENSAJE_SUBJECT, MENSAJE_CONTENT, MENSAJE_TIPO, "; 
      $query_nuevo_mensaje.= "MENSAJE_FSEND, MENSAJE_FREAD) "; 
      $query_nuevo_mensaje.= "VALUES(0,2,0,'Mantenimiento de Equipo Programado',"; 
      $query_nuevo_mensaje.= "'$mtto_text',1,'$fingreso','')";
			//echo $query_nuevo_mensaje; 
      //ejecucion del query para insertar nueva mensaje
      $nuevo_mensaje = nmysql2_query($query_nuevo_mensaje,$link);
      //query para el ingreso del mensaje PARA LA SECRETARIA
      $query_nuevo_mensaje = "INSERT INTO mensaje(MENSAJE_FROM, MENSAJE_TO, MENSAJE_CC, "; 
      $query_nuevo_mensaje.= "MENSAJE_SUBJECT, MENSAJE_CONTENT, MENSAJE_TIPO, "; 
      $query_nuevo_mensaje.= "MENSAJE_FSEND, MENSAJE_FREAD) "; 
      $query_nuevo_mensaje.= "VALUES(0,4,0,'Mantenimiento de Equipo Programado',"; 
      $query_nuevo_mensaje.= "'$mtto_text',1,'$fingreso','')"; 
      //ejecucion del query para insertar nueva mensaje
      $nuevo_mensaje = nmysql2_query($query_nuevo_mensaje,$link);
      //query para el ingreso del mensaje PARA EL GERENTE
      $query_nuevo_mensaje = "INSERT INTO mensaje(MENSAJE_FROM, MENSAJE_TO, MENSAJE_CC, "; 
      $query_nuevo_mensaje.= "MENSAJE_SUBJECT, MENSAJE_CONTENT, MENSAJE_TIPO, "; 
      $query_nuevo_mensaje.= "MENSAJE_FSEND, MENSAJE_FREAD) "; 
      $query_nuevo_mensaje.= "VALUES(0,5,0,'Mantenimiento de Equipo Programado',"; 
      $query_nuevo_mensaje.= "'$mtto_text',1,'$fingreso','')"; 
      //ejecucion del query para insertar nueva mensaje
      $nuevo_mensaje = nmysql2_query($query_nuevo_mensaje,$link);
		  //luego actualizamos el campo FECHA_MSG1 para que solo se envie un mensaje diario
		  $query_update_msg = "update maquina set MAQUINA_FECHA_MSG1 = '$fechaActual' where MAQUINA_ID = $maquina_id";
		  $update_msg = nmysql2_query($query_update_msg,$link);
		 }
		}
		if($maquina_horas2 > 0){
		 //busqueda del ultimo mantenimiento del mismo tipo
		 $query_mtto = "select MAX(MTTO_HORAS) as HORAS_MTTO from mtto ";
		 $query_mtto.= "where MTTO_TIPO = '$maquina_mtto2' and MTTO_MAQUINA_ID = $maquina_id";
     $mtto = nmysql2_query($query_mtto,$link); 
     $max_horas = mysql_result($mtto, 0, "HORAS_MTTO");
		 $fechaActual = date("Y-m-d");
		 if ((($sum_horas - $max_horas) > ($maquina_horas2)) && ("$fechaActual" != "$maquina_fecha2")){
      //preparacion del mensaje
		  $mtto_text = "MENSAJE PARA MANTENIMIENTO PREVENTIVO";
		  $mtto_text.= "\n\nLA MAQUINA : ".$maquina_nombre;
		  $mtto_text.= "\nREQUIERE : ".$maquina_mtto2;
		  $mtto_text.= "\nPROGRAMADO CADA : ".$maquina_horas2." Horas";
		  $mtto_text.= "\nHORAS DE TRABAJO ACTUALES : ".ceil($sum_horas). " Horas";
		  $mtto_text.= "\nULTIMO MTTO REALIZADO A LAS : ".$max_horas." Horas";
		  //se genera mensaje para recordar el mtto
      //query para el ingreso del mensaje PARA EL ADMINISTRADOR
      $query_nuevo_mensaje = "INSERT INTO mensaje(MENSAJE_FROM, MENSAJE_TO, MENSAJE_CC, "; 
      $query_nuevo_mensaje.= "MENSAJE_SUBJECT, MENSAJE_CONTENT, MENSAJE_TIPO, "; 
      $query_nuevo_mensaje.= "MENSAJE_FSEND, MENSAJE_FREAD) "; 
      $query_nuevo_mensaje.= "VALUES(0,2,0,'Mantenimiento de Equipo Programado',"; 
      $query_nuevo_mensaje.= "'$mtto_text',1,'$fingreso','')";
			//echo $query_nuevo_mensaje; 
      //ejecucion del query para insertar nueva mensaje
      $nuevo_mensaje = nmysql2_query($query_nuevo_mensaje,$link);
      //query para el ingreso del mensaje PARA LA SECRETARIA
      $query_nuevo_mensaje = "INSERT INTO mensaje(MENSAJE_FROM, MENSAJE_TO, MENSAJE_CC, "; 
      $query_nuevo_mensaje.= "MENSAJE_SUBJECT, MENSAJE_CONTENT, MENSAJE_TIPO, "; 
      $query_nuevo_mensaje.= "MENSAJE_FSEND, MENSAJE_FREAD) "; 
      $query_nuevo_mensaje.= "VALUES(0,4,0,'Mantenimiento de Equipo Programado',"; 
      $query_nuevo_mensaje.= "'$mtto_text',1,'$fingreso','')"; 
      //ejecucion del query para insertar nueva mensaje
      $nuevo_mensaje = nmysql2_query($query_nuevo_mensaje,$link);
      //query para el ingreso del mensaje PARA EL GERENTE
      $query_nuevo_mensaje = "INSERT INTO mensaje(MENSAJE_FROM, MENSAJE_TO, MENSAJE_CC, "; 
      $query_nuevo_mensaje.= "MENSAJE_SUBJECT, MENSAJE_CONTENT, MENSAJE_TIPO, "; 
      $query_nuevo_mensaje.= "MENSAJE_FSEND, MENSAJE_FREAD) "; 
      $query_nuevo_mensaje.= "VALUES(0,5,0,'Mantenimiento de Equipo Programado',"; 
      $query_nuevo_mensaje.= "'$mtto_text',1,'$fingreso','')"; 
      //ejecucion del query para insertar nueva mensaje
      $nuevo_mensaje = nmysql2_query($query_nuevo_mensaje,$link);
		  //luego actualizamos el campo FECHA_MSG1 para que solo se envie un mensaje diario
		  $query_update_msg = "update maquina set MAQUINA_FECHA_MSG2 = '$fechaActual' where MAQUINA_ID = $maquina_id";
		  $update_msg = nmysql2_query($query_update_msg,$link);
		 }
		}
		if($maquina_horas3 > 0){
		 //busqueda del ultimo mantenimiento del mismo tipo
		 $query_mtto = "select MAX(MTTO_HORAS) as HORAS_MTTO from mtto ";
		 $query_mtto.= "where MTTO_TIPO = '$maquina_mtto3' and MTTO_MAQUINA_ID = $maquina_id";
     $mtto = nmysql2_query($query_mtto,$link); 
     $max_horas = mysql_result($mtto, 0, "HORAS_MTTO");
		 $fechaActual = date("Y-m-d");
		 if ((($sum_horas - $max_horas) > ($maquina_horas3)) && ("$fechaActual" != "$maquina_fecha3")){
      //preparacion del mensaje
		  $mtto_text = "MENSAJE PARA MANTENIMIENTO PREVENTIVO";
		  $mtto_text.= "\n\nLA MAQUINA : ".$maquina_nombre;
		  $mtto_text.= "\nREQUIERE : ".$maquina_mtto3;
		  $mtto_text.= "\nPROGRAMADO CADA : ".$maquina_horas3." Horas";
		  $mtto_text.= "\nHORAS DE TRABAJO ACTUALES : ".ceil($sum_horas). " Horas";
		  $mtto_text.= "\nULTIMO MTTO REALIZADO A LAS : ".$max_horas." Horas";
		  //se genera mensaje para recordar el mtto
      //query para el ingreso del mensaje PARA EL ADMINISTRADOR
      $query_nuevo_mensaje = "INSERT INTO mensaje(MENSAJE_FROM, MENSAJE_TO, MENSAJE_CC, "; 
      $query_nuevo_mensaje.= "MENSAJE_SUBJECT, MENSAJE_CONTENT, MENSAJE_TIPO, "; 
      $query_nuevo_mensaje.= "MENSAJE_FSEND, MENSAJE_FREAD) "; 
      $query_nuevo_mensaje.= "VALUES(0,2,0,'Mantenimiento de Equipo Programado',"; 
      $query_nuevo_mensaje.= "'$mtto_text',1,'$fingreso','')";
			//echo $query_nuevo_mensaje; 
      //ejecucion del query para insertar nueva mensaje
      $nuevo_mensaje = nmysql2_query($query_nuevo_mensaje,$link);
      //query para el ingreso del mensaje PARA LA SECRETARIA
      $query_nuevo_mensaje = "INSERT INTO mensaje(MENSAJE_FROM, MENSAJE_TO, MENSAJE_CC, "; 
      $query_nuevo_mensaje.= "MENSAJE_SUBJECT, MENSAJE_CONTENT, MENSAJE_TIPO, "; 
      $query_nuevo_mensaje.= "MENSAJE_FSEND, MENSAJE_FREAD) "; 
      $query_nuevo_mensaje.= "VALUES(0,4,0,'Mantenimiento de Equipo Programado',"; 
      $query_nuevo_mensaje.= "'$mtto_text',1,'$fingreso','')"; 
      //ejecucion del query para insertar nueva mensaje
      $nuevo_mensaje = nmysql2_query($query_nuevo_mensaje,$link);
      //query para el ingreso del mensaje PARA EL GERENTE
      $query_nuevo_mensaje = "INSERT INTO mensaje(MENSAJE_FROM, MENSAJE_TO, MENSAJE_CC, "; 
      $query_nuevo_mensaje.= "MENSAJE_SUBJECT, MENSAJE_CONTENT, MENSAJE_TIPO, "; 
      $query_nuevo_mensaje.= "MENSAJE_FSEND, MENSAJE_FREAD) "; 
      $query_nuevo_mensaje.= "VALUES(0,5,0,'Mantenimiento de Equipo Programado',"; 
      $query_nuevo_mensaje.= "'$mtto_text',1,'$fingreso','')"; 
      //ejecucion del query para insertar nueva mensaje
      $nuevo_mensaje = nmysql2_query($query_nuevo_mensaje,$link);
		  //luego actualizamos el campo FECHA_MSG1 para que solo se envie un mensaje diario
		  $query_update_msg = "update maquina set MAQUINA_FECHA_MSG3 = '$fechaActual' where MAQUINA_ID = $maquina_id";
		  $update_msg = nmysql2_query($query_update_msg,$link);
		 }
		}
		if($maquina_horas4 > 0){
		 //busqueda del ultimo mantenimiento del mismo tipo
		 $query_mtto = "select MAX(MTTO_HORAS) as HORAS_MTTO from mtto ";
		 $query_mtto.= "where MTTO_TIPO = '$maquina_mtto4' and MTTO_MAQUINA_ID = $maquina_id";
     $mtto = nmysql2_query($query_mtto,$link); 
     $max_horas = mysql_result($mtto, 0, "HORAS_MTTO");
		 $fechaActual = date("Y-m-d");
		 if ((($sum_horas - $max_horas) > ($maquina_horas4)) && ("$fechaActual" != "$maquina_fecha4")){
      //preparacion del mensaje
		  $mtto_text = "MENSAJE PARA MANTENIMIENTO PREVENTIVO";
		  $mtto_text.= "\n\nLA MAQUINA : ".$maquina_nombre;
		  $mtto_text.= "\nREQUIERE : ".$maquina_mtto4;
		  $mtto_text.= "\nPROGRAMADO CADA : ".$maquina_horas4." Horas";
		  $mtto_text.= "\nHORAS DE TRABAJO ACTUALES : ".ceil($sum_horas). " Horas";
		  $mtto_text.= "\nULTIMO MTTO REALIZADO A LAS : ".$max_horas." Horas";
		  //se genera mensaje para recordar el mtto
      //query para el ingreso del mensaje PARA EL ADMINISTRADOR
      $query_nuevo_mensaje = "INSERT INTO mensaje(MENSAJE_FROM, MENSAJE_TO, MENSAJE_CC, "; 
      $query_nuevo_mensaje.= "MENSAJE_SUBJECT, MENSAJE_CONTENT, MENSAJE_TIPO, "; 
      $query_nuevo_mensaje.= "MENSAJE_FSEND, MENSAJE_FREAD) "; 
      $query_nuevo_mensaje.= "VALUES(0,2,0,'Mantenimiento de Equipo Programado',"; 
      $query_nuevo_mensaje.= "'$mtto_text',1,'$fingreso','')";
			//echo $query_nuevo_mensaje; 
      //ejecucion del query para insertar nueva mensaje
      $nuevo_mensaje = nmysql2_query($query_nuevo_mensaje,$link);
      //query para el ingreso del mensaje PARA LA SECRETARIA
      $query_nuevo_mensaje = "INSERT INTO mensaje(MENSAJE_FROM, MENSAJE_TO, MENSAJE_CC, "; 
      $query_nuevo_mensaje.= "MENSAJE_SUBJECT, MENSAJE_CONTENT, MENSAJE_TIPO, "; 
      $query_nuevo_mensaje.= "MENSAJE_FSEND, MENSAJE_FREAD) "; 
      $query_nuevo_mensaje.= "VALUES(0,4,0,'Mantenimiento de Equipo Programado',"; 
      $query_nuevo_mensaje.= "'$mtto_text',1,'$fingreso','')"; 
      //ejecucion del query para insertar nueva mensaje
      $nuevo_mensaje = nmysql2_query($query_nuevo_mensaje,$link);
      //query para el ingreso del mensaje PARA EL GERENTE
      $query_nuevo_mensaje = "INSERT INTO mensaje(MENSAJE_FROM, MENSAJE_TO, MENSAJE_CC, "; 
      $query_nuevo_mensaje.= "MENSAJE_SUBJECT, MENSAJE_CONTENT, MENSAJE_TIPO, "; 
      $query_nuevo_mensaje.= "MENSAJE_FSEND, MENSAJE_FREAD) "; 
      $query_nuevo_mensaje.= "VALUES(0,5,0,'Mantenimiento de Equipo Programado',"; 
      $query_nuevo_mensaje.= "'$mtto_text',1,'$fingreso','')"; 
      //ejecucion del query para insertar nueva mensaje
      $nuevo_mensaje = nmysql2_query($query_nuevo_mensaje,$link);
		  //luego actualizamos el campo FECHA_MSG1 para que solo se envie un mensaje diario
		  $query_update_msg = "update maquina set MAQUINA_FECHA_MSG4 = '$fechaActual' where MAQUINA_ID = $maquina_id";
		  $update_msg = nmysql2_query($query_update_msg,$link);
		 }
		}
		if($maquina_horas5 > 0){
		 //busqueda del ultimo mantenimiento del mismo tipo
		 $query_mtto = "select MAX(MTTO_HORAS) as HORAS_MTTO from mtto ";
		 $query_mtto.= "where MTTO_TIPO = '$maquina_mtto5' and MTTO_MAQUINA_ID = $maquina_id";
     $mtto = nmysql2_query($query_mtto,$link); 
     $max_horas = mysql_result($mtto, 0, "HORAS_MTTO");
		 $fechaActual = date("Y-m-d");
		 if ((($sum_horas - $max_horas) > ($maquina_horas5)) && ("$fechaActual" != "$maquina_fecha5")){
      //preparacion del mensaje
		  $mtto_text = "MENSAJE PARA MANTENIMIENTO PREVENTIVO";
		  $mtto_text.= "\n\nLA MAQUINA : ".$maquina_nombre;
		  $mtto_text.= "\nREQUIERE : ".$maquina_mtto5;
		  $mtto_text.= "\nPROGRAMADO CADA : ".$maquina_horas5." Horas";
		  $mtto_text.= "\nHORAS DE TRABAJO ACTUALES : ".ceil($sum_horas). " Horas";
		  $mtto_text.= "\nULTIMO MTTO REALIZADO A LAS : ".$max_horas." Horas";
		  //se genera mensaje para recordar el mtto
      //query para el ingreso del mensaje PARA EL ADMINISTRADOR
      $query_nuevo_mensaje = "INSERT INTO mensaje(MENSAJE_FROM, MENSAJE_TO, MENSAJE_CC, "; 
      $query_nuevo_mensaje.= "MENSAJE_SUBJECT, MENSAJE_CONTENT, MENSAJE_TIPO, "; 
      $query_nuevo_mensaje.= "MENSAJE_FSEND, MENSAJE_FREAD) "; 
      $query_nuevo_mensaje.= "VALUES(0,2,0,'Mantenimiento de Equipo Programado',"; 
      $query_nuevo_mensaje.= "'$mtto_text',1,'$fingreso','')";
			//echo $query_nuevo_mensaje; 
      //ejecucion del query para insertar nueva mensaje
      $nuevo_mensaje = nmysql2_query($query_nuevo_mensaje,$link);
      //query para el ingreso del mensaje PARA LA SECRETARIA
      $query_nuevo_mensaje = "INSERT INTO mensaje(MENSAJE_FROM, MENSAJE_TO, MENSAJE_CC, "; 
      $query_nuevo_mensaje.= "MENSAJE_SUBJECT, MENSAJE_CONTENT, MENSAJE_TIPO, "; 
      $query_nuevo_mensaje.= "MENSAJE_FSEND, MENSAJE_FREAD) "; 
      $query_nuevo_mensaje.= "VALUES(0,4,0,'Mantenimiento de Equipo Programado',"; 
      $query_nuevo_mensaje.= "'$mtto_text',1,'$fingreso','')"; 
      //ejecucion del query para insertar nueva mensaje
      $nuevo_mensaje = nmysql2_query($query_nuevo_mensaje,$link);
      //query para el ingreso del mensaje PARA EL GERENTE
      $query_nuevo_mensaje = "INSERT INTO mensaje(MENSAJE_FROM, MENSAJE_TO, MENSAJE_CC, "; 
      $query_nuevo_mensaje.= "MENSAJE_SUBJECT, MENSAJE_CONTENT, MENSAJE_TIPO, "; 
      $query_nuevo_mensaje.= "MENSAJE_FSEND, MENSAJE_FREAD) "; 
      $query_nuevo_mensaje.= "VALUES(0,5,0,'Mantenimiento de Equipo Programado',"; 
      $query_nuevo_mensaje.= "'$mtto_text',1,'$fingreso','')"; 
      //ejecucion del query para insertar nueva mensaje
      $nuevo_mensaje = nmysql2_query($query_nuevo_mensaje,$link);
		  //luego actualizamos el campo FECHA_MSG1 para que solo se envie un mensaje diario
		  $query_update_msg = "update maquina set MAQUINA_FECHA_MSG5 = '$fechaActual' where MAQUINA_ID = $maquina_id";
		  $update_msg = nmysql2_query($query_update_msg,$link);
		 }
		}
		if($maquina_horas6 > 0){
		 //busqueda del ultimo mantenimiento del mismo tipo
		 $query_mtto = "select MAX(MTTO_HORAS) as HORAS_MTTO from mtto ";
		 $query_mtto.= "where MTTO_TIPO = '$maquina_mtto6' and MTTO_MAQUINA_ID = $maquina_id";
     $mtto = nmysql2_query($query_mtto,$link); 
     $max_horas = mysql_result($mtto, 0, "HORAS_MTTO");
		 $fechaActual = date("Y-m-d");
		 if ((($sum_horas - $max_horas) > ($maquina_horas6)) && ("$fechaActual" != "$maquina_fecha6")){
      //preparacion del mensaje
		  $mtto_text = "MENSAJE PARA MANTENIMIENTO PREVENTIVO";
		  $mtto_text.= "\n\nLA MAQUINA : ".$maquina_nombre;
		  $mtto_text.= "\nREQUIERE : ".$maquina_mtto6;
		  $mtto_text.= "\nPROGRAMADO CADA : ".$maquina_horas6." Horas";
		  $mtto_text.= "\nHORAS DE TRABAJO ACTUALES : ".ceil($sum_horas). " Horas";
		  $mtto_text.= "\nULTIMO MTTO REALIZADO A LAS : ".$max_horas." Horas";
		  //se genera mensaje para recordar el mtto
      //query para el ingreso del mensaje PARA EL ADMINISTRADOR
      $query_nuevo_mensaje = "INSERT INTO mensaje(MENSAJE_FROM, MENSAJE_TO, MENSAJE_CC, "; 
      $query_nuevo_mensaje.= "MENSAJE_SUBJECT, MENSAJE_CONTENT, MENSAJE_TIPO, "; 
      $query_nuevo_mensaje.= "MENSAJE_FSEND, MENSAJE_FREAD) "; 
      $query_nuevo_mensaje.= "VALUES(0,2,0,'Mantenimiento de Equipo Programado',"; 
      $query_nuevo_mensaje.= "'$mtto_text',1,'$fingreso','')";
			//echo $query_nuevo_mensaje; 
      //ejecucion del query para insertar nueva mensaje
      $nuevo_mensaje = nmysql2_query($query_nuevo_mensaje,$link);
      //query para el ingreso del mensaje PARA LA SECRETARIA
      $query_nuevo_mensaje = "INSERT INTO mensaje(MENSAJE_FROM, MENSAJE_TO, MENSAJE_CC, "; 
      $query_nuevo_mensaje.= "MENSAJE_SUBJECT, MENSAJE_CONTENT, MENSAJE_TIPO, "; 
      $query_nuevo_mensaje.= "MENSAJE_FSEND, MENSAJE_FREAD) "; 
      $query_nuevo_mensaje.= "VALUES(0,4,0,'Mantenimiento de Equipo Programado',"; 
      $query_nuevo_mensaje.= "'$mtto_text',1,'$fingreso','')"; 
      //ejecucion del query para insertar nueva mensaje
      $nuevo_mensaje = nmysql2_query($query_nuevo_mensaje,$link);
      //query para el ingreso del mensaje PARA EL GERENTE
      $query_nuevo_mensaje = "INSERT INTO mensaje(MENSAJE_FROM, MENSAJE_TO, MENSAJE_CC, "; 
      $query_nuevo_mensaje.= "MENSAJE_SUBJECT, MENSAJE_CONTENT, MENSAJE_TIPO, "; 
      $query_nuevo_mensaje.= "MENSAJE_FSEND, MENSAJE_FREAD) "; 
      $query_nuevo_mensaje.= "VALUES(0,5,0,'Mantenimiento de Equipo Programado',"; 
      $query_nuevo_mensaje.= "'$mtto_text',1,'$fingreso','')"; 
      //ejecucion del query para insertar nueva mensaje
      $nuevo_mensaje = nmysql2_query($query_nuevo_mensaje,$link);
		  //luego actualizamos el campo FECHA_MSG1 para que solo se envie un mensaje diario
		  $query_update_msg = "update maquina set MAQUINA_FECHA_MSG6 = '$fechaActual' where MAQUINA_ID = $maquina_id";
		  $update_msg = nmysql2_query($query_update_msg,$link);
		 }
		}
	 }
	}
	nmysql2_free_result($review_maquina);
  */
  //FIN DE LA DESHABILITACION DE LOS MENSAJES GENERADOS A PARTIR DE LOS MTTOS
	//AGO-19-2008
 }
 /*-----------------------------------------------------------------------------
 Estados de los servicios:
 1.Compra (0-Anulada,1-Sin Pago,2-Cancelada Parcialmente,3-Cancelada Totalmente)
 2.Cheque (0-Anulado,1-Giradao,2-Pagado,3-Devuelto)
 3.Maquina (0-Inactiva,1-Disponible,2-Mantenimiento,3-Reparacion)
 4.Ord Trabajo (0-Anulada,1-Abierta,2-Finalizada)
 5.Factura (0-Anulada,1-Sin Pago,2-Cancelada Parcialmente,3-Cancelada Totalmente)
 -----------------------------------------------------------------------------*/

 //----------------------------------------------------------------
 //funcion para traduccion
 //FECHA: Noviembre 07 de 2012
 //CREADO POR: Javier García
function trans($data,$lang){
  $dictionary = array();
  $dictionary[]  =  array('Fecha','Date','日期');
  $dictionary[]  =  array('Fecha de la Orden','Date of Order','Date of Order');
  $dictionary[]  =  array('Ingeniero','Engineer','Engineer');
  $dictionary[]  =  array('Ingeniero de Soporte T&eacute;cnico','Technical Support Engineer','Technical Support Engineer');
  $dictionary[]  =  array('T&eacute;cnico','Technical','Technical');
  $dictionary[]  =  array('Personal de Prueba','Testing Personnel','Testing Personnel');
  $dictionary[]  =  array('Modelo Equipo / Serial','Equipment Model / Serial','Equipment Model / Serial');
  $dictionary[]  =  array('Modelo / Serial','Model / Serial','Model / Serial');
  $dictionary[]  =  array('MainBoard / HeadBoard','MainBoard / HeadBoard','MainBoard / HeadBoard');
  $dictionary[]  =  array('PhotoPrint / PhBoard','PhotoPrint / PhBoard','PhotoPrint / PhBoard');
  $dictionary[]  =  array('Fecha de Producci&oacute;n','ProductionDate','ProductionDate');
  $dictionary[]  =  array('Cliente / Canal','Customer / Canal','Customer / Canal');
  $dictionary[]  =  array('Consec.','Cons.','Cons.');
  $dictionary[]  =  array('Canal','Channel','Channel');
  $dictionary[]  =  array('Consecutivo','Consecutive','Consecutive');
  $dictionary[]  =  array('Est.','St.','St.');
  $dictionary[]  =  array('Estado','Status','进程');
  $dictionary[]  =  array('Certificados de Instalaci&oacute;n de Equipos','Equipment Installation Certificates','Equipment Installation Certificates');
  $dictionary[]  =  array('ESS Procesos de Prueba Espec&iacute;ficos de Equipos','ESS Machine Testing Specific Processes','ESS Machine Testing Specific Processes');
  $dictionary[]  =  array('Anulado','Cancelled','已取消');
  $dictionary[]  =  array('Finalizado','Finished','Finished');
  $dictionary[]  =  array('Pendiente','Pending','Pending');
  $dictionary[]  =  array('Mostrados','Displayed','预览');
  $dictionary[]  =  array('Registros de','Records of','记录');
  $dictionary[]  =  array('Buscar por . . .','Search for . . .','搜索...');
  $dictionary[]  =  array('No. Consecutivo','Consecutive','Consecutive');
  $dictionary[]  =  array('Cliente','Customer','客户');
  $dictionary[]  =  array('Canal','Canal','Canal');
  $dictionary[]  =  array('Instalaciones','Installations','Installations');
  $dictionary[]  =  array('Adicionar','Add','添加');
  $dictionary[]  =  array('Buscar','Search','查找');
  $dictionary[]  =  array('Modelo Equipo','Model Name','Model Name');
  $dictionary[]  =  array('Serial Equipo','Machine ID','Machine ID');
  $dictionary[]  =  array('MainBoard','MainBoard ID','MainBoard ID');
  $dictionary[]  =  array('HeadBoard','HeadBoard ID','HeadBoard ID');
  $dictionary[]  =  array('PhotoPrint','PhotoPrint ID','PhotoPrint ID');
  $dictionary[]  =  array('F.Producci&oacute;n(aaaa-mm-dd)','Production Date(yyyy-mm-dd)','Production Date(yyyy-mm-dd)');
  $dictionary[]  =  array('F.Producci&oacute;n','Production Date','Production Date');
  $dictionary[]  =  array('F.Prueba(aaaa-mm-dd)','Testing Date(yyyy-mm-dd)','Testing Date(yyyy-mm-dd)');
  $dictionary[]  =  array('F.Prueba','Testing Date','Testing Date');
  $dictionary[]  =  array('Mensajer&iacute;a Interna','Internal Messaging','Internal Messaging');
  $dictionary[]  =  array('Asunto','Subject','Subject');
  $dictionary[]  =  array('Remitente','From','From');
  $dictionary[]  =  array('Estado','Status','进程');
  $dictionary[]  =  array('Leido','Readed','Readed');
  $dictionary[]  =  array('Sin Leer','Not Readed','Not Readed');
  $dictionary[]  =  array('Mesajer&iacute;a interna : Leer','Internal Messaging : Read','Internal Messaging : Read');
  $dictionary[]  =  array('Mesajer&iacute;a interna : Responder','Internal Messaging : Reply','Internal Messaging : Reply');
  $dictionary[]  =  array('Mesajer&iacute;a interna : Redactar','Internal Messaging : Write','Internal Messaging : Write');
  $dictionary[]  =  array('Mesajer&iacute;a interna : Enviar','Internal Messaging : Send','Internal Messaging : Send');
  $dictionary[]  =  array('Datos del Mensaje','Message Data','Message Data');
  $dictionary[]  =  array('Fecha de Env&iacute;o','Sending Date','Sending Date');
  $dictionary[]  =  array('Fecha de Leido','Readed Date','Readed Date');
  $dictionary[]  =  array('Tipo del mensaje','Message Type','Message Type');
  $dictionary[]  =  array('Mensaje','Message','Message');
  $dictionary[]  =  array('Responder','Reply','Reply');
  $dictionary[]  =  array('Regresar','Back','后退');
  $dictionary[]  =  array('Confirmaci&oacute;n de Env&iacute;o','Sending Confirmation','Sending Confirmation');
  $dictionary[]  =  array('Destinatario','To','To');
  $dictionary[]  =  array('Respondido Correctamente','You Replied Ok','You Replied Ok');
  $dictionary[]  =  array('Se ha enviado una notificaci&oacute;n al buz&oacute;n de email de los destinatarios','An email with notification was be sending to all recipients','An email with notification was be sending to all recipients');
  $dictionary[]  =  array('Nuevo Mensaje','New Message','New Message');
  $dictionary[]  =  array('Eliminar','Delete','Delete');
  $dictionary[]  =  array('Fecha(aaaa-mm-dd)','Date(yyyy-mm-dd)','Date(yyyy-mm-dd)');
  $dictionary[]  =  array('Campos Obligatorios','Required Fields','必填项');
  $dictionary[]  =  array('Enviar Mensaje','Send Message','Send Message');
  $dictionary[]  =  array('Enviar Otro','Send Other','Send Other');
  $dictionary[]  =  array('Envi&oacute; Correctamente','Sending Ok','Sending Ok');
  $dictionary[]  =  array('Vacio','Empty','Empty');
  $dictionary[]  =  array('M&aacute;quinas','Machines','Machines');
  $dictionary[]  =  array('Fecha de Producci&oacute;n (AAAA-MM-DD)','Production Date (YYY-MM-DD)','Production Date (YYY-MM-DD)');
  $dictionary[]  =  array('Ver Registros','See Registers','See Registers');
  $dictionary[]  =  array('Volver a Lista de Carpetas','Back to Folder List','回到文件夹列表');
  $dictionary[]  =  array('Volver','Back','后退');
  $dictionary[]  =  array('Listar Todos los Registros','List All Registers','显示所有注册账户');
  $dictionary[]  =  array('TODOS','ALL','所有');
  $dictionary[]  =  array('Todos','All','所有');
  $dictionary[]  =  array('Reenviar','Forward','Forward');
  $dictionary[]  =  array('Reportes','Reports','Reports');
  $dictionary[]  =  array('Reportes de Servicio T&eacute;cnico','Service Reports','Service Reports');
  $dictionary[]  =  array('Registros','Files','文件');
  $dictionary[]  =  array('Garant&iacute;as','Warranties','Warranties');
  $dictionary[]  =  array('Pa&iacute;s','Country','国家');
  $dictionary[]  =  array('Inventario','Inventory','Inventory');
  $dictionary[]  =  array('Bodega','Hold','Hold');
  $dictionary[] = array('Log&iacute;stica','Logistics','Logistics');
  $dictionary[] = array('M&aacute;quina ID.','Machine ID.','Machine ID.');
  $dictionary[] = array('Despacho.','Dispatch.','Dispatch.');
  $dictionary[] = array('Preparado.','Prepared.','Prepared.');
  $dictionary[] = array('Pendiente Realizar Despacho.','Pending Make Dispatch.','Pending Make Dispatch.');
  $dictionary[] = array('Envio.','Shipment.','Shipment.');
  $dictionary[] = array('Llegada.','Arrival.','Arrival.');
  $dictionary[] = array('Estimado.','Estimated','Estimated');
  $dictionary[] = array('Cable de Red.','Network Cable.','Network Cable.');
  $dictionary[] = array('Destornillador de Pala.','Flat Screwdriver.','Flat Screwdriver.');
  $dictionary[] = array('Manija.','Handle.','Handle.');
  $dictionary[] = array('Soporte de Patas.','Leg Stand.','Leg Stand.');
  $dictionary[] = array('Cable de Poder.','Power Cable.','Power Cable.');
  $dictionary[] = array('Bolsa Tornillera.','Screwdriver Bag.','Screwdriver Bag.');
  $dictionary[] = array('Cable Polo a Tierra.','Grounded Pole Cable.','Grounded Pole Cable.');
  $dictionary[] = array('Kit de Cuchillas.','Blades Kit.','Blades Kit.');
  $dictionary[] = array('Lapiz de Grafado.','Graphite Pencil.','Graphite Pencil.');
  $dictionary[] = array('Abrazaderas.','Clamps.','Clamps.');
  $dictionary[] = array('Llaves de Puerta.','Door Keys.','Door Keys.');
  $dictionary[] = array('Cabezales.','Headers.','Headers.');
  $dictionary[] = array('Canal\Pa&iacute;s.','Channel\Country.','Channel\Country.');
  $dictionary[] = array('Entre.','Between.','Between.');
  $dictionary[] = array('Despacho','Dispatch','发货准备');
  $dictionary[] = array('Envio','Shipment','Shipment');
  $dictionary[] = array('Estimada','Estimated','Estimated');
  $dictionary[] = array('Arribo','Arrival','Arrival');
  $dictionary[] = array('Datos del Despacho','Dispatch Data','Dispatch Data');
  $dictionary[] = array('Datos del Envio','Shipment Data','Shipment Data');
  $dictionary[] = array('Datos Estimados de Llegada','Estimated  Arrival Data','Estimated  Arrival Data');
  $dictionary[] = array('Datos de Llegada','Arrival Data','Arrival Data');
  $dictionary[] = array('Fecha del Despacho','Dispatch Date','Dispatch Date');
  $dictionary[] = array('Fecha del Envio','Shipment Date','Shipment Date');
  $dictionary[] = array('Fecha Estima de Llegada','Estimated  Arrival Date','Estimated  Arrival Date');
  $dictionary[] = array('Fecha de Llegada','Arrival Date','Arrival Date');
  $dictionary[] = array('Contenido del Inventario de Despacho','Inventory the Dispatch Contents','Inventory the Dispatch Contents');
  $dictionary[] = array('Contenido del Inventario de Envio','Inventory the Shipment Contents','Inventory the Shipment Contents');
  $dictionary[] = array('Contenido del Inventario Estimado de Llegada','Inventory the Estimated Arrival Content','Inventory the Estimated Arrival Content');
  $dictionary[] = array('Contenido del Inventario de Llegada','Inventory the Arrival Contents','Inventory the Arrival Contents');
  $dictionary[] = array('Reportes de Inventario.','Inventory Reports','Inventory Reports');
  $dictionary[] = array('Bodega.','Cellar.','Cellar.');
  $dictionary[] = array('Tipo  de Movimiento.','Movement Type.','Movement Type.');
  $dictionary[] = array('Movimiento','Movement','Movement');
  $dictionary[] = array('Tercero.','Third.','Third.');
  $dictionary[] = array('Documento.','Document.','Document.');
  $dictionary[] = array('Categoria.','Category.','Category.');
  $dictionary[] = array('Art. Referencia.','Ref. Article.','Ref. Article.');
  $dictionary[]  =  array('Volver al Inventario','Back to Inventory','Back to Inventory');
  $dictionary[] = array('Configuraci&oacute;n de Alertas','Alerts Setup','Alerts Setup');
  $dictionary[] = array('Tipo de Alerta','Type of Alert','Type of Alert');
  $dictionary[] = array('Alertas','Alerts','Alerts');
  $dictionary[] = array('Ciudad','City','城市');
  $dictionary[] = array('Consolidado','Consolidated','Consolidated');
  $dictionary[] = array('Limpiar','Clear','Clear');
  $dictionary[] = array('Detalle','Details','Details');
  $dictionary[] = array('Proceso MTS','MTS Process','MTS Process');
  $dictionary[] = array('Digite el serial de la M&aacute;quina a consultar en el campo de texto','Enter the  Machine serial to check in the text field','Enter the  Machine serial to check in the text field');
  $dictionary[] = array('Instalaci&oacute;n','Installation','Installation');
  $dictionary[] = array('Reenv&iacute;o','Forwarding','Forwarding');
  $dictionary[] = array('Repuestos','Spares','Spares');
  $dictionary[] = array('Referencia','Reference','参考');
  $dictionary[] = array('Nombre del Repuesto','Spare Name','Spare Name');
  $dictionary[] = array('Reportes de Inventario','Inventory Reports','Inventory Reports');
  $dictionary[] = array('Tipo de Reporte','Type of Report','Type of Report');
  $dictionary[] = array('Fecha Solicitud','Date of Application','Date of Application');
  $dictionary[] = array('Pa&iacute;s','Country','国家');
  $dictionary[] = array('Descripción','Description','描述');
  $dictionary[] = array('Alertas Internas','Internal Alerts','Internal Alerts');
  $dictionary[] = array('Nueva Alerta','New Alert','New Alert');
  $dictionary[] = array('Datos de la alerta','Alert data','Alert data');
  $dictionary[] = array('Alerta','Alert','Alert');
  $dictionary[] = array('Atendida','Attended','Attended');
  $dictionary[] = array('Fecha de recibido','Date received','Date received');
  $dictionary[] = array('Tipo de alerta','Alert type','Alert type');
  $dictionary[] = array('Fecha atendido','Date attended','Date attended');
  $dictionary[] = array('Alertas Internas : Guardado atendido','Internal Alerts : Saved attended','Internal Alerts : Saved attended');
  $dictionary[] = array('Confirmación de alerta atendida','Confirmation of alert attended','Confirmation of alert attended');
  $dictionary[] = array('Guardado Correctamente','Saved Correctly','保存正确');
  $dictionary[] = array('No atendido','Not attended','Not attended');
  $dictionary[] = array('Sin atender','Not attended','Not attended');
  $dictionary[] = array('Fecha atendido','Date attended','Date attended');
  $dictionary[] = array('Grupos','Groups','Groups');
  $dictionary[] = array('Configuraci&oacute;n de Grupos','Groups Setup','Groups Setup');
  $dictionary[] = array('Nombre del grupo','Groups Name','Groups Name');
  $dictionary[] = array('Grupo Seleccionar . . .','Select','Select');
  $dictionary[] = array('Grupos : Agregar','Groups : Add','Groups : Add');
  $dictionary[] = array('Grupos : Editar','Groups : Edit','Groups : Edit');
  $dictionary[] = array('Activo','Active','Active');
  $dictionary[] = array('Inactivo','Inactive','Inactive');
  $dictionary[] = array('No se pudo guardar','Could not save','无法保存');
  $dictionary[] = array('Ya esta registrado','Are you already registered','Are you already registered');
  $dictionary[] = array('Filtrar por . . .','Filter by . . .','筛选');
  $dictionary[] = array('Crítica','Critical','Critical');
  $dictionary[] = array('Informativa','Informative','Informative');
  $dictionary[] = array('Filtros generales','General filter','General filter');
  $dictionary[] = array('Observaciones','Remarks','动态监控');
  $dictionary[] = array('Serial','Serial','Serial');
  $dictionary[] = array('Referencia unificada','Unified reference','Unified reference');
  $dictionary[] = array('Proveedor','Supplier','供应商');
  $dictionary[] = array('Contacto','Contact','联系人');
  $dictionary[] = array('No. Orden','Order No.','订单号');
  $dictionary[] = array('Histórico de ordenes de compra','Purchase order history','购买历史记录');
  $dictionary[] = array('Condiciones de pago','Payment conditions','付款状态');
  $dictionary[] = array('Cond. de pago','Payment cond.','付款状态');
  $dictionary[] = array('No aprobado','Not approved','不同意');
  $dictionary[] = array('Aprobado','Approved','同意');
  $dictionary[] = array('En proceso','In process','进行中');
  $dictionary[] = array('Ejecutado','Executed','已执行');
  $dictionary[] = array('Ref','Ref','参考');
  $dictionary[] = array('Cantidad','Quantity','数量');
  $dictionary[] = array('Imprimir reporte','Print report','打印报告');
  $dictionary[] = array('Descargar reporte','Download report','下载报告');
  $dictionary[] = array('Ordenes de compra','Purchase orders','购买订单');
  $dictionary[] = array('Agregar','Add','添加');
  $dictionary[] = array('Orden de compra','Purchase order','购买订单');
  $dictionary[] = array('Dirección de envió','Shipping address','发货地址');
  $dictionary[] = array('Archivos de subida','Upload files','上传文件');
  $dictionary[] = array('Archivos de subida como','Upload files as','上传此项文件');
  $dictionary[] = array('Máximo tamaño por archivo','Max size for file','最大上传文件');
  $dictionary[] = array('Debe ingresar','Must enter','必须输入');
  $dictionary[] = array('Debe colocar una referencia del ítem','You must enter a reference of the item','此项必须输入参考信息');
  $dictionary[] = array('Debe colocar una descripción del ítem','You must enter a description of the item','此项必须输入描述');
  $dictionary[] = array('Debe ingresar una cantidad en el ítem','You must enter a quantity in the item','此项必须输入数量');
  $dictionary[] = array('La cantidad en el ítem','The amount in the item','此项总数');
  $dictionary[] = array('no puede ser 0','can not be 0','不可写0');
  $dictionary[] = array('Digito un carácter no valido','Enter an invalid character','输入错误');
  $dictionary[] = array('El archivo "+name+" supera el m\u00e1ximo permitido','File "+name+" exceeds the maximum allowed','File "+name+" exceeds the maximum allowed');
  $dictionary[] = array('El archivo "+name+" no es del tipo de imagen permitida.','The file "+name+" is not the type of image allowed.','The file "+name+" is not the type of image allowed.');
  $dictionary[] = array('El archivo "+name+" no es del tipo de archivo permitido.','The file "+name+" is not the type of file allowed.','The file "+name+" is not the type of file allowed.');
  $dictionary[] = array('Quitar Ítem','Remove item','移除项');
  $dictionary[] = array('Se selecciona el usuario que será la persona de contacto','Select the user who will be the contact person','选择联系人');
  $dictionary[] = array('Agregar Item','Add item','增加项');
  $dictionary[] = array('Registrar','Register','注册');
  $dictionary[] = array('Registrar Orden de compra','Register purchase order','新增购买订单');
  $dictionary[] = array('Archivo','File',' 文件');
  $dictionary[] = array('Los campos del navegador web no están en correcto estado','Web browser fields are not in correct condition','浏览器区域状态不对');
  $dictionary[] = array('Confirmaci&oacute;n de Ingreso','Purchase Order confirmation','订单确认');
  $dictionary[] = array('Debe ingresar el PO de la orden de compra','You must enter the PO of the purchase order','新订单必须输入PO');
  $dictionary[] = array('Debe ingresar el No. de factura de la orden de compra','You must enter the bill number of the purchase order','新订单必须输入账单号');
  $dictionary[] = array('Está seguro de que desea aprobar','You are sure you need to approve','确认同意');
  $dictionary[] = array('Está seguro de que desea ejecutar','You are sure you need to execute','确认执行');
  $dictionary[] = array('Está seguro de que desea anular la orden de compra','You are sure that you need to cancel the purchase order','确认取消订单');
  $dictionary[] = array('Está seguro que la orden de compra se encuentra en proceso','You are sure that the purchase order is in process','确认订单在进行中');
  $dictionary[] = array('Por favor ingrese el PO de la orden de compra','Please enter PO for the purchase order','请输入订单号');
  $dictionary[] = array('Editar','Edit','编辑');
  $dictionary[] = array('No. factura','Bill number','账单号码');
  $dictionary[] = array('Aprobar','Approve',' 同意');
  $dictionary[] = array('Ejecutar','Execute','执行');
  $dictionary[] = array('Anular','Reverse','取消');//ajustado por uso de cancel
  $dictionary[] = array('Fecha de registro','Date registered','Date registered');
  $dictionary[] = array('Usuario','User','User');
  $dictionary[] = array('Nombre de usuario','User name','User name');
  $dictionary[] = array('Tipo de modificación','Type of modification','Type of modification');
  $dictionary[] = array('Reporte de histórico de las órdenes de compra','Historical report of purchase orders','Historical report of purchase orders');
  $dictionary[] = array('Reporte de actividad de usuarios','User activity report','User activity report');
  $dictionary[] = array('Nueva orden de compra','New purchase order','新订单');
  $dictionary[] = array('Se ha creado la nueva orden de compra','The new purchase order has been created','新订单已创建');
  $dictionary[] = array('Modificada','Modified','已修改');
  $dictionary[] = array('Se Modificado orden de compra el estado es','Modified purchase order the state is','订单状态已修改');
  $dictionary[] = array('Tiene','You have','You have');
  $dictionary[] = array('Mensaje(s) sin leer','Unread messages','Unread messages');
  $dictionary[] = array('Tiene Alerta(s)','You have alerts','You have alerts');
  $dictionary[] = array('Pendiente por leer','Pending to read','Pending to read');
  $dictionary[] = array('Descripción fabricante','Manufacturer description','生产商描述');
  $dictionary[] = array('Agregar fotos de despacho','Add dispatch photos','添加发货照片');
  $dictionary[] = array('Fotos de despacho','Dispatch photos','发货照片');
  $dictionary[] = array('Guardar','Save','Save');
  $dictionary[] = array('Descripción de la foto','Photo Description','Photo Description');
  $dictionary[] = array('Debe selecionar un archivo en la descripción','You must select a file in the description','You must select a file in the description');
  $dictionary[] = array('Desea cancelar el cargue de fotos','You want to cancel the photo upload','You want to cancel the photo upload');
  $dictionary[] = array('Dirección','Address','地址');
  $dictionary[] = array('País','Country','国家');
  $dictionary[] = array('Numero de teléfono','Phone number','电话号码');
  $dictionary[] = array('Correo electrónico','Email','邮箱');
  $dictionary[] = array('Información del cliente','Customer information','客户信息');
  $dictionary[] = array('Términos','Terms','条款');
  $dictionary[] = array('Tiempo de producción','Production time','生产时间');
  $dictionary[] = array('Mostrar calendario','Show calendar','展会日期');
  $dictionary[] = array('No. de factura','Invioce #','发票号');
  $dictionary[] = array('Cuenta','Account','账号');
  $dictionary[] = array('Tipo de envió','Shipping type','发货类型');
  $dictionary[] = array('Dirección de recogida','Pick up address','取货地址');
  $dictionary[] = array('Envió','Shipping','发货');
  $dictionary[] = array('Puerto','Port','港口');
  $dictionary[] = array('Embalaje','Packing','包装');
  $dictionary[] = array('Seleccione una opción','Select an option','选项');
  $dictionary[] = array('Información de orden','Order information','订单信息');
  $dictionary[] = array('Información de contacto del reenviador','Forwarder contact information','货代信息');
  $dictionary[] = array('Responsable comercial','Commercial responsible','销售负责人');
  $dictionary[] = array('Responsable logístico','Logistics responsible','交货负责人');
  $dictionary[] = array('Adjuntos','Attachments','附件');
  $dictionary[] = array('Lista de empaque','Packing list','箱单');
  $dictionary[] = array('Factura','Invoice','发票');
  $dictionary[] = array('Notas e instrucciones especiales','Notes and special instructions','特殊备注');
  $dictionary[] = array('Valor (Factura aduana)','Value (Customs invoice)','申报价值（客户发票）');
  $dictionary[] = array('Nombre de la compañia','Company name','Company name');
  $dictionary[] = array('Cant.','QTY','QTY');
  $dictionary[] = array('Debe ingresar mínimo una imagen del despacho del ítem No.','You must add at least one dispatch image of item No.','项目至少上传一张照片');
  $dictionary[] = array('Debe ingresar mínimo un archivo adjunto en Packing list','You must add at least one file in Packing list','最少上传一份箱单');
  $dictionary[] = array('Debe ingresar la información del serial unicamente cuando son maquinas (Aplicaciones)','You are enter the serial only when the article is a machine (Aplication)','机器货物必须输入编号');
  $dictionary[] = array('Unidades pendientes (Backorder)','Pending quantity (Backorder)','Pending quantity (Backorder)');
  $dictionary[] = array('Agregar item de backorder','Add item of backorder','Add item of backorder');
  $dictionary[] = array('Backorder','Backorder','Backorder');
  $dictionary[] = array('Unidades pendientes','Pending quantity','Pending quantity');
  $dictionary[] = array('El item backorder ya fue agregado en el item','The backorder item has already been added in the item','The backorder item has already been added in the item');
  $dictionary[] = array('El se ha ingresado una cantidad menor a la cantidad establecida en el item','An quantity less than the quantity established has been entered in the item','An quantity less than the quantity established has been entered in the item');
  $dictionary[] = array('la cantidad debe ser mínimo','the minimum quantity established has is 5','the minimum quantity established has is 5');
  $dictionary[] = array('error: Guardando cambios en los items','error: Save item changes','error: Save item changes');
  $dictionary[] = array('Agendamiento','Scheduling','Scheduling');
  $dictionary[] = array('F.Test','D.Test','D.Test');
  $dictionary[] = array('F.Registro','D.Registration','D.Registration');  
  $dictionary[] = array('ESS Heat Press Procesos de Prueba Espec&iacute;ficos de Equipos','ESS Heat Press Machine Testing Specific Processes','ESS Heat Press Machine Testing Specific Processes');
  $dictionary[] = array('Debe ingresar un patrón de búsqueda.','You have input a text to search','You have input a text to search');
  $dictionary[] = array('Debe selecionar un filtro de búsqueda.','You must select a search filter','You must select a search filter');
  $dictionary[] = array('Cliente/País','Customer/Country','Customer/Country');
  $dictionary[] = array('País del cliente','Customer country','Customer country');
  $dictionary[] = array('País del fabricante','Manufacturer country','Manufacturer country');
  $dictionary[] = array('Nombre del Servicio','Service name','Service name');
  $dictionary[] = array('Cons. item','Consecutive item','Consecutive item');
  $dictionary[] = array('Documentación completa','Documentation complete','Documentation complete');
  $dictionary[] = array('Reporte de salidas','Output report','Output report');
  $dictionary[] = array('Antigua Ref.','Old Ref.','Old Ref.');
  $dictionary[] = array('Instalación impresoras','Printer installation','Printer installation');
  $dictionary[] = array('Datos generales','General data','General data');
  $dictionary[] = array('Canal de distribución','Distribution Channel','Distribution Channel');
  $dictionary[] = array('Empresa','Company','Company');
  $dictionary[] = array('Responsable','Responsible party','Responsible party');
  $dictionary[] = array('Responsable de instalación','Responsible for installation','Responsible for installation');
  $dictionary[] = array('Fecha de instalación','Date of installation','Date of installation');
  $dictionary[] = array('Tiempo de garantía (meses)','Warranty time (months)','Warranty time (months)');
  $dictionary[] = array('Datos de instalación','Installation data','Installation data');
  $dictionary[] = array('Modelo de equipo','Equipment model','Equipment model');
  $dictionary[] = array('Serial del equipo','Equipment Serial Number','Equipment Serial Number');
  $dictionary[] = array('Serial printhead board','Printhead Board Serial Number','Printhead Board Serial Number');
  $dictionary[] = array('Serial mainboard','Mainboard Serial Number','Mainboard Serial Number');
  $dictionary[] = array('Software de impresión','Printing software','Printing software');
  $dictionary[] = array('Número de cabezales','Number of heads','Number of heads');
  $dictionary[] = array('Instalación','Installation','Installation');
  $dictionary[] = array('Armado completo de la Impresora','Complete assembly of the Printer','Complete assembly of the Printer');
  $dictionary[] = array('Instalación de softwares','Installation of software','Installation of software');
  $dictionary[] = array('Sistema eléctrico correcto','Correct electrical system','Correct electrical system');
  $dictionary[] = array('Verificación de movimientos de la impresora sin cabezales','Verification of movements of the printer without the heads','Verification of movements of the printer without the heads');
  $dictionary[] = array('Instalación de cabezales e ingreso de tinta','Head and ink input installation','Head and ink input installation');
  $dictionary[] = array('Test completo de los cabezales','Complete test of the heads','Complete test of the heads');
  $dictionary[] = array('Regulación altura de los cabezales','Height adjustment of the heads','Height adjustment of the heads');
  $dictionary[] = array('Calibración del sistema de limpieza','Calibration of the cleaning system','Calibration of the cleaning system');
  $dictionary[] = array('Calibración física y lógica de los cabezales','Physical and logical calibration of the heads','Physical and logical calibration of the heads');
  $dictionary[] = array('Creación de perfiles de color','Creation of color profiles','Creation of color profiles');
  $dictionary[] = array('Capacitación completa en el uso de la impresora','Complete training in the use of the printer','Complete training in the use of the printer');
  $dictionary[] = array('Instrucciones de mantenimiento diario y quincenal','Daily and fortnightly maintenance instructions','Daily and fortnightly maintenance instructions');
  $dictionary[] = array('Creación de carpeta ESS con información necesaria para el cliente','Creation of ESS folder with information necessary for the client','Creation of ESS folder with information necessary for the client');
  $dictionary[] = array('Aprobación del trabajo de la máquina por parte del cliente','Approval of the machine performance by the customer.','Approval of the machine performance by the customer.');
  $dictionary[] = array('Mencionar algún incidente en el proceso de armado, puesta en marcha y capacitación de la máquina','Mention any incident in the machine assembly, start-up and training process','Mention any incident in the machine assembly, start-up and training process');
  $dictionary[] = array('Términos de Garantía','Warranty Terms','Warranty Terms');
  $dictionary[] = array('ESS solo respaldará las partes que lleguen a destino con fallas de origen o que sus lesiones se hayan producido por el uso adecuado y ordenado de la máquina. Además se excluyen de garantía los daños en partes consideradas como consumibles. Las partes consideradas como consumibles son las siguientes','ESS will only support parts that arrive at their destination with original failures or that their injuries have been caused by the proper and orderly use of the machine. In addition, damages to parts considered as consumables are excluded from the guarantee. The parts considered as consumables are the following','ESS will only support parts that arrive at their destination with original failures or that their injuries have been caused by the proper and orderly use of the machine. In addition, damages to parts considered as consumables are excluded from the guarantee. The parts considered as consumables are the following');
  $dictionary[] = array('Cabezales','Heads','Heads');
  $dictionary[] = array('Partes del sistema de tinta : Bombas, capping, wiper, cartuchos','Parts of the ink system: Pumps, capping, wiper, cartridges','Parts of the ink system: Pumps, capping, wiper, cartridges');
  $dictionary[] = array('Resistencias de los calentadores','Heater resistors','Heater resistors');
  $dictionary[] = array('ESS no se hace responsable de la garantía de partes en las siguientes condiciones','ESS is not responsible for the warranty of parts in the following conditions','ESS is not responsible for the warranty of parts in the following conditions');
  $dictionary[] = array('Daños o reparaciones necesarias como consecuencia de fallas en instalaciones eléctricas o malas aplicaciones','Damage or necessary repairs as a result of failures in electrical installations or bad applications','Damage or necessary repairs as a result of failures in electrical installations or bad applications');
  $dictionary[] = array('Daños o reparaciones necesarias como consecuencia de un voltaje de entrada inadecuado','Damage or repair required as a result of improper input voltage','Damage or repair required as a result of improper input voltage');
  $dictionary[] = array('Abuso y alteraciones no autorizadas','Abuse and unauthorized tampering','Abuse and unauthorized tampering');
  $dictionary[] = array('No se admitirá reclamos en piezas removidas sin la autorización del personal de soporte de ESS','No claims will be accepted on parts removed without the authorization of ESS support personnel','No claims will be accepted on parts removed without the authorization of ESS support personnel');
  $dictionary[] = array('Solicitar la Carta de Garantía a su ejecutivo comercial','Request the Letter of Guarantee from your commercial executive','Request the Letter of Guarantee from your commercial executive');
  $dictionary[] = array('Estado de la Instalación','Installation status','Installation status');
  $dictionary[] = array('Anulada','Canceled','Canceled');
  $dictionary[] = array('Finalizada','Finished','Finished');
  $dictionary[] = array('Cancelar','Cancel','Cancel');
  $dictionary[] = array('Por favor ingrese el canal','Please enter distribution channel','Please enter distribution channel');
  $dictionary[] = array('Por favor ingrese la empresa','Please enter company','Please enter company');
  $dictionary[] = array('Por favor ingrese el responsable','Please enter responsible party','Please enter responsible party');
  $dictionary[] = array('Por favor seleccione el país','Please select country','Please select country');
  $dictionary[] = array('Por favor ingrese la ciudad','Please enter city','Please enter city');
  $dictionary[] = array('Por favor ingrese el email','Please enter email','Please enter email');
  $dictionary[] = array('Por favor ingrese el responsable de la instalación','Please enter responsible for installation','Please enter responsible for installation');
  $dictionary[] = array('Por favor ingrese la fecha de instalación','Please enter date of installation','Please enter date of installation');
  $dictionary[] = array('Por favor ingrese el tiempo de garantía','Please enter warranty time','Please enter warranty time');
  $dictionary[] = array('Por favor seleccione el modelo de la maquina','Please select equipment model','Please select equipment model');
  $dictionary[] = array('Por favor ingrese el serial del equipo','Please enter equipment serial number','Please enter equipment serial number');
  $dictionary[] = array('Por favor ingrese el serial del printhead board','Please enter printhead board serial number','Please enter printhead board serial number');
  $dictionary[] = array('Por favor ingrese el serial de la main board','Please enter mainboard serial number','Please enter mainboard serial number');
  $dictionary[] = array('Por favor ingrese el RIP id','Please enter RIP id','Please enter RIP id');
  $dictionary[] = array('Por favor ingrese la versión del RIP','Please enter RIP version','Please enter RIP version');
  $dictionary[] = array('Por favor ingrese el sofware de impresión','Please enter Printing software','Please enter Printing software');
  $dictionary[] = array('Por favor ingrese el número de cabezales','Please enter number of heads','Please enter number of heads');
  $dictionary[] = array('Seleccione el estado de la instalación','Please select Installation status','Please select Installation status');
  $dictionary[] = array('¡Lista de chequeo incompleta!\nPor favor chequee todos los ítems para continuar.\nItem','Incomplete checklist!\nPlease check all items to continue.\nitem','Incomplete checklist!\nPlease check all items to continue.\nitem');
  $dictionary[] = array('Requerido','Required','Required');
  $dictionary[] = array('Su sesión a finalizado, ingrese a la opción del menú de nuevo','Your session has ended, enter the menu option again','Your session has ended, enter the menu option again');
  $dictionary[] = array('Medida de manta','Blanket size','Blanket size');
  $dictionary[] = array('Medida de resistencias','Measurement of resistance','Measurement of resistance');
  $dictionary[] = array('Código Rotating Joint','Rotating Joint Code','Rotating Joint Code');
  $dictionary[] = array('Tamaño de la mesa','Table size','Table size');
  $dictionary[] = array('Número de rodillos','Number of rollers','Number of rollers');
  $dictionary[] = array('Número de resistencias','Number of resistors','Number of resistors');
  $dictionary[] = array('Armado completo de la Calandra','Complete assembly of the Calender','Complete assembly of the Calender');
  $dictionary[] = array('Armado completo de la mesa','Complete assembly of the table','Complete assembly of the table');
  $dictionary[] = array('Curado de la manta','Curing of the blanket','Curing of the blanket');
  $dictionary[] = array('Inspección del sistema mecánico','Inspection of the mechanical system','Inspection of the mechanical system');
  $dictionary[] = array('Inspección del sistema neumático','Inspection of the pneumatic system','Inspection of the pneumatic system');
  $dictionary[] = array('Inspección del sistema de calor','Inspection of the heat system','Inspection of the heat system');
  $dictionary[] = array('Calibración del controlador de temperatura','Calibration of the temperature controller','Calibration of the temperature controller');
  $dictionary[] = array('Inspección física de la máquina','Physical inspection of the machine','Physical inspection of the machine');
  $dictionary[] = array('Capacitación en el uso completo de la máquina (rollo a rollo)','Training in the full use of the machine (roll by roll)','Training in the full use of the machine (roll by roll)');
  $dictionary[] = array('Capacitación en el uso completo de la máquina (pieza a pieza)','Training in the full use of the machine (piece by piece)','Training in the full use of the machine (piece by piece)');
  $dictionary[] = array('Creación de perfil de color en la impresora ESS','Creation of color profile in ESS printer','Creation of color profile in ESS printer');
  $dictionary[] = array('Creación de perfil en impresora de otra marca','Creation of profile in printer of another brand','Creation of profile in printer of another brand');
  $dictionary[] = array("Verificación de la calidad del sublimado en la tela del cliente","Verification of the sublimated quality on the customer's fabric","Verification of the sublimated quality on the customer's fabric");
  $dictionary[] = array('Instrucciones de mantenimiento semanal y mensual','Weekly and monthly maintenance instructions.','Weekly and monthly maintenance instructions.');
  $dictionary[] = array('Manta (Blanket)','Blanket','Blanket');
  $dictionary[] = array('Resistencia (Heating Tube)','Heating Tube','Heating Tube');
  $dictionary[] = array('Aceite conductivo (Conduction Oil). Sólo Calandras usan aceite térmico','Conduction Oil. Only Calenders use thermal oil','Conduction Oil. Only Calenders use thermal oil');
  $dictionary[] = array('UPS : Algunos modelos disponen de ese accesorio','UPS: Some models have this accessory','UPS: Some models have this accessory');
  $dictionary[] = array('Daño en el sistema de tensión como consecuencia de un exceso en la manipulación de la manivela de tensión de la manta','Damage to the tensioning system as a result of excessively handling the blanket tensioning handle','Damage to the tensioning system as a result of excessively handling the blanket tensioning handle');
  $dictionary[] = array('Instalación calandra o plancha','Heat press installation','Heat press installation');
  $dictionary[] = array('ESS solo respaldará las partes que lleguen a destino con fallas de origen o que sus lesiones se hayan producido por el uso adecuado y ordenado de la máquina. Además se excluyen de garantía los daños en partes consideradas como consumibles. Las partes consideradas como consumibles son las siguientes:','ESS will only support parts that arrive at their destination with original failures or whose damage has been caused by the proper and orderly use of the machine. In addition, damages to parts considered as consumables are excluded from the warranty. The parts considered as consumables are the following','ESS will only support parts that arrive at their destination with original failures or whose damage has been caused by the proper and orderly use of the machine. In addition, damages to parts considered as consumables are excluded from the warranty. The parts considered as consumables are the following');
  $dictionary[] = array('Por favor ingrese la medida de la manta','Please enter blanket size','Please enter blanket size');
  $dictionary[] = array('Por favor ingrese la medida de las resistencias','Please enter measurement of resistance','Please enter measurement of resistance');
  $dictionary[] = array('Por favor ingrese el código rotating join','Please enter rotating joint Code','Please enter rotating joint Code');
  $dictionary[] = array('Por favor ingrese el tamaño de la mesa','Please enter table size','Please enter table size');
  $dictionary[] = array('Por favor ingrese el número de rodillos','Please enter number of rollers','Please enter number of rollers');
  $dictionary[] = array('Por favor ingrese el número de resistencias','Please enter number of resistors','Please enter number of resistors');
  $dictionary[] = array('Error de conexión','Connection error','Connection error');
  $dictionary[] = array('CERTIFICADO DE INSTALACION DE EQUIPO DE IMPRESION','IMPRESSION EQUIPMENT INSTALLATION CERTIFICATE','IMPRESSION EQUIPMENT INSTALLATION CERTIFICATE');
  $dictionary[] = array('Si acepto los Términos y Condiciones para el Sistema de Soporte y Garantía de ESS','Yes, I accept the Terms and Conditions for the ESS Support and Warranty System','Yes, I accept the Terms and Conditions for the ESS Support and Warranty System');
  $dictionary[] = array('FIRMA CLIENTE','CLIENT SIGNATURE','CLIENT SIGNATURE');
  $dictionary[] = array('FIRMA TÉCNICO','TECHNICAL SIGNATURE','TECHNICAL SIGNATURE');
  $dictionary[] = array('CERTIFICADO DE INSTALACION DE EQUIPO CALANDRA O PLANCHA','HEAT PRESS EQUIPMENT INSTALLATION CERTIFICATE','HEAT PRESS EQUIPMENT INSTALLATION CERTIFICATE');
  $dictionary[] = array('Nuevo Reporte Instalación','New Installation Report','New Installation Report');
  $dictionary[] = array('Seleccione el tipo de equipo','Select type of equipment','Select type of equipment');
  $dictionary[] = array('Siguiente','Next','Next');
  $dictionary[] = array('Debe seleccionar el tipo de equipo para el reporte','You must select type of equipment for the report','You must select type of equipment for the report');
  $dictionary[] = array('Instalación finalizada no se puede editar','Installation finished cannot be edited','Installation finished cannot be edited');
  $dictionary[] = array('Instalación anulada no se puede editar','Installation anulled cannot be edited','Installation anulled cannot be edited');
  $dictionary[] = array('Confirmación agregar','Confirmation add','Confirmation add');
  $dictionary[] = array('Guardado correctamente','Saved correctly','Saved correctly');
  $dictionary[] = array('Error guardando','Error saving','Error saving');
  $dictionary[] = array('El registro ya fue guardado','The record has already been saved','The record has already been saved');
  $dictionary[] = array('Aceptar','Accept','Accept');
  $dictionary[] = array('Confirmación editar','Confirmation edit','Confirmation edit');
  $dictionary[] = array('Reporte de servicio','Service report','Service report');
  $dictionary[] = array('Hora inicio','Start time','Start time');
  $dictionary[] = array('Hora final','End Time','End Time');
  $dictionary[] = array('Servicio por garantía','Warranty Service','Warranty Service');
  $dictionary[] = array('Si','Yes','Yes');
  $dictionary[] = array('No','Not','Not');
  $dictionary[] = array('Motivo del servicio','Reason for the service','Reason for the service');
  $dictionary[] = array('Mencionar la razón del servicio solicitado por el cliente','Mention the reason for the service requested by the client','Mention the reason for the service requested by the client');
  $dictionary[] = array('Diagnóstico','Diagnosis','Diagnosis');
  $dictionary[] = array('Explicar el procedimiento realizado para detectar la falla','Explain the procedure performed to detect the failure','Explain the procedure performed to detect the failure');
  $dictionary[] = array('Procedimiento correctivo','Corrective procedure','Corrective procedure');
  $dictionary[] = array('Mencionar el proceso realizado o repuesto necesario para solucionar la falla','Mention the process carried out or spare part necessary to solve the fault','Mention the process carried out or spare part necessary to solve the fault');
  $dictionary[] = array('Recomendaciones','Recommendations','Recommendations');
  $dictionary[] = array('Estado del servicio','Service status','Service status');
  $dictionary[] = array('Por favor ingrese la fecha del servicio','Please enter date of service','Please enter date of service');
  $dictionary[] = array('Por favor seleccione si el servicio es por garantía','Please select if the service is by warranty','Please select if the service is by warranty');
  $dictionary[] = array('Por favor ingrese la hora de inicio del servicio','Please enter service start time','Please enter service start time');
  $dictionary[] = array('Por favor ingrese los minutos de la hora de inicio del servicio','Enter the minutes for the service start time','Enter the minutes for the service start time');
  $dictionary[] = array('Por favor ingrese la hora de finalización del servicio','Please enter service end time','Please enter service end time');
  $dictionary[] = array('Por favor ingrese los minutos de la hora de finalización del servicio','Enter the minutes for the service end time','Enter the minutes for the service end time');  
  $dictionary[] = array('Por favor ingrese la razón del servicio solicitado por el cliente','Please enter the reason for the service requested by the customer','Please enter the reason for the service requested by the customer');
  $dictionary[] = array('Por favor ingrese el procedimiento realizado para detectar la falla','Please enter the procedure performed to detect the failure','Please enter the procedure performed to detect the failure');
  $dictionary[] = array('Por favor ingrese proceso realizado o repuesto necesario para solucionar la falla','Please enter the process carried out or spare part necessary to solve the fault','Please enter the process carried out or spare part necessary to solve the fault');
  $dictionary[] = array('Seleccione el estado del servicio','Please select service status','Please select service status');
  $dictionary[] = array('Responsable del servicio','Responsible for the service','Responsible for the service');
  $dictionary[] = array('Por favor ingrese el responsable del servicio','Please enter the responsible for the service','Please enter the responsible for the service');
  $dictionary[] = array('Datos del servicio','Service data','Service data');
  $dictionary[] = array('Instalación laminadora o cortadora','Laminator or cutter installation','Laminator or cutter installation');
  $dictionary[] = array('Verificación de movimientos de la máquina.','Verification of machine movements','Verification of machine movements');
  $dictionary[] = array('Instalación de accesorios y herramientas','Installation of accessories and tools','Installation of accessories and tools');
  $dictionary[] = array('Calibración de todas las herramientas.','Calibration of all tools','Calibration of all tools');
  $dictionary[] = array('Instalación de compresor y sistema de aire.','Installation of compressor and air system','Installation of compressor and air system');
  $dictionary[] = array('ESS solo respaldará las partes que lleguen a destino con fallas de origen o que sus lesiones se hayan producido por el uso adecuado y ordenado de la máquina. Además se excluyen de garantía los daños en partes consideradas como consumibles','ESS will only support parts that arrive at their destination with original failures or that their injuries have been caused by the proper and orderly use of the machine. In addition, damages to parts considered as consumables are excluded from the guarantee','ESS will only support parts that arrive at their destination with original failures or that their injuries have been caused by the proper and orderly use of the machine. In addition, damages to parts considered as consumables are excluded from the guarantee');
  $dictionary[] = array('CERTIFICADO DE INSTALACIÓN DE EQUIPO DE LAMINACIÓN O CORTE','LAMINATOR OR CUTTER INSTALLATION CERTIFICATE','LAMINATOR OR CUTTER INSTALLATION CERTIFICATE');
  $dictionary[] = array('Armado completo de la máquina','Complete assembly of the machine','Complete assembly of the machine');
  $dictionary[] = array('Capacitación completa en el uso de la máquina','Complete training in the use of the machine','Complete training in the use of the machine');
  $dictionary[] = array('Calculador de crédito para máquinas','Credit calculator for machines','Credit calculator for machines');
  $dictionary[] = array('Crédito','Credit','Credit');
  $dictionary[] = array('Datos del cliente','Customer data','Customer data');
  $dictionary[] = array('Nombre','Name','Name');
  $dictionary[] = array('Teléfono','Phone','Phone');
  $dictionary[] = array('Datos de la aplicación','Application data','Application data');
  $dictionary[] = array('Fecha venta','Sale date','Sale date');
  $dictionary[] = array('Modelo','Model','Model');
  $dictionary[] = array('Valor','Value','Value');
  $dictionary[] = array('Datos de la financiación','Financing data','Financing data');
  $dictionary[] = array('% Pago inicial','Downpayment %','Downpayment %');
  $dictionary[] = array('Pago Inicial','Downpayment value ','Downpayment value ');
  $dictionary[] = array('Val. a financiar','Val. to finance','Val. to finance');
  $dictionary[] = array('Valor a financiar','Value to finance','Value to finance');
  $dictionary[] = array('Plazo en meses','Term in month','Term in month');
  $dictionary[] = array('% Interés anual','Interest rate per year','Interest rate per year');
  $dictionary[] = array('% Interés mensual','Interest rate per month','Interest rate per month');
  $dictionary[] = array('Valor Final','Final value','Final value');
  $dictionary[] = array('Costo de la inversión','Investment cost','Investment cost');
  $dictionary[] = array('C.I. Interés anual','IC Interest rate per year','IC Interest rate per year');
  $dictionary[] = array('C.I. Interés mensual','IC Interest rate per month','IC Interest rate per month');
  $dictionary[] = array('Linea crédito Intereses','Credit line interest','Credit line interest');
  $dictionary[] = array('Meses recuperación','Months of recovery','Months of recovery');
  $dictionary[] = array('Meses utilidad','Months of usefulness','Months of usefulness');
  $dictionary[] = array('Costo del crédito','Credit cost','Credit cost');
  $dictionary[] = array('Pag. Total cliente con Int','Total payment of the client with interests','Total payment of the client with interests');
  $dictionary[] = array('Impuestos','Tax','Tax');
  $dictionary[] = array('Porcentaje','Percentage','Percentage');
  $dictionary[] = array('Costos','Cost','Cost');
  $dictionary[] = array('Costo máquina','Machine cost','Machine cost');
  $dictionary[] = array('Otros costos','Other cost','Other cost');
  $dictionary[] = array('Costo Maq. Total','Total cost of the machine','Total cost of the machine');
  $dictionary[] = array('Datos capital','Capital data','Capital data');
  $dictionary[] = array('Cap. Costo a financiar','Capital cost to finance','Capital cost to finance');
  $dictionary[] = array('Cap. Utilidad financiar','Capital usefulness to finance','Capital usefulness to finance');
  $dictionary[] = array('Cuota','Payment','Payment');
  $dictionary[] = array('Cuota mensual','Monthly payment','Monthly payment');
  $dictionary[] = array('Cuota anual','Annual payment','Annual payment');
  $dictionary[] = array('Total pagado','Total payment','Total payment');
  $dictionary[] = array('Otros','Other','Other');
  $dictionary[] = array('C.I. con el banco','IC with the bank','IC with the bank');
  $dictionary[] = array('Total financiado','Total financed','Total financed');
  $dictionary[] = array('Total rec. del cliente menos cost. de int.','Total received from client less interest cost','Total received from client less interest cost');
  $dictionary[] = array('Otro','Other','Other');
  $dictionary[] = array('Error de carga del formulario','Form loading error','Form loading error');
  $dictionary[] = array('Se generaron los siguientes errores','The following errors were generated','The following errors were generated');
  $dictionary[] = array('Debe ingresar el nombre o razón social','You must enter the name or business name','You must enter the name or business name');
  $dictionary[] = array('Debe ingresar el contacto','You must enter the contact','You must enter the contact');
  $dictionary[] = array('Debe seleccionar el tipo de documento','You must select the type of document','You must select the type of document');
  $dictionary[] = array('Debe ingresar el numero de documento','You must enter the document number','You must enter the document number');
  $dictionary[] = array('Desea continuar sin ingresar el dígito de verificación','You want to continue without entering the check digit','You want to continue without entering the check digit');
  $dictionary[] = array('Debe ingresar el teléfono','You must enter the phone','You must enter the phone');
  $dictionary[] = array('Debe ingresar la actividad','You must enter the activity','You must enter the activity');
  $dictionary[] = array('El cliente ya está registrado','The client is already registered','The client is already registered');
  $dictionary[] = array('Error guardando el nuevo cliente','Error saving new client','Error saving new client');
  $dictionary[] = array('Debe seleccionar un cliente','You must select a customer','You must select a customer');
  $dictionary[] = array('Debe ingresar la fecha de venta de la máquina','You must enter the machine sale date','You must enter the machine sale date');
  $dictionary[] = array('Debe seleccionar un modelo','You must select a model','You must select a model');
  $dictionary[] = array('Debe ingresar el valor de la máquina','You must enter the machine value','You must enter the machine value');
  $dictionary[] = array('Debe ingresar el porcentaje de pago inicial','You must enter the initial payment percentage','You must enter the initial payment percentage');
  $dictionary[] = array('Debe ingresar el plazo en meses a financiar','You must enter the loan term in months to finance','You must enter the loan term in months to finance');
  $dictionary[] = array('Debe ingresar el porcentaje de impuesto de la máquna','You must enter the tax percentage of the machine','You must enter the tax percentage of the machine');
  $dictionary[] = array('No hay registros','No records','No records');
  $dictionary[] = array('Usted ingreso un carácter invalido en','You entered an invalid character in','You entered an invalid character in');
  $dictionary[] = array('por favor revise','please review','please review');
  $dictionary[] = array('tabulaciones o saltos de linea no son permitidos','tabs or line breaks are not allowed','tabs or line breaks are not allowed');
  $dictionary[] = array('espacios no son permitidos','spaces are not allowed','spaces are not allowed');
  $dictionary[] = array('solo números son permitidos','only numbers are allowed','only numbers are allowed');
  $dictionary[] = array('solo números son permitidos con el símbolo punto como decimal','only numbers are allowed with the dot symbol as decimal','only numbers are allowed with the dot symbol as decimal');
  $dictionary[] = array('solo letras son permitidas','only letters are allowed','only letters are allowed');
  $dictionary[] = array('solo letras y números son permitidos','only letters and numbers are allowed','only letters and numbers are allowed');
  $dictionary[] = array('El valor ingresado en','The value entered in','The value entered in');
  $dictionary[] = array('debe ser mayor a cero','must be greater than zero','must be greater than zero');
  $dictionary[] = array('debe ser positivo','must be positive','must be positive');
  $dictionary[] = array('Nuevo cliente','New customer','New customer');
  $dictionary[] = array('Borrar selec.','Delete Select.','Delete Select.');
  $dictionary[] = array('Borra los datos del cliente seleccionado','Delete the data of the selected customer','Delete the data of the selected customer');
  $dictionary[] = array('Ingresar cliente','Add customer','Add customer');
  $dictionary[] = array('Nombre o Razón Social','Name or business name','Name or business name');
  $dictionary[] = array('Identificación','Identification','Identification');
  $dictionary[] = array('Página web','Web page','Web page');
  $dictionary[] = array('Datos Comerciales','Commercial data','Commercial data');
  $dictionary[] = array('Actividad','Activity','Activity');
  $dictionary[] = array('Cerrar','Close','Close');
  $dictionary[] = array('Error en cargue de datos','Data loading error','Data loading error');
  $dictionary[] = array('La máquina no tiene costos asignados','The machine has no assigned costs','The machine has no assigned costs');
  $dictionary[] = array('Seleccione','Select','Select');
  $dictionary[] = array('Error al guardar','Save failed','Save failed');
  $dictionary[] = array('El valor de plazo en meses debe ser máximo','The term value in months must be maximum','The term value in months must be maximum');
  $dictionary[] = array('Debe seleccionar las fechas para realizar la búsqueda','You must select the dates to perform the search','You must select the dates to perform the search');
  $dictionary[] = array('Filtrar por','Filter by','Filter by');
  $dictionary[] = array('Email','Email','Email');
  $dictionary[] = array('Debe ingresar un email','You must enter a email','You must enter a email');
  $dictionary[] = array('Usted ingreso un email incorrecto','You entered an incorrect email','You entered an incorrect email');
  $dictionary[] = array('Guardado','Saved','Saved');
  $dictionary[] = array('Nombre del cliente','Customer name','Customer name');
  $dictionary[] = array('Valor de máquina','Machine value','Machine value');
  $dictionary[] = array('Porcentaje interés anual','Interest rate per month','Interest rate per month');
  $dictionary[] = array('Usuario que Ingreso','User entry','User entry');
  $dictionary[] = array('Estado del crédito','Credit status','Credit status');
  $dictionary[] = array('Pago inicial','Downpayment','Downpayment');
  $dictionary[] = array('Pagado','Paid','Paid');
  $dictionary[] = array('Mora en el pago','Late payment','Late payment');
  $dictionary[] = array('F. Venta','D. Sale','D. Sale');
  $dictionary[] = array('Previo','Previous','Previous');
  $dictionary[] = array('Ir al inicio','Go to first','Go to first');
  $dictionary[] = array('Avanzar','Next','Next');
  $dictionary[] = array('Ir al final','Go to last','Go to last');
  $dictionary[] = array('Ver detalles','View details','View details');
  $dictionary[] = array('La cuota mensual calculada no coincide con la registrada en la base de datos','The monthly fee calculated does not match the one registered in the database','The monthly fee calculated does not match the one registered in the database');
  $dictionary[] = array('A el ','Between ','Between ');
  $dictionary[] = array('No se realizaron cambios en los datos','No changes were made to the data','No changes were made to the data');
  $dictionary[] = array('NIT/CC','Identification','Identification');
  $dictionary[] = array('Mostrar todos los canales','Show all channels','Show all channels');
  $dictionary[] = array('Mostrar referencias de todos los canales','Show references from all channels','Show references from all channels');
  $dictionary[] = array('No mostrar todos los canales',"Don't show all channels","Don't show all channels");
  $dictionary[] = array('No mostrar referencias de todos los canales',"Don't show references from all channels","Don't show references from all channels");
  $dictionary[] = array('Referencia detallada','Detailed reference','Detailed reference');
  $dictionary[] = array('Error al realizar el cálculo del crédito','Error in credit calculation','Error in credit calculation');
  $dictionary[] = array('Recibo de caja para máquinas','Cash receipt for machines','Cash receipt for machines');
  $dictionary[] = array('Recibo de caja','Cash receipt','Cash receipt');
  $dictionary[] = array('Datos del pago','Details of payment','Details of payment');
  $dictionary[] = array('Pagado con','Paid by','Paid by');  
  $dictionary[] = array('Efectivo','Cash','Cash');
  $dictionary[] = array('Tarjeta','Card','Card');
  $dictionary[] = array('Consignación','Consignment','Consignment');
  $dictionary[] = array('Transferencia bancaria','Bank transfer','Bank transfer');
  $dictionary[] = array('Recibido de','Received from','Received from');
  $dictionary[] = array('No. documento','Document no.','Document no.');
  $dictionary[] = array('La suma de','Value','Value');
  $dictionary[] = array('Debe ingresar una fecha de pago','You must enter the payment date','You must enter the payment date');
  $dictionary[] = array('Debe seleccionar un tipo de pago','You must select a payment type','You must select a payment type');
  $dictionary[] = array('Debe ingresar el numero de documento del pago','You must enter the document number of the payment','You must enter the document number of the payment');
  $dictionary[] = array('Debe ingresar el valor del pago','You must enter the payment value','You must enter the payment value');
  $dictionary[] = array('Debe ingresar el nombre de quien realizo el pago','You must enter the name of whoever made the payment','You must enter the name of whoever made the payment');
  $dictionary[] = array('Debe ingresar el tipo de documento de quien realizo el pago','You must enter the type document of whoever made the payment','You must enter the type document of whoever made the payment');
  $dictionary[] = array('Debe ingresar el numero del documento de quien realizo el pago','You must enter the document number of whoever made the payment','You must enter the document number of whoever made the payment');
  $dictionary[] = array('Recibido de DV','Received from DV','Received from DV');
  $dictionary[] = array('Pagos realizados','Payments made','Payments made');
  $dictionary[] = array('Registro de actividades','Activity log','Activity log');
  $dictionary[] = array('Motivo','Reason','Reason');
  $dictionary[] = array('Búsqueda','Search','Search');
  $dictionary[] = array('Máquina','Machine','Machine');
  $dictionary[] = array('Debe ingresar el motivo de la actividad','You must enter the reason for the activity','You must enter the reason for the activity');
  $dictionary[] = array('Debe ingresar la descripción de la actividad','You must enter the description of the activity','You must enter the description of the activity');
  $dictionary[] = array('Debe ingresar la fecha de la actividad','You must enter the date of the activity','You must enter the date of the activity');
  $dictionary[] = array('Llamada','Call','Call');
  $dictionary[] = array('Reunión por mora en el pago','Appointment for late payment','Appointment for late payment');
  $dictionary[] = array('Cliente se compromete a pagar','Client agrees to pay','Client agrees to pay');
  $dictionary[] = array('Revisión de pagos realizados','Review of payments made','Review of payments made');
  $dictionary[] = array('Certificado de paz y salvo de deudas','Certificate of good standing debts','Certificate of good standing debts');
  $dictionary[] = array('es mayor que','is greater than','is greater than');
  $dictionary[] = array('es menor que','is less than','is less than');
  $dictionary[] = array('La máquina no tiene valor asignado','The machine has no assigned value','The machine has no assigned value');
  $dictionary[] = array('Fecha autorización','Authorization date','Authorization date');
  $dictionary[] = array('Vendedor','Seller','Seller');
  $dictionary[] = array('Autorizaciones para crédito de máquinas','Authorizations for credit machines','Authorizations for credit machines');
  $dictionary[] = array('Descuento','Discount','Discount');
  $dictionary[] = array('Fecha de vigencia','Validity date','Validity date');
  $dictionary[] = array('Seleccionar empresa','Select company','Select company');
  $dictionary[] = array('Agendamiento','Scheduling','Scheduling');
  $dictionary[] = array('Fotos de la instalación','Installation photos','Installation photos');
  $dictionary[] = array('Seleccione las fotos de la instalación','Select the photos of the installation','Select the photos of the installation');
  $dictionary[] = array('Error al guardar imagen','Error saving image','Error saving image');
  $dictionary[] = array('Debe seleccionar un agendamiento para agregar una nueva instalación','You must select a schedule to add a new installation','You must select a schedule to add a new installation');
  $dictionary[] = array('Error revisando fotos agregadas','Error checking added photos','Error checking added photos');
  $dictionary[] = array('Usted debe cargar al menos una imagen','You must upload at least one image','You must upload at least one image');
  $dictionary[] = array('Usted debe cargar al menos una imagen del serial','You must load at least one image of the serial','You must load at least one image of the serial');
  $dictionary[] = array('Usted debe cargar al menos una imagen de la instalación','You must upload at least one installation image','You must upload at least one installation image');
  $dictionary[] = array('Desea quitar la imagen','You want to remove the image','You want to remove the image');
  $dictionary[] = array('Firma','Signature','Signature');
  $dictionary[] = array('Firma del cliente',"Client's signature","Client's signature");
  $dictionary[] = array('Error guardando firma del cliente','Error saving client signature','Error saving client signature');
  $dictionary[] = array('Borrar','Erase','Erase');
  $dictionary[] = array('Se debe ingresar la firma del cliente',"The customer's signature must be entered","The customer's signature must be entered");
  $dictionary[] = array('Certificado de instalación','Installation certificate','Installation certificate');
  $dictionary[] = array('Seleccione el certificado de Instalación de la máquina','Select the machine Installation certificate','Select the machine Installation certificate');
  $dictionary[] = array('No es una máquina de ESS','Not an ESS machine','Not an ESS machine');
  $dictionary[] = array('Fecha Inicio','Start date','Start date');
  $dictionary[] = array('Fecha finalización','End date','End date');
  $dictionary[] = array('Tipo de servicio','Type of service','Type of service');
  $dictionary[] = array('Seleccione el tipo de servicio','Select the type of service','Select the type of service');
  $dictionary[] = array('Debe seleccionar un certificado de Instalación de la máquina','You must select a certificate of Machine Installation','You must select a certificate of Machine Installation');
  $dictionary[] = array('Mostrar menú','Show menu','Show menu');
  $dictionary[] = array('Quitar menú','Remove menu','Remove menu');
  $dictionary[] = array('Debe ingresar un valor en','You must enter a value in','You must enter a value in');
  $dictionary[] = array('Datos de la autorización','Authorization data ','Authorization data ');
  $dictionary[] = array('Autorización','Authorization','Authorization');
  $dictionary[] = array('Porcentaje de descuento','Discount rate','Discount rate');
  $dictionary[] = array('Val. con desc.','Discounted value','Discounted value');
  $dictionary[] = array('Debe ingresar una fecha de vigencia','You must enter an effective date','You must enter an effective date');
  $dictionary[] = array('Debe ingresar el porcentaje de descuento','You must enter the discount percentage','You must enter the discount percentage');
  $dictionary[] = array('Debe seleccionar un vendedor','You must select a seller','You must select a seller');
  $dictionary[] = array('Estado de la autorización para crédito de máquinas','Authorization status for machine credit ','Authorization status for machine credit ');
  $dictionary[] = array('Seleccionar autorización','Select authorization ','Select authorization ');
  $dictionary[] = array('Por favor ingrese el texto de búsqueda','Please input text to search','Please input text to search');
  $dictionary[] = array('Código de Anillo electrónico','Slip ring','Slip ring');
  $dictionary[] = array('Periodo','Period','Period');
  $dictionary[] = array('Debe seleccionar un periodo','You must select a period','You must select a period');
  $dictionary[] = array('Nota: El valor de la máquina incluye impuestos','Note: The value of the machine includes taxes','Note: The value of the machine includes taxes');
  $dictionary[] = array('Datos de la máquina','Machine data','Machine data');
  $dictionary[] = array('Se adjunta el certificado de instalación del servicio','The service installation certificate is attached ','The service installation certificate is attached ');
  $dictionary[] = array('Cordial saludo','Dear','Dear');
  $dictionary[] = array('Imprimir','Print','Print');
  $dictionary[] = array('Enviar correo','Send mail','Send mail');
  $dictionary[] = array('Correo enviado correctamente','Mail sent successfully ','Mail sent successfully ');
  $dictionary[] = array('Error al enviar correo','Error sending mail ','Error sending mail ');
  $dictionary[] = array('Página','Page','Page');
  $dictionary[] = array('de','of','of');
  $dictionary[] = array('Se adjunta el reporte del servicio','The service report is attached','The service report is attached');
  $dictionary[] = array('Se adjunta el reporte del servicio por favor responder al correo con la conformidad del servicio y de lo descrito en el reporte','The service report is attached, please respond to the email with the agreement of the service and what is described in the report','The service report is attached, please respond to the email with the agreement of the service and what is described in the report');
  $dictionary[] = array('Gracias','Thanks','Thanks');
  $dictionary[] = array('Dpto Técnico','Technical Department','Technical Department');
  $dictionary[] = array('Correlativo','Correlative','Correlative');
  $dictionary[] = array('No. Correlativo','No. Correlative','No. Correlative');
  $dictionary[] = array('Se adjunta el reporte de la Instalación, por favor responder al correo con la conformidad del mismo y de lo descrito en el reporte','The Installation report is attached, please respond to the email with the agreement of the same and that described in the report','The Installation report is attached, please respond to the email with the agreement of the same and that described in the report');
  $dictionary[] = array('El cliente acepta que recibió la maquina instalada a satisfacción y que entendió el paso a paso de la capacitación','The client accepts that he received the machine installed to satisfaction and that he understood the step by step of the training','The client accepts that he received the machine installed to satisfaction and that he understood the step by step of the training');
  $dictionary[] = array('Se adjunta el reporte del servicio por favor responder al correo con la conformidad del mismo y de lo descrito en el reporte','The service report is attached, please respond to the email with the agreement of the same and that described in the report','The service report is attached, please respond to the email with the agreement of the same and that described in the report');
  $dictionary[] = array('El Cliente acepta satisfactoriamente el diagnostico realizado al equipo, asi como los correctivos realizados durante el servicio','The customer accepts the diagnosis made, as well as the corrections carried out during the service','The customer accepts the diagnosis made, as well as the corrections carried out during the service');
  $dictionary[] = array('El cliente acepta satisfactoriamente la instalación del equipo así como la capacitación detallada para la operación del mismo','The customer accepts the installation of the equipment, as well as the detailed training for the operation of the equipment','The customer accepts the installation of the equipment, as well as the detailed training for the operation of the equipment');
  $dictionary[] = array('El cliente revisa y acepta el diagnostico o el servicio prestado a satisfacción','The client satisfactorily accepts the installation of the equipment as well as the detailed training for its operation','The client satisfactorily accepts the installation of the equipment as well as the detailed training for its operation');
  $dictionary[] = array('Copiar reporte','Copy report','Copy report');
  $dictionary[] = array('Se realizara un nuevo registro con los datos del reporte','A new record will be made with the report data','A new record will be made with the report data');
  $dictionary[] = array('Desea enviar un correo del reporte al cliente?, el reporte sera guardado','You want to send an email of the report to the client?, the report will be saved','You want to send an email of the report to the client?, the report will be saved');
  $dictionary[] = array('Máquinas','Machines','Machines');
  $dictionary[] = array('Código','Code','Code');
  $dictionary[] = array('Tipo','Type','Type');
  $dictionary[] = array('Costo','Cost','Cost');
  $dictionary[] = array('Precio de venta','Sale price','Sale price');
  $dictionary[] = array('Iva','Vat','Vat');
  $dictionary[] = array('Debe ingresar el código','You must enter the code','You must enter the code');
  $dictionary[] = array('Debe ingresar el costo de la máquina','You must enter the cost of the machine','You must enter the cost of the machine');
  $dictionary[] = array('Cortadora','Cutting','Cutting');
  $dictionary[] = array('Calentadora','Heating','Heating');
  $dictionary[] = array('Laminadora','Laminator','Laminator');
  $dictionary[] = array('Prensa','Press','Press');
  $dictionary[] = array('Impresión','Print','Print');
  $dictionary[] = array('Debe ingresar el precio de venta para la máquina','You must enter the selling price for the machine','You must enter the selling price for the machine');
  $dictionary[] = array('Debe ingresar el iva de la máquina','You must enter the VAT of the machine','You must enter the VAT of the machine');
  $dictionary[] = array('Nombre del canal','Channel name','Channel name');
  $dictionary[] = array('Nota: El precio de venta de la máquina incluye impuestos','Note: The sale price of the machine includes taxes','Note: The sale price of the machine includes taxes');
  $dictionary[] = array('El código ya se encuentra registrado','The code is already registered','The code is already registered');
  $dictionary[] = array('Debe seleccionar al menos un canal, si seleccionar la opción seleccione','You must select at least one channel, if you select the option select','You must select at least one channel, if you select the option select');
  $dictionary[] = array('Nombre de responsable equipo cliente','Responsible party','Responsible party');
  $dictionary[] = array('Correo del cliente','Customer email','Customer email');
  $dictionary[] = array('Nombre de firmante del cliente','Client signer name','Client signer name');
  $dictionary[] = array('Debe ingresar el nombre del firmante','You must enter the name of the signer','You must enter the name of the signer');
  $dictionary[] = array('El cliente no quiso firmar','The client did not want to sign','The client did not want to sign');
  $dictionary[] = array('Motivo por el cual el cliente no quiso firmar','Reason why the client did not want to sign','Reason why the client did not want to sign');
  $dictionary[] = array('Usted selecciono el cliente no quiso firmar','You selected the client did not want to sign','You selected the client did not want to sign');
  $dictionary[] = array('Hay una firma registrada','There is a registered signature','There is a registered signature');
  $dictionary[] = array('Debe ingresar el motivo por el cual el cliente no quiso firmar','You must enter the reason why the client did not want to sign','You must enter the reason why the client did not want to sign');
  $dictionary[] = array('Debe ingresar la fecha de inicio','You must enter the start date','You must enter the start date');
  $dictionary[] = array('Debe ingresar la fecha de finalización','You must enter the end date','You must enter the end date');
  $dictionary[] = array('Por favor ingrese el nombre de responsable equipo cliente','Please enter responsible party','Please enter responsible party');
  $dictionary[] = array('Por favor ingrese el correo del cliente',"Please enter the customer's email","Please enter the customer's email");
  $dictionary[] = array('Error ajustando la firma','Error adjusting the signature','Error adjusting the signature');
  $dictionary[] = array('Desea rotar la firma','You want to rotate the signature','You want to rotate the signature');
  $dictionary[] = array('Rotar a la izquierda','Rotate left','Rotate left');
  $dictionary[] = array('Rotar a la derecha','Rotate right','Rotate right');
  $dictionary[] = array('Restablecer','Restore','Restore');
  $dictionary[] = array('Enviamos copia del reporte de tu servicio, por favor confirma que lo recibiste','We sent a copy of your service report, please confirm that you received it','We sent a copy of your service report, please confirm that you received it');
  $dictionary[] = array('Se borrara la firma','The signature will be erased','The signature will be erased');
  $dictionary[] = array('Debe seleccionar un agendamiento para agregar un nuevo reporte de servicio','You must select a schedule to add a new service report','You must select a schedule to add a new service report');
  $dictionary[] = array('Fuera de Garant&iacute;a','Out of warranty','Out of warranty');
  $dictionary[] = array('Garantia','Warranty','Warranty');
  $dictionary[] = array('Cambio de Tinta','Ink change ','Ink change ');
  $dictionary[] = array('Mantenimiento General','General Maintenance','General Maintenance');
  $dictionary[] = array('Servicio técnico','Technical service','Technical service');
  $dictionary[] = array('Facturar','Bill','Bill');
  $dictionary[] = array('Facturado','Invoiced','Invoiced');
  $dictionary[] = array('Perfilación (Obsequio)','Profiling (Gift)','Profiling (Gift)');
  $dictionary[] = array('Perfilación (Facturado)','Profiling (Bill)','Profiling (Bill)');
  $dictionary[] = array('Online','Online','Online');
  $dictionary[] = array('Servicio','Service','Service');
  $dictionary[] = array('Venta de partes','Sale of parts','Sale of parts');
  $dictionary[] = array('Diagnostico','Diagnosis','Diagnosis');
  $dictionary[] = array('Solicitud de partes','Parts request','Parts request');
  $dictionary[] = array('Correo','Email','Email');
  $dictionary[] = array('Fecha solicitud','Request date','Request date');
  $dictionary[] = array('Ventas Rep.','Sales Rep.','Sales Rep.');
  $dictionary[] = array('Enviar Vía','Ship Via','Ship Via');
  $dictionary[] = array('Aire','Air','Air');
  $dictionary[] = array('Mar','Sea','Sea');
  $dictionary[] = array('Descripción pedido','Order description','Order description');
  $dictionary[] = array('Código del producto','Product code','Product code');
  $dictionary[] = array('Venta','Sale','Sale');
  $dictionary[] = array('Nombre de parte','Part name','Part name');
  $dictionary[] = array('Imagen de referencia','Reference image','Reference image');
  $dictionary[] = array('Total articulos solicitados','Total items requested','Total items requested');
  $dictionary[] = array('Desea quitar el ítem','You want to remove the item','You want to remove the item');
  $dictionary[] = array('Cantidad ítem','Item quantity','Item quantity');
  $dictionary[] = array('Debe ingresar la empresa','You must enter the company','You must enter the company');
  $dictionary[] = array('Debe ingresar la fecha de solicitud','You must enter the date','You must enter the date');
  $dictionary[] = array('Debe seleccionar el país','You must select the country','You must select the country');
  $dictionary[] = array('Debe ingresar el vendedor','You must enter the sales rep.','You must enter the sales rep.');
  $dictionary[] = array('Debe ingresar la dirección','You must enter the address','You must enter the address');
  $dictionary[] = array('Debe seleccionar el tipo de envío','You must select the ship via','You must select the ship via');
  $dictionary[] = array('Debe ingresar el correo','You must enter the email','You must enter the email');
  $dictionary[] = array('Debe seleccionar los términos','You must select the terms','You must select the terms');
  $dictionary[] = array('Usted ingreso un correo incorrecto','You entered the invalid email','You entered the invalid email');
  $dictionary[] = array('El ítem','Item','Item');
  $dictionary[] = array('no tiene imágenes, desea continuar','you have no images, you want to continue','you have no images, you want to continue');
  $dictionary[] = array('Debe ingresar el motivo de compra del ítem','You must enter the reason for the purchase of the item','You must enter the reason for the purchase of the item');
  $dictionary[] = array('Debe ingresar el','You must enter the','You must enter the');
  $dictionary[] = array('Solicitud','Request','Request');
  $dictionary[] = array('Debe ingresar la','You must enter the','You must enter the');
  $dictionary[] = array('Descripción ítem','Item description','Item description');
  $dictionary[] = array('Debe ingresar ítems a la solicitud de partes','You must enter items to the parts request','You must enter items to the parts request');
  $dictionary[] = array('Seleccionar código','Select code','Select code');
  $dictionary[] = array('Estado de solicitud','Request status','Request status');
  $dictionary[] = array('Inventario exportar','inventory export','inventory export');
  $dictionary[] = array('Por favor ingrese la fecha del certificado','Please enter the date of the certificate','Please enter the date of the certificate');
  $dictionary[] = array('Por favor ingrese el nombre de la capacitación','Please enter the name of the training','Please enter the name of the training');
  $dictionary[] = array('Debe ingresar la ciudad de documento','You must enter the document city','You must enter the document city');
  $dictionary[] = array('Por favor ingrese la ciudad del certificado','Please enter the city of the certificate','Please enter the city of the certificate');
  $dictionary[] = array('Por favor ingrese la intensidad del certificado','Please enter the intensity hours of the certificate','Please enter the intensity hours of the certificate');
  $dictionary[] = array('Tema','Topic','Topic');
  $dictionary[] = array('Horas de intensidad','Hours of intensity','Hours of intensity');
  $dictionary[] = array('Modificar','Modify','Modify');
  $dictionary[] = array('Por favor ingrese el modelo de la maquina','Please enter the machine model','Please enter the machine model');
  $dictionary[] = array('Referencia igual','Equal reference','Equal reference');
  $dictionary[] = array('El ítem de la solicitud ya fue agregado en el ítem','The request item has already been added to the item','The request item has already been added to the item');
  $dictionary[] = array('Consignación de partes','Consignment of parts','Consignment of parts');
  $dictionary[] = array('No se pudo guardar la imagen','Image could not be saved','Image could not be saved');
  $dictionary[] = array('Debe seleccionar una bodega','You must select a cellar','You must select a cellar');
  $dictionary[] = array('Encargado','Manager','Manager');
  $dictionary[] = array('Por favor ingrese el texto a buscar','Please input text to search','Please input text to search');
  $dictionary[] = array('Ítem sin unidades','Item without units','Item without units');
  $dictionary[] = array('Ingreso cantidades no disponibles','Income amounts not available','Income amounts not available');
  $dictionary[] = array('Ingresado','Input','Input');
  $dictionary[] = array('Características','Characteristics','Characteristics');
  $dictionary[] = array('Características ítem','Item characteristics','Item characteristics');
  $dictionary[] = array('no tiene características, desea continuar','has no characteristics, you want to continue','has no characteristics, you want to continue');
  $dictionary[] = array('Nueva','New','New');
  $dictionary[] = array('Actualización','Update','Update');
  $dictionary[] = array('DESCRIPCIÓN DEL PEDIDO','ORDER DESCRIPTION','ORDER DESCRIPTION');
  $dictionary[] = array('Devolución de partes','Return of parts','Return of parts');
  $dictionary[] = array('Devolución','Return','Return');
  $dictionary[] = array('Debe ingresar la fecha de devolución','You must enter the return date','You must enter the return date');
  $dictionary[] = array('Debe ingresar ítems a la devolución de partes','You must enter items to return parts','You must enter items to return parts');
  $dictionary[] = array('Debe ingresar la fecha de consignación','You must enter the consignment date','You must enter the consignment date');
  $dictionary[] = array('Debe ingresar ítems a la consignación de partes','You must enter items to the consignment of parts','You must enter items to the consignment of parts');
  $dictionary[] = array('Subcategoria.','Subcategory.','子類.');
  $dictionary[] = array('Fecha devolución','Return date','Return date');
  $dictionary[] = array('Estado de devolución','Return status','Return status');
  $dictionary[] = array('Documento Helisa','Helisa Document','Helisa Document');
  $dictionary[] = array('Fecha de factura','Invoice date','Invoice date');
  $dictionary[] = array('Falta información de la máquina','Machine information is missing','Machine information is missing');
  $dictionary[] = array('Fecha instalación','Installation date','Installation date');
  $dictionary[] = array('F. Instalación','D. Installation','D. Installation');
  $dictionary[] = array('(Cuando el producto se encuentran en el almacén o en las manos del cliente)','(When the product is in the warehouse or in the hands of the customer)','(When the product is in the warehouse or in the hands of the customer)');
  $dictionary[] = array('Bodega de artículos en transito','Warehouse of articles in transit','Warehouse of articles in transit');
  $dictionary[] = array('Historial de reportes','Report history','Report history');
  $dictionary[] = array('Historial de instalaciones','Installation history','Installation history');
  $dictionary[] = array('Total artículos consignados','Total consigned items','Total consigned items');
  $dictionary[] = array('Total artículos devueltos','Total returned items','Total returned items');
  $dictionary[] = array('Cantidad ítem devolución','Return item quantity','Return item quantity');
  $dictionary[] = array('Cantidad Dev.','Return qty','Return qty');
  $dictionary[] = array('Cantidad Fact.','Invoice qty','Invoice qty');
  $dictionary[] = array('Disponible','Available','Available');
  $dictionary[] = array('Debe ingresar la cantidad en el ítem','You must enter the quantity in the item','You must enter the quantity in the item');
  $dictionary[] = array('Debe realizar la búsqueda','You must perform the search','You must perform the search');
  $dictionary[] = array('Stock en el inventario','Stock in inventory','Stock in inventory');
  $dictionary[] = array('Origen','Origin','Origin');
  $dictionary[] = array('Prioridad','Priority','Priority');
  $dictionary[] = array('Almacén Miami','Miami Warehouse','Miami Warehouse');
  $dictionary[] = array('Atención inmediata','Inmediate attention','Inmediate attention');
  $dictionary[] = array('Muy urgente','Very urgent','Very urgent');
  $dictionary[] = array('Urgente','Urgent','Urgent');
  $dictionary[] = array('No ingreso el origen, desea continuar','You did not enter the origin, you want to continue','You did not enter the origin, you want to continue');
  $dictionary[] = array('Debe seleccionar la prioridad','You must select the priority','You must select the priority');
  $dictionary[] = array('(Cuando se contacta con el proveedor para la compra del producto)','(When contacting the supplier to purchase the product)','(When contacting the supplier to purchase the product)');
  $dictionary[] = array('Ubicación','Location','Location');
  $dictionary[] = array('Saldo','balance','balance');
  $dictionary[] = array('Ítem','Item','Item');
  $dictionary[] = array('Descargar inventario','Download inventory','Download inventory');
  $dictionary[] = array('Volver a listado','Back to listing','Back to listing');
  $dictionary[] = array('Numero consecutivo','Consecutive number','Consecutive number');
  $dictionary[] = array('Numero correlativo','Correlative number','Correlative number');
  $dictionary[] = array('Fecha registro','Registration date','Registration date');
  $dictionary[] = array('Usuario responsable','Responsible user','Responsible user');
  $dictionary[] = array('Usuario registro','User registration','User registration');
  $dictionary[] = array('Guardar y ejecutar','Save and execute','Save and execute');
  $dictionary[] = array('Esta seguro de ejecutar la consignación','Are you sure to execute the consignment','Are you sure to execute the consignment');
  $dictionary[] = array('Aprobaciones','Approvals','Approvals');
  $dictionary[] = array('Área técnica','Technical area','Technical area');
  $dictionary[] = array('Auditoria','Audit','Audit');
  $dictionary[] = array('Esta seguro de establecer el documento como aprobado','Are you sure you set the document as approved','Are you sure you set the document as approved');
  $dictionary[] = array('Falta que el área técnica realice la aprobación','The technical area needs to carry out the approval','The technical area needs to carry out the approval');
  $dictionary[] = array('Esta seguro de ejecutar la devolución','Are you sure to execute the return','Are you sure to execute the return');
  $dictionary[] = array('Cantidad consignada','Consigned quantity','Consigned quantity');
  $dictionary[] = array('Cantidad pendiente','Pending amount','Pending amount');
  $dictionary[] = array('Cantidad devuelta','Return Amount','Return Amount');
  $dictionary[] = array('Fact/Garantía','Inv/Warranty','Inv/Warranty');
  $dictionary[] = array('Cantidad Fact./Grtía.','Quantity Inv./Wty.','Quantity Inv./Wty.');
  $dictionary[] = array('Debe ingresar el responsable','Responsible must enter','Responsible must enter');
  $dictionary[] = array('Consignación de artículos','Consigned of articles','Consigned of articles');
  $dictionary[] = array('Devolución de artículos','Return of articles','Return of articles');
  $dictionary[] = array('Cantidad facturada/garantía','Quantity invoiced / guarantee','Quantity invoiced / guarantee');
  $dictionary[] = array('Cantidad fact/garantía','Cantidad fact/garantía','Cantidad fact/garantía');
  $dictionary[] = array('Instalado','Installed','Installed');
  $dictionary[] = array('Cantidad instalada','Installed quantity','Installed quantity');
  $dictionary[] = array('Plazo entrega','Delivery time','Delivery time');
  $dictionary[] = array('Reportes de Servicio T&eacute;cnico','Technical Service Reports','Technical Service Reports');
  $dictionary[] = array('No. agendamiento','Scheduling No.','Scheduling No.');
  $dictionary[] = array('Fecha del servicio','Service date','Service date');
  $dictionary[] = array('Debe ingresar un patr\u00f3n de búsqueda','You must enter a search pattern','You must enter a search pattern');
  $dictionary[] = array('Debe seleccionar un criterio de búsqueda','You must select a search criteria','You must select a search criteria');
  $dictionary[] = array('Debe seleccionar las fechas para realizar la búsqueda','You must select the dates to perform the search','You must select the dates to perform the search');
  $dictionary[] = array('Nuevo Reporte','New Report','New Report');
  $dictionary[] = array('Primero','First','First');
  $dictionary[] = array('Anterior','Back','Back');
  $dictionary[] = array('Último','Last','Last');
  $dictionary[] = array('&Uacute;ltimo','Last','Last');
  $dictionary[] = array('Realizar Encuesta','Take Survey','Take Survey');
  $dictionary[] = array('Ver Encuesta','View Survey','View Survey');
  $dictionary[] = array('Terminar Encuesta','End Survey','End Survey');
  $dictionary[] = array('Ver Anulada','View Canceled','View Canceled');
  $dictionary[] = array('No tiene permiso para ver los Reportes de Servicio','You do not have permission to view the Service Reports','You do not have permission to view the Service Reports');
  $dictionary[] = array('Imprimir Certificado','Print Certificate','Print Certificate');
  $dictionary[] = array('Fecha Orden','Order Date','Order Date');
  $dictionary[] = array('Fecha de Instalaci&oacute;n','Installation Date','Installation Date');
  $dictionary[] = array('Reportes de servicio','Service reports','Service reports');
  $dictionary[] = array('Certificado de capacitación','Training certificate','Training certificate');
  $dictionary[] = array('Nuevo certificado','New certificate','New certificate');
  $dictionary[] = array('Enviar certificado','Send certificate','Send certificate');
  $dictionary[] = array('Firmar','Sign','Sign');
  $dictionary[] = array('Salir','Exit','Exit');
  $dictionary[] = array('Descargar a una hoja de calculo','Download to a spreadsheet','Download to a spreadsheet');
  $dictionary[] = array('CONSIGNACIÓN','CONSIGNMENT','CONSIGNMENT');
  $dictionary[] = array('Esta seguro que desea anular la consignación','Are you sure you want to cancel the consignment','Are you sure you want to cancel the consignment');
  $dictionary[] = array('Esta seguro que desea anular la devolución','Are you sure you want to cancel the return','Are you sure you want to cancel the return');
  $dictionary[] = array('Se adjunta la devolución de artículos','Return of items is attached','Return of items is attached');
  $dictionary[] = array('Notas','Note','Note');
  $dictionary[] = array('Unidades pendientes agregadas en devolución pero sin ejecutar','Pending units added in return but not executed','Pending units added in return but not executed');
  $dictionary[] = array('Inventario de unidades disponibles','Inventory of available units','Inventory of available units');
  $dictionary[] = array('Plancha','Press','Press');
  $dictionary[] = array('Debe seleccionar el tipo de máquina para generar el certificado de capacitación','You must select the type of machine to generate the training certificate','You must select the type of machine to generate the training certificate');
  $dictionary[] = array('Tiene abierta una ventana de certificado de capacitación debe cerrarla','You have a training certificate window open, you must close it','You have a training certificate window open, you must close it');
  $dictionary[] = array('Debe permitir ventanas emergentes en el navegador','You must allow pop-ups in the browser','You must allow pop-ups in the browser');
  $dictionary[] = array('Facturado','Invoiced','Invoiced');
  $dictionary[] = array('Pruebas','Tests','Tests');
  $dictionary[] = array('Legalizar','Legalize','Legalize');
  $dictionary[] = array('Debe seleccionar el tipo de legalización','You must select the type of legalization','You must select the type of legalization');
  $dictionary[] = array('Total artículos legalizados','Total legalized articles','Total legalized articles');
  $dictionary[] = array('Total artículos devueltos','Total items returned','Total items returned');
  $dictionary[] = array('Solicita','Request','Request');
  $dictionary[] = array('Aprueba','Approve','Approve');
  $dictionary[] = array('Entrega','Delivery','Delivery');
  $dictionary[] = array('Enviar&#160;Vía','Ship&#160;Via','Ship&#160;Via');
  $dictionary[] = array('Bodega de consignación','Consignment warehouse','Consignment warehouse');
  $dictionary[] = array('Nombre común','Common name','Common name');
  $dictionary[] = array('Peso','Weight','Weight');
  $dictionary[] = array('Cambiar fecha de plazo de legalización','Change legalization deadline date','Change legalization deadline date');
  $dictionary[] = array('Error guardando la fecha de plazo de legalización','Error saving the legalization deadline date','Error saving the legalization deadline date');
  $dictionary[] = array('No se realizaron cambios','No changes made','No changes made');
  $dictionary[] = array('Garantía activa','Active warranty','Active warranty');
  $dictionary[] = array('Garantía inactiva','Inactive warranty','Inactive warranty');
  $dictionary[] = array('Procedimiento realizado','Procedure performed','Procedure performed');
  $dictionary[] = array('Nombre de parte cliente','Client Party Name','Client Party Name');
  $dictionary[] = array('Fotos del estado de la máquina','Photos of the state of the machine','Photos of the state of the machine');
  $dictionary[] = array('Estado&#160;inicial','Initial&#160;state','Initial&#160;state');
  $dictionary[] = array('Estado&#160;finalizado','Status&#160;finished','Status&#160;finished');
  $dictionary[] = array('Ver fotos inicio','See photos home','See photos home');
  $dictionary[] = array('Ver fotos finalización','See completion photos','See completion photos');
  $dictionary[] = array('Soporte fotográfico','Photographic support','Photographic support');
  $dictionary[] = array('Debe ingresar las fotos de estado de la máquina','You must enter the state photos of the machine','You must enter the state photos of the machine');
  $dictionary[] = array('Debe ingresar las fotos del estado inicial de la máquina','You must enter the photos of the initial state of the machine','You must enter the photos of the initial state of the machine');
  $dictionary[] = array('No ha cargado fotos','You have not uploaded photos','You have not uploaded photos');
  $dictionary[] = array('desea continuar','Do you wish to continue','Do you wish to continue');
  $dictionary[] = array('Bodegas en general','Cellars in general','Cellars in general');
  $dictionary[] = array('Se adjunta la consignación de artículos','The consignment of articles is attached','The consignment of articles is attached');
  $dictionary[] = array("No ha ingresado la firma del cliente desea continuar","You have not entered the client's signature, do you want to continue","You have not entered the client's signature, do you want to continue");
  $dictionary[] = array('Nuevo','New','New');
  $dictionary[] = array('Modificado','Modified','Modified');
  $dictionary[] = array('Reporte de pendientes','Report of pending legalization','Report of pending legalization');
  $dictionary[] = array('Pendiente por legalizar','Qty. Pending to legalize','Qty. Pending to legalize');
  $dictionary[] = array('Novedades de entrega','Delivery novelty','Delivery novelty');
  $dictionary[] = array('Cabezal o recogido por logística','Head or picked up by logistics','Head or picked up by logistics');
  $dictionary[] = array('Recogido por técnico','Picked up by technician','Picked up by technician');
  $dictionary[] = array('Debe seleccionar una novedad en la entrega','You must select a novelty in the delivery','You must select a novelty in the delivery');
  $dictionary[] = array('Descargar artículos pendientes por entregar','Download items pending delivery','Download items pending delivery');
  $dictionary[] = array('Cant. Pendiente por entregar','Pending quantity to be delivered','Pending quantity to be delivered');
  $dictionary[] = array('Artículo sin unidades disponibles, desea continuar','Article without units available, do you want to continue','Article without units available, do you want to continue');
  
	for ($i=0;$i<count($dictionary);$i++){
	 if (strcmp($data,$dictionary[$i][0]) == 0){
	   $new_data = $dictionary[$i][$lang];
		 break;
		}
	 else $new_data = $data;
	}
//	$new_data = ucwords($new_data);
	return $new_data;
 }
 //----------------------------------------------------------------
 //funcion para devolver el nombre de un pais a partir de su abreviatura o sigla
 //FECHA: Julio 29 de 2013
 //CREADO POR: Javier García
 function traerPais($sigla){ 
  $pais = "";
  if ( $sigla == "AF" ) $pais = "Afganistan";
  else if ( $sigla == "AL" ) $pais = "Albania";
  else if ( $sigla == "DZ" ) $pais = "Argelia";
  else if ( $sigla == "AS" ) $pais = "Samoa Americana";
  else if ( $sigla == "AD" ) $pais = "Andorra";
  else if ( $sigla == "AO" ) $pais = "Angola";
  else if ( $sigla == "AI" ) $pais = "Anguilla";
  else if ( $sigla == "AQ" ) $pais = "Antartida";
  else if ( $sigla == "AG" ) $pais = "Antigua y Barbuda";
  else if ( $sigla == "AR" ) $pais = "Argentina";
  else if ( $sigla == "AM" ) $pais = "Armenia";
  else if ( $sigla == "AW" ) $pais = "Aruba";
  else if ( $sigla == "AU" ) $pais = "Australia";
  else if ( $sigla == "AT" ) $pais = "Austria";
  else if ( $sigla == "AZ" ) $pais = "Azerbaiyan";
  else if ( $sigla == "BS" ) $pais = "Bahamas";
  else if ( $sigla == "BH" ) $pais = "Bahrein";
  else if ( $sigla == "BD" ) $pais = "Bangladesh";
  else if ( $sigla == "BB" ) $pais = "Barbados";
  else if ( $sigla == "BY" ) $pais = "Bielorrusia";
  else if ( $sigla == "BE" ) $pais = "Belgica";
  else if ( $sigla == "BZ" ) $pais = "Belice";
  else if ( $sigla == "BJ" ) $pais = "Benin";
  else if ( $sigla == "BM" ) $pais = "Bermudas";
  else if ( $sigla == "BT" ) $pais = "Butan";
  else if ( $sigla == "BO" ) $pais = "Bolivia";
  else if ( $sigla == "BA" ) $pais = "Bosnia y Herzegovina";
  else if ( $sigla == "BW" ) $pais = "Botswana";
  else if ( $sigla == "BV" ) $pais = "Isla Bouvet";
  else if ( $sigla == "BR" ) $pais = "Brasil";
  else if ( $sigla == "IO" ) $pais = "Territorios britanicos del oceano Indico";
  else if ( $sigla == "BN" ) $pais = "Brunei";
  else if ( $sigla == "BG" ) $pais = "Bulgaria";
  else if ( $sigla == "BF" ) $pais = "Burkina Faso";
  else if ( $sigla == "BI" ) $pais = "Burundi";
  else if ( $sigla == "KH" ) $pais = "Camboya";
  else if ( $sigla == "CM" ) $pais = "Camerun";
  else if ( $sigla == "CA" ) $pais = "Canada";
  else if ( $sigla == "CV" ) $pais = "Cabo Verde";
  else if ( $sigla == "KY" ) $pais = "Islas Caiman";
  else if ( $sigla == "CF" ) $pais = "Republica Centroafricana";
  else if ( $sigla == "TD" ) $pais = "Chad";
  else if ( $sigla == "CL" ) $pais = "Chile";
  else if ( $sigla == "CN" ) $pais = "China";
  else if ( $sigla == "CX" ) $pais = "Isla de Christmas";
  else if ( $sigla == "CC" ) $pais = "Islas de Cocos o Keeling";
  else if ( $sigla == "CO" ) $pais = "Colombia";
  else if ( $sigla == "KM" ) $pais = "Comores";
  else if ( $sigla == "CG" ) $pais = "Congo";
  else if ( $sigla == "CD" ) $pais = "Congo, Republica Democrática del";
  else if ( $sigla == "CK" ) $pais = "Islas Cook";
  else if ( $sigla == "CR" ) $pais = "Costa Rica";
  else if ( $sigla == "CI" ) $pais = "Costa de Marfil";
  else if ( $sigla == "HR" ) $pais = "Croacia (Hrvatska)";
  else if ( $sigla == "CU" ) $pais = "Cuba";
  else if ( $sigla == "CY" ) $pais = "Chipre";
  else if ( $sigla == "CZ" ) $pais = "Republica Checa";
  else if ( $sigla == "DK" ) $pais = "Dinamarca";
  else if ( $sigla == "DJ" ) $pais = "Djibouti";
  else if ( $sigla == "DM" ) $pais = "Dominica";
  else if ( $sigla == "DO" ) $pais = "Republica Dominicana";
  else if ( $sigla == "TP" ) $pais = "Timor Oriental";
  else if ( $sigla == "EC" ) $pais = "Ecuador";
  else if ( $sigla == "EG" ) $pais = "Egipto";
  else if ( $sigla == "SV" ) $pais = "El Salvador";
  else if ( $sigla == "GQ" ) $pais = "Guinea Ecuatorial";
  else if ( $sigla == "ER" ) $pais = "Eritrea";
  else if ( $sigla == "EE" ) $pais = "Estonia";
  else if ( $sigla == "ET" ) $pais = "Etiopia";
  else if ( $sigla == "FK" ) $pais = "Islas Malvinas";
  else if ( $sigla == "FO" ) $pais = "Islas Faroe";
  else if ( $sigla == "FJ" ) $pais = "Fiji";
  else if ( $sigla == "FI" ) $pais = "Finlandia";
  else if ( $sigla == "FR" ) $pais = "Francia";
  else if ( $sigla == "GF" ) $pais = "Guayana Francesa";
  else if ( $sigla == "PF" ) $pais = "Polinesia Francesa";
  else if ( $sigla == "TF" ) $pais = "Territorios franceses del Sur";
  else if ( $sigla == "GA" ) $pais = "Gabon";
  else if ( $sigla == "GM" ) $pais = "Gambia";
  else if ( $sigla == "GE" ) $pais = "Georgia";
  else if ( $sigla == "DE" ) $pais = "Alemania";
  else if ( $sigla == "GH" ) $pais = "Ghana";
  else if ( $sigla == "GI" ) $pais = "Gibraltar";
  else if ( $sigla == "GR" ) $pais = "Grecia";
  else if ( $sigla == "GL" ) $pais = "Groenlandia";
  else if ( $sigla == "GD" ) $pais = "Granada";
  else if ( $sigla == "GP" ) $pais = "Guadalupe";
  else if ( $sigla == "GU" ) $pais = "Guam";
  else if ( $sigla == "GT" ) $pais = "Guatemala";
  else if ( $sigla == "GN" ) $pais = "Guinea";
  else if ( $sigla == "GW" ) $pais = "Guinea-Bissau";
  else if ( $sigla == "GY" ) $pais = "Guayana";
  else if ( $sigla == "HT" ) $pais = "Haiti";
  else if ( $sigla == "HM" ) $pais = "Islas Heard y McDonald";
  else if ( $sigla == "HN" ) $pais = "Honduras";
  else if ( $sigla == "HK" ) $pais = "Hong Kong";
  else if ( $sigla == "HU" ) $pais = "Hungria";
  else if ( $sigla == "IS" ) $pais = "Islandia";
  else if ( $sigla == "IN" ) $pais = "India";
  else if ( $sigla == "ID" ) $pais = "Indonesia";
  else if ( $sigla == "IR" ) $pais = "Irán";
  else if ( $sigla == "IQ" ) $pais = "Irak";
  else if ( $sigla == "IE" ) $pais = "Irlanda";
  else if ( $sigla == "IL" ) $pais = "Israel";
  else if ( $sigla == "IT" ) $pais = "Italia";
  else if ( $sigla == "JM" ) $pais = "Jamaica";
  else if ( $sigla == "JP" ) $pais = "Japon";
  else if ( $sigla == "JO" ) $pais = "Jordania";
  else if ( $sigla == "KZ" ) $pais = "Kazajistan";
  else if ( $sigla == "KE" ) $pais = "Kenia";
  else if ( $sigla == "KI" ) $pais = "Kiribati";
  else if ( $sigla == "KR" ) $pais = "Corea";
  else if ( $sigla == "KP" ) $pais = "Corea del Norte";
  else if ( $sigla == "KW" ) $pais = "Kuwait";
  else if ( $sigla == "KG" ) $pais = "Kirguizistan";
  else if ( $sigla == "LA" ) $pais = "Laos";
  else if ( $sigla == "LV" ) $pais = "Letonia";
  else if ( $sigla == "LB" ) $pais = "Líbano";
  else if ( $sigla == "LS" ) $pais = "Lesotho";
  else if ( $sigla == "LR" ) $pais = "Liberia";
  else if ( $sigla == "LY" ) $pais = "Libia";
  else if ( $sigla == "LI" ) $pais = "Liechtenstein";
  else if ( $sigla == "LT" ) $pais = "Lituania";
  else if ( $sigla == "LU" ) $pais = "Luxemburgo";
  else if ( $sigla == "MO" ) $pais = "Macao";
  else if ( $sigla == "MG" ) $pais = "Madagascar";
  else if ( $sigla == "MW" ) $pais = "Malawi";
  else if ( $sigla == "MY" ) $pais = "Malasia";
  else if ( $sigla == "MV" ) $pais = "Maldivas";
  else if ( $sigla == "ML" ) $pais = "Mali";
  else if ( $sigla == "MT" ) $pais = "Malta";
  else if ( $sigla == "MH" ) $pais = "Islas Marshall";
  else if ( $sigla == "MQ" ) $pais = "Martinica";
  else if ( $sigla == "MR" ) $pais = "Mauritania";
  else if ( $sigla == "MU" ) $pais = "Mauricio";
  else if ( $sigla == "YT" ) $pais = "Mayotte";
  else if ( $sigla == "MX" ) $pais = "Mexico";
  else if ( $sigla == "FM" ) $pais = "Micronesia";
  else if ( $sigla == "MD" ) $pais = "Moldavia";
  else if ( $sigla == "MC" ) $pais = "Monaco";
  else if ( $sigla == "MN" ) $pais = "Mongolia";
  else if ( $sigla == "MS" ) $pais = "Montserrat";
  else if ( $sigla == "MA" ) $pais = "Marruecos";
  else if ( $sigla == "MZ" ) $pais = "Mozambique";
  else if ( $sigla == "MM" ) $pais = "Birmania";
  else if ( $sigla == "NA" ) $pais = "Namibia";
  else if ( $sigla == "NR" ) $pais = "Nauru";
  else if ( $sigla == "NP" ) $pais = "Nepal";
  else if ( $sigla == "AN" ) $pais = "Antillas Holandesas";
  else if ( $sigla == "NL" ) $pais = "Paises Bajos";
  else if ( $sigla == "NC" ) $pais = "Nueva Caledonia";
  else if ( $sigla == "NZ" ) $pais = "Nueva Zelanda";
  else if ( $sigla == "NI" ) $pais = "Nicaragua";
  else if ( $sigla == "NE" ) $pais = "Niger";
  else if ( $sigla == "NG" ) $pais = "Nigeria";
  else if ( $sigla == "NU" ) $pais = "Niue";
  else if ( $sigla == "NF" ) $pais = "Norfolk";
  else if ( $sigla == "MP" ) $pais = "Islas Marianas del Norte";
  else if ( $sigla == "NO" ) $pais = "Noruega";
  else if ( $sigla == "OM" ) $pais = "Oman";
  else if ( $sigla == "PK" ) $pais = "Paquistan";
  else if ( $sigla == "PW" ) $pais = "Islas Palau";
  else if ( $sigla == "PA" ) $pais = "Panama";
  else if ( $sigla == "PG" ) $pais = "Papua Nueva Guinea";
  else if ( $sigla == "PY" ) $pais = "Paraguay";
  else if ( $sigla == "PE" ) $pais = "Peru";
  else if ( $sigla == "PH" ) $pais = "Filipinas";
  else if ( $sigla == "PN" ) $pais = "Pitcairn";
  else if ( $sigla == "PL" ) $pais = "Polonia";
  else if ( $sigla == "PT" ) $pais = "Portugal";
  else if ( $sigla == "PR" ) $pais = "Puerto Rico";
  else if ( $sigla == "QA" ) $pais = "Qatar";
  else if ( $sigla == "RE" ) $pais = "Reunion";
  else if ( $sigla == "RO" ) $pais = "Rumania";
  else if ( $sigla == "RU" ) $pais = "Rusia";
  else if ( $sigla == "RW" ) $pais = "Ruanda";
  else if ( $sigla == "SH" ) $pais = "Santa Helena";
  else if ( $sigla == "KN" ) $pais = "Saint Kitts y Nevis";
  else if ( $sigla == "LC" ) $pais = "Santa Lucia";
  else if ( $sigla == "PM" ) $pais = "St. Pierre y Miquelon";
  else if ( $sigla == "VC" ) $pais = "San Vicente y Granadinas";
  else if ( $sigla == "WS" ) $pais = "Samoa";
  else if ( $sigla == "SM" ) $pais = "San Marino";
  else if ( $sigla == "ST" ) $pais = "Santo Tomas y Principe";
  else if ( $sigla == "SA" ) $pais = "Arabia Saudi";
  else if ( $sigla == "SN" ) $pais = "Senegal";
  else if ( $sigla == "SC" ) $pais = "Seychelles";
  else if ( $sigla == "SL" ) $pais = "Sierra Leona";
  else if ( $sigla == "SG" ) $pais = "Singapur";
  else if ( $sigla == "SK" ) $pais = "Republica Eslovaca";
  else if ( $sigla == "SI" ) $pais = "Eslovenia";
  else if ( $sigla == "SB" ) $pais = "Islas Salomon";
  else if ( $sigla == "SO" ) $pais = "Somalia";
  else if ( $sigla == "ZA" ) $pais = "Republica de Sudafrica";
  else if ( $sigla == "ES" ) $pais = "España";
  else if ( $sigla == "LK" ) $pais = "Sri Lanka";
  else if ( $sigla == "SD" ) $pais = "Sudan";
  else if ( $sigla == "SR" ) $pais = "Surinam";
  else if ( $sigla == "SJ" ) $pais = "Islas Svalbard y Jan Mayen";
  else if ( $sigla == "SZ" ) $pais = "Suazilandia";
  else if ( $sigla == "SE" ) $pais = "Suecia";
  else if ( $sigla == "CH" ) $pais = "Suiza";
  else if ( $sigla == "SY" ) $pais = "Siria";
  else if ( $sigla == "TW" ) $pais = "Taiwán";
  else if ( $sigla == "TJ" ) $pais = "Tayikistan";
  else if ( $sigla == "TZ" ) $pais = "Tanzania";
  else if ( $sigla == "TH" ) $pais = "Tailandia";
  else if ( $sigla == "TG" ) $pais = "Togo";
  else if ( $sigla == "TK" ) $pais = "Islas Tokelau";
  else if ( $sigla == "TO" ) $pais = "Tonga";
  else if ( $sigla == "TT" ) $pais = "Trinidad y Tobago";
  else if ( $sigla == "TN" ) $pais = "Tunez";
  else if ( $sigla == "TR" ) $pais = "Turquia";
  else if ( $sigla == "TM" ) $pais = "Turkmenistan";
  else if ( $sigla == "TC" ) $pais = "Islas Turks y Caicos";
  else if ( $sigla == "TV" ) $pais = "Tuvalu";
  else if ( $sigla == "UG" ) $pais = "Uganda";
  else if ( $sigla == "UA" ) $pais = "Ucrania";
  else if ( $sigla == "AE" ) $pais = "Emiratos Arabes Unidos";
  else if ( $sigla == "UK" ) $pais = "Reino Unido";
  else if ( $sigla == "US" ) $pais = "Estados Unidos";
  else if ( $sigla == "UM" ) $pais = "Islas menores de Estados Unidos";
  else if ( $sigla == "UY" ) $pais = "Uruguay";
  else if ( $sigla == "UZ" ) $pais = "Uzbekistan";
  else if ( $sigla == "VU" ) $pais = "Vanuatu";
  else if ( $sigla == "VA" ) $pais = "Ciudad del Vaticano (Santa Sede)";
  else if ( $sigla == "VE" ) $pais = "Venezuela";
  else if ( $sigla == "VN" ) $pais = "Vietnam";
  else if ( $sigla == "VG" ) $pais = "Islas Virgenes (Reino Unido)";
  else if ( $sigla == "VI" ) $pais = "Islas Virgenes (EE.UU.)";
  else if ( $sigla == "WF" ) $pais = "Islas Wallis y Futuna";
  else if ( $sigla == "YE" ) $pais = "Yemen";
  else if ( $sigla == "YU" ) $pais = "Yugoslavia";
  else if ( $sigla == "ZM" ) $pais = "Zambia";
  else if ( $sigla == "ZW" ) $pais = "Zimbabue";
  return($pais);
 }
 //----------------------------------------------------------------
 //consulta los permisos de usuarios
function consultaPermisoUsuario($servicio_id,$link){
        $queryTipoUsuario="SELECT * FROM usuario WHERE USUARIO_LOGIN='$_SESSION[nombreuser]'";
        $resultTipoUsuario=nmysql2_query($queryTipoUsuario,$link);
        $tipoUsuario=nmysql2_fetch_assoc($resultTipoUsuario);
        if($tipoUsuario["USUARIO_TIPO_ID"]==1){
         $arregloAux[0]=1;
         $arregloAux[1]=1;
         $arregloAux[2]=1;
         $arregloAux[3]=1;
         $arregloAux[4]=1;
         $arregloAux[5]=1;
         return $arregloAux;
        }
        else{
        $queryPermiso=" SELECT * FROM permiso ";
        $queryPermiso.=" WHERE PERMISO_USUARIO=(SELECT USUARIO_ID FROM usuario WHERE USUARIO_LOGIN='$_SESSION[nombreuser]') ";
        $queryPermiso.=" AND PERMISO_SERVICIO=$servicio_id ";
        $resultPermiso=nmysql2_query($queryPermiso,$link);
        $fila=nmysql2_num_rows($resultPermiso);
        if($fila==0){
         $arregloAux[0]=0;
         $arregloAux[1]=0;
         $arregloAux[2]=0;
         $arregloAux[3]=0;
         $arregloAux[4]=0;
         $arregloAux[5]=0;
         return $arregloAux;
        }
        else{
         $arregloPermiso=nmysql2_fetch_assoc($resultPermiso);
         $arregloAux[0]=$arregloPermiso["PERMISO_LISTAR"];
         $arregloAux[1]=$arregloPermiso["PERMISO_DETALLE"];
         $arregloAux[2]=$arregloPermiso["PERMISO_EDITAR"];
         $arregloAux[3]=$arregloPermiso["PERMISO_ADICIONAR"];
         $arregloAux[4]=$arregloPermiso["PERMISO_ESPECIAL"];
         $arregloAux[5]=$arregloPermiso["PERMISO_DESCARGA"];
         return $arregloAux;
         }
        }
        }
 //----------------------------------------------------------------
       // funcion que retorna el mensaje del permiso
       function mensajePermiso($servicio,$opcion){
         switch($opcion){
             CASE 0: $mensajeCadena="No tiene permisos para listar el servicio  $servicio";
                     break;
             CASE 1: $mensajeCadena="No tiene permisos para ver detalles del servicio  $servicio";
                     break;
             CASE 2: $mensajeCadena="No tiene permisos para editar el servicio  $servicio";
                     break;
             CASE 3: $mensajeCadena="No tiene permisos para Agregar en el servicio  $servicio";
                     break;
             CASE 4: $mensajeCadena="No tiene permiso Especial el servicio  $servicio";
                     break;
             CASE 5: $mensajeCadena="No tiene permiso Descargar el servicio  $servicio";
                     break;
         }
         return $mensajeCadena;
        }
 //----------------------------------------------------------------
function editarPermisoUsuario($numeroDecimal){
	$binario=decbin($numeroDecimal);
	switch($numeroDecimal){
		case 0	:		$listar=0;
					$detalle=0;
					$editar=0;
					$adicionar=0;
					$especial=0;
					$descarga=0;
					break;
		case 1	:		$listar=1;
					$detalle=0;
					$editar=0;
					$adicionar=0;
					$especial=0;
					$descarga=0;
					break;
		case 2	:		$listar=0;
					$detalle=1;
					$editar=0;
					$adicionar=0;
					$especial=0;
					$descarga=0;
					break;
		case 3	:		$listar=1;
					$detalle=1;
					$editar=0;
					$adicionar=0;
					$especial=0;
					$descarga=0;
					break;
		case 4	:		$listar=0;
					$detalle=0;
					$editar=1;
					$adicionar=0;
					$especial=0;
					$descarga=0;
					break;
		case 5	:		$listar=1;
					$detalle=0;
					$editar=1;
					$adicionar=0;
					$especial=0;
					$descarga=0;
					break;
		case 6	:		$listar=0;
					$detalle=1;
					$editar=1;
					$adicionar=0;
					$especial=0;
					$descarga=0;
					break;
		case 7	:		$listar=1;
					$detalle=1;
					$editar=1;
					$adicionar=0;
					$especial=0;
					$descarga=0;
					break;
		case 8	:		$listar=0;
					$detalle=0;
					$editar=0;
					$adicionar=1;
					$especial=0;
					$descarga=0;
					break;
		case 9	:		$listar=1;
					$detalle=0;
					$editar=0;
					$adicionar=1;
					$especial=0;
					$descarga=0;
					break;
        	case 10	:		$listar=0;
					$detalle=1;
					$editar=0;
					$adicionar=1;
					$especial=0;
					$descarga=0;
					break;
		case 11	:		$listar=1;
					$detalle=1;
					$editar=0;
					$adicionar=1;
					$especial=0;
					$descarga=0;
					break;
		case 12	:		$listar=0;
					$detalle=0;
					$editar=1;
					$adicionar=1;
					$especial=0;
					$descarga=0;
					break;
		case 13	:		$listar=1;
					$detalle=0;
					$editar=1;
					$adicionar=1;
					$especial=0;
					$descarga=0;
					break;
		case 14	:		$listar=0;
					$detalle=1;
					$editar=1;
					$adicionar=1;
					$especial=0;
					$descarga=0;
					break;
		case 15	:		$listar=1;
					$detalle=1;
					$editar=1;
					$adicionar=1;
					$especial=0;
					$descarga=0;
					break;
		case 16 :		$listar=0;
					$detalle=0;
					$editar=0;
					$adicionar=0;
					$especial=1;
					$descarga=0;
					break;
		case 17 :		$listar=1;
					$detalle=0;
					$editar=0;
					$adicionar=0;
					$especial=1;
					$descarga=0;
					break;
		case 18 :		$listar=0;
					$detalle=1;
					$editar=0;
					$adicionar=0;
					$especial=1;
					$descarga=0;
					break;
		case 19 :		$listar=1;
					$detalle=1;
					$editar=0;
					$adicionar=0;
					$especial=1;
					$descarga=0;
					break;
		case 20 :		$listar=0;
					$detalle=0;
					$editar=1;
					$adicionar=0;
					$especial=1;
					$descarga=0;
					break;
		case 21 :		$listar=1;
					$detalle=0;
					$editar=1;
					$adicionar=0;
					$especial=1;
					$descarga=0;
					break;
		case 22 :		$listar=0;
					$detalle=1;
					$editar=1;
					$adicionar=0;
					$especial=1;
					$descarga=0;
					break;
		case 23 :		$listar=1;
					$detalle=1;
					$editar=1;
					$adicionar=0;
					$especial=1;
					$descarga=0;
					break;
		case 24 :		$listar=0;
					$detalle=0;
					$editar=0;
					$adicionar=1;
					$especial=1;
					$descarga=0;
					break;
		case 25 :		$listar=1;
					$detalle=0;
					$editar=0;
					$adicionar=1;
					$especial=1;
					$descarga=0;
					break;
		case 26 :		$listar=0;
					$detalle=1;
					$editar=0;
					$adicionar=1;
					$costos=1;
					$descarga=0;
					break;
		case 27 :		$listar=1;
					$detalle=1;
					$editar=0;
					$adicionar=1;
					$especial=1;
					$descarga=0;
					break;
		case 28 :		$listar=0;
					$detalle=0;
					$editar=1;
					$adicionar=1;
					$especial=1;
					$descarga=0;
					break;
		case 29 :		$listar=1;
					$detalle=0;
					$editar=1;
					$adicionar=1;
					$especial=1;
					$descarga=0;
					break;
		case 30 :		$listar=0;
					$detalle=1;
					$editar=1;
					$adicionar=1;
					$especial=1;
					$descarga=0;
					break;
		case 31 :		$listar=1;
					$detalle=1;
					$editar=1;
					$adicionar=1;
					$especial=1;
					$descarga=0;
					break;
		case 32 :		$listar=0;
					$detalle=0;
					$editar=0;
					$adicionar=0;
					$especial=0;
					$descarga=1;
					break;
		case 33 :		$listar=1;
					$detalle=0;
					$editar=0;
					$adicionar=0;
					$especial=0;
					$descarga=1;
					break;
		case 34 :		$listar=0;
					$detalle=1;
					$editar=0;
					$adicionar=0;
					$especial=0;
					$descarga=1;
					break;
		case 35 :		$listar=1;
					$detalle=1;
					$editar=0;
					$adicionar=0;
					$especial=0;
					$descarga=1;
					break;
		case 36 :		$listar=0;
					$detalle=0;
					$editar=1;
					$adicionar=0;
					$especial=0;
					$descarga=1;
					break;
		case 37 :		$listar=1;
					$detalle=0;
					$editar=1;
					$adicionar=0;
					$especial=0;
					$descarga=1;
					break;
		case 38 :		$listar=0;
					$detalle=1;
					$editar=1;
					$adicionar=0;
					$especial=0;
					$descarga=1;
					break;
		case 39 :		$listar=1;
					$detalle=1;
					$editar=1;
					$adicionar=0;
					$especial=0;
					$descarga=1;
					break;
		case 40 :		$listar=0;
					$detalle=0;
					$editar=0;
					$adicionar=1;
					$especial=0;
					$descarga=1;
					break;
		case 41 :		$listar=1;
					$detalle=0;
					$editar=0;
					$adicionar=1;
					$especial=0;
					$descarga=1;
					break;
		case 42 :		$listar=0;
					$detalle=1;
					$editar=0;
					$adicionar=1;
					$especial=0;
					$descarga=1;
					break;
		case 43 :		$listar=1;
					$detalle=1;
					$editar=0;
					$adicionar=1;
					$especial=0;
					$descarga=1;
					break;
		case 44 :		$listar=0;
					$detalle=0;
					$editar=1;
					$adicionar=1;
					$especial=0;
					$descarga=1;
					break;
		case 45 :		$listar=1;
					$detalle=0;
					$editar=1;
					$adicionar=1;
					$especial=0;
					$descarga=1;
					break;
		case 46 :		$listar=0;
					$detalle=1;
					$editar=1;
					$adicionar=1;
					$especial=0;
					$descarga=1;
					break;
		case 47 :		$listar=1;
					$detalle=1;
					$editar=1;
					$adicionar=1;
					$especial=0;
					$descarga=1;
					break;
		case 48 :		$listar=0;
					$detalle=0;
					$editar=0;
					$adicionar=0;
					$especial=1;
					$descarga=1;
					break;
		case 49 :		$listar=1;
					$detalle=0;
					$editar=0;
					$adicionar=0;
					$especial=1;
					$descarga=1;
					break;
		case 50 :		$listar=0;
					$detalle=1;
					$editar=0;
					$adicionar=0;
					$especial=1;
					$descarga=1;
					break;
		case 51 :		$listar=1;
					$detalle=1;
					$editar=0;
					$adicionar=0;
					$especial=1;
					$descarga=1;
					break;
		case 52 :		$listar=0;
					$detalle=0;
					$editar=1;
					$adicionar=0;
					$especial=1;
					$descarga=1;
					break;
		case 53 :		$listar=1;
					$detalle=0;
					$editar=1;
					$adicionar=0;
					$especial=1;
					$descarga=1;
					break;
		case 54 :		$listar=0;
					$detalle=1;
					$editar=1;
					$adicionar=0;
					$especial=1;
					$descarga=1;
					break;
		case 55 :		$listar=1;
					$detalle=1;
					$editar=1;
					$adicionar=0;
					$especial=1;
					$descarga=1;
					break;
		case 56 :		$listar=0;
					$detalle=0;
					$editar=0;
					$adicionar=1;
					$especial=1;
					$descarga=1;
					break;
		case 57 :		$listar=1;
					$detalle=0;
					$editar=0;
					$adicionar=1;
					$especial=1;
					$descarga=1;
					break;
		case 58 :		$listar=0;
					$detalle=1;
					$editar=0;
					$adicionar=1;
					$especial=1;
					$descarga=1;
					break;
		case 59 :		$listar=1;
					$detalle=1;
					$editar=0;
					$adicionar=1;
					$especial=1;
					$descarga=1;
					break;
		case 60 :		$listar=0;
					$detalle=0;
					$editar=1;
					$adicionar=1;
					$especial=1;
					$descarga=1;
					break;
		case 61 :		$listar=1;
					$detalle=0;
					$editar=1;
					$adicionar=1;
					$especial=1;
					$descarga=1;
					break;
		case 62 :		$listar=0;
					$detalle=1;
					$editar=1;
					$adicionar=1;
					$especial=1;
					$descarga=1;
					break;
		case 63 :		$listar=1;
					$detalle=1;
					$editar=1;
					$adicionar=1;
					$especial=1;
					$descarga=1;
					break;
	}
	$arregloRetorna[0]=$listar;
	$arregloRetorna[1]=$detalle;
	$arregloRetorna[2]=$editar;
	$arregloRetorna[3]=$adicionar;
	$arregloRetorna[4]=$especial;
	$arregloRetorna[5]=$descarga;
	return $arregloRetorna;	
 }
 //---------------------------------------------------------------- 
 //funcion para registrar serial
 function registrarSerial($articulo_id,$serial1,$serial2,$movimiento_tipo,$movimiento_item_id,$cantidad,$link){ 
 $querySerial = "INSERT INTO articulo_serial (SERIAL_ARTICULO_ID, SERIAL1, SERIAL2, SERIAL_MOVIMIENTO_TIPO, SERIAL_MOVIMIENTO_ITEM_ID, SERIAL_CANTIDAD) VALUES ($articulo_id,$serial1,$serial2,'$movimiento_tipo',$movimiento_item_id,$cantidad)"; 
 $querySerial = nmysql2_query($querySerial,$link);
  if(nmysql2_affected_rows($link)>0)
  {    
    return true;
  }
 return false;
 }
 //funcion para registrar fecha de la tinta
 function registrarFechaTinta($articulo_id,$fecha,$movimiento_tipo,$movimiento_item_id,$cantidad,$link){ 
 $queryFechaTinta = "INSERT INTO articulo_fecha_tinta (TINTA_ARTICULO_ID, TINTA_FECHA, TINTA_MOVIMIENTO_TIPO, TINTA_MOVIMIENTO_ITEM_ID, TINTA_CANTIDAD) VALUES ($articulo_id,$fecha,'$movimiento_tipo',$movimiento_item_id,$cantidad)";
 $queryFechaTinta = nmysql2_query($queryFechaTinta,$link);
  if(nmysql2_affected_rows($link)>0)
  {    
    return true;
  } 
 return false;  
 }
 //funcion para registrar una entrada de inventario en una salida de inventario
 function registrarEntradaInventario($articulo_id,$fecha_movimiento_salida,$movimiento_tipo,$movimiento_id_entrada,$movimiento_id_salida,$movimiento_item_id_salida,$cantidad,$link){ 
 $queryEntradaInventario = "INSERT INTO articulo_salida_inventario (ARTICULO_SALIDA_INVENTARIO_ARTICULO_ID, ARTICULO_SALIDA_INVENTARIO_FECHA, ARTICULO_SALIDA_INVENTARIO_MOVIMIENTO_TIPO, ARTICULO_SALIDA_INVENTARIO_ENTRADA_MOVIMIENTO_ID, ARTICULO_SALIDA_INVENTARIO_SALIDA_MOVIMIENTO_ID, ARTICULO_SALIDA_INVENTARIO_SALIDA_MOVIMIENTO_ITEM_ID, ARTICULO_SALIDA_INVENTARIO_SALIDA_CANTIDAD) VALUES ($articulo_id,'$fecha_movimiento_salida','$movimiento_tipo',$movimiento_id_entrada,$movimiento_id_salida,$movimiento_item_id_salida,$cantidad)";
 $queryEntradaInventario = nmysql2_query($queryEntradaInventario,$link);
  if(nmysql2_affected_rows($link)>0)
  {    
    return true;
  } 
 return false;  
 } 
 //funcion para crear un nuevo mensaje
 //$fingreso fecha de envio
 //$usuario usuario
 //$subject asunto
 //$content contenido
 //$id_notificacion id de notificación de alertas Nulo si no se usa
 function enviarMensaje($fingreso,$usuario,$grupo,$subject,$content,$link,$id_notificacion){
 if($id_notificacion==NULL){
 $id_notificacion="NULL";
 }
 if($grupo==0){
 $queryUsuario = " SELECT * "
            . " FROM usuario "
            . " WHERE USUARIO_ID=".$usuario." AND USUARIO_ESTADO=1";
 $resultUsuario = nmysql2_query($queryUsuario,$link);
 while ($row = nmysql2_fetch_array($resultUsuario)) {
       $textoUsuario=$row["USUARIO_NOMBRES"]." ".$row["USUARIO_APELLIDOS"];
 }
 
 $query_nuevo_mensaje = "INSERT INTO mensaje(MENSAJE_FROM, MENSAJE_TO,  ";
 $query_nuevo_mensaje.= "MENSAJE_SUBJECT, MENSAJE_CONTENT, ";
 $query_nuevo_mensaje.= "MENSAJE_FSEND, MENSAJE_FREAD,MENSAJE_NOTIFICACION_ID) ";
 $query_nuevo_mensaje.= "VALUES(6,$usuario,'$subject','$content',";
 $query_nuevo_mensaje.= "'$fingreso','0000-00-00 00:00:00',$id_notificacion)";       
 $resultMensaje = nmysql2_query($query_nuevo_mensaje,$link); 
  if(nmysql2_affected_rows($link)>0)
  {    
    registrar("Mensajeria Interna", "mensaje", 4, "from=Sistema iSupport to=$textoUsuario  - subject=$subject - content=$content", $link);
  }
 }
 if($grupo>0){ 
    $queryUsuario = " SELECT * "
            . " FROM usuario u INNER JOIN grupo g ON g.GRUPO_ID=u.USUARIO_GRUPO_ID"
            . " WHERE g.GRUPO_ID=".$grupo." AND USUARIO_ESTADO=1";
    $resultUsuario = nmysql2_query($queryUsuario,$link);
    while ($row = nmysql2_fetch_array($resultUsuario)) {
      $id_usuario=$row["USUARIO_ID"];
      $textoUsuario=$row["USUARIO_NOMBRES"]." ".$row["USUARIO_APELLIDOS"];
      $query_nuevo_mensaje = "INSERT INTO mensaje(MENSAJE_FROM, MENSAJE_TO,  ";
      $query_nuevo_mensaje.= "MENSAJE_SUBJECT, MENSAJE_CONTENT, ";
      $query_nuevo_mensaje.= "MENSAJE_FSEND, MENSAJE_FREAD,MENSAJE_NOTIFICACION_ID) ";
      $query_nuevo_mensaje.= "VALUES(6,$id_usuario,'$subject','$content',";
      $query_nuevo_mensaje.= "'$fingreso','0000-00-00 00:00:00',$id_notificacion)";       
      $resultMensaje = nmysql2_query($query_nuevo_mensaje,$link); 
       if(nmysql2_affected_rows($link)>0){    
       registrar("Mensajeria Interna", "mensaje", 4, "from=Sistema iSupport to=$textoUsuario  - subject=$subject - content=$content", $link);
       }
    }
 } 
 if(nmysql2_affected_rows($link)>0){    
 return true;
 }

 return false;
 }
//esta función comprime la imagen 
function comprimirImagen($imagen,$tipo,$quality) {  

if($tipo=='image/jpg'){
$imagen = imagecreatefromjpeg($imagen);
}
if($tipo=='image/jpeg'){
$imagen = imagecreatefromjpeg($imagen);
}
if($tipo=='image/gif'){
$imagen = imagecreatefromgif($imagen);
}
if($tipo=='image/png'){
$imagen = imagecreatefrompng($imagen);
}
imagejpeg($imagen, null, $quality);
} 
function comprimirImagen2($imagen,$quality) {  

$info = getimagesize($imagen);

if ($info['mime'] == 'image/jpeg'){
$image = imagecreatefromjpeg($imagen);
}elseif ($info['mime'] == 'image/gif'){
 $image = imagecreatefromgif($imagen);
 }elseif ($info['mime'] == 'image/png'){ 
 $image = imagecreatefrompng($imagen);
 }elseif ($info['mime'] == 'image/jpeg'){ 
 $image = imagecreatefromjpeg($imagen);
}

ob_start();

imagejpeg($image, null, $quality);

$imagen_resultado = ob_get_contents();

ob_end_clean();

return $imagen_resultado;
}
function redimensionarImagen($archivo){
// El archivo
$nombre_archivo = $archivo;
$porcentaje = 0.5;

// Obtener nuevas dimensiones
list($ancho, $alto) = getimagesize($nombre_archivo);
$nuevo_ancho = $ancho * $porcentaje;
$nuevo_alto = $alto * $porcentaje;

// Redimensionar
$imagen_p = imagecreatetruecolor($nuevo_ancho, $nuevo_alto);
$imagen = imagecreatefromjpeg($nombre_archivo);
imagecopyresampled($imagen_p, $imagen, 0, 0, 0, 0, $nuevo_ancho, $nuevo_alto, $ancho, $alto);

ob_start();

imagejpeg($imagen_p, null, 100);

$imagen_resultado = ob_get_contents();

ob_end_clean();

return $imagen_resultado;
}
//funcion para cambiar la resolucion de la imagen
function resizeImage($originalImage,$toWidth,$toHeight,$tipo){
    // Get the original geometry and calculate scales
    list($width, $height) = getimagesize($originalImage);
    $xscale=$width/$toWidth;
    $yscale=$height/$toHeight;

    // Recalculate new size with default ratio
    if ($yscale>$xscale){
        $new_width = round($width * (1/$yscale));
        $new_height = round($height * (1/$yscale));
    }
    else {
        $new_width = round($width * (1/$xscale));
        $new_height = round($height * (1/$xscale));
    }

    // Resize the original image    
    if($tipo=='image/jpg'){
    $imageTmp = imagecreatefromjpeg($originalImage);
    }
    if($tipo=='image/jpeg'){
    $imageTmp = imagecreatefromjpeg($originalImage);
    }
    if($tipo=='image/gif'){
    $imageTmp = imagecreatefromgif($originalImage);
    }
    if($tipo=='image/png'){
    $imageTmp = imagecreatefrompng($originalImage);
    }    
    
    $imageResized = imagecreatetruecolor($new_width, $new_height);
    imagecopyresampled($imageResized, $imageTmp, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

    return $imageResized;
}
//registro de seguimiento de las ordens de compra grupo ess sas
function seguimientoComprasOrden($fecha,$proveedor,$bodega,$direccion,$contacto,$no_orden,$observaciones,$condiciones,$po,$no_factura,$estado,$tipo_modificacion,$link,$fecha_registro,$usuario){
$query_seguimiento_ordenes=" INSERT INTO compra_orden_historico "
                          ." (COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_DATE, COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_SUPPLIER," 
                          ." COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_TO_BODEGA_ID, COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_TO_ADDRESS, COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_CONTACT_ID,"
                          ." COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_ID,COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_COMMENTS," 
                          ." COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_PAYMENT_CONDITION, COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_PO," 
                          ." COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_NUMERO_FACTURA, COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_ESTADO,COMPRA_ORDEN_HISTORICO_TIPO_MODIFICACION,COMPRA_ORDEN_HISTORICO_FECHA_REGISTRO,COMPRA_ORDEN_HISTORICO_USUARIO_LOGIN)" 
                          ." VALUES ('$fecha','$proveedor',$bodega,'$direccion',$contacto,$no_orden,$observaciones,$condiciones,$po,$no_factura,$estado,'$tipo_modificacion','$fecha_registro','$usuario')";
$query_resultado=nmysql2_query($query_seguimiento_ordenes,$link);
 if(nmysql2_affected_rows($link)>0){
 return nmysqli2_insert_id($link);
 }
return 0; 
}
//registro de seguimiento de las ordens de compra ESS
function seguimientoComprasOrdenN2($fecha,$proveedor,$bodega,$direccion,$contacto,$no_orden,$observaciones,$condiciones,$po,$no_factura,$estado,$tipo_modificacion,$link,$fecha_registro,$usuario,$cliente_direccion,$cliente_ciudad,$cliente_direccion_pais,$cliente_pais,$cliente_telefono,$cliente_contacto,$cliente_email,$terminos,$etd,$tiempo_produccion,$eta,$bl,$shipping_type,$cuenta,$sea_packing,$puerto,$pick_up_address,$forwader_contact,$logistics_responsible,$comercial_responsible,$fabricante_pais){
if($po==0){
$po='NULL';
}
$query_seguimiento_ordenes=" INSERT INTO compra_orden_historico_ess "
                          ." (COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_DATE, COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_SUPPLIER," 
                          ." COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_TO_CLIENTE, COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_TO_ADDRESS, COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_CONTACT_ID,"
                          ." COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_ID,COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_COMMENTS," 
                          ." COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_PAYMENT_CONDITION, COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_PO," 
                          ." COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_NUMERO_FACTURA, COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_ESTADO,COMPRA_ORDEN_HISTORICO_TIPO_MODIFICACION,COMPRA_ORDEN_HISTORICO_FECHA_REGISTRO,COMPRA_ORDEN_HISTORICO_USUARIO_LOGIN,"
                          ." COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_CLIENTE_ADDRESS,"
                          ." COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_CLIENTE_ADDRESS_CITY,"
                          ." COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_CLIENTE_ADDRESS_COUNTRY_ID,"
                          ." COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_CLIENTE_COUNTRY_ID,"
                          ." COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_TO_CLIENTE_PHONE_NUMBER,"
                          ." COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_TO_CLIENTE_CONTACT,"
                          ." COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_TO_CLIENTE_EMAIL,"
                          ." COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_TO_CLIENTE_TERMS,"
                          ." COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_ETD,"
                          ." COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_PRODUCTION_TIME,"
                          ." COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_ETA,"
                          ." COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_BL,"
                          ." COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_SHIPPING,"
                          ." COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_SHIPPING_AIR_ACCOUNT,"
                          ." COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_SHIPPING_SEA_PACKING,"
                          ." COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_SHIPPING_SEA_PORT,"
                          ." COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_PICK_UP_ADDRESS,"
                          ." COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_FORWARDER_CONTACT,"
                          ." COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_LOGISTICS_RESPONSIBLE,"
                          ." COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_COMMERCIAL_RESPONSIBLE,"
                          ." COMPRA_ORDEN_HISTORICO_COMPRA_ORDEN_FABRICANTE_PAIS)" 
                          ." VALUES ('$fecha','$proveedor','$bodega','$direccion',$contacto,$no_orden,$observaciones,$condiciones,$po,$no_factura,$estado,'$tipo_modificacion','$fecha_registro','$usuario',
			  '$cliente_direccion','$cliente_ciudad',$cliente_direccion_pais,$cliente_pais,'$cliente_telefono','$cliente_contacto','$cliente_email','$terminos',
			  $etd,'$tiempo_produccion',$eta,'$bl','$shipping_type','$cuenta','$sea_packing','$puerto','$pick_up_address','$forwader_contact','$logistics_responsible','$comercial_responsible','$fabricante_pais')";
$query_resultado=nmysql2_query($query_seguimiento_ordenes,$link);
 if(nmysql2_affected_rows($link)>0){
 return nmysqli2_insert_id($link);
 }
return 0; 
}
//registro de seguimiento de las ordens de compra de los item grupo ess sas
function seguimientoComprasOrdenItem($id_orden_historico,$referencia,$descripcion,$cantidad,$link){
$query_seguimiento_ordenes=" INSERT INTO compra_orden_historico_item "
                          ." (COMPRA_ORDEN_HISTORICO_ITEM_COMPRA_ORDEN_HISTORICO_ID,COMPRA_ORDEN_HISTORICO_ITEM_COMPRA_ORDEN_REFERENCIA, COMPRA_ORDEN_HISTORICO_ITEM_COMPRA_ORDEN_DESCRIPCION, "
                          ." COMPRA_ORDEN_HISTORICO_ITEM_COMPRA_ORDEN_CANTIDAD)" 
                          ." VALUES ($id_orden_historico,'$referencia','$descripcion',$cantidad)";
$query_resultado=nmysql2_query($query_seguimiento_ordenes,$link);
}
////registro de seguimiento de las ordens de compra item ESS
function seguimientoComprasOrdenItemN2($id_orden_historico,$referencia,$descripcion,$cantidad,$serial,$valor,$descripcion_fabricante,$cantidad_pendiente,$link){

$query_seguimiento_ordenes=" INSERT INTO compra_orden_historico_item_ess "
                          ." (COMPRA_ORDEN_HISTORICO_ITEM_COMPRA_ORDEN_HISTORICO_ID,COMPRA_ORDEN_HISTORICO_ITEM_COMPRA_ORDEN_REFERENCIA, COMPRA_ORDEN_HISTORICO_ITEM_COMPRA_ORDEN_DESCRIPCION, "
                          ." COMPRA_ORDEN_HISTORICO_ITEM_COMPRA_ORDEN_CANTIDAD,"
                          ." COMPRA_ORDEN_HISTORICO_ITEM_COMPRA_ORDEN_SERIAL_NUMBER,"
                          ." COMPRA_ORDEN_HISTORICO_ITEM_COMPRA_ORDEN_VALUE_CUSTOMS_INVOICE,"
                          ." COMPRA_ORDEN_HISTORICO_ITEM_COMPRA_ORDEN_DESCRIPCION_FABRICANTE,COMPRA_ORDEN_HISTORICO_ITEM_COMPRA_ORDEN_CANTIDAD_PENDIENTE)" 
                          ." VALUES ($id_orden_historico,'$referencia','$descripcion',$cantidad,'$serial',$valor,'$descripcion_fabricante',$cantidad_pendiente)";
$query_resultado=nmysql2_query($query_seguimiento_ordenes,$link);
}
//guarda las refeerencias etiquetadas como referencias
function validarDuplicados($referencia,$bodega,$link){
$resultado=array();
$queryDuplicado = " SELECT ARTICULO_REFERENCIA,ARTICULO_REFERENCIA_ESS,ARTICULO_CANTIDAD"
            . " FROM articulo "
            . " WHERE ARTICULO_REFERENCIA='$referencia' AND ARTICULO_BODEGA=$bodega AND ARTICULO_UNIFICADO_ESTADO=1 AND ARTICULO_ESTADO=1";
$resultDuplicado = nmysql2_query($queryDuplicado,$link);
 if(nmysql2_num_rows($resultDuplicado)>0){
  while ($row = nmysql2_fetch_array($resultDuplicado)) {
  $resultado[0]=$row["ARTICULO_REFERENCIA"];
  $resultado[1]=$row["ARTICULO_REFERENCIA_ESS"];
  $resultado[2]=$row["ARTICULO_CANTIDAD"];
  }
 }
return $resultado;
}
//calcula el total de unidades de una referencia duplicada
function SumarDuplicados($referencia,$bodega,$link){
$resultado=array();
$queryDuplicado = " SELECT SUM(ARTICULO_CANTIDAD) as TOTAL"
            . " FROM articulo "
            . " WHERE ARTICULO_REFERENCIA_ESS='$referencia' AND ARTICULO_BODEGA=$bodega AND ARTICULO_UNIFICADO_ESTADO=1 AND ARTICULO_ESTADO=1";
$resultDuplicado = nmysql2_query($queryDuplicado,$link);
 if(nmysql2_num_rows($resultDuplicado)>0){
  while ($row = nmysql2_fetch_array($resultDuplicado)) {
  return $row["TOTAL"];
  }
 }
}
//valida si hay tintas por vencerse
function validarFechaTinta($referencia,$bodega,$link){
date_default_timezone_set("America/Bogota");
$revisada = date_create(date("Y-m-d"));

$query_fechas_tinta = " SELECT * FROM movimiento_item mvitem"
		    . " JOIN movimiento mv on mv.MOVIMIENTO_ID = mvitem.MOVIMIENTO_ID" 
		    . " JOIN movimiento_tipo mvtipo on mvtipo.MOVIMIENTO_ID=mv.MOVIMIENTO_TIPO" 
		    . " WHERE mvtipo.MOVIMIENTO_TIPO='ENTRADA'"
		    . " AND ARTICULO_REFERENCIA='$referencia'"
                    . " AND MOVIMIENTO_ITEM_BODEGA=$bodega"
                    . " AND MOVIMIENTO_ITEM_FECHA_TINTA IS NOT NULL";

$resultado_query_fecha=nmysql2_query($query_fechas_tinta,$link);
if(nmysql2_num_rows($resultado_query_fecha)>0){
 while($row=nmysql2_fetch_array($resultado_query_fecha)){ 
 $fecha_movimento=date_create(obtenerFecha($row["MOVIMIENTO_FECHA"]));
 $res=date_create(obtenerFecha($row["MOVIMIENTO_ITEM_FECHA_TINTA"]));
 //calcula las fechas de las tintas que se les haya colocado fecha de expedición
 if($res<=$fecha_movimento){
 $res->add(new DateInterval("P5Y"));
 }
 //calcula las fechas normales
 $ndias=date_diff($revisada,$res); 
 $ndias=$ndias->format("%R%a");
 $ndias=(int)$ndias;     
  if(($ndias<=60)&&($ndias>=0)){
  return true;
  }
  
 }
}
return false;
}
//registra cuando un articulo ha sido deshabilitado por ser duplicado
function registrar_articulo_deshabilitado($id_articulo,$referencia,$referencia_nueva,$movimiento_item_id,$cantidad,$cantidad_inicial,$usuario,$link){
$query_registro_deshabilitar=" INSERT INTO seguimiento_modificaciones" 
			    ." (SEGUIMIENTO_MODIFICACIONES_ARTICULO_ID,"
			    ." SEGUIMIENTO_MODIFICACIONES_REFERENCIA,"
			    ." SEGUIMIENTO_MODIFICACIONES_REFERENCIA_NUEVA,"
			    ." SEGUIMIENTO_MODIFICACIONES_MOVIMIENTO_ITEM_ID,"
			    ." SEGUIMIENTO_MODIFICACIONES_ARTICULO_CANTIDAD,"
			    ." SEGUIMIENTO_MODIFICACIONES_ARTICULO_CANTIDAD_INICIAL_MODIFICADO,"
			    ." SEGUIMIENTO_MODIFICACIONES_USUARIO_LOGIN)"
			    ." VALUES"
			    ." ($id_articulo,'$referencia','$referencia_nueva',$movimiento_item_id,$cantidad,$cantidad_inicial,'$usuario')";
$resultado_registro_deshabilitar=nmysql2_query($query_registro_deshabilitar,$link);
}
//optiene el nombre del pais utilizando el id
function obtenerNombrePais($codigo,$link){
$query=" SELECT PAIS_NOMBRE FROM pais WHERE PAIS_ID=$codigo";
$resultadoQuery=nmysql2_query($query,$link);
 if(nmysql2_num_rows($resultadoQuery)>0){
 $row=nmysql2_fetch_array($resultadoQuery);
 return $row["PAIS_NOMBRE"];
 }
return ""; 
}
//obtiene el grupo de un usuario usando su username
function obtenerIdGrupo($usuario_login,$link){
          $queryUsuario = " SELECT GRUPO_ID "
            . " FROM usuario u INNER JOIN grupo g ON g.GRUPO_ID=u.USUARIO_GRUPO_ID"
            . " WHERE USUARIO_LOGIN='$usuario_login'";
          $resultUsuario = nmysql2_query($queryUsuario,$link);
          while ($row = nmysql2_fetch_array($resultUsuario)) {
          $grupo = $row["GRUPO_ID"];
          return $grupo;
          }
return 0;          
}
//registra cuando se hace alguna modificación en el agendamiento
function registrarAgendamiento($fecha_registro,$id_agendamiento_inicial,$id_reagendamiento,$estado,$fecha_prestacion_servicio_inicial,$fecha_prestacion_servicio_final,$id_usuario,$fecha_agendamiento_tecnico,$link){
if(($fecha_agendamiento_tecnico=='NULL')||($fecha_agendamiento_tecnico==NULL)){
$fecha_agendamiento_tecnico="NULL";
}else{
$fecha_agendamiento_tecnico="'".$fecha_agendamiento_tecnico."'";
}

$usuario_canal_id=obtenerUsuarioCanalId($link);

$query_registro_agendamiento_seguimiento=" INSERT INTO tecnico_agendamiento_historico" 
			    ." (TECNICO_AGENDAMIENTO_HISTORICO_AGENDAMIENTO_FECHA_REGISTRO,"			    			    
			    ." TECNICO_AGENDAMIENTO_HISTORICO_CASO_INICIAL_AGENDAMIENTO_ID,"
			    ." TECNICO_AGENDAMIENTO_HISTORICO_REAGENDAMIENTO_AGENDAMIENTO_ID,"			    
			    ." TECNICO_AGENDAMIENTO_HISTORICO_ESTADO_CASO,"
			    ." TECNICO_AGENDAMIENTO_HISTORICO_FECHA_PRESTACION_SERVICIO_INICIO,"
			    ." TECNICO_AGENDAMIENTO_HISTORICO_FECHA_PRESTACION_SERVICIO_FINAL,"
			    ." TECNICO_AGENDAMIENTO_HISTORICO_USUARIO_ID,"
			    ." TECNICO_AGENDAMIENTO_HISTORICO_FECHA_TECNICO_AGENDAMIENTO,"
			    ." TECNICO_AGENDAMIENTO_HISTORICO_CANAL_ID) "
			    ." VALUES"
			    ." ('$fecha_registro',$id_agendamiento_inicial,$id_reagendamiento,$estado,$fecha_prestacion_servicio_inicial,$fecha_prestacion_servicio_final,$id_usuario,$fecha_agendamiento_tecnico,$usuario_canal_id)";

$resultado_registro_agendamiento_seguimiento=nmysql2_query($query_registro_agendamiento_seguimiento,$link);
}
//valida el ultimo id cuando se ha realizado el agendamiento
function validarUltimoReagendamiento($id_agendamiento,$link){
$res=0;
if(is_null($id_agendamiento)==false){
$query_ValidarUltimoReagendamiento=" SELECT MAX(TECNICO_AGENDAMIENTO_ID) AS TECNICO_AGENDAMIENTO_ID FROM tecnico_agendamiento WHERE TECNICO_AGENDAMIENTO_ESTADO<>0 AND TECNICO_AGENDAMIENTO_AGENDAMIENTO_PENDIENTE_ID=$id_agendamiento AND TECNICO_AGENDAMIENTO_DESHABILITADO<>1 ";
$resultado_query_ValidarUltimoReagendamiento=nmysql2_query($query_ValidarUltimoReagendamiento,$link);
 if(nmysql2_num_rows($resultado_query_ValidarUltimoReagendamiento)>0){
  while($row = nmysql2_fetch_array($resultado_query_ValidarUltimoReagendamiento)){
  $res = $row["TECNICO_AGENDAMIENTO_ID"];
  }
 }
} 
return $res; 
}
//valida si se hizo un reagendamiento de un servicio
function validarExistenciaReagendamiento($id_agendamiento,$link){
$res=false;
$query_ValidarExistenciaReagendamiento=" SELECT TECNICO_AGENDAMIENTO_ID FROM tecnico_agendamiento WHERE TECNICO_AGENDAMIENTO_ESTADO<>0 AND TECNICO_AGENDAMIENTO_AGENDAMIENTO_PENDIENTE_ID=$id_agendamiento AND TECNICO_AGENDAMIENTO_DESHABILITADO<>1 ";
$resultado_query_ValidarExistenciaReagendamiento=nmysql2_query($query_ValidarExistenciaReagendamiento,$link);
 if(nmysql2_num_rows($resultado_query_ValidarExistenciaReagendamiento)>0){
 $res=true;
 } 
return $res; 
}
//obtiene los reportes de los tecnicos de un determinado agendamiento
function obtenerReporteTecnico($id_agendamiento,$id_agendamiento_inicial,$validar_ultimo_agendamiento,$link){
$res="";
if($validar_ultimo_agendamiento==1){
$query_reporteTecnico=" SELECT TECNICO_AGENDAMIENTO_TECNICO_REPORTE_VISITA FROM tecnico_agendamiento WHERE TECNICO_AGENDAMIENTO_AGENDAMIENTO_PENDIENTE_ID=$id_agendamiento_inicial AND TECNICO_AGENDAMIENTO_ESTADO<>0 AND TECNICO_AGENDAMIENTO_DESHABILITADO<>1 ";
}
if($validar_ultimo_agendamiento==0){
$query_reporteTecnico=" SELECT TECNICO_AGENDAMIENTO_TECNICO_REPORTE_VISITA FROM tecnico_agendamiento WHERE TECNICO_AGENDAMIENTO_AGENDAMIENTO_PENDIENTE_ID=$id_agendamiento_inicial AND TECNICO_AGENDAMIENTO_ESTADO<>0 AND TECNICO_AGENDAMIENTO_ID<=$id_agendamiento AND TECNICO_AGENDAMIENTO_DESHABILITADO<>1 ";
}
$resultado_query_reporteTecnico=nmysql2_query($query_reporteTecnico,$link);
 if(nmysql2_num_rows($resultado_query_reporteTecnico)>0){
  while($row=nmysql2_fetch_assoc($resultado_query_reporteTecnico)){
   if(is_null($row["TECNICO_AGENDAMIENTO_TECNICO_REPORTE_VISITA"])==false){
   $res.=$row["TECNICO_AGENDAMIENTO_TECNICO_REPORTE_VISITA"]."\n";  
   }
  } 
 } 
return $res; 
}
//obtiene el nombre del canal de un usuario usando su username
function obtenerNombreCanalUsuario($usuario_login,$link){
          $queryUsuario = " SELECT USUARIO_CANAL_NOMBRE "
            . " FROM usuario "
            . " WHERE USUARIO_LOGIN='$usuario_login'";
          $resultUsuario = nmysql2_query($queryUsuario,$link);
          while ($row = nmysql2_fetch_array($resultUsuario)) {
          $nombre_canal = $row["USUARIO_CANAL_NOMBRE"];
          return $nombre_canal;
          }
return 0;          
}
//Valida si hubo un reporte de un agendamiento
function obtenerEstadoReporteAgendamiento($id_agendamiento,$link){
$estado_reporte=array();

$estado_reporte[0]=false;
$estado_reporte[1]=false;
$estado_reporte[2]=false;
$estado_reporte[3]=false;
$estado_reporte[4]=false;
$estado_reporte[5]=false;
$estado_reporte[6]="";
$estado_reporte[7]="";

          $queryInstalacion = " SELECT INSTALACION_ID,INSTALACION_CONSECUTIVO,INSTALACION_CLIENTE_NO_FIRMA,INSTALACION_CLIENTE_MOTIVO_NO_FIRMA "
            . " FROM instalacion "
            . " WHERE INSTALACION_AGENDAMIENTO_ID='$id_agendamiento'";
          $resultInstalacion = nmysql2_query($queryInstalacion,$link);
          if(nmysql2_num_rows($resultInstalacion)>0){
          
           while($row=nmysql2_fetch_assoc($resultInstalacion)){
           $instalacion_id=$row["INSTALACION_ID"];
           $cliente_no_firma=$row["INSTALACION_CLIENTE_NO_FIRMA"];
           $cliente_motivo_no_firma=$row["INSTALACION_CLIENTE_MOTIVO_NO_FIRMA"];
           }
          
          $estado_reporte[0]=true;
           $query_firma="SELECT INSTALACION_IMAGEN_CLIENTE_ID FROM instalacion_imagen_cliente WHERE INSTALACION_IMAGEN_CLIENTE_INSTALACION_ID=".$instalacion_id.";";
           $resultado_query_firma = nmysql2_query($query_firma,$link);
           if(nmysql2_num_rows($resultado_query_firma)>0){
           $estado_reporte[2]=true;
           }
           if($cliente_no_firma==1){
           $estado_reporte[4]=true;
           $estado_reporte[6]=$cliente_motivo_no_firma;
           }
          }
          $queryServicioTecnico = " SELECT REPORTE_ID,REPORTE_INSTALACION_CONSECUTIVO,REPORTE_CLIENTE_NO_FIRMA,REPORTE_CLIENTE_MOTIVO_NO_FIRMA "
            . " FROM reporte "
            . " WHERE REPORTE_AGENDAMIENTO_ID='$id_agendamiento'";
          $resultServicioTecnico = nmysql2_query($queryServicioTecnico,$link);
          if(nmysql2_num_rows($resultServicioTecnico)>0){
          
           while($row=nmysql2_fetch_assoc($resultServicioTecnico)){
           $reporte_id=$row["REPORTE_ID"];
           $cliente_no_firma=$row["REPORTE_CLIENTE_NO_FIRMA"];
           $cliente_motivo_no_firma=$row["REPORTE_CLIENTE_MOTIVO_NO_FIRMA"];
           }

           $estado_reporte[1]=true;
           $query_firma="SELECT REPORTE_IMAGEN_CLIENTE_ID FROM reporte_imagen_cliente WHERE REPORTE_IMAGEN_CLIENTE_REPORTE_ID=".$reporte_id.";";
           $resultado_query_firma = nmysql2_query($query_firma,$link);
           if(nmysql2_num_rows($resultado_query_firma)>0){
           $estado_reporte[3]=true;
           }
           if($cliente_no_firma==1){
           $estado_reporte[5]=true;
           $estado_reporte[7]=$cliente_motivo_no_firma;
           }
          }
          /*
          $query_reporteTecnico=" SELECT TECNICO_AGENDAMIENTO_ID FROM tecnico_agendamiento WHERE TECNICO_AGENDAMIENTO_AGENDAMIENTO_PENDIENTE_ID=$id_agendamiento AND TECNICO_AGENDAMIENTO_DESHABILITADO<>1";
          $resultado_query_reporteTecnico=nmysql2_query($query_reporteTecnico,$link);
           if(nmysql2_num_rows($resultado_query_reporteTecnico)>0){
            while($row=nmysql2_fetch_assoc($resultado_query_reporteTecnico)){
            $id_reagendamiento=$row["TECNICO_AGENDAMIENTO_ID"];                          
	      $queryInstalacion = " SELECT INSTALACION_CONSECUTIVO "
		. " FROM instalacion "
		. " WHERE INSTALACION_AGENDAMIENTO_ID='$id_reagendamiento'";
	      $resultInstalacion = nmysql2_query($queryInstalacion,$link);
	      if(nmysql2_num_rows($resultInstalacion)>0){
	      return true;
	      }
	      $queryServicioTecnico = " SELECT REPORTE_INSTALACION_CONSECUTIVO "
		. " FROM reporte "
		. " WHERE REPORTE_AGENDAMIENTO_ID='$id_reagendamiento'";
	      $resultServicioTecnico = nmysql2_query($queryServicioTecnico,$link);
	      if(nmysql2_num_rows($resultServicioTecnico)>0){
	      return true;
	      }            
            }
           }          
           */
return $estado_reporte;          
}
//Valida si hubo un reporte de un agendamiento
function obtenerEstadoReporteReagendamiento($id_agendamiento,$link){
$estado_reporte=array();

$estado_reporte[0]=false;
$estado_reporte[1]=false;
$estado_reporte[2]=false;
$estado_reporte[3]=false;
$estado_reporte[4]=false;
$estado_reporte[5]=false;
$estado_reporte[6]="";
$estado_reporte[7]="";

          $queryInstalacion = " SELECT INSTALACION_ID,INSTALACION_CONSECUTIVO,INSTALACION_CLIENTE_NO_FIRMA,INSTALACION_CLIENTE_MOTIVO_NO_FIRMA "
            . " FROM instalacion "
            . " WHERE INSTALACION_AGENDAMIENTO_ID='$id_agendamiento'";
          $resultInstalacion = nmysql2_query($queryInstalacion,$link);
          if(nmysql2_num_rows($resultInstalacion)>0){
          
           while($row=nmysql2_fetch_assoc($resultInstalacion)){
           $instalacion_id=$row["INSTALACION_ID"];
           $cliente_no_firma=$row["INSTALACION_CLIENTE_NO_FIRMA"];
           $cliente_motivo_no_firma=$row["INSTALACION_CLIENTE_MOTIVO_NO_FIRMA"];
           }
          
          $estado_reporte[0]=true;
           $query_firma="SELECT INSTALACION_IMAGEN_CLIENTE_ID FROM instalacion_imagen_cliente WHERE INSTALACION_IMAGEN_CLIENTE_INSTALACION_ID=".$instalacion_id.";";
           $resultado_query_firma = nmysql2_query($query_firma,$link);
           if(nmysql2_num_rows($resultado_query_firma)>0){
           $estado_reporte[2]=true;
           }
           if($cliente_no_firma==1){
           $estado_reporte[4]=true;
           $estado_reporte[6]=$cliente_motivo_no_firma;
           }
          }
          $queryServicioTecnico = " SELECT REPORTE_ID,REPORTE_INSTALACION_CONSECUTIVO,REPORTE_CLIENTE_NO_FIRMA,REPORTE_CLIENTE_MOTIVO_NO_FIRMA "
            . " FROM reporte "
            . " WHERE REPORTE_AGENDAMIENTO_ID='$id_agendamiento'";
          $resultServicioTecnico = nmysql2_query($queryServicioTecnico,$link);
          if(nmysql2_num_rows($resultServicioTecnico)>0){
          
           while($row=nmysql2_fetch_assoc($resultServicioTecnico)){
           $reporte_id=$row["REPORTE_ID"];
           $cliente_no_firma=$row["REPORTE_CLIENTE_NO_FIRMA"];
           $cliente_motivo_no_firma=$row["REPORTE_CLIENTE_MOTIVO_NO_FIRMA"];
           }

           $estado_reporte[1]=true;
           $query_firma="SELECT REPORTE_IMAGEN_CLIENTE_ID FROM reporte_imagen_cliente WHERE REPORTE_IMAGEN_CLIENTE_REPORTE_ID=".$reporte_id.";";
           $resultado_query_firma = nmysql2_query($query_firma,$link);
           if(nmysql2_num_rows($resultado_query_firma)>0){
           $estado_reporte[3]=true;
           }
           if($cliente_no_firma==1){
           $estado_reporte[5]=true;
           $estado_reporte[7]=$cliente_motivo_no_firma;
           }
          }
return $estado_reporte;          
}
//obtiene el nombre del canal de un usuario usando su username
function obtenerTelefonoUsuario($usuario_id,$link){
          $queryUsuario = " SELECT USUARIO_TEL1 "
            . " FROM usuario "
            . " WHERE USUARIO_ID='$usuario_id' AND USUARIO_TEL1 IS NOT NULL AND USUARIO_TEL1<>'0'";
          $resultUsuario = nmysql2_query($queryUsuario,$link);
          while ($row = nmysql2_fetch_array($resultUsuario)) {
          $telefono = $row["USUARIO_TEL1"];
          return $telefono;
          }
return 0;          
}
//valida si una orden de instalación fue agregada a una agendamiento
function obtenerEstadoServicioOrden($id_ServicioOrden,$link){
          $queryServicioOrden = " SELECT TECNICO_AGENDAMIENTO_SERVICIO_ORDEN_ID "
            . " FROM tecnico_agendamiento "
            . " WHERE TECNICO_AGENDAMIENTO_SERVICIO_ORDEN_ID='$id_ServicioOrden' AND TECNICO_AGENDAMIENTO_DESHABILITADO<>1 ";
          $resultServicioOrden = nmysql2_query($queryServicioOrden,$link);
          if(nmysql2_num_rows($resultServicioOrden)>0) {
          return true;
          }
return false;          
}
//cambia el estado de una orden de instalación
function cambiarEstadoAgendOrdenServicio($id_ServicioOrden,$link){
          $queryServicioOrden = " UPDATE servicio_orden"
            . " SET SERVICIO_ORDEN_ESTADO=2 "
            . " WHERE SERVICIO_ORDEN_ID='$id_ServicioOrden'";
          $resultServicioOrden = nmysql2_query($queryServicioOrden,$link);
          if(nmysql2_affected_rows($link)>0) {
          return true;
          }
return false;          
}
//notifica un agendamiento por whatsapp
function notificarAgendamientoW($id_agendamiento,$link){
$confirmacion_envio_mensaje=array();
$confirmacion_envio_mensaje[0]=false;
$confirmacion_envio_mensaje[3]=0;

//Carga array que muestra el dia	 
$semana = array('Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado');

  $query_agendamiento=" SELECT "
		    ." TECNICO_AGENDAMIENTO_ID, "
		    ." TECNICO_AGENDAMIENTO_FECHA_REGISTRO, "
		    ." TECNICO_AGENDAMIENTO_FECHA_LLAMADA, "
		    ." TECNICO_AGENDAMIENTO_CIUDAD, "
		    ." TECNICO_AGENDAMIENTO_EMPRESA, "
		    ." TECNICO_AGENDAMIENTO_CONTACTO, "
		    ." TECNICO_AGENDAMIENTO_CELULAR, "
		    ." TECNICO_AGENDAMIENTO_DIRECCION," 
		    ." TECNICO_AGENDAMIENTO_APLICACION, " 
		    ." TECNICO_AGENDAMIENTO_FECHA_COMPRA, "
		    ." TECNICO_AGENDAMIENTO_DANO_REPORTADO, " 
		    ." TECNICO_AGENDAMIENTO_TIPO_DE_SERVICIO, " 
		    ." TECNICO_AGENDAMIENTO_FECHA_AGENDAMIENTO_TECNICO,"
		    ." TECNICO_AGENDAMIENTO_TECNICO_ID, "
		    ." TECNICO_AGENDAMIENTO_OBSERVACION," 
		    ." TECNICO_AGENDAMIENTO_ESTADO, "
		    ." TECNICO_AGENDAMIENTO_TECNICO_FECHA_REVISION, "
		    ." TECNICO_AGENDAMIENTO_TECNICO_REPORTE_VISITA, "
		    ." TECNICO_AGENDAMIENTO_FECHA_CASO_CERRADO, "
		    ." TECNICO_AGENDAMIENTO_TECNICO_REPORTE_VISITA, "
		    ." TECNICO_AGENDAMIENTO_TECNICO_FECHA_PRESTACION_SERVICIO_INICIO,"
		    ." TECNICO_AGENDAMIENTO_TECNICO_FECHA_PRESTACION_SERVICIO_FINAL,"
		    ." TECNICO_AGENDAMIENTO_AGENDAMIENTO_PENDIENTE_ID,"
		    ." TECNICO_USUARIO_ID,"
		    ." TECNICO_NOMBRE,"
		    ." TECNICO_ID,"
		    ." TECNICO_AGENDAMIENTO_NOMBRE_PERSONA_CAMBIO_DE_TINTA,"
		    ." TECNICO_AGENDAMIENTO_SERVICIO_APROBADO,"
		    ." TECNICO_AGENDAMIENTO_EMAIL"
		    ." FROM tecnico_agendamiento "
		    ." JOIN tecnico ON TECNICO_ID=TECNICO_AGENDAMIENTO_TECNICO_ID "
		    ." WHERE TECNICO_AGENDAMIENTO_ID=$id_agendamiento";

  $resultado_agendamiento = nmysql2_query($query_agendamiento,$link); 		   
		    
  if (nmysql2_num_rows($resultado_agendamiento)!=0){
  while($row = nmysql2_fetch_array($resultado_agendamiento)){
    $fecha_registro = $row["TECNICO_AGENDAMIENTO_FECHA_REGISTRO"];
    $fecha_llamada = $row["TECNICO_AGENDAMIENTO_FECHA_LLAMADA"];
    $ciudad = $row["TECNICO_AGENDAMIENTO_CIUDAD"];
    $empresa = $row["TECNICO_AGENDAMIENTO_EMPRESA"];
    $celular = $row["TECNICO_AGENDAMIENTO_CELULAR"];
    $contacto = $row["TECNICO_AGENDAMIENTO_CONTACTO"];
    $direccion = $row["TECNICO_AGENDAMIENTO_DIRECCION"];
    $aplicacion = $row["TECNICO_AGENDAMIENTO_APLICACION"];
    $dano_reportado = $row["TECNICO_AGENDAMIENTO_DANO_REPORTADO"];
    $valor_tipo_servicio = $row["TECNICO_AGENDAMIENTO_TIPO_DE_SERVICIO"];
    $fecha_agendamiento_tecnico = $row["TECNICO_AGENDAMIENTO_FECHA_AGENDAMIENTO_TECNICO"];
    $observacion = $row["TECNICO_AGENDAMIENTO_OBSERVACION"];
    $tecnico_nombre = $row["TECNICO_NOMBRE"];
    $fecha_compra = $row["TECNICO_AGENDAMIENTO_FECHA_COMPRA"];
    $id_tecnico = $row["TECNICO_ID"];
    $estado = $row["TECNICO_AGENDAMIENTO_ESTADO"];
    $tecnico_reporte_visita = $row["TECNICO_AGENDAMIENTO_TECNICO_REPORTE_VISITA"];
    $fecha_prestacion_servicio_inicio = $row["TECNICO_AGENDAMIENTO_TECNICO_FECHA_PRESTACION_SERVICIO_INICIO"]; 
    $fecha_prestacion_servicio_final = $row["TECNICO_AGENDAMIENTO_TECNICO_FECHA_PRESTACION_SERVICIO_FINAL"]; 
    $id_tecnico_usuario=$row["TECNICO_USUARIO_ID"];
    $fecha_tecnico_revision=$row["TECNICO_AGENDAMIENTO_TECNICO_FECHA_REVISION"];
    $id_agendamiento_pendiente=$row["TECNICO_AGENDAMIENTO_AGENDAMIENTO_PENDIENTE_ID"];
    $nombre_persona_cambio_de_tinta=$row["TECNICO_AGENDAMIENTO_NOMBRE_PERSONA_CAMBIO_DE_TINTA"];
    $servicio_aprobado=$row["TECNICO_AGENDAMIENTO_SERVICIO_APROBADO"];
    $email=$row["TECNICO_AGENDAMIENTO_EMAIL"];
  }
}  


if($valor_tipo_servicio=="0") {$tipo_servicio_nombre="Fuera de Garantía";}
if($valor_tipo_servicio=="1") {$tipo_servicio_nombre="GARANTIA";}
if($valor_tipo_servicio=="2") {$tipo_servicio_nombre="Cambio de Tinta";}
if($valor_tipo_servicio=="3") {$tipo_servicio_nombre="Mantenimiento General";}
if($valor_tipo_servicio=="4") {$tipo_servicio_nombre="Servicio técnico";}
if($valor_tipo_servicio=="5") {$tipo_servicio_nombre="Facturado";}
if($valor_tipo_servicio=="6") {$tipo_servicio_nombre="Perfilación (Obsequio)";}
if($valor_tipo_servicio=="7") {$tipo_servicio_nombre="Perfilación (Facturado)";}
if($valor_tipo_servicio=="8") {$tipo_servicio_nombre="Instalación";}
if($valor_tipo_servicio=="9") {$tipo_servicio_nombre="Online";}

if($estado==0)
$nombre_estado="Anulado";
if($estado==1)
$nombre_estado="Abierto";
if($estado==2)
$nombre_estado="Cerrado";

$frecibSplit1 = explode(" ",$fecha_agendamiento_tecnico);
$frecibSplit2 = explode("-",$frecibSplit1[0]);
$diaSemanaServicio = date("w",mktime(0,0,0,$frecibSplit2[1],$frecibSplit2[2],$frecibSplit2[0]));
if ($diaSemanaServicio==0) $diaSemanaServicio = $semana[$diaSemanaServicio];
else $diaSemanaServicio = $semana[$diaSemanaServicio];

   $rusuario=$_SESSION['nombreuser'];     
   //consulta el id del usuario
   $query_usuario = "SELECT USUARIO_ID FROM usuario WHERE USUARIO_LOGIN='$rusuario'";
   $resultado_query_usuario = nmysql2_query($query_usuario,$link);    
   if(nmysql2_num_rows($resultado_query_usuario)>0){ 
   $row = nmysql2_fetch_array($resultado_query_usuario);
   $id_usuario=$row["USUARIO_ID"];
   } 

$telefono_envio=obtenerTelefonoUsuario($id_tecnico_usuario,$link);                       
if($telefono_envio>0){
 $confirmacion_envio_mensaje=enviarMensajeW("Nuevo agendamiento\n------------------------------\n\nAgendamiento No:$id_agendamiento\n\nEmpresa: $empresa\nDirección: $direccion\nCiudad: $ciudad\nContacto: $contacto\nCelular: $celular\n\nFecha del servicio:\n$fecha_agendamiento_tecnico $diaSemanaServicio \nTipo de servicio:\n$tipo_servicio_nombre\n\nAplicación:\n$aplicacion\nDaño reportado:\n$dano_reportado\n\nFecha de compra: $fecha_compra\n\nObservaciones:\n$observacion\n\n",$telefono_envio,6,$id_agendamiento);
  if($confirmacion_envio_mensaje[0]){
  return $confirmacion_envio_mensaje;  
  }                         
}
else{
$confirmacion_envio_mensaje[3]=-1;
return $confirmacion_envio_mensaje;
}

return $confirmacion_envio_mensaje;
}
//contiene las cantidades auxiliares de una referencia
function validarCantidadesAux($cantidad,$referencia,$bodega,$link){
$resultado=array();

$resultado[0]=0;
$resultado[1]=0;
$resultado[2]=0;
$resultado[3]=0;
$resultado[4]=0;
$resultado[5]=0;

$queryCantidadAux = " SELECT ARTICULO_AUX_CANTIDAD,ARTICULO_AUX_TIPO_NOMBRE"
            . " FROM articulo_aux JOIN articulo_aux_tipo ON ARTICULO_AUX_ARTICULO_AUX_TIPO_ID=ARTICULO_AUX_TIPO_ID"
            . " WHERE ARTICULO_AUX_ARTICULO_REFERENCIA='$referencia' AND ARTICULO_AUX_BODEGA_ID=$bodega";
$resultCantidadAux = nmysql2_query($queryCantidadAux,$link);
 if(nmysql2_num_rows($resultCantidadAux)>0){
  while ($row = nmysql2_fetch_array($resultCantidadAux)) {
  $cantidad_revisada=$row["ARTICULO_AUX_CANTIDAD"];
  $cantidad_tipo=$row["ARTICULO_AUX_TIPO_NOMBRE"];
   if($cantidad_tipo=="USADAS"){
   $resultado[1]+=$cantidad_revisada;
   }   
   if($cantidad_tipo=="SIN ARREGLAR"){
   $resultado[2]+=$cantidad_revisada;
   }   
   if($cantidad_tipo=="USADAS ARREGLADAS VENTA"){
   $resultado[3]+=$cantidad_revisada;
   }   
   if($cantidad_tipo=="NUEVA"){
   $resultado[4]+=$cantidad_revisada;
   }   
  }
 }
 
$queryCantidadAux = " SELECT ARTICULO_AUX_APARTADO_CANTIDAD"
            . " FROM articulo_aux_reserva JOIN articulo_aux_tipo ON ARTICULO_AUX_APARTADO_ARTICULO_AUX_TIPO_ID=ARTICULO_AUX_TIPO_ID"
            . " WHERE ARTICULO_AUX_APARTADO_REFERENCIA='$referencia' AND ARTICULO_AUX_APARTADO_BODEGA_ID=$bodega AND ARTICULO_AUX_APARTADO_ESTADO=1";
$resultCantidadAux = nmysql2_query($queryCantidadAux,$link); 
 if(nmysql2_num_rows($resultCantidadAux)>0){
  while ($row = nmysql2_fetch_array($resultCantidadAux)) {
  $resultado[5]+=$row["ARTICULO_AUX_APARTADO_CANTIDAD"];
  }
 }
 
$resultado[0]=$cantidad-($resultado[2]+$resultado[5]);
return $resultado;
}
//registra el estado de un articulo
function RegistrarEstadoArticulo($movimiento_item_id,$referencia,$bodega,$tipo_estado,$tipo_movimiento,$cantidad,$link){

 $query_existencia_articulo="SELECT ARTICULO_AUX_CANTIDAD FROM articulo_aux "
                          ." WHERE ARTICULO_AUX_ARTICULO_REFERENCIA='".nmysql2_escape_string($referencia,$link)."' "
                          ." AND ARTICULO_AUX_BODEGA_ID=$bodega " 
                          ." AND ARTICULO_AUX_ARTICULO_AUX_TIPO_ID=$tipo_estado";

 $result=  nmysql2_query($query_existencia_articulo,$link);  
 
 if(nmysql2_num_rows($result)>0){
 
 $row=  nmysql2_fetch_array($result);
 $existencias=$row['ARTICULO_AUX_CANTIDAD'];
 
 if($tipo_movimiento=="ENTRADA"){
 $operacion="+";
 }
 if($tipo_movimiento=="SALIDA"){
 $operacion="-";
 }
 
 $actualiza_existencias="UPDATE articulo_aux SET ARTICULO_AUX_CANTIDAD=ARTICULO_AUX_CANTIDAD $operacion $cantidad "
                      . "WHERE ARTICULO_AUX_ARTICULO_REFERENCIA='".nmysql2_escape_string($referencia,$link)."' " 
                      . "AND ARTICULO_AUX_BODEGA_ID=$bodega "
                      . "AND ARTICULO_AUX_ARTICULO_AUX_TIPO_ID=$tipo_estado";
 $query_actualiza_existencias=nmysql2_query($actualiza_existencias,$link);
 }
 else{
 $actualiza_existencias="INSERT INTO articulo_aux (ARTICULO_AUX_ARTICULO_REFERENCIA, ARTICULO_AUX_BODEGA_ID,ARTICULO_AUX_ARTICULO_AUX_TIPO_ID,ARTICULO_AUX_CANTIDAD)"
                       ."VALUES ('".nmysql2_escape_string($referencia,$link)."',$bodega,$tipo_estado,$cantidad)";

 $query_actualiza_existencias=nmysql2_query($actualiza_existencias,$link); 
 }
 
 if (nmysql2_affected_rows($link)>0)
 {
 $queryEstadoArticulo = "INSERT INTO movimiento_item_aux (MOVIMIENTO_ITEM_AUX_MOVIMIENTO_ITEM_ID,"
		   ."MOVIMIENTO_ITEM_AUX_REFERENCIA,"
		   ."MOVIMIENTO_ITEM_AUX_BODEGA_ID,"
		   ."MOVIMIENTO_ITEM_AUX_ARTICULO_AUX_TIPO_ID,"
		   ."MOVIMIENTO_ITEM_AUX_CANTIDAD)" 
                   ."VALUES ($movimiento_item_id,'$referencia',$bodega,$tipo_estado,$cantidad)";
 $queryEstadoArticulo = nmysql2_query($queryEstadoArticulo,$link);
  if(nmysql2_affected_rows($link)>0)
  {    
    return true;
  } 
 }
 
 return false;  
}
//deshabilita volver a una plantilla
function RetornoPlantilla($nombre,$valor){
$nombre_variable="deshabilitar_retorno_".$nombre;
$_SESSION["$nombre_variable"]=$valor; 
}
//obtiene el estado de deshabilita volver a una plantilla
function ObtenerEstRetornoPlantilla($nombre){
$nombre_variable="deshabilitar_retorno_".$nombre;
 if(isset($_SESSION["$nombre_variable"])){
 return $_SESSION["$nombre_variable"];
 }
 else{
 return -1;
 }
}
//registra un id al guardar una plantilla
function RegistrarIdRevPlantilla($nombre,$valor){
 if(!isset($_SESSION['id_registro_revisado_plantilla'])){
 $_SESSION['id_registro_revisado_plantilla']=array();
 $_SESSION['id_registro_revisado_plantilla']["$nombre"]=$valor;
 }else{
 $_SESSION['id_registro_revisado_plantilla']["$nombre"]=$valor;
 }  
}
//registra un id al guardar una plantilla
function ObtenerIdRevPlantilla($nombre){
 if(isset($_SESSION['id_registro_revisado_plantilla'])){
  if(array_key_exists("$nombre",$_SESSION["id_registro_revisado_plantilla"])){
  return $_SESSION['id_registro_revisado_plantilla']["$nombre"];  
  }
  else{
  return -1;
  }  
 }else{
 return -1;
 }
}
//formatea datos tipo texto
function FormatearDatosEscStringT($texto,$link){
$texto1=preg_replace('[\n|\r|\n\r|\t|\x0B]',' ', trim($texto));
$texto1=preg_replace('[\0]', ' ', $texto1);
$texto1=trim($texto1);
$texto1=suprimirDuplicadoEspaciosTexto($texto1);
return nmysql2_escape_string($texto1,$link);
}
//formatea datos tipo texto tipo char
function FormatearDatosEscStringV($texto,$link){
$texto1=preg_replace('[\n|\r|\n\r|\t|\x0B]',' ', trim($texto));
$texto1=preg_replace('[\0]', ' ', $texto1);
$texto1=trim($texto1);
$texto1=suprimirDuplicadoEspaciosTexto($texto1);
return nmysql2_escape_string($texto1,$link);
}
//formatea texto pero sin cambiar el formato del texto
function FormatearDatosEscTextoConFormat($texto,$link){
$texto1=trim($texto);
$texto1=suprimirDuplicadoEspaciosTexto($texto1);
return nmysql2_escape_string($texto1,$link);
}
//formatea datos tipo texto tipo char
function FDatosEscStringV($texto){
$texto1=preg_replace('[\n|\r|\n\r|\t|\x0B]',' ', trim($texto));
$texto1=preg_replace('[\0]', ' ', $texto1);
$texto1=trim($texto1);
$texto1=suprimirDuplicadoEspaciosTexto($texto1);
return $texto1;
}
//valida si hay permisos para poder acceder al menu
function validarPermisoMenu($menu_id,$link){
$resultado=false;
$numero_autorizados=0;
	
 $query_menu = "SELECT SERVICIO_ID "
			 ."FROM menu "
			 ."JOIN servicio ON MENU_ID=SERVICIO_MENU_ID "
			 ."where MENU_ID = $menu_id and MENU_ESTADO = 1 "
			 ."GROUP BY SERVICIO_ID;";
 
 $menu = nmysql2_query($query_menu,$link);
 
 if (nmysql2_num_rows($menu)>0){
 
  while($row = nmysql2_fetch_array($menu)) {
     $menu_servicio_id = $row["SERVICIO_ID"];	 
	 $permiso=consultaPermisoUsuario($menu_servicio_id,$link);
	 $permisoList=$permiso[0];
	 if($permisoList!=0){
	 $numero_autorizados+=1;	   
	 }
  }
}

if($numero_autorizados>0){
$resultado=true;	
}

return $resultado;
}
//valida si hay permisos para poder acceder al modulo
function validarPermisoModulo($modulo_id,$link){
$resultado=false;
$numero_autorizados=0;

 $query_menu = "SELECT SERVICIO_ID "
			 ."FROM menu "
			 ."JOIN modulo ON MODULO_ID=MENU_MODULO_ID "
			 ."JOIN servicio ON MENU_ID=SERVICIO_MENU_ID "
			 ."where MODULO_ID = $modulo_id and MODULO_ESTADO = 1 "
			 ."GROUP BY SERVICIO_ID;";
 
 $menu = nmysql2_query($query_menu,$link);
 
 if (nmysql2_num_rows($menu)>0){
 
  while($row = nmysql2_fetch_array($menu)) {
     $menu_servicio_id = $row["SERVICIO_ID"];	 
	 $permiso=consultaPermisoUsuario($menu_servicio_id,$link);
	 $permisoList=$permiso[0];
	 if($permisoList!=0){
	 $numero_autorizados+=1;	   
	 }
  }
}

if($numero_autorizados>0){
$resultado=true;	
}

return $resultado;
}
//valida si esta habilitado el menu para la plantilla de aplicacion
function validarMenuPlantillaAplicacion($menu_id,$plantilla_aplicacion_id,$link){
$resultado=false;
$numero_autorizados=0;

 $query_menu = "SELECT SERVICIO_PLANTILLA_APLICACION_ID "
			 ."FROM menu "
			 ."JOIN modulo ON MODULO_ID=MENU_MODULO_ID "
			 ."JOIN servicio ON MENU_ID=SERVICIO_MENU_ID "
			 ."where MENU_ID = $menu_id and MENU_ESTADO = 1 "
			 ."GROUP BY SERVICIO_ID;";
 
 $menu = nmysql2_query($query_menu,$link);
 
 if (nmysql2_num_rows($menu)>0){
 
  while($row = nmysql2_fetch_array($menu)) {
  $menu_servicio_plantilla_aplicacion_id=$row["SERVICIO_PLANTILLA_APLICACION_ID"];
  
  $revision_menu_plantilla_aplicacion=array(); 
  
  $revision_menu_plantilla_aplicacion=explode(",",$menu_servicio_plantilla_aplicacion_id);
  
  for($i=0;$i<count($revision_menu_plantilla_aplicacion);$i++){
   if($plantilla_aplicacion_id==$revision_menu_plantilla_aplicacion[$i]){
   $numero_autorizados+=1;
   }
  }
  
  }
}

if($numero_autorizados>0){
$resultado=true;	
}

return $resultado;	
}
//valida si esta habilitado el servicio para la plantilla de aplicacion
function validarServicioPlantillaAplicacion($servicio_id,$plantilla_aplicacion_id,$link){
$resultado=false;
$numero_autorizados=0;

 $query_menu = "SELECT SERVICIO_PLANTILLA_APLICACION_ID "
			 ."FROM menu "
			 ."JOIN servicio ON MENU_ID=SERVICIO_MENU_ID "
			 ."where SERVICIO_ID = $servicio_id and SERVICIO_VISIBLE = 1;";

 $menu = nmysql2_query($query_menu,$link);
 
 if (nmysql2_num_rows($menu)>0){
 
  while($row = nmysql2_fetch_array($menu)) {
  $menu_servicio_plantilla_aplicacion_id=$row["SERVICIO_PLANTILLA_APLICACION_ID"];
  
  $busqueda_menu=false;
  $revision_menu_plantilla_aplicacion=array(); 
  
  $revision_menu_plantilla_aplicacion=explode(",",$menu_servicio_plantilla_aplicacion_id);
  
  for($i=0;$i<count($revision_menu_plantilla_aplicacion);$i++){
   if($plantilla_aplicacion_id==$revision_menu_plantilla_aplicacion[$i]){
   $resultado=true;
   }
  }
  
  }
}

return $resultado;	
}

//obtiene el nombre de una bodega usando el id
function obtenerNombreBodegaCodigoBarras($id_bodega,$link){
    $query = "SELECT BODEGA_NOMBRE FROM bodega WHERE BODEGA_ID=$id_bodega";
    $query = nmysql2_query($query,$link);    
    if(nmysql2_num_rows($query)>0){ 
    $row = nmysql2_fetch_array($query);
    return $row["BODEGA_NOMBRE"];
    }
}
//obtiene el def de una bodega usando el id
function obtenerDefBodegaCodigoBarras($id_bodega,$link){
    $query = "SELECT BODEGA_DEF FROM bodega WHERE BODEGA_ID=$id_bodega";
    $query = nmysql2_query($query,$link);    
    if(nmysql2_num_rows($query)>0){ 
    $row = nmysql2_fetch_array($query);
    return $row["BODEGA_DEF"];
    }
}
//obtiene el def de una bodega usando el id
function obtenerDefBodegaCanalIdentificacion($id_bodega,$link){
    $query = "SELECT BODEGA_CANAL FROM bodega WHERE BODEGA_ID=$id_bodega";
    $query = nmysql2_query($query,$link);    
    if(nmysql2_num_rows($query)>0){ 
    $row = nmysql2_fetch_array($query);
    return $row["BODEGA_CANAL"];
    }
}
//reporta registros
function reportarRegistroCodigoBarras($accion,$id_item,$indice_item,$servicio,$ejecucion,$link){
$res="0";

$registro_session_id=$_SESSION["nombreuser_registro_sesion_id"];

$rusuario=$_SESSION['nombreuser'];

$query = "SELECT USUARIO_ID FROM usuario WHERE USUARIO_LOGIN='$rusuario'";
$query = nmysql2_query($query,$link);    

if(nmysql2_num_rows($query)>0){ 
$row = nmysql2_fetch_array($query);
$usuario=$row["USUARIO_ID"];
}

$sesion_id=$registro_session_id.session_id();

$agregar=false;
//reporta registro en seguimiento
$consulta_validar=" SELECT SEGUIMIENTO_CODIGO_BARRAS_ID,SEGUIMIENTO_CODIGO_BARRAS_PROCEDIMIENTO FROM seguimiento_registros_codigo_barras "
					."WHERE SEGUIMIENTO_CODIGO_BARRAS_USUARIO_ID=$usuario "
					."AND SEGUIMIENTO_CODIGO_BARRAS_PROCEDIMIENTO=$accion "
					."AND SEGUIMIENTO_CODIGO_BARRAS_REGISTRO_INDICE=$indice_item "
					."AND SEGUIMIENTO_CODIGO_BARRAS_REGISTRO_ID=$id_item "
					."AND SEGUIMIENTO_CODIGO_BARRAS_SERVICIO='$servicio' "
					."AND SEGUIMIENTO_CODIGO_BARRAS_SESION_ID='".$sesion_id."' ";

$queryValidar=nmysql2_query($consulta_validar,$link);
if(nmysql2_num_rows($queryValidar)==0){
 $agregar=true;
}
if(nmysql2_num_rows($queryValidar)>0)
{
 /*while($row=nmysql2_fetch_array($queryValidar)){
 if($row["SEGUIMIENTO_CODIGO_BARRAS_PROCEDIMIENTO"]==2){

 }
 }*/
}

if($accion==2){
}

date_default_timezone_set("America/Bogota");
$fecha=date("Y-m-d H:i:s");

if($agregar){
 if($ejecucion==0){
 $query=" INSERT INTO seguimiento_registros_codigo_barras" 
       ." (SEGUIMIENTO_CODIGO_BARRAS_SERVICIO,SEGUIMIENTO_CODIGO_BARRAS_REGISTRO_INDICE,SEGUIMIENTO_CODIGO_BARRAS_REGISTRO_ID,SEGUIMIENTO_CODIGO_BARRAS_PROCEDIMIENTO,SEGUIMIENTO_CODIGO_BARRAS_USUARIO_ID,SEGUIMIENTO_CODIGO_BARRAS_FECHA,SEGUIMIENTO_CODIGO_BARRAS_SESION_ID,SEGUIMIENTO_CODIGO_BARRAS_FECHA_REGISTRO_DETALLE)"
       ." VALUES ('$servicio',$indice_item,$id_item,$accion,$usuario,'$fecha','".$sesion_id."','$fecha')";
 }
 else{ 
 $query=" INSERT INTO seguimiento_registros_codigo_barras" 
       ." (SEGUIMIENTO_CODIGO_BARRAS_SERVICIO,SEGUIMIENTO_CODIGO_BARRAS_REGISTRO_INDICE,SEGUIMIENTO_CODIGO_BARRAS_REGISTRO_ID,SEGUIMIENTO_CODIGO_BARRAS_PROCEDIMIENTO,SEGUIMIENTO_CODIGO_BARRAS_USUARIO_ID,SEGUIMIENTO_CODIGO_BARRAS_FECHA,SEGUIMIENTO_CODIGO_BARRAS_SESION_ID)"
       ." VALUES ('$servicio',$indice_item,$id_item,$accion,$usuario,'$fecha','".$sesion_id."')";
 }      
}
else{
 if($ejecucion==0){
 $query=" UPDATE seguimiento_registros_codigo_barras SET "
       ." SEGUIMIENTO_CODIGO_BARRAS_SERVICIO='$servicio',"
       ." SEGUIMIENTO_CODIGO_BARRAS_PROCEDIMIENTO=$accion,"
       ." SEGUIMIENTO_CODIGO_BARRAS_FECHA='$fecha',"
       ." SEGUIMIENTO_CODIGO_BARRAS_SESION_ID='".$sesion_id."',"      
       ." SEGUIMIENTO_CODIGO_BARRAS_FECHA_REGISTRO_DETALLE='$fecha'"
       ." WHERE SEGUIMIENTO_CODIGO_BARRAS_USUARIO_ID=$usuario AND SEGUIMIENTO_CODIGO_BARRAS_PROCEDIMIENTO=$accion AND SEGUIMIENTO_CODIGO_BARRAS_REGISTRO_INDICE=$indice_item AND SEGUIMIENTO_CODIGO_BARRAS_REGISTRO_ID=$id_item AND SEGUIMIENTO_CODIGO_BARRAS_SERVICIO='$servicio' AND SEGUIMIENTO_CODIGO_BARRAS_SESION_ID='".$sesion_id."' ";
 }
 else{
 $query=" UPDATE seguimiento_registros_codigo_barras SET "
       ." SEGUIMIENTO_CODIGO_BARRAS_SERVICIO='$servicio',"
       ." SEGUIMIENTO_CODIGO_BARRAS_PROCEDIMIENTO=$accion,"
       ." SEGUIMIENTO_CODIGO_BARRAS_FECHA='$fecha',"
       ." SEGUIMIENTO_CODIGO_BARRAS_SESION_ID='".$sesion_id."'"      
       ." WHERE SEGUIMIENTO_CODIGO_BARRAS_USUARIO_ID=$usuario AND SEGUIMIENTO_CODIGO_BARRAS_PROCEDIMIENTO=$accion AND SEGUIMIENTO_CODIGO_BARRAS_REGISTRO_INDICE=$indice_item AND SEGUIMIENTO_CODIGO_BARRAS_REGISTRO_ID=$id_item AND SEGUIMIENTO_CODIGO_BARRAS_SERVICIO='$servicio' AND SEGUIMIENTO_CODIGO_BARRAS_SESION_ID='".$sesion_id."' "; 
 }
}

$resultado_query=nmysql2_query($query,$link);

if(nmysql2_affected_rows($link)>0){
$res="1";
}

return $res;
}
function revisarRegistroCodigoBarras($accion,$id_item,$indice_item,$servicio,$revisar_sin_list,$link){

function consultarIdUsuario($id_usuario,$indice_item,$id_item,$servicio,$accion){
global $link;
$res=0;
$query = "SELECT SEGUIMIENTO_CODIGO_BARRAS_ID FROM seguimiento_registros_codigo_barras "
		."WHERE SEGUIMIENTO_CODIGO_BARRAS_USUARIO_ID=$id_usuario "
		."AND SEGUIMIENTO_CODIGO_BARRAS_REGISTRO_INDICE=$indice_item "
		."AND SEGUIMIENTO_CODIGO_BARRAS_REGISTRO_ID=$id_item "
		."AND SEGUIMIENTO_CODIGO_BARRAS_SERVICIO='$servicio' "
		."AND SEGUIMIENTO_CODIGO_BARRAS_PROCEDIMIENTO=$accion";
$query = nmysql2_query($query,$link);    
if(nmysql2_num_rows($query)>0){ 
 $row=nmysql2_fetch_array($query);
 $res=$row["SEGUIMIENTO_CODIGO_BARRAS_ID"];
}
nmysql2_free_result($query);
return $res;
}
function consultarFechaDetalleUsuario($id_usuario,$indice_item,$id_item,$servicio,$accion){
global $link;
$res=0;
$query = "SELECT SEGUIMIENTO_CODIGO_BARRAS_FECHA_REGISTRO_DETALLE FROM seguimiento_registros_codigo_barras "
		."WHERE SEGUIMIENTO_CODIGO_BARRAS_USUARIO_ID=$id_usuario "
		."AND SEGUIMIENTO_CODIGO_BARRAS_REGISTRO_INDICE=$indice_item "
		."AND SEGUIMIENTO_CODIGO_BARRAS_REGISTRO_ID=$id_item "
		."AND SEGUIMIENTO_CODIGO_BARRAS_SERVICIO='$servicio' "
		."AND SEGUIMIENTO_CODIGO_BARRAS_PROCEDIMIENTO=$accion";
$query = nmysql2_query($query,$link);    
if(nmysql2_num_rows($query)>0){ 
 $row=nmysql2_fetch_array($query);
 $res=date_create($row["SEGUIMIENTO_CODIGO_BARRAS_FECHA_REGISTRO_DETALLE"]);
}
nmysql2_free_result($query);
return $res;
}

//revisa el registro de codigo de barras
$res=0;

$registro_session_id=$_SESSION["nombreuser_registro_sesion_id"];
	
$rusuario=$_SESSION['nombreuser'];

$listado=false;

$query = "SELECT USUARIO_ID FROM usuario WHERE USUARIO_LOGIN='$rusuario'";
$query = nmysql2_query($query,$link);    

if(nmysql2_num_rows($query)>0){ 
$row = nmysql2_fetch_array($query);
$usuario=$row["USUARIO_ID"];
}

$sesion_id=$registro_session_id.session_id();

     //$diferencia=date_diff($fecha_usuario,$fecha);
     //$diferencia=(int)$diferencia->format('%s');
     //$sesion_id
     //$fecha_inicial_comparada=date("Y-m-d H:i:s");
     //$fecha_inicial_comparada=date_create($fecha_inicial_comparada);
     
if($revisar_sin_list==0){

//revision registro en seguimiento
$consulta_validar=" SELECT SEGUIMIENTO_CODIGO_BARRAS_ID,SEGUIMIENTO_CODIGO_BARRAS_PROCEDIMIENTO,SEGUIMIENTO_CODIGO_BARRAS_USUARIO_ID,SEGUIMIENTO_CODIGO_BARRAS_SESION_ID,SEGUIMIENTO_CODIGO_BARRAS_FECHA FROM seguimiento_registros_codigo_barras WHERE SEGUIMIENTO_CODIGO_BARRAS_USUARIO_ID<>$usuario AND SEGUIMIENTO_CODIGO_BARRAS_SERVICIO='$servicio' AND SEGUIMIENTO_CODIGO_BARRAS_PROCEDIMIENTO=1 ORDER BY SEGUIMIENTO_CODIGO_BARRAS_FECHA_REGISTRO_DETALLE";
$queryValidar=nmysql2_query($consulta_validar,$link);
     //$diferencia=date_diff($fecha_usuario,$fecha);
     //$diferencia=(int)$diferencia->format('%s');

if(nmysql2_num_rows($queryValidar)>0){
  while($row=nmysql2_fetch_array($queryValidar)){     
    $id_revisado=$row["SEGUIMIENTO_CODIGO_BARRAS_USUARIO_ID"]; 
    $consulta_validar_usuario=" SELECT SEGUIMIENTO_CODIGO_BARRAS_ID,"
							."SEGUIMIENTO_CODIGO_BARRAS_PROCEDIMIENTO,"
							."SEGUIMIENTO_CODIGO_BARRAS_USUARIO_ID,"
							."SEGUIMIENTO_CODIGO_BARRAS_SESION_ID,"
							."SEGUIMIENTO_CODIGO_BARRAS_FECHA,"
							."SEGUIMIENTO_CODIGO_BARRAS_FECHA_REGISTRO_DETALLE FROM seguimiento_registros_codigo_barras "
							."WHERE SEGUIMIENTO_CODIGO_BARRAS_USUARIO_ID=$id_revisado "
							."AND SEGUIMIENTO_CODIGO_BARRAS_REGISTRO_INDICE=$indice_item "
							."AND SEGUIMIENTO_CODIGO_BARRAS_REGISTRO_ID=$id_item "
							."AND SEGUIMIENTO_CODIGO_BARRAS_SERVICIO='$servicio' "
							."AND SEGUIMIENTO_CODIGO_BARRAS_PROCEDIMIENTO=2 "
							."ORDER BY SEGUIMIENTO_CODIGO_BARRAS_FECHA_REGISTRO_DETALLE";
    $queryValidarUsuario=nmysql2_query($consulta_validar_usuario,$link);
    //valida si el usuario encontrado esta editando el registro
    if(nmysql2_num_rows($queryValidarUsuario)>0){              
     while($datosUsuario=nmysql2_fetch_array($queryValidarUsuario)){          
     date_default_timezone_set("America/Bogota");
     $fecha=date("Y-m-d H:i:s");    
     $fecha=date_create($fecha);
     $fecha_usuario=date_create($datosUsuario["SEGUIMIENTO_CODIGO_BARRAS_FECHA"]);     
     $fecha_usuario_detalle=date_create($datosUsuario["SEGUIMIENTO_CODIGO_BARRAS_FECHA_REGISTRO_DETALLE"]);         
     $diferencia=$fecha->getTimestamp()-$fecha_usuario->getTimestamp();     
     $fecha2=consultarFechaDetalleUsuario($usuario,$indice_item,$id_item,$servicio,2);          
     //echo $fecha->getTimestamp()."-".$fecha_usuario->getTimestamp()."<br>";
      //la conversion a int es automatica en $diferencia
      if(($diferencia>=-30)&&($diferencia<30)&&($fecha_usuario_detalle<$fecha2)){
       //Se valida el nombre del usuario que esta editando el registro
       $nombre= "";
       $query = "SELECT CONCAT(USUARIO_NOMBRES,' ',USUARIO_APELLIDOS) AS NOMBRE_USUARIO,SEGUIMIENTO_CODIGO_BARRAS_FECHA_REGISTRO_DETALLE,SEGUIMIENTO_CODIGO_BARRAS_FECHA FROM usuario, seguimiento_registros_codigo_barras WHERE SEGUIMIENTO_CODIGO_BARRAS_USUARIO_ID=USUARIO_ID AND SEGUIMIENTO_CODIGO_BARRAS_REGISTRO_ID=$id_item AND SEGUIMIENTO_CODIGO_BARRAS_SERVICIO='$servicio' AND SEGUIMIENTO_CODIGO_BARRAS_PROCEDIMIENTO=2 ORDER BY SEGUIMIENTO_CODIGO_BARRAS_FECHA_REGISTRO_DETALLE DESC";       
       $query = nmysql2_query($query,$link);    
       $nombre="";
       $fecha_detalle=date("Y-m-d H:i:s");    
       $fecha_detalle=date_create($fecha_detalle);                  
        if(nmysql2_num_rows($query)>0){ 
         while($datosEditor = nmysql2_fetch_array($query)){  
          $fecha=date("Y-m-d H:i:s");    
          $fecha=date_create($fecha);                                     
          $fechaRevisada=date_create($datosEditor["SEGUIMIENTO_CODIGO_BARRAS_FECHA"]);          
          $fechaDetalleRevizada=date_create($datosEditor["SEGUIMIENTO_CODIGO_BARRAS_FECHA_REGISTRO_DETALLE"]);
          $diferencia=$fecha->getTimestamp()-$fechaRevisada->getTimestamp();          
          //$nombre.="Diferencia:$diferencia<br>".$datosEditor["SEGUIMIENTO_CODIGO_BARRAS_FECHA_REGISTRO_DETALLE"]."|".$datosEditor["NOMBRE_USUARIO"];
          if(($diferencia>=-30)&&($diferencia<30)){
           if($fechaDetalleRevizada<=$fecha_detalle){
           $fecha_detalle=$fechaDetalleRevizada;
	   $nombre=$datosEditor["NOMBRE_USUARIO"];
           }
          }
         }               
        }      
      
       $res="The record is view by $nombre";
       }
      }      
     }
    }
 } 
 
}

if($revisar_sin_list==1){
    $consulta_validar_usuario=" SELECT SEGUIMIENTO_CODIGO_BARRAS_ID,"
							."SEGUIMIENTO_CODIGO_BARRAS_PROCEDIMIENTO,"
							."SEGUIMIENTO_CODIGO_BARRAS_USUARIO_ID,"
							."SEGUIMIENTO_CODIGO_BARRAS_SESION_ID,"
							."SEGUIMIENTO_CODIGO_BARRAS_FECHA,"
							."SEGUIMIENTO_CODIGO_BARRAS_FECHA_REGISTRO_DETALLE "
							."FROM seguimiento_registros_codigo_barras "
							."WHERE SEGUIMIENTO_CODIGO_BARRAS_REGISTRO_INDICE=$indice_item "
							."AND SEGUIMIENTO_CODIGO_BARRAS_REGISTRO_ID=$id_item "
							."AND SEGUIMIENTO_CODIGO_BARRAS_SERVICIO='$servicio' "
							."AND SEGUIMIENTO_CODIGO_BARRAS_PROCEDIMIENTO=2 "
							."ORDER BY SEGUIMIENTO_CODIGO_BARRAS_FECHA_REGISTRO_DETALLE";

    $queryValidarUsuario=nmysql2_query($consulta_validar_usuario,$link);	
    //valida si el usuario encontrado esta editando el registro
    if(nmysql2_num_rows($queryValidarUsuario)>0){              
     while($datosUsuario=nmysql2_fetch_array($queryValidarUsuario)){          
     date_default_timezone_set("America/Bogota");
     $fecha=date("Y-m-d H:i:s");    
     $fecha=date_create($fecha);
     $fecha_usuario=date_create($datosUsuario["SEGUIMIENTO_CODIGO_BARRAS_FECHA"]);     
     $fecha_usuario_detalle=date_create($datosUsuario["SEGUIMIENTO_CODIGO_BARRAS_FECHA_REGISTRO_DETALLE"]);         
     $diferencia=$fecha->getTimestamp()-$fecha_usuario->getTimestamp();     
     $fecha2=consultarFechaDetalleUsuario($usuario,$indice_item,$id_item,$servicio,2);          
     //echo $fecha->getTimestamp()."-".$fecha_usuario->getTimestamp()."<br>";
      //la conversion a int es automatica en $diferencia
      if(($diferencia>=-30)&&($diferencia<30)&&($fecha_usuario_detalle<$fecha2)){
       //Se valida el nombre del usuario que esta editando el registro
       $nombre= "";
       $query = "SELECT CONCAT(USUARIO_NOMBRES,' ',USUARIO_APELLIDOS) AS NOMBRE_USUARIO,SEGUIMIENTO_CODIGO_BARRAS_FECHA_REGISTRO_DETALLE,SEGUIMIENTO_CODIGO_BARRAS_FECHA FROM usuario, seguimiento_registros_codigo_barras WHERE SEGUIMIENTO_CODIGO_BARRAS_USUARIO_ID=USUARIO_ID AND SEGUIMIENTO_CODIGO_BARRAS_REGISTRO_ID=$id_item AND SEGUIMIENTO_CODIGO_BARRAS_SERVICIO='$servicio' AND SEGUIMIENTO_CODIGO_BARRAS_PROCEDIMIENTO=2 ORDER BY SEGUIMIENTO_CODIGO_BARRAS_FECHA_REGISTRO_DETALLE DESC";       
       $query = nmysql2_query($query,$link);    
       $nombre="";
       $fecha_detalle=date("Y-m-d H:i:s");    
       $fecha_detalle=date_create($fecha_detalle);                  
        if(nmysql2_num_rows($query)>0){ 
         while($datosEditor = nmysql2_fetch_array($query)){  
          $fecha=date("Y-m-d H:i:s");    
          $fecha=date_create($fecha);                                     
          $fechaRevisada=date_create($datosEditor["SEGUIMIENTO_CODIGO_BARRAS_FECHA"]);          
          $fechaDetalleRevizada=date_create($datosEditor["SEGUIMIENTO_CODIGO_BARRAS_FECHA_REGISTRO_DETALLE"]);
          $diferencia=$fecha->getTimestamp()-$fechaRevisada->getTimestamp();          
          //$nombre.="Diferencia:$diferencia<br>".$datosEditor["SEGUIMIENTO_CODIGO_BARRAS_FECHA_REGISTRO_DETALLE"]."|".$datosEditor["NOMBRE_USUARIO"];
          if(($diferencia>=-30)&&($diferencia<30)){
           if($fechaDetalleRevizada<=$fecha_detalle){
           $fecha_detalle=$fechaDetalleRevizada;
	   $nombre=$datosEditor["NOMBRE_USUARIO"];
           }
          }
         }               
        }      
      
       $res="The record is view by $nombre";
       }
      }      
     }
}

$consulta_validar_usuario=" SELECT SEGUIMIENTO_CODIGO_BARRAS_ID,"
						."SEGUIMIENTO_CODIGO_BARRAS_PROCEDIMIENTO,"
						."SEGUIMIENTO_CODIGO_BARRAS_USUARIO_ID,"
						."SEGUIMIENTO_CODIGO_BARRAS_SESION_ID,"
						."SEGUIMIENTO_CODIGO_BARRAS_FECHA,"
						."SEGUIMIENTO_CODIGO_BARRAS_FECHA_REGISTRO_DETALLE "
						."FROM seguimiento_registros_codigo_barras "
						."WHERE SEGUIMIENTO_CODIGO_BARRAS_USUARIO_ID=$usuario "
						."AND SEGUIMIENTO_CODIGO_BARRAS_REGISTRO_INDICE=$indice_item "
						."AND SEGUIMIENTO_CODIGO_BARRAS_REGISTRO_ID=$id_item "
						."AND SEGUIMIENTO_CODIGO_BARRAS_SERVICIO='$servicio' "
						."AND SEGUIMIENTO_CODIGO_BARRAS_PROCEDIMIENTO=2 "
						."AND SEGUIMIENTO_CODIGO_BARRAS_SESION_ID<>'$sesion_id' ";
$queryValidarUsuario=nmysql2_query($consulta_validar_usuario,$link);

$otro_usuario=false;

//valida si el usuario encontrado esta editando el registro
if(nmysql2_num_rows($queryValidarUsuario)>0){              
 while($datosUsuario=nmysql2_fetch_array($queryValidarUsuario)){  
    
     $fecha=date("Y-m-d H:i:s");    
     $fecha=date_create($fecha);
     $fecha_usuario=date_create($datosUsuario["SEGUIMIENTO_CODIGO_BARRAS_FECHA"]);
     $fecha_usuario_detalle=date_create($datosUsuario["SEGUIMIENTO_CODIGO_BARRAS_FECHA_REGISTRO_DETALLE"]);
     $id_sesion=$datosUsuario["SEGUIMIENTO_CODIGO_BARRAS_SESION_ID"];

     $diferencia_usuario=$fecha->getTimestamp()-$fecha_usuario->getTimestamp();
     $diferencia_usuario_detalle=$fecha->getTimestamp()-$fecha_usuario_detalle->getTimestamp();
     
     if(($diferencia_usuario>=-30)&&($diferencia_usuario<30)){
      
     $otro_usuario=true;
      
     }
 }

if($otro_usuario){
$res="The record is being view by you in another session";
}

}

return $res;
	
}//revisado barcode
function reportarRegistro($link,$accion,$id_item,$servicio,$ejecucion){
$res="0";

$registro_session_id=$_SESSION["nombreuser_registro_sesion_id"];

$rusuario=$_SESSION['nombreuser'];

$query = "SELECT USUARIO_ID FROM usuario WHERE USUARIO_LOGIN='$rusuario'";
$query = nmysql2_query($query,$link);    

if(nmysql2_num_rows($query)>0){ 
$row = nmysql2_fetch_array($query);
$usuario=$row["USUARIO_ID"];
}

$sesion_id=$registro_session_id.session_id();

$agregar=false;
//reporta registro en seguimiento
$consulta_validar=" SELECT SEGUIMIENTO_ID,SEGUIMIENTO_PROCEDIMIENTO FROM seguimiento_registros WHERE SEGUIMIENTO_USUARIO_ID=$usuario AND SEGUIMIENTO_PROCEDIMIENTO=$accion AND SEGUIMIENTO_REGISTRO_ID=$id_item AND SEGUIMIENTO_SERVICIO='$servicio' AND SEGUIMIENTO_SESION_ID='".$sesion_id."' ";
$queryValidar=nmysql2_query($consulta_validar,$link);
if(nmysql2_num_rows($queryValidar)==0){
 $agregar=true;
}
if(nmysql2_num_rows($queryValidar)>0)
{
 /*while($row=nmysql2_fetch_array($queryValidar)){
 if($row["SEGUIMIENTO_PROCEDIMIENTO"]==2){

 }
 }*/
}

if($accion==2){
}

date_default_timezone_set("America/Bogota");
$fecha=date("Y-m-d H:i:s");

if($agregar){
 if($ejecucion==0){
 $query=" INSERT INTO seguimiento_registros" 
       ." (SEGUIMIENTO_SERVICIO,SEGUIMIENTO_REGISTRO_ID,SEGUIMIENTO_PROCEDIMIENTO,SEGUIMIENTO_USUARIO_ID,SEGUIMIENTO_FECHA,SEGUIMIENTO_SESION_ID,SEGUIMIENTO_FECHA_REGISTRO_DETALLE)"
       ." VALUES ('$servicio',$id_item,$accion,$usuario,'$fecha','".$sesion_id."','$fecha')";
 }
 else{ 
 $query=" INSERT INTO seguimiento_registros" 
       ." (SEGUIMIENTO_SERVICIO,SEGUIMIENTO_REGISTRO_ID,SEGUIMIENTO_PROCEDIMIENTO,SEGUIMIENTO_USUARIO_ID,SEGUIMIENTO_FECHA,SEGUIMIENTO_SESION_ID)"
       ." VALUES ('$servicio',$id_item,$accion,$usuario,'$fecha','".$sesion_id."')";
 }      
}
else{
 if($ejecucion==0){
 $query=" UPDATE seguimiento_registros SET"
       ." SEGUIMIENTO_SERVICIO='$servicio',"
       ." SEGUIMIENTO_PROCEDIMIENTO=$accion,"
       ." SEGUIMIENTO_FECHA='$fecha',"
       ." SEGUIMIENTO_SESION_ID='".$sesion_id."',"      
       ." SEGUIMIENTO_FECHA_REGISTRO_DETALLE='$fecha'"
       ." WHERE SEGUIMIENTO_USUARIO_ID=$usuario AND SEGUIMIENTO_PROCEDIMIENTO=$accion AND SEGUIMIENTO_REGISTRO_ID=$id_item AND SEGUIMIENTO_SERVICIO='$servicio' AND SEGUIMIENTO_SESION_ID='".$sesion_id."' ";
 }
 else{
 $query=" UPDATE seguimiento_registros SET"
       ." SEGUIMIENTO_SERVICIO='$servicio',"
       ." SEGUIMIENTO_PROCEDIMIENTO=$accion,"
       ." SEGUIMIENTO_FECHA='$fecha',"
       ." SEGUIMIENTO_SESION_ID='".$sesion_id."'"      
       ." WHERE SEGUIMIENTO_USUARIO_ID=$usuario AND SEGUIMIENTO_PROCEDIMIENTO=$accion AND SEGUIMIENTO_REGISTRO_ID=$id_item AND SEGUIMIENTO_SERVICIO='$servicio' AND SEGUIMIENTO_SESION_ID='".$sesion_id."' "; 
 }
}

$resultado_query=nmysql2_query($query,$link);

if(nmysql2_affected_rows($link)>0){
$res="1";
}

return $res;
}
function revisarRegistro($link,$accion,$id_item,$servicio,$revisar_sin_list){
function consultarIdUsuario($id_usuario,$id_item,$servicio,$accion){
global $link;
$res=0;
$query = "SELECT SEGUIMIENTO_ID FROM seguimiento_registros WHERE SEGUIMIENTO_USUARIO_ID=$id_usuario AND SEGUIMIENTO_REGISTRO_ID=$id_item AND SEGUIMIENTO_SERVICIO='$servicio' AND SEGUIMIENTO_PROCEDIMIENTO=$accion";
$query = nmysql2_query($query,$link);    
if(nmysql2_num_rows($query)>0){ 
 $row=nmysql2_fetch_array($query);
 $res=$row["SEGUIMIENTO_ID"];
}
nmysql2_free_result($query);
return $res;
}
function consultarFechaDetalleUsuario($id_usuario,$id_item,$servicio,$accion){
global $link;
$res=0;
$query = "SELECT SEGUIMIENTO_FECHA_REGISTRO_DETALLE FROM seguimiento_registros WHERE SEGUIMIENTO_USUARIO_ID=$id_usuario AND SEGUIMIENTO_REGISTRO_ID=$id_item AND SEGUIMIENTO_SERVICIO='$servicio' AND SEGUIMIENTO_PROCEDIMIENTO=$accion";
$query = nmysql2_query($query,$link);    
if(nmysql2_num_rows($query)>0){ 
 $row=nmysql2_fetch_array($query);
 $res=date_create($row["SEGUIMIENTO_FECHA_REGISTRO_DETALLE"]);
}
nmysql2_free_result($query);
return $res;
}

//revisa el registro
$res=0;
$registro_session_id=$_SESSION["nombreuser_registro_sesion_id"];
$rusuario=$_SESSION['nombreuser'];
$listado=false;

$query = "SELECT USUARIO_ID FROM usuario WHERE USUARIO_LOGIN='$rusuario'";
$query = nmysql2_query($query,$link);    

if(nmysql2_num_rows($query)>0){ 
$row = nmysql2_fetch_array($query);
$usuario=$row["USUARIO_ID"];
}

$sesion_id=$registro_session_id.session_id();

if($revisar_sin_list==0){

//revision registro en seguimiento
$consulta_validar=" SELECT SEGUIMIENTO_ID,SEGUIMIENTO_PROCEDIMIENTO,SEGUIMIENTO_USUARIO_ID,SEGUIMIENTO_SESION_ID,SEGUIMIENTO_FECHA FROM seguimiento_registros WHERE SEGUIMIENTO_USUARIO_ID<>$usuario AND SEGUIMIENTO_SERVICIO='$servicio' AND SEGUIMIENTO_PROCEDIMIENTO=1 ORDER BY SEGUIMIENTO_FECHA_REGISTRO_DETALLE";
$queryValidar=nmysql2_query($consulta_validar,$link);
     //$diferencia=date_diff($fecha_usuario,$fecha);
     //$diferencia=(int)$diferencia->format('%s');

if(nmysql2_num_rows($queryValidar)>0){
  while($row=nmysql2_fetch_array($queryValidar)){     
    $id_revisado=$row["SEGUIMIENTO_USUARIO_ID"]; 
    $consulta_validar_usuario=" SELECT SEGUIMIENTO_ID,SEGUIMIENTO_PROCEDIMIENTO,SEGUIMIENTO_USUARIO_ID,SEGUIMIENTO_SESION_ID,SEGUIMIENTO_FECHA,SEGUIMIENTO_FECHA_REGISTRO_DETALLE FROM seguimiento_registros WHERE SEGUIMIENTO_USUARIO_ID=$id_revisado AND SEGUIMIENTO_REGISTRO_ID=$id_item AND SEGUIMIENTO_SERVICIO='$servicio' AND SEGUIMIENTO_PROCEDIMIENTO=2 ORDER BY SEGUIMIENTO_FECHA_REGISTRO_DETALLE";
    $queryValidarUsuario=nmysql2_query($consulta_validar_usuario,$link);
    //valida si el usuario encontrado esta editando el registro
    if(nmysql2_num_rows($queryValidarUsuario)>0){              
     while($datosUsuario=nmysql2_fetch_array($queryValidarUsuario)){          
     date_default_timezone_set("America/Bogota");
     $fecha=date("Y-m-d H:i:s");    
     $fecha=date_create($fecha);
     $fecha_usuario=date_create($datosUsuario["SEGUIMIENTO_FECHA"]);     
     $fecha_usuario_detalle=date_create($datosUsuario["SEGUIMIENTO_FECHA_REGISTRO_DETALLE"]);         
     $diferencia=$fecha->getTimestamp()-$fecha_usuario->getTimestamp();     
     $fecha2=consultarFechaDetalleUsuario($usuario,$id_item,$servicio,2);          
     //echo $fecha->getTimestamp()."-".$fecha_usuario->getTimestamp()."<br>";
      //la conversion a int es automatica en $diferencia
      if(($diferencia>=-30)&&($diferencia<30)&&($fecha_usuario_detalle<$fecha2)){
       //Se valida el nombre del usuario que esta editando el registro
       $nombre= "";
       $query = "SELECT CONCAT(USUARIO_NOMBRES,' ',USUARIO_APELLIDOS) AS NOMBRE_USUARIO,SEGUIMIENTO_FECHA_REGISTRO_DETALLE,SEGUIMIENTO_FECHA FROM usuario, seguimiento_registros WHERE SEGUIMIENTO_USUARIO_ID=USUARIO_ID AND SEGUIMIENTO_REGISTRO_ID=$id_item AND SEGUIMIENTO_SERVICIO='$servicio' AND SEGUIMIENTO_PROCEDIMIENTO=2 ORDER BY SEGUIMIENTO_FECHA_REGISTRO_DETALLE DESC";       
       $query = nmysql2_query($query,$link);    
       $nombre="";
       $fecha_detalle=date("Y-m-d H:i:s");    
       $fecha_detalle=date_create($fecha_detalle);                  
        if(nmysql2_num_rows($query)>0){ 
         while($datosEditor = nmysql2_fetch_array($query)){  
          $fecha=date("Y-m-d H:i:s");    
          $fecha=date_create($fecha);                                     
          $fechaRevisada=date_create($datosEditor["SEGUIMIENTO_FECHA"]);          
          $fechaDetalleRevizada=date_create($datosEditor["SEGUIMIENTO_FECHA_REGISTRO_DETALLE"]);
          $diferencia=$fecha->getTimestamp()-$fechaRevisada->getTimestamp();          
          //$nombre.="Diferencia:$diferencia<br>".$datosEditor["SEGUIMIENTO_FECHA_REGISTRO_DETALLE"]."|".$datosEditor["NOMBRE_USUARIO"];
          if(($diferencia>=-30)&&($diferencia<30)){
           if($fechaDetalleRevizada<=$fecha_detalle){
           $fecha_detalle=$fechaDetalleRevizada;
	   $nombre=$datosEditor["NOMBRE_USUARIO"];
           }
          }
         }               
        }      
      
       $res="El registro esta siendo visto por (The record is view by) $nombre";
       }
      }      
     }
    }
 } 
 
}

if($revisar_sin_list==1){
    $consulta_validar_usuario=" SELECT SEGUIMIENTO_ID,SEGUIMIENTO_PROCEDIMIENTO,SEGUIMIENTO_USUARIO_ID,SEGUIMIENTO_SESION_ID,SEGUIMIENTO_FECHA,SEGUIMIENTO_FECHA_REGISTRO_DETALLE FROM seguimiento_registros WHERE SEGUIMIENTO_REGISTRO_ID=$id_item AND SEGUIMIENTO_SERVICIO='$servicio' AND SEGUIMIENTO_PROCEDIMIENTO=2 ORDER BY SEGUIMIENTO_FECHA_REGISTRO_DETALLE";
    $queryValidarUsuario=nmysql2_query($consulta_validar_usuario,$link);
    //valida si el usuario encontrado esta editando el registro
    if(nmysql2_num_rows($queryValidarUsuario)>0){              
     while($datosUsuario=nmysql2_fetch_array($queryValidarUsuario)){          
     date_default_timezone_set("America/Bogota");
     $fecha=date("Y-m-d H:i:s");    
     $fecha=date_create($fecha);
     $fecha_usuario=date_create($datosUsuario["SEGUIMIENTO_FECHA"]);     
     $fecha_usuario_detalle=date_create($datosUsuario["SEGUIMIENTO_FECHA_REGISTRO_DETALLE"]);         
     $diferencia=$fecha->getTimestamp()-$fecha_usuario->getTimestamp();     
     $fecha2=consultarFechaDetalleUsuario($usuario,$id_item,$servicio,2);          
     //echo $fecha->getTimestamp()."-".$fecha_usuario->getTimestamp()."<br>";
      //la conversion a int es automatica en $diferencia
      if(($diferencia>=-30)&&($diferencia<30)&&($fecha_usuario_detalle<$fecha2)){
       //Se valida el nombre del usuario que esta editando el registro
       $nombre= "";
       $query = "SELECT CONCAT(USUARIO_NOMBRES,' ',USUARIO_APELLIDOS) AS NOMBRE_USUARIO,SEGUIMIENTO_FECHA_REGISTRO_DETALLE,SEGUIMIENTO_FECHA FROM usuario, seguimiento_registros WHERE SEGUIMIENTO_USUARIO_ID=USUARIO_ID AND SEGUIMIENTO_REGISTRO_ID=$id_item AND SEGUIMIENTO_SERVICIO='$servicio' AND SEGUIMIENTO_PROCEDIMIENTO=2 ORDER BY SEGUIMIENTO_FECHA_REGISTRO_DETALLE DESC";       
       $query = nmysql2_query($query,$link);    
       $nombre="";
       $fecha_detalle=date("Y-m-d H:i:s");    
       $fecha_detalle=date_create($fecha_detalle);                  
        if(nmysql2_num_rows($query)>0){ 
         while($datosEditor = nmysql2_fetch_array($query)){  
          $fecha=date("Y-m-d H:i:s");    
          $fecha=date_create($fecha);                                     
          $fechaRevisada=date_create($datosEditor["SEGUIMIENTO_FECHA"]);          
          $fechaDetalleRevizada=date_create($datosEditor["SEGUIMIENTO_FECHA_REGISTRO_DETALLE"]);
          $diferencia=$fecha->getTimestamp()-$fechaRevisada->getTimestamp();          
          //$nombre.="Diferencia:$diferencia<br>".$datosEditor["SEGUIMIENTO_FECHA_REGISTRO_DETALLE"]."|".$datosEditor["NOMBRE_USUARIO"];
          if(($diferencia>=-30)&&($diferencia<30)){
           if($fechaDetalleRevizada<=$fecha_detalle){
           $fecha_detalle=$fechaDetalleRevizada;
	   $nombre=$datosEditor["NOMBRE_USUARIO"];
           }
          }
         }               
        }      
      
       $res="El registro esta siendo visto por (The record is view by) $nombre";
       }
      }      
     }
}

$consulta_validar_usuario=" SELECT SEGUIMIENTO_ID,"
                         ."SEGUIMIENTO_PROCEDIMIENTO,"
                         ."SEGUIMIENTO_USUARIO_ID,"
                         ."SEGUIMIENTO_SESION_ID,"
                         ."SEGUIMIENTO_FECHA,"
                         ."SEGUIMIENTO_FECHA_REGISTRO_DETALLE "
                         ."FROM seguimiento_registros "
                         ."WHERE SEGUIMIENTO_USUARIO_ID=$usuario "
                         ."AND SEGUIMIENTO_REGISTRO_ID=$id_item "
                         ."AND SEGUIMIENTO_SERVICIO='$servicio' "
                         ."AND SEGUIMIENTO_PROCEDIMIENTO=2 "
                         ."AND SEGUIMIENTO_SESION_ID<>'$sesion_id' ";
$queryValidarUsuario=nmysql2_query($consulta_validar_usuario,$link);
//valida si el usuario encontrado esta editando el registro

$otro_usuario=false;

if(nmysql2_num_rows($queryValidarUsuario)>0){              
 while($datosUsuario=nmysql2_fetch_array($queryValidarUsuario)){

     $fecha=date("Y-m-d H:i:s");    
     $fecha=date_create($fecha);
     $fecha_usuario=date_create($datosUsuario["SEGUIMIENTO_FECHA"]);
     $fecha_usuario_detalle=date_create($datosUsuario["SEGUIMIENTO_FECHA_REGISTRO_DETALLE"]);
     $id_sesion=$datosUsuario["SEGUIMIENTO_SESION_ID"];
            
    $diferencia_usuario=$fecha->getTimestamp()-$fecha_usuario->getTimestamp();
    $diferencia_usuario_detalle=$fecha->getTimestamp()-$fecha_usuario_detalle->getTimestamp();

    if((($diferencia_usuario>=-30)&&($diferencia_usuario<30))){
    $otro_usuario=true;
    }       

}     

if($otro_usuario){
$res="El registro se está siendo visto por usted en otra sesión (The record is being view by you in another session)";
}

}


 
return $res;
	
}
function encriptarCont($texto){
$cenc=session_id()."63iqkosw1ld8m51ywkup9qvve5g1i7";
return password_hash(hash_hmac("sha512", $texto, $cenc),PASSWORD_DEFAULT);
}
function validarCont($texto,$texto2){
$cenc=session_id()."63iqkosw1ld8m51ywkup9qvve5g1i7";
if(password_verify(hash_hmac("sha512", $texto, $cenc),$texto2)){
return true;
}else{
return false;	
}
}
function encriptarObtenerTexto($texto,$procedimiento,$sesion_id_usar=false){
$cenc="l6eaffuxtje";
$vrev_sesion_id="[?]qP>4+r+T-Qr";
if($sesion_id_usar){
$vrev_sesion_id=session_id();
}
$cenc_m=hash("sha256",hash_hmac("sha256", $vrev_sesion_id."63iqkosw1ld8m51ywkup9qvve5g1i7", $cenc));

$cenc2="_QWzA6}";
$cenc_m2=substr(hash("sha256",$cenc2),0,16);
	
 if($procedimiento=="enc"){
 $res=openssl_encrypt($texto,"AES-256-CBC",$cenc_m,0,$cenc_m2);
 $res=base64_encode($res);
 return $res;
 }
 
 if($procedimiento=="desenc"){
 return openssl_decrypt(base64_decode($texto),"AES-256-CBC",$cenc_m,0,$cenc_m2);
 }

}
function encQRB($version,$texto,$procedimiento,$retornar_datos=0){

$conversion_caracteres="";
$texto_revisado="";

$error=false;

if($version==1){
$array_valores=["0"=>"Q","1"=>"T","2"=>"R","3"=>"J","4"=>"O","5"=>"H","6"=>"B","7"=>"U","8"=>"G","9"=>"A","A"=>"7","B"=>"9","C"=>"0","D"=>"X","E"=>"C","F"=>"Z","G"=>"3","H"=>"Y","I"=>"K","J"=>"M","K"=>"F","L"=>"4","M"=>"E","N"=>"L","O"=>"D","P"=>"8","Q"=>"I","R"=>"W","S"=>"1","T"=>"6","U"=>"P","V"=>"S","W"=>"2","X"=>"V","Y"=>"5","Z"=>"N"];
}

$array_valores2=["AF"=>"0003","AL"=>"0091","DZ"=>"0237","AS"=>"0140","AD"=>"0165","AO"=>"0023","AI"=>"0208","AQ"=>"0166","AG"=>"0249","AR"=>"0210","AM"=>"0006","AW"=>"0176","AU"=>"0024","AT"=>"0214","AZ"=>"0193","BS"=>"0069","BH"=>"0136","BD"=>"0012","BB"=>"0139","BY"=>"0094","BE"=>"0220","BZ"=>"0093","BJ"=>"0153","BM"=>"0095","BT"=>"0199","BO"=>"0189","BA"=>"0115","BW"=>"0240","BV"=>"0196","BR"=>"0164","IO"=>"0191","BN"=>"0089","BG"=>"0175","BF"=>"0198","BI"=>"0011","KH"=>"0035","CM"=>"0080","CA"=>"0138","CV"=>"0042","KY"=>"0230","CF"=>"0241","TD"=>"0161","CL"=>"0122","CN"=>"0047","CX"=>"0061","CC"=>"0221","CO"=>"0090","KM"=>"0030","CG"=>"0070","CD"=>"0102","CK"=>"0104","CR"=>"0032","CI"=>"0075","HR"=>"0127","CU"=>"0028","CY"=>"0117","CZ"=>"0108","DK"=>"0105","DJ"=>"0126","DM"=>"0197","DO"=>"0096","TL"=>"0087","EC"=>"0173","EG"=>"0055","SV"=>"0187","GQ"=>"0171","ER"=>"0184","EE"=>"0133","ET"=>"0167","FK"=>"0002","FO"=>"0194","FJ"=>"0205","FI"=>"0004","FR"=>"0088","GF"=>"0013","PF"=>"0066","TF"=>"0157","GA"=>"0172","GM"=>"0099","GE"=>"0137","DE"=>"0152","GH"=>"0009","GI"=>"0056","GR"=>"0118","GL"=>"0017","GD"=>"0074","GP"=>"0228","GU"=>"0227","GT"=>"0142","GN"=>"0150","GW"=>"0146","GY"=>"0119","HT"=>"0160","HM"=>"0203","HN"=>"0113","HK"=>"0050","HU"=>"0225","IS"=>"0107","IN"=>"0051","ID"=>"0060","IR"=>"0064","IQ"=>"0222","IE"=>"0071","IL"=>"0010","IT"=>"0245","JM"=>"0170","JP"=>"0049","JO"=>"0054","KZ"=>"0076","KE"=>"0178","KI"=>"0015","KR"=>"0001","KP"=>"0186","KW"=>"0072","KG"=>"0250","LA"=>"0216","LV"=>"0121","LB"=>"0131","LS"=>"0124","LR"=>"0086","LY"=>"0141","LI"=>"0063","LT"=>"0144","LU"=>"0179","MO"=>"0025","MG"=>"0040","MW"=>"0018","MY"=>"0019","MV"=>"0177","ML"=>"0242","MT"=>"0112","MH"=>"0079","MQ"=>"0082","MR"=>"0077","MU"=>"0008","YT"=>"0182","MX"=>"0036","FM"=>"0217","MD"=>"0026","MC"=>"0044","MN"=>"0224","MS"=>"0190","MA"=>"0229","MZ"=>"0206","MM"=>"0043","NA"=>"0053","NR"=>"0195","NP"=>"0101","AN"=>"0065","NL"=>"0239","NC"=>"0215","NZ"=>"0168","NI"=>"0033","NE"=>"0106","NG"=>"0226","NU"=>"0129","NF"=>"0052","MP"=>"0155","NO"=>"0130","OM"=>"0169","PK"=>"0109","PW"=>"0120","PA"=>"0148","PG"=>"0207","PY"=>"0103","PE"=>"0159","PH"=>"0132","PN"=>"0027","PL"=>"0192","PT"=>"0041","PR"=>"0183","QA"=>"0031","RE"=>"0014","RO"=>"0181","RU"=>"0125","RW"=>"0158","SH"=>"0219","KN"=>"0128","LC"=>"0007","PM"=>"0110","VC"=>"0185","WS"=>"0247","SM"=>"0067","ST"=>"0135","SA"=>"0034","SN"=>"0204","SC"=>"0149","SL"=>"0174","SG"=>"0163","SK"=>"0234","SI"=>"0123","SB"=>"0045","SO"=>"0083","ZA"=>"0116","ES"=>"0114","LK"=>"0213","SD"=>"0068","SR"=>"0154","SJ"=>"0046","SZ"=>"0058","SE"=>"0084","CH"=>"0231","SY"=>"0078","TW"=>"0100","TJ"=>"0218","TZ"=>"0048","TH"=>"0202","TG"=>"0005","TK"=>"0162","TO"=>"0145","TT"=>"0244","TN"=>"0236","TR"=>"0098","TM"=>"0201","TC"=>"0248","TV"=>"0211","UG"=>"0057","UA"=>"0223","AE"=>"0059","GB"=>"0246","US"=>"0085","UM"=>"0156","UY"=>"0209","UZ"=>"0143","VU"=>"0134","VA"=>"0029","VE"=>"0021","VN"=>"0039","VG"=>"0200","VI"=>"0238","WF"=>"0188","YE"=>"0037","YU"=>"0151","ZM"=>"0038","ZW"=>"0073","AX"=>"0111","BQ"=>"0233","CW"=>"0232","GG"=>"0180","IM"=>"0016","JE"=>"0062","MK"=>"0022","ME"=>"0097","PS"=>"0251","BL"=>"0243","MF"=>"0235","RS"=>"0212","SX"=>"0147","GS"=>"0081","SS"=>"0092","EH"=>"0020"];

$array_valores3=["1A"=>"11","1B"=>"12"];

$array_valores4=["EX"=>"001","NW"=>"187","US"=>"055"];

if($procedimiento=="enc"){

$array_valores6=[];
$array_valores6[0]=substr($texto,0,2);
$array_valores6[1]=substr($texto,2,2);
$array_valores6[2]=substr($texto,4,2);
$array_valores6[3]=substr($texto,6,4);
$array_valores6[4]=substr($texto,10,4);

if(array_key_exists("$array_valores6[0]",$array_valores3)){
$array_valores6[0]=$array_valores3["$array_valores6[0]"];
}
else{
$error=true;	
}

if(array_key_exists("$array_valores6[1]",$array_valores2)){
$array_valores6[1]=$array_valores2["$array_valores6[1]"];
}
else{
$error=true;	
}

if(array_key_exists("$array_valores6[2]",$array_valores4)){
$array_valores6[2]=$array_valores4["$array_valores6[2]"];
}
else{
$error=true;	
}

if($array_valores6[4]=="0"){
$array_valores6[4]="0000";
}

for($i=0;$i<count($array_valores6);$i++){
$texto_revisado.=$array_valores6[$i];
}

$texto_revisado=base_convert($texto_revisado,10,35);

$texto_revisado=mb_strtoupper($texto_revisado);

$no_caracteres=strlen($texto_revisado);

 for($i=0;$i<$no_caracteres;$i++){
 $caracter=substr($texto_revisado,$i,1);
  if(array_key_exists("$caracter",$array_valores)){	 
  $conversion_caracteres.=$array_valores["$caracter"];
  }
  else{
  $error=true;
  break;
  }
 }
 
}

if($procedimiento=="desenc"){

$array_valores6=[];
$texto_resultado="";
$no_caracteres=strlen($texto);

 for($i=0;$i<$no_caracteres;$i++){
 $caracter=substr($texto,$i,1);

  if(in_array("$caracter",$array_valores)){	 
  $texto_resultado.=array_search("$caracter",$array_valores,true);
  }
  else{
  $error=true;
  break;
  }

 }

$texto_revisado=base_convert($texto_resultado,35,10);

$array_valores6[0]=substr($texto_revisado,0,2);
$array_valores6[1]=substr($texto_revisado,2,4);
$array_valores6[2]=substr($texto_revisado,6,3);
$array_valores6[3]=substr($texto_revisado,9,4);
$array_valores6[4]=substr($texto_revisado,13,4);

if(in_array("$array_valores6[0]",$array_valores3)){
$array_valores6[0]=array_search("$array_valores6[0]",$array_valores3,true);
}
else{
$error=true;
}

if(in_array("$array_valores6[1]",$array_valores2)){
$array_valores6[1]=array_search("$array_valores6[1]",$array_valores2,true);
}
else{
$error=true;
}

if(in_array("$array_valores6[2]",$array_valores4)){
$array_valores6[2]=array_search("$array_valores6[2]",$array_valores4,true);
}
else{
$error=true;
}

if($array_valores6[4]=="0000"){
$array_valores6[4]="0";
}

if($retornar_datos==0){

 for($i=0;$i<count($array_valores6);$i++){
 $conversion_caracteres.=$array_valores6[$i];
 }

}
else {
 if($retornar_datos==1){
 $arreglo_retorno=[]; 
 
  for($i=0;$i<count($array_valores6);$i++){
   if($i>=2){	  
   $arreglo_retorno[$i]=$array_valores6[$i];
   }
   else{
   $arreglo_retorno[$i]="";
   }
  }  

  if($arreglo_retorno[3]!="0000"){
  $arreglo_retorno[3]=date("Y-m-d",strtotime("20".substr($arreglo_retorno[3],0,2)."W".substr($arreglo_retorno[3],2,2)));
  
  $chequeo_mes=(int)date("m",strtotime($arreglo_retorno[3]));
  $chequeo_dia=(int)date("d",strtotime($arreglo_retorno[3]));
  $chequeo_ano=(int)date("Y",strtotime($arreglo_retorno[3]));
   if(!checkdate($chequeo_mes,$chequeo_dia,$chequeo_ano)){
   $error=true;
   }
  }

  if($arreglo_retorno[4]!="0"){
  $arreglo_retorno[4]=date("Y-m-d",strtotime("20".substr($arreglo_retorno[4],0,2)."W".substr($arreglo_retorno[4],2,2)));;
  
  $chequeo_mes=(int)date("m",strtotime($arreglo_retorno[4]));
  $chequeo_dia=(int)date("d",strtotime($arreglo_retorno[4]));
  $chequeo_ano=(int)date("Y",strtotime($arreglo_retorno[4]));
   if(!checkdate($chequeo_mes,$chequeo_dia,$chequeo_ano)){
   $error=true;
   }  
  
  }
 
 $conversion_caracteres=$arreglo_retorno;
 
 }
}

}

if($error){
$conversion_caracteres=-1;
}

return $conversion_caracteres;

}

function obtenerNombreSiglaTipoEquipo($sigla="",$grupo="",$link){

$resultado_nombre_sigla=[];

if(($sigla=="")&&($grupo!="")){
$query_nombre_sigla="SELECT ARTICULO_REFERENCIA_TIPO_EQUIPO_NOMBRE "
					."FROM articulo_referencia_tipo_equipo "
					."WHERE ARTICULO_REFERENCIA_TIPO_EQUIPO_GRUPO_ID='$grupo' ";

}

if(($sigla!="")&&($grupo=="")){
	
$query_nombre_sigla="SELECT ARTICULO_REFERENCIA_TIPO_EQUIPO_NOMBRE "
					."FROM articulo_referencia_tipo_equipo "
					."WHERE ARTICULO_REFERENCIA_TIPO_EQUIPO_SIGLA='$sigla' ";

}

if(($sigla!="")&&($grupo!="")){
	
$query_nombre_sigla="SELECT ARTICULO_REFERENCIA_TIPO_EQUIPO_NOMBRE "
					."FROM articulo_referencia_tipo_equipo "
					."WHERE ARTICULO_REFERENCIA_TIPO_EQUIPO_SIGLA='$sigla' "
					."AND ARTICULO_REFERENCIA_TIPO_EQUIPO_GRUPO_ID='$grupo' ";

}

$resultado_query_nombre_sigla=nmysql2_query($query_nombre_sigla,$link);

if($resultado_query_nombre_sigla){
 if(nmysql2_num_rows($resultado_query_nombre_sigla)>0){
  while($row=nmysql2_fetch_assoc($resultado_query_nombre_sigla)){	 
  $resultado_nombre_sigla[]=$row["ARTICULO_REFERENCIA_TIPO_EQUIPO_NOMBRE"];
  }
 }
}

return $resultado_nombre_sigla;
}

function ArtDuplicarImagenBarcode($referencia,$id_imagen,$archivo_nombre,$archivo_ruta,$archivo_tipo,$link){

 $query_articulo_codigo_barras="";
 
 $query_articulo_codigo_barras_indice="SELECT ARTICULO_CODIGO_BARRAS_INDICE_CONSECUTIVO_ID AS ARTICULO_CODIGO_BARRAS_INDICE FROM articulo_codigo_barras_indice_consecutivo;";
 $resultado_query_articulo_codigo_barras_indice=nmysql2_query($query_articulo_codigo_barras_indice,$link);
      
 if($resultado_query_articulo_codigo_barras_indice){
  if(nmysql2_num_rows($resultado_query_articulo_codigo_barras_indice)>0){

   while($row1=nmysql2_fetch_assoc($resultado_query_articulo_codigo_barras_indice)){
   $articulo_codigo_barras_indice_rev=$row1["ARTICULO_CODIGO_BARRAS_INDICE"];
   
    if($query_articulo_codigo_barras==""){
    $query_articulo_codigo_barras.="(SELECT "
				  ."ARTICULO_CODIGO_BARRAS_ID, "
				  ."ARTICULO_CODIGO_BARRAS_REFERENCIA_TEMPORAL, "
				  ."'$articulo_codigo_barras_indice_rev' AS ARTICULO_CODIGO_BARRAS_INDICE "
				  ."FROM articulo_codigo_barras_$articulo_codigo_barras_indice_rev "
				  ."WHERE ARTICULO_CODIGO_BARRAS_REFERENCIA_TEMPORAL = '$referencia' GROUP BY ARTICULO_CODIGO_BARRAS_REFERENCIA_TEMPORAL) ";
    }else{
    $query_articulo_codigo_barras.="UNION (SELECT " 
                  ."ARTICULO_CODIGO_BARRAS_ID, "
                  ."ARTICULO_CODIGO_BARRAS_REFERENCIA_TEMPORAL, "
				  ."'$articulo_codigo_barras_indice_rev' AS ARTICULO_CODIGO_BARRAS_INDICE "
				  ."FROM articulo_codigo_barras_$articulo_codigo_barras_indice_rev "
				  ."WHERE ARTICULO_CODIGO_BARRAS_REFERENCIA_TEMPORAL = '$referencia' GROUP BY ARTICULO_CODIGO_BARRAS_REFERENCIA_TEMPORAL) ";    
    }
   }
  
  $query_articulo_codigo_barras.="";
  
  }			
 }
 //echo $query_articulo_codigo_barras;
 $resultado_query_articulo_codigo_barras = nmysql2_query($query_articulo_codigo_barras,$link);
 
 if($resultado_query_articulo_codigo_barras){
 
    if(nmysql2_num_rows($resultado_query_articulo_codigo_barras)>0){
    
      while($row=nmysql2_fetch_assoc($resultado_query_articulo_codigo_barras)){
      
      $articulo_indice=$row["ARTICULO_CODIGO_BARRAS_INDICE"];
      $articulo_id=$row["ARTICULO_CODIGO_BARRAS_ID"];
      
			$imagen_indice=0;
			  $query_imagen_indice="SELECT MAX(ARTICULO_CODIGO_BARRAS_IMAGEN_INDICE_CONSECUTIVO_ID) AS INDICE_IMAGEN FROM articulo_codigo_barras_imagen_indice_consecutivo;";
			  $resultado_query_imagen_indice=nmysql2_query($query_imagen_indice,$link);
			  
			  if($resultado_query_imagen_indice){
			    if(nmysql2_num_rows($resultado_query_imagen_indice)>0){
			    while($row1=nmysql2_fetch_assoc($resultado_query_imagen_indice)){
			    $imagen_indice=$row1["INDICE_IMAGEN"];
			    
                    $query_imagen_temporal = "INSERT INTO articulo_codigo_barras_imagen_$imagen_indice "
                                ."(ARTICULO_CODIGO_BARRAS_IMAGEN_IN,"
                                ."ARTICULO_CODIGO_BARRAS_IMAGEN_NOMBRE, "
                                ."ARTICULO_CODIGO_BARRAS_IMAGEN_TIPO, "
                                ."ARTICULO_CODIGO_BARRAS_IMAGEN_ARTICULO_CODIGO_BARRAS_ID, "
                                ."ARTICULO_CODIGO_BARRAS_IMAGEN_ARTICULO_CODIGO_BARRAS_IN, "
                                ."ARTICULO_CODIGO_BARRAS_IMAGEN_IMAGEN_ARTICULO_RUTA_ID, "
                                ."ARTICULO_CODIGO_BARRAS_IMAGEN_IMAGEN_ARTICULO_RUTA_DIRECTORIO) "
                                ."VALUES "
                                ."('$imagen_indice','$archivo_nombre','$archivo_tipo',$articulo_id,$articulo_indice,$id_imagen,'".$archivo_ruta."'); ";

//echo $query_imagen_temporal;
                                
                    $resultado_imagen_temporal = nmysql2_query($query_imagen_temporal,$link);
                    if (nmysql2_affected_rows($link)<1){
                        /*
                        if(){
                        "The file ".$nombre."could not saved correctly";
                        }
                        else{
                        ", The file ".$nombre."could not saved correctly";
                        }*/
                    }else{
                    registrar("Articulo barcode","imagen barcode",4,"indice=$articulo_indice id=$articulo_id\narchivo=$archivo_nombre\n",$link);
                    }			    
			    
			    
			    }
			    
			    }
			  }
			  
			  if($imagen_indice>0){
			  
			  
			  
			  }
			  
      }
      
    }
 }


}

function ArtBorrarDuplicadoImagenBarcode($id_imagen,$link){

       $query_imagen_indice="SELECT ARTICULO_CODIGO_BARRAS_IMAGEN_INDICE_CONSECUTIVO_ID AS INDICE_IMAGEN FROM articulo_codigo_barras_imagen_indice_consecutivo;";
       $resultado_query_imagen_indice=nmysql2_query($query_imagen_indice,$link);
      
       if($resultado_query_imagen_indice){
        if(nmysql2_num_rows($resultado_query_imagen_indice)>0){

         while($row1=nmysql2_fetch_assoc($resultado_query_imagen_indice)){
         
         $articulo_imagen_indice=$row1["INDICE_IMAGEN"];

	     $query_imagen= "DELETE FROM articulo_codigo_barras_imagen_$articulo_imagen_indice "
                       ."WHERE ARTICULO_CODIGO_BARRAS_IMAGEN_IMAGEN_ARTICULO_RUTA_ID=$id_imagen; ";
                       
            $resultado_imagen = nmysql2_query($query_imagen,$link);

            if($resultado_imagen){

            if(nmysql2_affected_rows($link)>0){
            
            registrar("Articulo barcode","imagen barcode",5,"articulo_ruta_id=$id_imagen",$link);
            
            }

            }
                       
                       
        }
       }
      }

}

function obtenerDatosArticuloReferencia($referencia,$link){

$arreglo_datos=[];

$referencia_categoria=13;
$referencia_aplicacion=30;
$referencia_grupo=0;

$revision_referencia= explode("-", $referencia);
$revision_equipo=$revision_referencia[0];
$revision_parte=$revision_referencia[1];

$query_equipo="SELECT ARTICULO_REFERENCIA_GRUPO_ID "
             ."FROM articulo_referencia_tipo_equipo "
             ."JOIN articulo_referencia_grupo "
             ."ON ARTICULO_REFERENCIA_GRUPO_ID=ARTICULO_REFERENCIA_TIPO_EQUIPO_GRUPO_ID "
             ."WHERE ARTICULO_REFERENCIA_TIPO_EQUIPO_SIGLA='".$revision_equipo."' ";

$resultado_query_equipo=nmysql2_query($query_equipo,$link);

if($resultado_query_equipo){
 if(nmysql2_num_rows($resultado_query_equipo)>0){
  while($row=nmysql2_fetch_assoc($resultado_query_equipo)){
  $referencia_grupo=$row["ARTICULO_REFERENCIA_GRUPO_ID"];
   if($referencia_grupo==1){
   $referencia_categoria=1;
    if($revision_equipo=='HT'){
    $referencia_aplicacion=76;
    }
    if($revision_equipo=='ES'){
    $referencia_aplicacion=77;
    }
   }else{
    if($referencia_grupo==2){
     
     if($revision_equipo=="Q4"){
      if($revision_parte=="SL"){
      $referencia_categoria=8;
      }else{
      $referencia_categoria=7;
      }
     }
     
     if($revision_parte=="PL"){
     $referencia_categoria=8;
     }
     else{
     $referencia_categoria=9;
     }
    }else{
    $referencia_categoria=7;
    }
   }
  }
 }
}

$arreglo_datos[]=$referencia_categoria;
$arreglo_datos[]=$referencia_aplicacion;
$arreglo_datos[]=$referencia_grupo;

return $arreglo_datos;

}
function obtenerQRUrlPublicidad($link,$referencia,$categoria,$canal){

$url="";

$query_publicidad="SELECT CODIGO_BARRAS_TEMPORAL_PUBLICIDAD_URL,"
                 ."CODIGO_BARRAS_TEMPORAL_PUBLICIDAD_DESCRIPCION,"
                 ."CODIGO_BARRAS_TEMPORAL_PUBLICIDAD_CATEGORIA,"
                 ."CODIGO_BARRAS_TEMPORAL_PUBLICIDAD_SUBCATEGORIA "
                 ."FROM codigo_barras_temporal_publicidad "
                 ."WHERE CODIGO_BARRAS_TEMPORAL_PUBLICIDAD_CANAL_ID=$canal ";

                 
return $url;
}
function formatoNumeroQuitarSeparador($texto){
$resultado=str_replace(",","",$texto);
return $resultado;
}
function formatoNumeroDecimal($texto){
if($texto===""){
$resultado="";
}else{

try{

if(is_null($texto)){
$texto=0;
}

$texto=(float)$texto;

$texto=round($texto,2);

$resultado=number_format($texto,2,".",",");

}
catch (Exception $e) {
$resultado=$texto;
}
 
}
return $resultado;
}

function suprimirDuplicadoEspaciosTexto($cadena){

    $texto_salida    = "";
    $arreglo    = array();
    
    // divido la cadena con todos los espacios q haya
    $arreglo = explode(" ",$cadena);
    
    foreach($arreglo as $subcadena)
    {
        // de cada subcadena elimino sus espacios a los lados
        $subcadena = trim($subcadena);
        
        // luego lo vuelvo a unir con un espacio para formar la nueva cadena limpia
        // omitir los que sean unicamente espacios en blanco
        if($subcadena!="")
        { $texto_salida .= $subcadena." "; }
    }
    $texto_salida = trim($texto_salida);
    
    return $texto_salida;

}
function obtenerCorrelativoInstConConsec($consecutivo,$link){
$resultado="";

$query="SELECT INSTALACION_CORRELATIVO FROM instalacion WHERE INSTALACION_CONSECUTIVO='$consecutivo'; ";

$resultado_query=nmysql2_query($query,$link);

if($resultado_query){
 while($row=nmysql2_fetch_assoc($resultado_query)){
 $resultado=$row["INSTALACION_CORRELATIVO"];
 }
}

return $resultado;
}
function obtenerCorrelativoRepServConConsec($consecutivo,$link){
$resultado="";

$query="SELECT REPORTE_CORRELATIVO FROM reporte WHERE REPORTE_CONSECUTIVO='$consecutivo'; ";

$resultado_query=nmysql2_query($query,$link);

if($resultado_query){
 while($row=nmysql2_fetch_assoc($resultado_query)){
 $resultado=$row["REPORTE_CORRELATIVO"];
 }
}

return $resultado;
}
function validarCopiaReporteInstConsecutivo($consecutivo,$instalacion_id,$link){
$resultado=false;
$id_valor=[];
$query_validacion_duplicado="SELECT INSTALACION_ID FROM instalacion WHERE INSTALACION_CONSECUTIVO = '$consecutivo' AND INSTALACION_COPIA=1 ORDER BY INSTALACION_ID; ";
$resultado_query_validacion_duplicado=nmysql2_query($query_validacion_duplicado,$link);
 if(nmysql2_num_rows($resultado_query_validacion_duplicado)>0){
  while($row=nmysql2_fetch_assoc($resultado_query_validacion_duplicado)){
  $id_valor[]=$row["INSTALACION_ID"];
  }
  if(max($id_valor)!=$instalacion_id){
  $resultado=true;
  }
 }
return $resultado;
}
function validarCopiaReporteServConsecutivo($consecutivo,$reporte_id,$link){
$resultado=false;
$id_valor=[];
$query_validacion_duplicado="SELECT REPORTE_ID FROM reporte WHERE REPORTE_CONSECUTIVO = '$consecutivo' AND REPORTE_COPIA=1 ORDER BY REPORTE_ID; ";
$resultado_query_validacion_duplicado=nmysql2_query($query_validacion_duplicado,$link);
 if(nmysql2_num_rows($resultado_query_validacion_duplicado)>0){
  while($row=nmysql2_fetch_assoc($resultado_query_validacion_duplicado)){
  $id_valor[]=$row["REPORTE_ID"];
  }
  if(max($id_valor)!=$reporte_id){
  $resultado=true;
  }
 }
return $resultado;
}
function copiarEstadoReporteInstConsecutivo($instalacion_id,$link){
$resultado=false;

$query_concecutivo="SELECT INSTALACION_CONSECUTIVO,INSTALACION_ESTADO from instalacion WHERE INSTALACION_ID=$instalacion_id; ";
$resultado_query_concecutivo=nmysql2_query($query_concecutivo,$link);
 if($resultado_query_concecutivo){
  while($row=nmysql2_fetch_assoc($resultado_query_concecutivo)){
  $consecutivo=$row["INSTALACION_CONSECUTIVO"];
  $estado=$row["INSTALACION_ESTADO"];
    $query_ajuste_estado="UPDATE instalacion SET INSTALACION_ESTADO=$estado WHERE INSTALACION_CONSECUTIVO = '$consecutivo';";
    $resultado_query_ajuste_estado=nmysql2_query($query_ajuste_estado,$link);
    if($resultado_query_ajuste_estado){
    $resultado=true;
    }
  }
 }   
return $resultado;
}
function copiarEstadoReporteServConsecutivo($reporte_id,$link){
$resultado=false;

$query_concecutivo="SELECT REPORTE_CONSECUTIVO,REPORTE_ESTADO from reporte WHERE REPORTE_ID=$reporte_id; ";
$resultado_query_concecutivo=nmysql2_query($query_concecutivo,$link);
 if($resultado_query_concecutivo){
  while($row=nmysql2_fetch_assoc($resultado_query_concecutivo)){
  $consecutivo=$row["REPORTE_CONSECUTIVO"];
  $estado=$row["REPORTE_ESTADO"];
    $query_ajuste_estado="UPDATE reporte SET REPORTE_ESTADO=$estado WHERE REPORTE_CONSECUTIVO = '$consecutivo';";
    $resultado_query_ajuste_estado=nmysql2_query($query_ajuste_estado,$link);
    if($resultado_query_ajuste_estado){
    $resultado=true;
    }
  }
 }   
return $resultado;
}
function obtenerUsuarioCanalId($link){

$usuario_login = $_SESSION['nombreuser'];

$usuario_canal_id=0;

$query_usuario="SELECT USUARIO_CANAL_ID from usuario WHERE USUARIO_LOGIN='$usuario_login'; ";
$resultado_query_usuario=nmysql2_query($query_usuario,$link);
 if($resultado_query_usuario){
  while($row=nmysql2_fetch_assoc($resultado_query_usuario)){
  $usuario_canal_id=$row["USUARIO_CANAL_ID"];
  }
 }

return $usuario_canal_id;
}
function obtenerUsuarioId($link){
  
  $rusuario=$_SESSION['nombreuser'];

  $query_usuario = "SELECT USUARIO_ID FROM usuario WHERE USUARIO_LOGIN='$rusuario'";
  $query = nmysql2_query($query_usuario,$link);    

  if($query){ 
  $row = nmysql2_fetch_array($query);
  $usuario_id=$row["USUARIO_ID"];
  }
  
return $usuario_id;  
}
function obtenerUsuarioEmailUsLog($login,$link){

  $query_usuario = "SELECT USUARIO_EMAIL FROM usuario WHERE USUARIO_LOGIN='$login'";
  $query = nmysql2_query($query_usuario,$link);    

  if($query){ 
  $row = nmysql2_fetch_array($query);
  $usuario_email=$row["USUARIO_EMAIL"];
  }
  
return $usuario_email;  
}
function obtenerArregloTipoServicioAgend($lan){

$etiqueta_fuera_de_garantia=trans('Fuera de Garant&iacute;a',$lan);
$etiqueta_garantia=trans('Garantia',$lan);
$etiqueta_cambio_de_tinta=trans('Cambio de Tinta',$lan);
$etiqueta_mantenimiento_general=trans('Mantenimiento General',$lan);
$etiqueta_servicio_tecnico=trans('Servicio técnico',$lan);
$etiqueta_facturar=trans('Facturado',$lan);
$etiqueta_perfilacion_obsequio=trans('Perfilación (Obsequio)',$lan);
$etiqueta_perfilacion_facturado=trans('Perfilación (Facturado)',$lan);
$etiqueta_instalacion=trans('Instalación',$lan);
$etiqueta_online=trans('Online',$lan);
$etiqueta_servicio=trans('Servicio',$lan);
$etiqueta_venta_partes=trans('Venta de partes',$lan);
$etiqueta_diagnostico=trans('Diagnostico',$lan);

$arreglo_tipo_servicio_agendamiento=[["0","377",$etiqueta_fuera_de_garantia],
                                     ["1","377",$etiqueta_garantia],
                                     ["2","377",$etiqueta_cambio_de_tinta],
                                     ["3","377",$etiqueta_mantenimiento_general],
                                     ["4","377",$etiqueta_servicio_tecnico],
                                     ["5","377",$etiqueta_facturar],
                                     ["6","377",$etiqueta_perfilacion_obsequio],
                                     ["7","377",$etiqueta_perfilacion_facturado],
                                     ["8","377",$etiqueta_instalacion],
                                     ["9","377",$etiqueta_online],
                                     ["1","346",$etiqueta_garantia],
                                     ["9","346",$etiqueta_online],
                                     ["10","346",$etiqueta_servicio],
                                     ["11","346",$etiqueta_venta_partes],
                                     ["12","346",$etiqueta_diagnostico]];
return $arreglo_tipo_servicio_agendamiento;
}
function obtenerNombArregloTipoServicioAgend($lan,$id,$canal_id){
$resultado="";

$arreglo_tipo_servicio_agendamiento=obtenerArregloTipoServicioAgend($lan);

for($i=0;$i<count($arreglo_tipo_servicio_agendamiento);$i++){
 if($arreglo_tipo_servicio_agendamiento[$i][0]==$id&&$arreglo_tipo_servicio_agendamiento[$i][1]==$canal_id){
 $resultado=$arreglo_tipo_servicio_agendamiento[$i][2];
 }
}

return $resultado;
}
function obtenerArregloCompraOrdenTipoEnvio($lan){

$etiqueta_aire=trans('Aire',$lan);
$etiqueta_mar=trans('Mar',$lan);

$arreglo_enviar_via=["AIR"=>$etiqueta_aire,"SEA"=>$etiqueta_mar];

return $arreglo_enviar_via;
}
function obtenerArregloCompraOrdenTerminos($lan){

$arreglo_terminos=["EXW"=>"EXW","FOB"=>"FOB","CIF"=>"CIF","DDP"=>"DDP","CURRIER"=>"CURRIER"];

return $arreglo_terminos;
}
function obtenerArregloCompraOrdenSolicitudOr($lan){

$etiqueta_almacen=trans('Almacén Miami',$lan);

$arreglo_solicitud_origen=["1"=>$etiqueta_almacen,"2"=>"China"];

return $arreglo_solicitud_origen;
}
function obtenerArregloCompraOrdenPrioridad($lan){

$etiqueta_atencion_inmediata=trans('Atención inmediata',$lan);
$etiqueta_muy_urgente=trans('Muy urgente',$lan);
$etiqueta_urgente=trans('Urgente',$lan);

$arreglo_prioridad=["1"=>$etiqueta_atencion_inmediata,"2"=>$etiqueta_muy_urgente,"3"=>$etiqueta_urgente,"5"=>"Normal"];

return $arreglo_prioridad;
}
function obtenerArregloCompraOrdenMotivoParte($lan){

$etiqueta_garantia=trans('Garantía',$lan);
$etiqueta_venta=trans('Venta',$lan);

$arreglo_motivo=[$etiqueta_garantia,$etiqueta_venta];

return $arreglo_motivo;
}
function obtenerArregloCompraEstado($lan){

$etiqueta_no_aprobado=trans('No aprobado',$lan); 
$etiqueta_aprobado=trans('Aprobado',$lan); 
$etiqueta_en_proceso=trans('En proceso',$lan);
$etiqueta_ejecutado=trans('Ejecutado',$lan); 
$etiqueta_anulado=trans('Anulado',$lan);

$arreglo_compra_estado=["1"=>$etiqueta_no_aprobado,"2"=>$etiqueta_aprobado,"3"=>$etiqueta_ejecutado,"4"=>$etiqueta_anulado,"5"=>$etiqueta_en_proceso];

return $arreglo_compra_estado;
}
function obtenerArregloArtConsignacionEstado($lan){

$etiqueta_anulado=trans('Anulado',$lan);
$etiqueta_en_proceso=trans('En proceso',$lan);
$etiqueta_ejecutado=trans('Ejecutado',$lan); 

$arreglo_art_consignacion_estado=["0"=>$etiqueta_anulado,"1"=>$etiqueta_ejecutado,"2"=>$etiqueta_en_proceso];

return $arreglo_art_consignacion_estado;
}
function obtenerUnidadesArtIdBT($link,$articulo_id,$canal_id){

$unidades="";

if($canal_id==346){

$query_unidades="SELECT (ARTICULO_CANTIDAD-getUnidadesUsuarioPrest(ARTICULO_ID)) AS ARTICULO_CANTIDAD FROM articulo WHERE ARTICULO_ID=$articulo_id ";

}else{

$query_unidades="SELECT ARTICULO_CANTIDAD FROM articulo WHERE ARTICULO_ID=$articulo_id ";

}

$resultado_query_unidades=nmysql2_query($query_unidades,$link);

while($row=nmysql2_fetch_assoc($resultado_query_unidades)){
$unidades=$row["ARTICULO_CANTIDAD"];
}

return $unidades;
}
function bod_trans_inv_realizar_registro($link,$arreglo_datos_registro,$arreglo_articulos,$bodega_id,$tipo,$articulo_barcode_indice,$articulo_origen_bodega_id=0,$consignacion_id=null,$devolucion_id=null){

$control_reload=array(); 

if($articulo_origen_bodega_id!=0){
 for($cont_art_origen=0;$cont_art_origen<count($arreglo_articulos);$cont_art_origen++){
 $query_chequeo_articulo="SELECT ARTICULO_ID FROM articulo WHERE ARTICULO_REFERENCIA='".nmysql2_escape_string($arreglo_articulos[$cont_art_origen]["INV_REGISTRO_ITEM_REFERENCIA"],$link)."' AND ARTICULO_BODEGA=$bodega_id ";
 $resultado_query_chequeo_articulo=nmysql2_query($query_chequeo_articulo,$link); 
  if($resultado_query_chequeo_articulo){
  $num_reg_articulo_revision=nmysql2_num_rows($resultado_query_chequeo_articulo);
  
   if($num_reg_articulo_revision==0){
   
 $query_copia="";
 
 $query_max_id="SELECT MAX(ARTICULO_ID) AS MAXIMO FROM articulo ";
 $maximo=  nmysql2_query($query_max_id,$link);
 $max;
 if (nmysql2_num_rows($maximo)!=0){
  while($row = nmysql2_fetch_array($maximo)) {
      $max=$row["MAXIMO"]+1;      
  }
 }
 
 $arreglo_campos_restringidos=["ARTICULO_UBICACION","ARTICULO_PRECIO_VENTA","ARTICULO_COSTO_VENTA"];
 
 $arreglo_modficados=["ARTICULO_ID"=>$max,"ARTICULO_BODEGA"=>$bodega_id,"ARTICULO_CANTIDAD"=>"0"];
 
 $query_campos_reporte="SELECT * FROM articulo WHERE ARTICULO_REFERENCIA='".nmysql2_escape_string($arreglo_articulos[$cont_art_origen]["INV_REGISTRO_ITEM_REFERENCIA"],$link)."' AND ARTICULO_BODEGA = $articulo_origen_bodega_id ";
 
 $resultado_query_campos_reporte=nmysql2_query($query_campos_reporte,$link);
 
 if($resultado_query_campos_reporte){
 
  $cont=0;
  
  $numero_campos=0;
  
  $campos_origen="";
  
  $campos_destino="";
  
  $contador_campos_agregados=0;
  
  $numero_campos=mysql2_num_fields($resultado_query_campos_reporte);
  
  $numero_campos_agregados=$numero_campos-count($arreglo_campos_restringidos);
    
  $query_copia="INSERT INTO articulo (";
  
  while($cont<$numero_campos){
  
  $busqueda_restringido=false;
  
  $campo_modificado=false;
  
  $campo_valor="";

  $simbolo_coma="";

   $campo = mysql2_fetch_field($resultado_query_campos_reporte);
   
   for($i=0;$i<count($arreglo_campos_restringidos);$i++){
   
    if($campo->name==$arreglo_campos_restringidos[$i]){
    
    $busqueda_restringido=true;
    
    }
   
   }

   if(!$busqueda_restringido){
      
     if(($contador_campos_agregados)!=($numero_campos_agregados-1)){
     $simbolo_coma=",";
     }
      
   $campos_origen.=$campo->name.$simbolo_coma;
   
      foreach ($arreglo_modficados as $key_campo_modificado=>$campo_modificado_valor) {

        if($key_campo_modificado==$campo->name){
    
        $campo_modificado=true;
        
        $campo_valor=$campo_modificado_valor;
        
        }
   
      }
   
   if(!$campo_modificado){
   
   $campos_destino.=$campo->name.$simbolo_coma;
   
   }else{
   
   $campos_destino.="'".$campo_valor."'".$simbolo_coma;
   
   }
   
  $contador_campos_agregados++;
      
  }
  
  $cont++;
  
  }

  $query_copia.=$campos_origen.") SELECT ".$campos_destino." FROM articulo WHERE ARTICULO_REFERENCIA='".nmysql2_escape_string($arreglo_articulos[$cont_art_origen]["INV_REGISTRO_ITEM_REFERENCIA"],$link)."' AND ARTICULO_BODEGA = $articulo_origen_bodega_id;";
  
  $resultado_query_copia=nmysql2_query($query_copia,$link);
  
  if($resultado_query_copia){
   if(nmysql2_affected_rows($link)>0){
   $copiar_articulo_id=nmysqli2_insert_id($link);
   
    $queryMaxImagen="SELECT MAX(IMAGEN_ID) AS IMAGEN_ID FROM imagen ";
    $resultMaxImagen=  nmysql2_query($queryMaxImagen,$link);
    $maxImagen=  nmysql2_fetch_assoc($resultMaxImagen);
    $maxImg=$maxImagen["IMAGEN_ID"]+1;
    $query_insert_imagen="INSERT INTO imagen (IMAGEN_ID,IMAGEN_ARTICULO_REFERENCIA,IMAGEN_ARTICULO_BODEGA)VALUES($maxImg,'".nmysql2_escape_string($arreglo_articulos[$cont_art_origen]["INV_REGISTRO_ITEM_REFERENCIA"],$link)."',$bodega_id)";
    $insert_imagen=  nmysql2_query($query_insert_imagen,$link);  

    $query_insert_articulo_aplicacion="INSERT INTO articulo_aplicacion (ARTICULO_REFERENCIA,APLICACION_ID,ARTICULO_BODEGA) SELECT '".nmysql2_escape_string($arreglo_articulos[$cont_art_origen]["INV_REGISTRO_ITEM_REFERENCIA"],$link)."',APLICACION_ID,'$bodega_id' FROM articulo_aplicacion WHERE ARTICULO_REFERENCIA='".nmysql2_escape_string($arreglo_articulos[$cont_art_origen]["INV_REGISTRO_ITEM_REFERENCIA"],$link)."' AND ARTICULO_BODEGA = $articulo_origen_bodega_id ";
    $insert_articulo = nmysql2_query($query_insert_articulo_aplicacion,$link);

   }
  }else{
  registrar("Articulo","articulo",7,"Ingreso articulo referencia=".$arreglo_articulos[$cont_art_origen]["INV_REGISTRO_ITEM_REFERENCIA"]." bodega=".$articulo_origen_bodega_id,$link);
  }
  
 }else{
 registrar("Articulo","articulo",7,"Ingreso articulo referencia=".$arreglo_articulos[$cont_art_origen]["INV_REGISTRO_ITEM_REFERENCIA"]." bodega=".$articulo_origen_bodega_id,$link);
 }
   
   }
   
  } 
 }
}

$fecha=$arreglo_datos_registro["INV_REGISTRO_MOVIMIENTO_FECHA"];
$tercero=$arreglo_datos_registro["INV_REGISTRO_MOVIMIENTO_CLIENTE_ID"];
$movimiento_tipo=$arreglo_datos_registro["INV_REGISTRO_MOVIMIENTO_TIPO"];
$documento=$arreglo_datos_registro["INV_REGISTRO_DOCUMENTO"];
$documento_fecha=$arreglo_datos_registro["INV_REGISTRO_DOCUMENTO_FECHA"];
$usuario_id=$arreglo_datos_registro["INV_REGISTRO_MOVIMIENTO_USUARIO_ID"];
$rusuario=$arreglo_datos_registro["INV_REGISTRO_MOVIMIENTO_USUARIO"];
$tecnico=$arreglo_datos_registro["INV_REGISTRO_MOVIMIENTO_USUARIO_TECNICO"];
$observaciones=$arreglo_datos_registro["INV_REGISTRO_MOVIMIENTO_OBSERVACIONES"];

 if(($tercero=="")||($tercero==null)){
 $vtercero="NULL";
 }
 else{
 $vtercero=$tercero;
 }  

 if(($documento=="")||($documento==null)){
 $vdocumento="'".$documento."'";
 }
 else{
 $vdocumento="'".FormatearDatosEscStringT($documento,$link)."'";
 } 
 
 if(($documento_fecha=="")||($documento_fecha==null)){
 $vdocumento_fecha="NULL";
 }
 else{
 $vdocumento_fecha="'".$documento_fecha."'";
 }
 
 if(($movimiento_tipo=="")||($movimiento_tipo==null)){
 $vmovimiento_tipo="NULL";
 }
 else{
 $vmovimiento_tipo=$movimiento_tipo;
 }
 
 if(($observaciones=="")||($observaciones==null)){
 $vobservaciones="NULL";
 }
 else{
 $vobservaciones="'".FormatearDatosEscStringT($observaciones,$link)."'";
 }

 if(($consignacion_id=="")||($consignacion_id==null)){
 $vconsignacion_id="NULL";
 }
 else{
 $vconsignacion_id=$consignacion_id;
 }

 if(($devolucion_id=="")||($devolucion_id==null)){
 $vdevolucion_id="NULL";
 }
 else{
 $vdevolucion_id=$devolucion_id;
 }
 
 $control_reload[0]=0;
 $control_reload[1]=0;
 $control_reload[2]=0;
 $control_reload[3]=0;
 $control_reload[4]=0;
 $control_reload[5]=0;
 $control_reload[6]=[];
 
 if($tipo=="ENTRADA"){
 $operacion_simbolo="+";
 }
 
 if($tipo=="SALIDA"){
 $operacion_simbolo="-";
 }
 
     if(is_null($tecnico))
         $query_insert_movimiento="INSERT INTO movimiento(DOCUMENTO,MOVIMIENTO_CLIENTE_ID,MOVIMIENTO_FECHA,MOVIMIENTO_TIPO,MOVIMIENTO_BODEGA,MOVIMIENTO_OBSERVACION,MOVIMIENTO_USUARIO,MOVIMIENTO_ARTICULO_CONSIGNACION_PARTE_ID,MOVIMIENTO_ARTICULO_DEVOLUCION_PARTE_ID)VALUES($vdocumento,$vtercero,'$fecha',$vmovimiento_tipo,$bodega_id,$vobservaciones,'$rusuario',$vconsignacion_id,$vdevolucion_id)";
     else
         $query_insert_movimiento="INSERT INTO movimiento(DOCUMENTO,MOVIMIENTO_CLIENTE_ID,MOVIMIENTO_FECHA,MOVIMIENTO_TIPO,MOVIMIENTO_BODEGA,MOVIMIENTO_OBSERVACION,MOVIMIENTO_USUARIO,MOVIMIENTO_USUARIO_TECNICO,MOVIMIENTO_ARTICULO_CONSIGNACION_PARTE_ID,MOVIMIENTO_ARTICULO_DEVOLUCION_PARTE_ID)VALUES($vdocumento,$vtercero,'$fecha',$vmovimiento_tipo,$bodega_id,$vobservaciones,'$rusuario','$tecnico',$vconsignacion_id,$vdevolucion_id)";

 $resultado_movimiento_id_tabla_origen= nmysql2_query($query_insert_movimiento,$link);
 
 if($resultado_movimiento_id_tabla_origen){

 $movimiento_id= nmysqli2_insert_id($link);
 
 //verificamos que se halla ralizado el insert
 if(nmysql2_affected_rows($link)>0){
 
 $no_articulos=count($arreglo_articulos);
 
 for($cont=0;$cont<$no_articulos;$cont++){ 
 
 if($arreglo_articulos[$cont]["INV_REGISTRO_ITEM_BODEGA_ID"]!=$arreglo_articulos[$cont]["INV_REGISTRO_ITEM_LEGALIZADO_BODEGA_ID"]){
 
 $query_validacion_articulo="SELECT ARTICULO_ID FROM articulo WHERE ARTICULO_REFERENCIA='".nmysql2_escape_string($arreglo_articulos[$cont]["INV_REGISTRO_ITEM_REFERENCIA"],$link)."' AND ARTICULO_BODEGA=".$arreglo_articulos[$cont]["INV_REGISTRO_ITEM_LEGALIZADO_BODEGA_ID"]." ";
 
 $resultado_query_validacion_articulo=nmysql2_query($query_validacion_articulo,$link);
 
 if($resultado_query_validacion_articulo){
 
  if(nmysql2_num_rows($resultado_query_validacion_articulo)==0){
  
 $query_copia="";
 
 $query_max_id="SELECT MAX(ARTICULO_ID) AS MAXIMO FROM articulo ";
 $maximo=  nmysql2_query($query_max_id,$link);
 $max;
 if (nmysql2_num_rows($maximo)!=0){
  while($row = nmysql2_fetch_array($maximo)) {
      $max=$row["MAXIMO"]+1;      
  }
 }
 
 $arreglo_campos_restringidos=["ARTICULO_UBICACION","ARTICULO_PRECIO_VENTA","ARTICULO_COSTO_VENTA"];
 
 $arreglo_modficados=["ARTICULO_ID"=>$max,"ARTICULO_BODEGA"=>$arreglo_articulos[$cont]["INV_REGISTRO_ITEM_LEGALIZADO_BODEGA_ID"],"ARTICULO_CANTIDAD"=>"0"];
 
 $query_campos_reporte="SELECT * FROM articulo WHERE ARTICULO_REFERENCIA='".nmysql2_escape_string($arreglo_articulos[$cont]["INV_REGISTRO_ITEM_REFERENCIA"],$link)."' AND ARTICULO_BODEGA = ".$arreglo_articulos[$cont]["INV_REGISTRO_ITEM_BODEGA_ID"]." ";
 
 $resultado_query_campos_reporte=nmysql2_query($query_campos_reporte,$link);
 
 if($resultado_query_campos_reporte){
 
  $contador2=0;
  
  $numero_campos=0;
  
  $campos_origen="";
  
  $campos_destino="";
  
  $contador_campos_agregados=0;
  
  $numero_campos=mysql2_num_fields($resultado_query_campos_reporte);
  
  $numero_campos_agregados=$numero_campos-count($arreglo_campos_restringidos);
    
  $query_copia="INSERT INTO articulo (";
  
  while($contador2<$numero_campos){
  
  $busqueda_restringido=false;
  
  $campo_modificado=false;
  
  $campo_valor="";

  $simbolo_coma="";

   $campo = mysql2_fetch_field($resultado_query_campos_reporte);
   
   for($i=0;$i<count($arreglo_campos_restringidos);$i++){
   
    if($campo->name==$arreglo_campos_restringidos[$i]){
    
    $busqueda_restringido=true;
    
    }
   
   }

   if(!$busqueda_restringido){
      
     if(($contador_campos_agregados)!=($numero_campos_agregados-1)){
     $simbolo_coma=",";
     }
      
   $campos_origen.=$campo->name.$simbolo_coma;
   
      foreach ($arreglo_modficados as $key_campo_modificado=>$campo_modificado_valor) {

        if($key_campo_modificado==$campo->name){
    
        $campo_modificado=true;
        
        $campo_valor=$campo_modificado_valor;
        
        }
   
      }
   
   if(!$campo_modificado){
   
   $campos_destino.=$campo->name.$simbolo_coma;
   
   }else{
   
   $campos_destino.="'".$campo_valor."'".$simbolo_coma;
   
   }
   
  $contador_campos_agregados++;
      
  }
  
  $contador2++;
  
  }

  $query_copia.=$campos_origen.") SELECT ".$campos_destino." FROM articulo WHERE ARTICULO_REFERENCIA='".nmysql2_escape_string($arreglo_articulos[$cont]["INV_REGISTRO_ITEM_REFERENCIA"],$link)."' AND ARTICULO_BODEGA = ".$arreglo_articulos[$cont]["INV_REGISTRO_ITEM_BODEGA_ID"].";";
  
  $resultado_query_copia=nmysql2_query($query_copia,$link);
  
  if($resultado_query_copia){
   if(nmysql2_affected_rows($link)>0){
   $copiar_articulo_id=nmysqli2_insert_id($link);
   
    $queryMaxImagen="SELECT MAX(IMAGEN_ID) AS IMAGEN_ID FROM imagen ";
    $resultMaxImagen=  nmysql2_query($queryMaxImagen,$link);
    $maxImagen=  nmysql2_fetch_assoc($resultMaxImagen);
    $maxImg=$maxImagen["IMAGEN_ID"]+1;
    $query_insert_imagen="INSERT INTO imagen (IMAGEN_ID,IMAGEN_ARTICULO_REFERENCIA,IMAGEN_ARTICULO_BODEGA)VALUES($maxImg,'".nmysql2_escape_string($arreglo_articulos[$cont]["INV_REGISTRO_ITEM_REFERENCIA"],$link)."',".$arreglo_articulos[$cont]["INV_REGISTRO_ITEM_LEGALIZADO_BODEGA_ID"].") ";
    $insert_imagen=  nmysql2_query($query_insert_imagen,$link);

    $query_insert_articulo_aplicacion="INSERT INTO articulo_aplicacion (ARTICULO_REFERENCIA,APLICACION_ID,ARTICULO_BODEGA) SELECT '".nmysql2_escape_string($arreglo_articulos[$cont]["INV_REGISTRO_ITEM_REFERENCIA"],$link)."',APLICACION_ID,'".$arreglo_articulos[$cont]["INV_REGISTRO_ITEM_LEGALIZADO_BODEGA_ID"]."' FROM articulo_aplicacion WHERE ARTICULO_REFERENCIA='".nmysql2_escape_string($arreglo_articulos[$cont]["INV_REGISTRO_ITEM_REFERENCIA"],$link)."' AND ARTICULO_BODEGA = ".$arreglo_articulos[$cont]["INV_REGISTRO_ITEM_BODEGA_ID"]." ";
    $insert_articulo = nmysql2_query($query_insert_articulo_aplicacion,$link);

   }
  }else{
  registrar("Articulo","articulo",7,"Ingreso articulo referencia=".$arreglo_articulos[$cont]["INV_REGISTRO_ITEM_REFERENCIA"]." bodega=".$arreglo_articulos[$cont]["INV_REGISTRO_ITEM_BODEGA_ID"],$link);
  }
  
 }else{
 registrar("Articulo","articulo",7,"Ingreso articulo referencia=".$arreglo_articulos[$cont]["INV_REGISTRO_ITEM_REFERENCIA"]." bodega=".$arreglo_articulos[$cont]["INV_REGISTRO_ITEM_BODEGA_ID"],$link);
 }  
  
  }
 
 }
 
 }
 
  $query_cantidades="UPDATE articulo SET ARTICULO_CANTIDAD=ARTICULO_CANTIDAD $operacion_simbolo ".$arreglo_articulos[$cont]["INV_REGISTRO_ITEM_CANTIDAD"]." WHERE ARTICULO_REFERENCIA='".nmysql2_escape_string($arreglo_articulos[$cont]["INV_REGISTRO_ITEM_REFERENCIA"],$link)."' AND ARTICULO_BODEGA=$bodega_id;";
  
  $resultado_query_cantidades=nmysql2_query($query_cantidades,$link);
  
  $consig_parte_item_id=$arreglo_articulos[$cont]["INV_REGISTRO_ITEM_DOC_ITEM_ID"];
  $articulo_id=$arreglo_articulos[$cont]["INV_REGISTRO_ITEM_ARTICULO_ID"];
  $articulo_referencia=$arreglo_articulos[$cont]["INV_REGISTRO_ITEM_REFERENCIA"];
  $cantidad=$arreglo_articulos[$cont]["INV_REGISTRO_ITEM_CANTIDAD"];
  
  if($resultado_query_cantidades){
  
  $serial1=$arreglo_articulos[$cont]["INV_REGISTRO_ITEM_SERIAL1"];
  
  	if(($serial1=="")||($serial1==NULL)){
	$serial1="NULL";
	}
	if($serial1!="NULL"){
	$serial1="'".nmysql2_escape_string($serial1,$link)."'";
	}
  
  $serial2=$arreglo_articulos[$cont]["INV_REGISTRO_ITEM_SERIAL2"];
  
  	if(($serial2=="")||($serial2==NULL)){
	$serial2="NULL";
	}
	if($serial2!="NULL"){
	$serial2="'".nmysql2_escape_string($serial2,$link)."'";
	}
  
  	$query_movimiento_item="INSERT INTO movimiento_item(MOVIMIENTO_ID,ARTICULO_REFERENCIA,CANTIDAD,MOVIMIENTO_ITEM_BODEGA,MOVIMIENTO_ITEM_SERIAL1,MOVIMIENTO_ITEM_SERIAL2)VALUES($movimiento_id ,'".nmysql2_escape_string($articulo_referencia,$link)."',$cantidad,$bodega_id,".$serial1.",".$serial2.")";
	$movimiento_item=  nmysql2_query($query_movimiento_item,$link);
                    
	if(nmysql2_affected_rows($link)==1){
	
    $movimiento_item_id=nmysqli2_insert_id($link);
    //Registra serial si es necesario
     if(($serial1!="NULL")||($serial2!="NULL")){                        
     registrarSerial($articulo_id,$serial1,$serial2,$tipo,$movimiento_item_id,$cantidad,$link);    
     }	
	
	}else{
	
        if($tipo=="ENTRADA"){
        $correccion_operador="-";
        }
        
        if($tipo=="SALIDA"){
        $correccion_operador="+";
        }		
	
    $query_cantidades_error="UPDATE articulo SET ARTICULO_CANTIDAD=ARTICULO_CANTIDAD $correccion_operador ".$arreglo_articulos[$cont]["INV_REGISTRO_ITEM_CANTIDAD"]." WHERE ARTICULO_REFERENCIA='".nmysql2_escape_string($arreglo_articulos[$cont]["INV_REGISTRO_ITEM_REFERENCIA"],$link)."' AND ARTICULO_BODEGA=$bodega_id;";
    
    $resultado_query_cantidades_error=nmysql2_query($query_cantidades_error,$link);

    $arreglo_item_error["ERROR_TIPO"]="CONSULTA";    
    $arreglo_item_error["ERROR_DOC_ITEM_ID"]=$consig_parte_item_id;
    $arreglo_item_error["ERROR_REFERENCIA"]=$articulo_referencia;
    $arreglo_item_error["ERROR_CANTIDAD"]=$cantidad;
        
    $control_reload[6][]=$arreglo_item_error;
	
	}

  }else{
  
  $arreglo_item_error=array();
  
  $error_cantidades=mysqli_error($link);

  $cantidades_bodega=explode('Error_cant_sin_unidades_',$error_cantidades);

  if(count($cantidades_bodega)>1){

  $arreglo_item_error["ERROR_TIPO"]="SIN_UNIDADES";

  }else{
  
  $arreglo_item_error["ERROR_TIPO"]="CONSULTA";
  
  }
      
  $arreglo_item_error["ERROR_DOC_ITEM_ID"]=$consig_parte_item_id;
  $arreglo_item_error["ERROR_REFERENCIA"]=$articulo_referencia;
  $arreglo_item_error["ERROR_CANTIDAD"]=$cantidad;
        
  $control_reload[6][]=$arreglo_item_error;
  
  }
  
 } 
 
 if(($bodega_id==1)||($bodega_id==4)||($bodega_id==10)||($bodega_id==30)){
 $control_reload_duplicado=bod_trans_inv_duplicarInventarioBarcode($link,$movimiento_id,$usuario_id,$tipo);
  for($i=0;$i<count($control_reload_duplicado);$i++){
   if($control_reload_duplicado[$i]!=0){
   registrar("bodega_realizar_registro","Bodega_realizar_registro",7,"Error barcode duplicado movimiento_id=$movimiento_id usuario_id=$usuario_id tipo=$tipo control_reload$i=$control_reload_duplicado[$i]",$link);
   }
  }
 }
 
}else{
$control_reload[0] = 1;
}

}else{
$control_reload[0] = 1;
}

return $control_reload;

}
function bod_trans_inv_duplicarInventarioBarcode($link,$movimiento_id,$usuario_id,$tipo){

$control_reload=array();
 
$control_reload[0]=0;
$control_reload[1]=0;
$control_reload[2]=0;
$control_reload[3]=0;
$control_reload[4]=0;
$control_reload[5]=0;
$control_reload[6]=0;

if($tipo=="ENTRADA"){

    $query_codigo_barras="INSERT INTO movimiento_codigo_barras "
    ."(MOVIMIENTO_CODIGO_BARRAS_ID,"
	."MOVIMIENTO_CODIGO_BARRAS_FECHA,"
    ."MOVIMIENTO_CODIGO_BARRAS_TIPO,"
    ."MOVIMIENTO_CODIGO_BARRAS_BODEGA_ID,"
    ."MOVIMIENTO_CODIGO_BARRAS_USUARIO,"
	."MOVIMIENTO_CODIGO_BARRAS_USUARIO_ID,"
	."MOVIMIENTO_CODIGO_BARRAS_MOVIMIENTO_CODIGO_BARRAS_TEMPORAL_ID,"
	."MOVIMIENTO_CODIGO_BARRAS_CLIENTE_ID,"
	."MOVIMIENTO_CODIGO_BARRAS_TIPO_DETALLE,"
	."MOVIMIENTO_CODIGO_BARRAS_DOCUMENTO,"
	."MOVIMIENTO_CODIGO_BARRAS_DOCUMENTO_FECHA,"
	."MOVIMIENTO_CODIGO_BARRAS_OBSERVACION,"
	."MOVIMIENTO_CODIGO_BARRAS_ARTICULO_CONSIGNACION_PARTE_ID,"
	."MOVIMIENTO_CODIGO_BARRAS_ARTICULO_DEVOLUCION_PARTE_ID)"
	."SELECT $movimiento_id,"
	."MOVIMIENTO_FECHA,"
	."1,"
	."MOVIMIENTO_BODEGA,"
	."MOVIMIENTO_USUARIO,"
	."$usuario_id,"
	."0,"
	."MOVIMIENTO_CLIENTE_ID,"
	."MOVIMIENTO_TIPO,"
	."DOCUMENTO,"
	."DOCUMENTO_FECHA,"
	."MOVIMIENTO_OBSERVACION,"
	."MOVIMIENTO_ARTICULO_CONSIGNACION_PARTE_ID,"
	."MOVIMIENTO_ARTICULO_DEVOLUCION_PARTE_ID "
	."FROM "
	."movimiento "
	."WHERE MOVIMIENTO_ID=$movimiento_id;";
	
    //echo $query_codigo_barras;
    $resultado_query_codigo_barras=nmysql2_query($query_codigo_barras,$link);
    
    if($resultado_query_codigo_barras){
      if(nmysql2_affected_rows($link)==0)
      {
      $control_reload[3] = 1;
      }
      if(nmysql2_affected_rows($link)>0){     
	  	  	  
		$query_codigo_barras_item="SELECT ARTICULO_REFERENCIA,"
								."CANTIDAD,"
								."MOVIMIENTO_ITEM_BODEGA,"
								."MOVIMIENTO_ITEM_SERIAL1,"
								."MOVIMIENTO_ITEM_SERIAL2,"
								."MOVIMIENTO_ITEM_FECHA_TINTA "
								."FROM "
								."movimiento_item "
								."WHERE MOVIMIENTO_ID=$movimiento_id;";
		
		//echo $query_codigo_barras_item;
		
		$resultado_query_codigo_barras_item=nmysql2_query($query_codigo_barras_item,$link);
		
		if($resultado_query_codigo_barras_item){
		
          while($row=nmysql2_fetch_assoc($resultado_query_codigo_barras_item)){
			$articulo_referencia_revisada=$row["ARTICULO_REFERENCIA"];
			$articulo_cantidad_revisada=$row["CANTIDAD"];			
			$bodega_id_revisada=$row["MOVIMIENTO_ITEM_BODEGA"];
			$articulo_serial1_revisada=$row["MOVIMIENTO_ITEM_SERIAL1"];
			$articulo_serial2_revisada=$row["MOVIMIENTO_ITEM_SERIAL2"];
			$articulo_fecha_tinta_revisada=$row["MOVIMIENTO_ITEM_FECHA_TINTA"];
						
			$query_articulo_codigo_barras_indice="SELECT ARTICULO_CODIGO_BARRAS_INDICE_CONSECUTIVO_ID FROM articulo_codigo_barras_indice_consecutivo";
			$resultado_query_articulo_codigo_barras_indice=nmysql2_query($query_articulo_codigo_barras_indice,$link);
    
			if($resultado_query_articulo_codigo_barras_indice){
			  if(nmysql2_num_rows($resultado_query_articulo_codigo_barras_indice)>0){
			   while($row1=nmysql2_fetch_assoc($resultado_query_articulo_codigo_barras_indice)){
			    $articulo_codigo_barras_indice=$row1["ARTICULO_CODIGO_BARRAS_INDICE_CONSECUTIVO_ID"];
				$query_barcode="SELECT ARTICULO_CODIGO_BARRAS_INDICE,ARTICULO_CODIGO_BARRAS_ID FROM articulo_codigo_barras_$articulo_codigo_barras_indice "
							  ."WHERE ARTICULO_CODIGO_BARRAS_REFERENCIA_TEMPORAL='".nmysql2_escape_string($articulo_referencia_revisada,$link)."' "
							  ."AND ARTICULO_CODIGO_BARRAS_BODEGA=$bodega_id_revisada;";
				//echo $query_barcode;			  
				$resultado_query_barcode=nmysql2_query($query_barcode,$link);
				if( nmysql2_num_rows($resultado_query_barcode) > 0 ){
				
				$row2=nmysql2_fetch_assoc($resultado_query_barcode);
				
				$articulo_id=$row2["ARTICULO_CODIGO_BARRAS_ID"];		
		        $articulo_indice=$row2["ARTICULO_CODIGO_BARRAS_INDICE"];
		  
			$query_codigo_barras_item_cantidad="UPDATE articulo_codigo_barras_$articulo_indice art "
											  ."SET art.ARTICULO_CODIGO_BARRAS_CANTIDAD=(art.ARTICULO_CODIGO_BARRAS_CANTIDAD+$articulo_cantidad_revisada) "
											  ."WHERE art.ARTICULO_CODIGO_BARRAS_ID=$articulo_id;";
			
			//echo $query_codigo_barras_item_cantidad;
			
			$resultado_query_codigo_barras_item_cantidad=nmysql2_query($query_codigo_barras_item_cantidad,$link);
		
		    if($resultado_query_codigo_barras_item_cantidad){		    			
				
				$query_movimiento_item_codigo_barras="INSERT INTO movimiento_item_codigo_barras "
				."(MOVIMIENTO_ITEM_CODIGO_BARRAS_MOVIMIENTO_CODIGO_BARRAS_ID,"
				."MOVIMIENTO_ITEM_CODIGO_BARRAS_ARTICULO_CODIGO_BARRAS_ID,"
				."MOVIMIENTO_ITEM_CODIGO_BARRAS_ARTICULO_CODIGO_BARRAS_IN,"
				."MOVIMIENTO_ITEM_CODIGO_BARRAS_BODEGA_ID,"				
				."MOVIMIENTO_ITEM_CODIGO_BARRAS_CANTIDAD,"
				."MOVIMIENTO_ITEM_CODIGO_BARRAS_ESTADO,"
				."MOVIMIENTO_ITEM_CODIGO_BARRAS_USUARIO_ID,"
				."MOVIMIENTO_ITEM_CODIGO_BARRAS_MOVIMIENTO_ITEM_CODIGO_BARRAS_T_ID) "				
				."VALUES "
				."($movimiento_id,"
				."$articulo_id,"
				."$articulo_indice,"
				."$bodega_id_revisada,"
				."$articulo_cantidad_revisada,"
				."1,"
				."$usuario_id,"
				."0);";


				//echo $query_movimiento_item_codigo_barras;
				
				$resultado_query_codigo_barras=nmysql2_query($query_movimiento_item_codigo_barras,$link);			
				
				if($resultado_query_codigo_barras){
				
				$num_registros_afectados=nmysql2_affected_rows($link);					
				
				  if($num_registros_afectados==0){
				  $control_reload[4]=1;
				  }

				  if($num_registros_afectados>0){
				  $movimiento_item_id=nmysqli2_insert_id($link);
				  
				  if((!(is_null($articulo_fecha_tinta_revisada)))&&($articulo_fecha_tinta_revisada!="")){
				  
				  $query_movimiento_item_guardados="INSERT INTO movimiento_item_codigo_barras_fecha_vencimiento "
					."(MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_V_MOVIMIENTO_ITEM_COD_BAR_ID,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_V_ARTICULO_CODIGO_BARRAS_IN,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_V_ARTICULO_CODIGO_BARRAS_ID,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_V_ITEM_FECHA,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_V_MOVIMIENTO_TIPO,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_V_MOVIMIENTO_ITEM_F_VT_ID) "
					."VALUES "
					."($movimiento_item_id,"			
					."$articulo_indice,"
					."$articulo_id,"
					."'$articulo_fecha_tinta_revisada',"
					."1,"
					."0); ";
					
				  //echo $query_movimiento_item_guardados;	
				  $resultado_query_movimiento_item_guardados=nmysql2_query($query_movimiento_item_guardados,$link);
				  
				  if($resultado_query_movimiento_item_guardados){
				 
				  }
				  else{
				  $control_reload[5]=1;
				  }
				  
				  }
				  
				  if((!(is_null($articulo_serial1_revisada)))&&($articulo_serial1_revisada!="")){
				  
				  	$query_movimiento_item_guardados="INSERT INTO movimiento_item_codigo_barras_serial "
					."(MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_MOVIMIENTO_ITEM_COD_BAR_ID,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_ARTICULO_CODIGO_BARRAS_IN,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_ARTICULO_CODIGO_BARRAS_ID,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_ITEM_SERIAL,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_ITEM_SERIAL_NUMERO,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_MOVIMIENTO_TIPO,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_MOVIMIENTO_ITEM_S_T_ID) "
					."VALUES "
					."($movimiento_item_id,"
					."$articulo_indice,"
					."$articulo_id,"
					."'".nmysql2_escape_string($articulo_serial1_revisada,$link)."',"
					."0,"
					."1,"
					."0);";
					
				  //echo $query_movimiento_item_guardados;
				  $resultado_query_movimiento_item_guardados=nmysql2_query($query_movimiento_item_guardados,$link);
				  
				  if($resultado_query_movimiento_item_guardados){
				 
				  }
				  else{
				  $control_reload[5]=1;
				  }				  
				  
				  }

				  if((!(is_null($articulo_serial2_revisada)))&&($articulo_serial2_revisada!="")){
				  
				  	$query_movimiento_item_guardados="INSERT INTO movimiento_item_codigo_barras_serial "
					."(MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_MOVIMIENTO_ITEM_COD_BAR_ID,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_ARTICULO_CODIGO_BARRAS_IN,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_ARTICULO_CODIGO_BARRAS_ID,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_ITEM_SERIAL,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_ITEM_SERIAL_NUMERO,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_MOVIMIENTO_TIPO,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_MOVIMIENTO_ITEM_S_T_ID) "
					."VALUES "
					."($movimiento_item_id,"
					."$articulo_indice,"
					."$articulo_id,"
					."'".nmysql2_escape_string($articulo_serial2_revisada,$link)."',"
					."1,"
					."1,"
					."0);";
					
				  //echo $query_movimiento_item_guardados;
				  $resultado_query_movimiento_item_guardados=nmysql2_query($query_movimiento_item_guardados,$link);
				  
				  if($resultado_query_movimiento_item_guardados){
				 
				  }
				  else{
				  $control_reload[5]=1;
				  }				  
				  
				  }
				  
				  }

				}
				else{
				$control_reload[4]=1;
				}
			  
			} else{
	        $control_reload[4]=1;
			}					
				
				}	
			   }
			  }
			}				 		 
		
		}
	  
	   }
	  
      }
    }
    else{
    $control_reload[3] = 1;
    }

}


if($tipo=="SALIDA"){

    $query_codigo_barras="INSERT INTO movimiento_codigo_barras "
    ."(MOVIMIENTO_CODIGO_BARRAS_ID,"
	."MOVIMIENTO_CODIGO_BARRAS_FECHA,"
    ."MOVIMIENTO_CODIGO_BARRAS_TIPO,"
    ."MOVIMIENTO_CODIGO_BARRAS_BODEGA_ID,"
    ."MOVIMIENTO_CODIGO_BARRAS_USUARIO,"
	."MOVIMIENTO_CODIGO_BARRAS_USUARIO_ID,"
	."MOVIMIENTO_CODIGO_BARRAS_MOVIMIENTO_CODIGO_BARRAS_TEMPORAL_ID,"
	."MOVIMIENTO_CODIGO_BARRAS_CLIENTE_ID,"
	."MOVIMIENTO_CODIGO_BARRAS_TIPO_DETALLE,"
	."MOVIMIENTO_CODIGO_BARRAS_DOCUMENTO,"
	."MOVIMIENTO_CODIGO_BARRAS_DOCUMENTO_FECHA,"
	."MOVIMIENTO_CODIGO_BARRAS_OBSERVACION)"
	."SELECT $movimiento_id,"
	."MOVIMIENTO_FECHA,"
	."2,"
	."MOVIMIENTO_BODEGA,"
	."MOVIMIENTO_USUARIO,"
	."$usuario_id,"
	."0,"
	."MOVIMIENTO_CLIENTE_ID,"
	."MOVIMIENTO_TIPO,"
	."DOCUMENTO,"
	."DOCUMENTO_FECHA,"
	."MOVIMIENTO_OBSERVACION "
	."FROM "
	."movimiento "
	."WHERE MOVIMIENTO_ID=$movimiento_id;";
	
    //echo $query_codigo_barras;
    $resultado_query_codigo_barras=nmysql2_query($query_codigo_barras,$link);
    
    if($resultado_query_codigo_barras){
      if(nmysql2_affected_rows($link)==0)
      {
      $control_reload[3] = 1;
      }
      if(nmysql2_affected_rows($link)>0){     
	  	  	  
		$query_codigo_barras_item="SELECT ARTICULO_REFERENCIA,"
								."CANTIDAD,"
								."MOVIMIENTO_ITEM_BODEGA,"
								."MOVIMIENTO_ITEM_SERIAL1,"
								."MOVIMIENTO_ITEM_SERIAL2,"
								."MOVIMIENTO_ITEM_FECHA_TINTA, "
								."MOVIMIENTO_ITEM_ENTRADA_MOVIMIENTO_ID,"
								."MOVIMIENTO_ITEM_FECHA_INGRESO "
								."FROM "
								."movimiento_item "
								."WHERE MOVIMIENTO_ID=$movimiento_id;";
		
		//echo $query_codigo_barras_item;
		
		$resultado_query_codigo_barras_item=nmysql2_query($query_codigo_barras_item,$link);
		
		if($resultado_query_codigo_barras_item){
		
          while($row=nmysql2_fetch_assoc($resultado_query_codigo_barras_item)){
			$articulo_referencia_revisada=$row["ARTICULO_REFERENCIA"];
			$articulo_cantidad_revisada=$row["CANTIDAD"];			
			$bodega_id_revisada=$row["MOVIMIENTO_ITEM_BODEGA"];
			$articulo_serial1_revisada=$row["MOVIMIENTO_ITEM_SERIAL1"];
			$articulo_serial2_revisada=$row["MOVIMIENTO_ITEM_SERIAL2"];
			$articulo_fecha_tinta_revisada=$row["MOVIMIENTO_ITEM_FECHA_TINTA"];
			$articulo_movimiento_id_revisada=$row["MOVIMIENTO_ITEM_ENTRADA_MOVIMIENTO_ID"];
			$articulo_fecha_ingreso_revisada=$row["MOVIMIENTO_ITEM_FECHA_INGRESO"];
			
			$query_articulo_codigo_barras_indice="SELECT ARTICULO_CODIGO_BARRAS_INDICE_CONSECUTIVO_ID FROM articulo_codigo_barras_indice_consecutivo";
			$resultado_query_articulo_codigo_barras_indice=nmysql2_query($query_articulo_codigo_barras_indice,$link);
    
			if($resultado_query_articulo_codigo_barras_indice){
			  if(nmysql2_num_rows($resultado_query_articulo_codigo_barras_indice)>0){
			   while($row1=nmysql2_fetch_assoc($resultado_query_articulo_codigo_barras_indice)){
			    $articulo_codigo_barras_indice=$row1["ARTICULO_CODIGO_BARRAS_INDICE_CONSECUTIVO_ID"];
				$query_barcode="SELECT ARTICULO_CODIGO_BARRAS_INDICE,ARTICULO_CODIGO_BARRAS_ID FROM articulo_codigo_barras_$articulo_codigo_barras_indice "
							  ."WHERE ARTICULO_CODIGO_BARRAS_REFERENCIA_TEMPORAL='".nmysql2_escape_string($articulo_referencia_revisada,$link)."' "
							  ."AND ARTICULO_CODIGO_BARRAS_BODEGA=$bodega_id_revisada;";
				//echo $query_barcode;			  
				$resultado_query_barcode=nmysql2_query($query_barcode,$link);
				if( nmysql2_num_rows($resultado_query_barcode) > 0 ){
				
				$row2=nmysql2_fetch_assoc($resultado_query_barcode);
				
				$articulo_id=$row2["ARTICULO_CODIGO_BARRAS_ID"];		
		        $articulo_indice=$row2["ARTICULO_CODIGO_BARRAS_INDICE"];
		  
			$query_codigo_barras_item_cantidad="UPDATE articulo_codigo_barras_$articulo_indice art "
											  ."SET art.ARTICULO_CODIGO_BARRAS_CANTIDAD=(art.ARTICULO_CODIGO_BARRAS_CANTIDAD-$articulo_cantidad_revisada) "
											  ."WHERE art.ARTICULO_CODIGO_BARRAS_ID=$articulo_id;";
			
			//echo $query_codigo_barras_item_cantidad;
			
			$resultado_query_codigo_barras_item_cantidad=nmysql2_query($query_codigo_barras_item_cantidad,$link);
		
		    if($resultado_query_codigo_barras_item_cantidad){		    			
				
				$query_movimiento_item_codigo_barras="INSERT INTO movimiento_item_codigo_barras "
				."(MOVIMIENTO_ITEM_CODIGO_BARRAS_MOVIMIENTO_CODIGO_BARRAS_ID,"
				."MOVIMIENTO_ITEM_CODIGO_BARRAS_ARTICULO_CODIGO_BARRAS_ID,"
				."MOVIMIENTO_ITEM_CODIGO_BARRAS_ARTICULO_CODIGO_BARRAS_IN,"
				."MOVIMIENTO_ITEM_CODIGO_BARRAS_BODEGA_ID,"				
				."MOVIMIENTO_ITEM_CODIGO_BARRAS_CANTIDAD,"
				."MOVIMIENTO_ITEM_CODIGO_BARRAS_ESTADO,"
				."MOVIMIENTO_ITEM_CODIGO_BARRAS_USUARIO_ID,"
				."MOVIMIENTO_ITEM_CODIGO_BARRAS_MOVIMIENTO_ITEM_CODIGO_BARRAS_T_ID) "				
				."VALUES "
				."($movimiento_id,"
				."$articulo_id,"
				."$articulo_indice,"
				."$bodega_id_revisada,"
				."$articulo_cantidad_revisada,"
				."1,"
				."$usuario_id,"
				."0);";


				//echo $query_movimiento_item_codigo_barras;
				
				$resultado_query_codigo_barras=nmysql2_query($query_movimiento_item_codigo_barras,$link);			
				
				if($resultado_query_codigo_barras){
				
				$num_registros_afectados=nmysql2_affected_rows($link);					
				
				  if($num_registros_afectados==0){
				  $control_reload[4]=1;
				  }

				  if($num_registros_afectados>0){
				  $movimiento_item_id=nmysqli2_insert_id($link);
				  
				  if((!(is_null($articulo_fecha_tinta_revisada)))&&($articulo_fecha_tinta_revisada!="")){
				  
				  $query_movimiento_item_guardados="INSERT INTO movimiento_item_codigo_barras_fecha_vencimiento "
					."(MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_V_MOVIMIENTO_ITEM_COD_BAR_ID,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_V_ARTICULO_CODIGO_BARRAS_IN,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_V_ARTICULO_CODIGO_BARRAS_ID,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_V_ITEM_FECHA,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_V_MOVIMIENTO_TIPO,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_V_MOVIMIENTO_ITEM_F_VT_ID) "
					."VALUES "
					."($movimiento_item_id,"			
					."$articulo_indice,"
					."$articulo_id,"
					."'$articulo_fecha_tinta_revisada',"
					."2,"
					."0); ";
					
				  //echo $query_movimiento_item_guardados;	
				  $resultado_query_movimiento_item_guardados=nmysql2_query($query_movimiento_item_guardados,$link);
				  
				  if($resultado_query_movimiento_item_guardados){
				 
				  }
				  else{
				  $control_reload[5]=1;
				  }
				  
				  }
				  
				  if((!(is_null($articulo_serial1_revisada)))&&($articulo_serial1_revisada!="")){
				  
				  	$query_movimiento_item_guardados="INSERT INTO movimiento_item_codigo_barras_serial "
					."(MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_MOVIMIENTO_ITEM_COD_BAR_ID,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_ARTICULO_CODIGO_BARRAS_IN,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_ARTICULO_CODIGO_BARRAS_ID,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_ITEM_SERIAL,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_ITEM_SERIAL_NUMERO,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_MOVIMIENTO_TIPO,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_MOVIMIENTO_ITEM_S_T_ID) "
					."VALUES "
					."($movimiento_item_id,"
					."$articulo_indice,"
					."$articulo_id,"
					."'".nmysql2_escape_string($articulo_serial1_revisada,$link)."',"
					."0,"
					."2,"
					."0);";
					
				  //echo $query_movimiento_item_guardados;
				  $resultado_query_movimiento_item_guardados=nmysql2_query($query_movimiento_item_guardados,$link);
				  
				  if($resultado_query_movimiento_item_guardados){
				 
				  }
				  else{
				  $control_reload[5]=1;
				  }				  
				  
				  }
				  
				  if((!(is_null($articulo_serial2_revisada)))&&($articulo_serial2_revisada!="")){
				  
				  	$query_movimiento_item_guardados="INSERT INTO movimiento_item_codigo_barras_serial "
					."(MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_MOVIMIENTO_ITEM_COD_BAR_ID,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_ARTICULO_CODIGO_BARRAS_IN,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_ARTICULO_CODIGO_BARRAS_ID,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_ITEM_SERIAL,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_ITEM_SERIAL_NUMERO,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_MOVIMIENTO_TIPO,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_MOVIMIENTO_ITEM_S_T_ID) "
					."VALUES "
					."($movimiento_item_id,"
					."$articulo_indice,"
					."$articulo_id,"
					."'".nmysql2_escape_string($articulo_serial2_revisada,$link)."',"
					."1,"
					."2,"
					."0);";
					
				  //echo $query_movimiento_item_guardados;
				  $resultado_query_movimiento_item_guardados=nmysql2_query($query_movimiento_item_guardados,$link);
				  
				  if($resultado_query_movimiento_item_guardados){
				 
				  }
				  else{
				  $control_reload5=1;
				  }				  
				  
				  }

				  if((!(is_null($articulo_movimiento_id_revisada)))&&($articulo_movimiento_id_revisada!="")){

				  	$query_movimiento_item_guardados="INSERT INTO movimiento_item_codigo_barras_fecha_ingreso "
					."(MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_I_MOVIMIENTO_ITEM_COD_BAR_ID,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_I_ARTICULO_CODIGO_BARRAS_IN,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_I_ARTICULO_CODIGO_BARRAS_ID,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_I_ITEM_FECHA,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_I_MOVIMIENTO_TIPO,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_I_MOVIMIENTO_ITEM_F_IT_ID) "
					."SELECT $movimiento_item_id,"
					."$articulo_indice,"
					."$articulo_id,"
					."MOVIMIENTO_FECHA,"
					."2, "
					."0 "
					."FROM movimiento "
					."WHERE MOVIMIENTO_ID=$articulo_movimiento_id_revisada; ";
					
				  //echo $query_movimiento_item_guardados;
				  $resultado_query_movimiento_item_guardados=nmysql2_query($query_movimiento_item_guardados,$link);
				  
				  if($resultado_query_movimiento_item_guardados){
				 
				  }
				  else{
				  $control_reload[5]=1;
				  }				  
				  
				  }
				  				  

				  if((!(is_null($articulo_fecha_ingreso_revisada)))&&($articulo_fecha_ingreso_revisada!="")){

				  	$query_movimiento_item_guardados="INSERT INTO movimiento_item_codigo_barras_fecha_ingreso "
					."(MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_I_MOVIMIENTO_ITEM_COD_BAR_ID,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_I_ARTICULO_CODIGO_BARRAS_IN,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_I_ARTICULO_CODIGO_BARRAS_ID,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_I_ITEM_FECHA,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_I_MOVIMIENTO_TIPO,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_I_MOVIMIENTO_ITEM_F_IT_ID) "
					."VALUES ($movimiento_item_id,"
					."$articulo_indice,"
					."$articulo_id,"
					."'$articulo_fecha_ingreso_revisada',"
					."2, "
					."0); ";
					
				  //echo $query_movimiento_item_guardados;
				  $resultado_query_movimiento_item_guardados=nmysql2_query($query_movimiento_item_guardados,$link);
				  
				  if($resultado_query_movimiento_item_guardados){
				 
				  }
				  else{
				  $control_reload[5]=1;
				  }				  
				  
				  }
				  
				  }

				}
				else{
				$control_reload[4]=1;
				}
			  
			} else{
	        $control_reload[4]=1;
			}					
				
				}	
			   }
			  }
			}				 		 
		
		}
	  
	   }
	  
      }
    }
    else{
    $control_reload[3] = 1;
    }

}

return $control_reload;
    
}
function bod_trans_inv_ajustar_articulo($link,$articulo_id,$cliente_id,$cantidad,$documento_numero,$documento_tipo,$documento_item_id,$tipo_ajuste,$bodega_id,$usuario_id,$serial1=null,$serial2=null,$vendedor_usuario_id){

$resultado_ajuste=false;

$cantidad_local=0;
$cantidad_extranjero=0;
$articulo_transito_id=0;
$articulo_transito_nuevo=false;

if(bod_trans_obtener_tipo_cantidad($documento_tipo)=="local"){
$cantidad_local=$cantidad;
}

if(bod_trans_obtener_tipo_cantidad($documento_tipo)=="extranjero"){
$cantidad_extranjero=$cantidad;
}

$query_validar_articulo="SELECT ARTICULO_TRANSITO_ID FROM articulo_transito WHERE ARTICULO_TRANSITO_ARTICULO_ID=$articulo_id AND ARTICULO_TRANSITO_BODEGA_ID=$bodega_id;";

$resultado_query_validar_articulo=nmysql2_query($query_validar_articulo,$link);

if($resultado_query_validar_articulo){

 $numero_registros=nmysql2_num_rows($resultado_query_validar_articulo);

 if($numero_registros==0){
 
  $query_insertar_articulo="INSERT INTO articulo_transito "
                          ."(ARTICULO_TRANSITO_ARTICULO_ID,"
                          ."ARTICULO_TRANSITO_CANTIDAD_LOCAL,"
                          ."ARTICULO_TRANSITO_CANTIDAD_EXTRANJERO,"
                          ."ARTICULO_TRANSITO_BODEGA_ID,"
                          ."ARTICULO_TRANSITO_ESTADO)"
                          ."VALUES "
                          ."($articulo_id,"
                          ."$cantidad_local,"
                          ."$cantidad_extranjero,"
                          ."$bodega_id,"
                          ."1);";
                          
  $resultado_query_insertar_articulo=nmysql2_query($query_insertar_articulo,$link);
  
  if($resultado_query_insertar_articulo){
  
  $articulo_transito_id=nmysqli2_insert_id($link);  
  
  $articulo_transito_nuevo=true;
  
  }
 
 }

 if($numero_registros>0){
 
 $row=nmysql2_fetch_assoc($resultado_query_validar_articulo);
 
 $articulo_transito_id=$row["ARTICULO_TRANSITO_ID"];
  
 }

if($articulo_transito_id>0){

 if($tipo_ajuste=='ENTRADA'){

 $simbolo_ajuste="+";
 
 }

 if($tipo_ajuste=='SALIDA'){

 $simbolo_ajuste="-";
 
 }
 
 $resultado_query_cantidad=false;
 
if(!$articulo_transito_nuevo){

 $query_cantidad="";
 
 if($cantidad_local>0){
 
 $query_cantidad="UPDATE articulo_transito SET ARTICULO_TRANSITO_CANTIDAD_LOCAL=ARTICULO_TRANSITO_CANTIDAD_LOCAL $simbolo_ajuste $cantidad_local WHERE ARTICULO_TRANSITO_ID=$articulo_transito_id ";
 
 }

 if($cantidad_extranjero>0){
 
 $query_cantidad="UPDATE articulo_transito SET ARTICULO_TRANSITO_CANTIDAD_EXTRANJERO=ARTICULO_TRANSITO_CANTIDAD_EXTRANJERO $simbolo_ajuste $cantidad_extranjero WHERE ARTICULO_TRANSITO_ID=$articulo_transito_id ";
 
 }


 if($query_cantidad!=""){
 
 $resultado_query_cantidad=nmysql2_query($query_cantidad,$link);
 
 }

}
 
 $fecha_registro = date("Y-m-d H:i:s"); 
 
  if($cliente_id==null||$cliente_id==""){
  $vcliente="NULL";
  }else{
  $vcliente=$cliente_id;
  }
 
  if($serial1==null||$serial1==""){
  $vserial1="NULL";
  }else{
  $vserial1="'".nmysql2_escape_string($serial1,$link)."'";
  }
 
  if($serial2==null||$serial2==""){
  $vserial2="NULL";
  }else{
  $vserial2="'".nmysql2_escape_string($serial2,$link)."'";
  }        
  
  if(($articulo_transito_nuevo)||(!$articulo_transito_nuevo&&$resultado_query_cantidad)){

  $query_transito_item="INSERT INTO articulo_transito_item "
                      ."(ARTICULO_TRANSITO_ITEM_ARTICULO_TRANSITO_ID,"
                      ."ARTICULO_TRANSITO_ITEM_ARTICULO_ID,"
                      ."ARTICULO_TRANSITO_ITEM_FECHA,"
                      ."ARTICULO_TRANSITO_ITEM_CLIENTE_ID,"
                      ."ARTICULO_TRANSITO_ITEM_CANTIDAD,"
                      ."ARTICULO_TRANSITO_ITEM_DOCUMENTO_NO_ITEM_ID,"
                      ."ARTICULO_TRANSITO_ITEM_DOCUMENTO_NO,"
                      ."ARTICULO_TRANSITO_ITEM_DOCUMENTO_TIPO,"
                      ."ARTICULO_TRANSITO_ITEM_MOVIMIENTO_TIPO,"
                      ."ARTICULO_TRANSITO_ITEM_BODEGA_ID,"
                      ."ARTICULO_TRANSITO_ITEM_SERIAL1,"
                      ."ARTICULO_TRANSITO_ITEM_SERIAL2,"
                      ."ARTICULO_TRANSITO_ITEM_USUARIO_ID,"
                      ."ARTICULO_TRANSITO_ITEM_VENDEDOR_ID)"
                      ."VALUES "
                      ."($articulo_transito_id,"
                      ."$articulo_id,"
                      ."'$fecha_registro',"
                      .$vcliente.","
                      ."$cantidad,"
                      ."$documento_item_id,"
                      ."'$documento_numero',"
                      ."'$documento_tipo',"                      
                      ."'$tipo_ajuste',"
                      ."$bodega_id,"
                      .$vserial1.","
                      .$vserial2.","
                      .$usuario_id.","
                      .$vendedor_usuario_id.");";
  
  $resultado_query_transito_item=nmysql2_query($query_transito_item,$link);
  
  if($resultado_query_transito_item){
  
   if(nmysql2_affected_rows($link)>0){
   $resultado_ajuste=true;
   }
  
  }
 
  }

}

}

return $resultado_ajuste;
}
function bod_trans_obtener_tipo_cantidad($documento_tipo){
if($documento_tipo=="consignacion"){
return "local";
}
if($documento_tipo=="devolucion"){
return "local";
}
if($documento_tipo=="compra_importacion"){
return "extranjero";
}
}
function inventario_dev_obtener_pendientes($link,$item_id,$consignacion_parte_id,$devolucion_parte_id){

$unidades_pendientes="";

$query_dev_item_pendientes="SELECT (ARTICULO_CONSIGNACION_PARTE_ITEM_CANTIDAD"
                          ."-(ARTICULO_CONSIGNACION_PARTE_ITEM_DEVOLUCION_CANTIDAD+obtenerPendientesInvConsig($consignacion_parte_id,ARTICULO_CONSIGNACION_PARTE_ITEM_ID,3,$devolucion_parte_id)"
                          ."+ARTICULO_CONSIGNACION_PARTE_ITEM_FACTURADO_CANTIDAD+obtenerPendientesInvConsig($consignacion_parte_id,ARTICULO_CONSIGNACION_PARTE_ITEM_ID,1,$devolucion_parte_id)"
                          ."+ARTICULO_CONSIGNACION_PARTE_ITEM_GARANTIA_CANTIDAD+obtenerPendientesInvConsig($consignacion_parte_id,ARTICULO_CONSIGNACION_PARTE_ITEM_ID,2,$devolucion_parte_id)"
                          ."+ARTICULO_CONSIGNACION_PARTE_ITEM_PRUEBAS_CANTIDAD+obtenerPendientesInvConsig($consignacion_parte_id,ARTICULO_CONSIGNACION_PARTE_ITEM_ID,0,$devolucion_parte_id)"
                          ."+ARTICULO_CONSIGNACION_PARTE_ITEM_SALIDA_MERCANCIA_CANTIDAD+obtenerPendientesInvConsig($consignacion_parte_id,ARTICULO_CONSIGNACION_PARTE_ITEM_ID,4,$devolucion_parte_id)"
                          ."+ARTICULO_CONSIGNACION_PARTE_ITEM_TRASLADO_CANTIDAD+obtenerPendientesInvConsig($consignacion_parte_id,ARTICULO_CONSIGNACION_PARTE_ITEM_ID,5,$devolucion_parte_id)"
                          .")) AS CANTIDAD "
                          ."FROM articulo_consignacion_parte_item WHERE ARTICULO_CONSIGNACION_PARTE_ITEM_ID=$item_id ";

$resultado_query_dev_item_pendientes=nmysql2_query($query_dev_item_pendientes,$link);

if($resultado_query_dev_item_pendientes){

 while($row_pendiente=nmysql2_fetch_assoc($resultado_query_dev_item_pendientes)){
 
 $unidades_pendientes=$row_pendiente["CANTIDAD"];
 
 }
 
}

return $unidades_pendientes;
}
function inventario_dev_obtener_consec($link,$art_consignacion_parte_id){
$consecutivos=[];
$consecutivos["consecutivo"]="";
$consecutivos["correlativo"]="";

$query_datos_art_consignacion_parte="SELECT ARTICULO_CONSIGNACION_PARTE_CONSECUTIVO,ARTICULO_CONSIGNACION_PARTE_CORRELATIVO FROM articulo_consignacion_parte WHERE ARTICULO_CONSIGNACION_PARTE_ID=$art_consignacion_parte_id ";

$resultado_query_datos_art_consignacion_parte=nmysql2_query($query_datos_art_consignacion_parte,$link);

if($resultado_query_datos_art_consignacion_parte){

 while($row=nmysql2_fetch_assoc($resultado_query_datos_art_consignacion_parte)){
 $consecutivos["consecutivo"]=$row["ARTICULO_CONSIGNACION_PARTE_CONSECUTIVO"];
 $consecutivos["correlativo"]=$row["ARTICULO_CONSIGNACION_PARTE_CORRELATIVO"];
 }

}

return $consecutivos;
}
function ingresar_serial_aplicacion($link,$referencia,$bodega,$serial){

$error=0;
$no_registros=0;

   $query_serial_consulta="SELECT ARTICULO_APLICACION_SERIAL FROM articulo_aplicacion_serial "
                ."WHERE ARTICULO_APLICACION_SERIAL_REFERENCIA='".nmysql2_escape_string(trim($referencia),$link)."' "
                ."AND ARTICULO_APLICACION_SERIAL_BODEGA=$bodega AND ARTICULO_APLICACION_SERIAL=".$serial."; ";

   $resultado_query_consulta_serial=nmysql2_query($query_serial_consulta,$link);
   
   if($resultado_query_consulta_serial){   
    $no_registros=nmysql2_num_rows($resultado_query_consulta_serial);
   }

if($no_registros==0){
   
$query_serial="INSERT INTO articulo_aplicacion_serial "
             ."(ARTICULO_APLICACION_SERIAL_REFERENCIA,"
             ."ARTICULO_APLICACION_SERIAL_BODEGA,"
             ."ARTICULO_APLICACION_SERIAL) "
             ."VALUES "
             ."('".nmysql2_escape_string(trim($referencia),$link)."',"
             ."$bodega,"
             ."".$serial.");";

$resultado_query_serial=nmysql2_query($query_serial,$link);

if($resultado_query_serial){
 if(nmysql2_affected_rows($link)<1){
 $error=1;
 }
}else{
$error=1;
}

if($error==1){
registrar("Articulo aplicacion serial","articulo aplicacion serial",7,"No se pudo ingresar serial Referencia:".$referencia." Bodega:".$bodega." Serial:".$serial);
}

}

if($no_registros==1){

$query_serial="UPDATE articulo_aplicacion_serial "
             ."SET ARTICULO_APLICACION_DESCONTADO=0 "
             ."WHERE ARTICULO_APLICACION_SERIAL_REFERENCIA='".nmysql2_escape_string(trim($referencia),$link)."' "
             ."AND ARTICULO_APLICACION_SERIAL_BODEGA=$bodega AND ARTICULO_APLICACION_SERIAL=".($serial)."; ";

$resultado_query_serial=nmysql2_query($query_serial,$link);

if($resultado_query_serial){
 if(nmysql2_affected_rows($link)<1){
 $error=1;
 }
}else{
$error=1;
}

if($error==1){
registrar("Articulo aplicacion serial","articulo aplicacion serial",7,"No se pudo descontar serial Referencia:".$referencia." Bodega:".$bodega." Serial:".$serial);
}

}

}
function descontar_serial_aplicacion($link,$referencia,$bodega,$serial){

$error=0;

$busqueda=0;

   $query_serial_consulta="SELECT ARTICULO_APLICACION_SERIAL FROM articulo_aplicacion_serial "
                ."WHERE ARTICULO_APLICACION_SERIAL_REFERENCIA='".nmysql2_escape_string(trim($referencia),$link)."' "
                ."AND ARTICULO_APLICACION_SERIAL_BODEGA=$bodega AND ARTICULO_APLICACION_SERIAL=".$serial."; ";

   $resultado_query_consulta_serial=nmysql2_query($query_serial_consulta,$link);
   
   if($resultado_query_consulta_serial){   
    if(nmysql2_num_rows($resultado_query_consulta_serial)>0){
    $busqueda=1;
    }    
   }

if($busqueda==1){
   
$query_serial="UPDATE articulo_aplicacion_serial "
             ."SET ARTICULO_APLICACION_DESCONTADO=1 "
             ."WHERE ARTICULO_APLICACION_SERIAL_REFERENCIA='".nmysql2_escape_string(trim($referencia),$link)."' "
             ."AND ARTICULO_APLICACION_SERIAL_BODEGA=$bodega AND ARTICULO_APLICACION_SERIAL=".$serial."; ";

$resultado_query_serial=nmysql2_query($query_serial,$link);

if($resultado_query_serial){
 if(nmysql2_affected_rows($link)<1){
 $error=1;
 }
}else{
$error=1;
}

if($error==1){
registrar("Articulo aplicacion serial","articulo aplicacion serial",7,"No se pudo descontar serial Referencia:".$referencia." Bodega:".$bodega." Serial:".$serial);
}
         
}

}
function bod_trans_grupo_revisado_login(){
$arreglo_grupo_login=array();
$arreglo_grupo_login[]="jarismendi";
return $arreglo_grupo_login;
}
function bod_trans_grupo_aprobado_login(){
$arreglo_grupo_login=array();
$arreglo_grupo_login[]="hvanegas";
$arreglo_grupo_login[]="bodega_ess";
$arreglo_grupo_login[]="eguevara";
$arreglo_grupo_login[]="fperez";
$arreglo_grupo_login[]="ilizarazo";
$arreglo_grupo_login[]="msanchez";
$arreglo_grupo_login[]="mflorez";
$arreglo_grupo_login[]="asalas";
$arreglo_grupo_login[]="avillaquiran";
$arreglo_grupo_login[]="daldana";
$arreglo_grupo_login[]="jvillalba";
$arreglo_grupo_login[]="jecheverry";
$arreglo_grupo_login[]="acarreno";
$arreglo_grupo_login[]="yaldana";
$arreglo_grupo_login[]="ydaza";
$arreglo_grupo_login[]="khernandez";
return $arreglo_grupo_login;
}
function bod_trans_val_pcons_usr($link,$plazo_dias,$consulta_general=false,$validar_no_legalizadas=true,$canal_id=377){

$pendientes=[];

if(!$consulta_general){

$usuario_login=$_SESSION['nombreuser'];

$fecha_revision=date("Y-m-d");

$query_pendientes="SELECT ARTICULO_CONSIGNACION_PARTE_ID, obtenerDiasLab(IF(ARTICULO_CONSIGNACION_PARTE_FECHA_PLAZO IS NULL,0,ARTICULO_CONSIGNACION_PARTE_FECHA_PLAZO),'$fecha_revision','dias_no_restados') AS DIAS_LABORALES FROM articulo_consignacion_parte "
                 ."JOIN articulo_consignacion_parte_item "
                 ."ON ARTICULO_CONSIGNACION_PARTE_ITEM_ARTICULO_CONSIGNACION_PARTE_ID=ARTICULO_CONSIGNACION_PARTE_ID "
                 ."JOIN usuario "
                 ."ON USUARIO_ID=ARTICULO_CONSIGNACION_PARTE_VENDEDOR_ID "
                 ."WHERE ARTICULO_CONSIGNACION_PARTE_ESTADO=1 "
                 ."AND ARTICULO_CONSIGNACION_PARTE_ITEM_INVENTARIO_ESTADO=1 "
                 ."AND ARTICULO_CONSIGNACION_PARTE_ITEM_CANTIDAD"
                 ."-(ARTICULO_CONSIGNACION_PARTE_ITEM_DEVOLUCION_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_FACTURADO_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_GARANTIA_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_PRUEBAS_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_SALIDA_MERCANCIA_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_TRASLADO_CANTIDAD"
                 .")<>0 "
                 ."AND USUARIO_LOGIN='".$usuario_login."'; ";
                 
}else{

$fecha_revision=date("Y-m-d");

$query_pendientes="SELECT ARTICULO_CONSIGNACION_PARTE_ID, obtenerDiasLab(IF(ARTICULO_CONSIGNACION_PARTE_FECHA_PLAZO IS NULL,0,ARTICULO_CONSIGNACION_PARTE_FECHA_PLAZO),'$fecha_revision','dias_no_restados') AS DIAS_LABORALES FROM articulo_consignacion_parte "
                 ."JOIN articulo_consignacion_parte_item "
                 ."ON ARTICULO_CONSIGNACION_PARTE_ITEM_ARTICULO_CONSIGNACION_PARTE_ID=ARTICULO_CONSIGNACION_PARTE_ID "
                 ."WHERE ARTICULO_CONSIGNACION_PARTE_ESTADO=1 "
                 ."AND ARTICULO_CONSIGNACION_PARTE_ITEM_INVENTARIO_ESTADO=1 "
                 ."AND ARTICULO_CONSIGNACION_PARTE_ITEM_CANTIDAD"
                 ."-(ARTICULO_CONSIGNACION_PARTE_ITEM_DEVOLUCION_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_FACTURADO_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_GARANTIA_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_PRUEBAS_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_SALIDA_MERCANCIA_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_TRASLADO_CANTIDAD"
                 .")<>0 "
                 ."AND ARTICULO_CONSIGNACION_PARTE_CANAL_ID=$canal_id; ";

}
                 
$resultado_query_pendientes=nmysql2_query($query_pendientes,$link);

if($resultado_query_pendientes){

 if(nmysql2_num_rows($resultado_query_pendientes)>0){
 
  while($row=nmysql2_fetch_assoc($resultado_query_pendientes)){
  
  $dias_laborales=$row["DIAS_LABORALES"];
  
  if(($dias_laborales>=0)&&($dias_laborales>$plazo_dias)){
   
   $bus_consc=inventario_dev_obtener_consec($link,$row["ARTICULO_CONSIGNACION_PARTE_ID"]);
   
   $bus_consc=$bus_consc["consecutivo"];
  
   if(!in_array($bus_consc,$pendientes)){
   
   $pendientes[]=$bus_consc;
   
   }
  
  }
  
  }
 
 }

}

if(!$consulta_general){

$query_pendientes2="SELECT ARTICULO_CONSIGNACION_PARTE_ID FROM articulo_consignacion_parte "
                 ."JOIN articulo_consignacion_parte_item "
                 ."ON ARTICULO_CONSIGNACION_PARTE_ITEM_ARTICULO_CONSIGNACION_PARTE_ID=ARTICULO_CONSIGNACION_PARTE_ID "
                 ."JOIN usuario "
                 ."ON USUARIO_ID=ARTICULO_CONSIGNACION_PARTE_VENDEDOR_ID "
                 ."WHERE ARTICULO_CONSIGNACION_PARTE_ESTADO=1 "
                 ."AND ARTICULO_CONSIGNACION_PARTE_ITEM_INVENTARIO_ESTADO=1 "
                 ."AND ARTICULO_CONSIGNACION_PARTE_ITEM_CANTIDAD"
                 ."-(ARTICULO_CONSIGNACION_PARTE_ITEM_DEVOLUCION_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_FACTURADO_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_GARANTIA_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_PRUEBAS_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_SALIDA_MERCANCIA_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_TRASLADO_CANTIDAD"
                 .")<>0 "
                 ."AND USUARIO_LOGIN='".$usuario_login."'; ";

}else{

$query_pendientes2="SELECT ARTICULO_CONSIGNACION_PARTE_ID FROM articulo_consignacion_parte "
                 ."JOIN articulo_consignacion_parte_item "
                 ."ON ARTICULO_CONSIGNACION_PARTE_ITEM_ARTICULO_CONSIGNACION_PARTE_ID=ARTICULO_CONSIGNACION_PARTE_ID "
                 ."JOIN usuario "
                 ."ON USUARIO_ID=ARTICULO_CONSIGNACION_PARTE_VENDEDOR_ID "
                 ."WHERE ARTICULO_CONSIGNACION_PARTE_ESTADO=1 "
                 ."AND ARTICULO_CONSIGNACION_PARTE_ITEM_INVENTARIO_ESTADO=1 "
                 ."AND ARTICULO_CONSIGNACION_PARTE_ITEM_CANTIDAD"
                 ."-(ARTICULO_CONSIGNACION_PARTE_ITEM_DEVOLUCION_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_FACTURADO_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_GARANTIA_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_PRUEBAS_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_SALIDA_MERCANCIA_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_TRASLADO_CANTIDAD"                 
                 .")<>0 "
                 ."AND ARTICULO_CONSIGNACION_PARTE_CANAL_ID=$canal_id; ";

}

$resultado_query_pendientes2=nmysql2_query($query_pendientes2,$link);

if($resultado_query_pendientes2){
 
 while($row2=nmysql2_fetch_assoc($resultado_query_pendientes2)){
   
   $bus_consc=inventario_dev_obtener_consec($link,$row2["ARTICULO_CONSIGNACION_PARTE_ID"]);
   
   $bus_consc=$bus_consc["consecutivo"];
  
   if(!in_array($bus_consc,$pendientes)){
   
   $pendientes[]=$bus_consc;
   
   }
 
 }

}

return $pendientes;

}
function bod_trans_val_pcorr_usr($link,$plazo_dias,$consulta_general=false,$validar_no_legalizadas=true,$canal_id=346){

$pendientes=[];

if(!$consulta_general){

$usuario_login=$_SESSION['nombreuser'];

$fecha_revision=date("Y-m-d");

$query_pendientes="SELECT ARTICULO_CONSIGNACION_PARTE_ID, obtenerDiasLab(IF(ARTICULO_CONSIGNACION_PARTE_FECHA_PLAZO IS NULL,0,ARTICULO_CONSIGNACION_PARTE_FECHA_PLAZO),'$fecha_revision','dias_no_restados') AS DIAS_LABORALES FROM articulo_consignacion_parte "
                 ."JOIN articulo_consignacion_parte_item "
                 ."ON ARTICULO_CONSIGNACION_PARTE_ITEM_ARTICULO_CONSIGNACION_PARTE_ID=ARTICULO_CONSIGNACION_PARTE_ID "
                 ."JOIN usuario "
                 ."ON USUARIO_ID=ARTICULO_CONSIGNACION_PARTE_VENDEDOR_ID "
                 ."WHERE ARTICULO_CONSIGNACION_PARTE_ESTADO=1 "
                 ."AND ARTICULO_CONSIGNACION_PARTE_ITEM_INVENTARIO_ESTADO=1 "
                 ."AND ARTICULO_CONSIGNACION_PARTE_ITEM_CANTIDAD"
                 ."-(ARTICULO_CONSIGNACION_PARTE_ITEM_DEVOLUCION_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_FACTURADO_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_GARANTIA_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_PRUEBAS_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_SALIDA_MERCANCIA_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_TRASLADO_CANTIDAD"
                 .")<>0 "
                 ."AND USUARIO_LOGIN='".$usuario_login."'; ";
                 
}else{

$fecha_revision=date("Y-m-d");

$query_pendientes="SELECT ARTICULO_CONSIGNACION_PARTE_ID, obtenerDiasLab(IF(ARTICULO_CONSIGNACION_PARTE_FECHA_PLAZO IS NULL,0,ARTICULO_CONSIGNACION_PARTE_FECHA_PLAZO),'$fecha_revision','dias_no_restados') AS DIAS_LABORALES FROM articulo_consignacion_parte "
                 ."JOIN articulo_consignacion_parte_item "
                 ."ON ARTICULO_CONSIGNACION_PARTE_ITEM_ARTICULO_CONSIGNACION_PARTE_ID=ARTICULO_CONSIGNACION_PARTE_ID "
                 ."WHERE ARTICULO_CONSIGNACION_PARTE_ESTADO=1 "
                 ."AND ARTICULO_CONSIGNACION_PARTE_ITEM_INVENTARIO_ESTADO=1 "
                 ."AND ARTICULO_CONSIGNACION_PARTE_ITEM_CANTIDAD"
                 ."-(ARTICULO_CONSIGNACION_PARTE_ITEM_DEVOLUCION_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_FACTURADO_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_GARANTIA_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_PRUEBAS_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_SALIDA_MERCANCIA_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_TRASLADO_CANTIDAD"
                 .")<>0 "
                 ."AND ARTICULO_CONSIGNACION_PARTE_CANAL_ID=$canal_id; ";

}
                 
$resultado_query_pendientes=nmysql2_query($query_pendientes,$link);

if($resultado_query_pendientes){

 if(nmysql2_num_rows($resultado_query_pendientes)>0){
 
  while($row=nmysql2_fetch_assoc($resultado_query_pendientes)){
  
  $dias_laborales=$row["DIAS_LABORALES"];
  
  if(($dias_laborales>=0)&&($dias_laborales>$plazo_dias)){
   
   $bus_consc=inventario_dev_obtener_consec($link,$row["ARTICULO_CONSIGNACION_PARTE_ID"]);
   
   $bus_consc=$bus_consc["correlativo"];
  
   if(!in_array($bus_consc,$pendientes)){
   
   $pendientes[]=$bus_consc;
   
   }
  
  }
  
  }
 
 }

}

if(!$consulta_general){

$query_pendientes2="SELECT ARTICULO_CONSIGNACION_PARTE_ID FROM articulo_consignacion_parte "
                 ."JOIN articulo_consignacion_parte_item "
                 ."ON ARTICULO_CONSIGNACION_PARTE_ITEM_ARTICULO_CONSIGNACION_PARTE_ID=ARTICULO_CONSIGNACION_PARTE_ID "
                 ."JOIN usuario "
                 ."ON USUARIO_ID=ARTICULO_CONSIGNACION_PARTE_VENDEDOR_ID "
                 ."WHERE ARTICULO_CONSIGNACION_PARTE_ESTADO=1 "
                 ."AND ARTICULO_CONSIGNACION_PARTE_ITEM_INVENTARIO_ESTADO=1 "
                 ."AND ARTICULO_CONSIGNACION_PARTE_ITEM_CANTIDAD"
                 ."-(ARTICULO_CONSIGNACION_PARTE_ITEM_DEVOLUCION_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_FACTURADO_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_GARANTIA_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_PRUEBAS_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_SALIDA_MERCANCIA_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_TRASLADO_CANTIDAD"
                 .")<>0 "
                 ."AND USUARIO_LOGIN='".$usuario_login."'; ";

}else{

$query_pendientes2="SELECT ARTICULO_CONSIGNACION_PARTE_ID FROM articulo_consignacion_parte "
                 ."JOIN articulo_consignacion_parte_item "
                 ."ON ARTICULO_CONSIGNACION_PARTE_ITEM_ARTICULO_CONSIGNACION_PARTE_ID=ARTICULO_CONSIGNACION_PARTE_ID "
                 ."JOIN usuario "
                 ."ON USUARIO_ID=ARTICULO_CONSIGNACION_PARTE_VENDEDOR_ID "
                 ."WHERE ARTICULO_CONSIGNACION_PARTE_ESTADO=1 "
                 ."AND ARTICULO_CONSIGNACION_PARTE_ITEM_INVENTARIO_ESTADO=1 "
                 ."AND ARTICULO_CONSIGNACION_PARTE_ITEM_CANTIDAD"
                 ."-(ARTICULO_CONSIGNACION_PARTE_ITEM_DEVOLUCION_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_FACTURADO_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_GARANTIA_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_PRUEBAS_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_SALIDA_MERCANCIA_CANTIDAD"
                 ."+ARTICULO_CONSIGNACION_PARTE_ITEM_TRASLADO_CANTIDAD"                 
                 .")<>0 "
                 ."AND ARTICULO_CONSIGNACION_PARTE_CANAL_ID=$canal_id; ";

}

$resultado_query_pendientes2=nmysql2_query($query_pendientes2,$link);

if($resultado_query_pendientes2){
 
 while($row2=nmysql2_fetch_assoc($resultado_query_pendientes2)){
   
   $bus_consc=inventario_dev_obtener_consec($link,$row2["ARTICULO_CONSIGNACION_PARTE_ID"]);
   
   $bus_consc=$bus_consc["correlativo"];
  
   if(!in_array($bus_consc,$pendientes)){
   
   $pendientes[]=$bus_consc;
   
   }
 
 }

}

return $pendientes;

}
function bod_trans_fplazo_cons($fecha,$dias)
{
$fechaInicial = $fecha;  
$MaxDias = $dias;    
    
$dias=0;

for ($i=1; $i<$MaxDias; $i++){  
$dias+=1;
        
$caduca = date("D",strtotime("+ $i days ",strtotime($fechaInicial)));  
    
 if($caduca=="Sat"||$caduca == "Sun"){
 $dias+= 1;
 }

}  

$FechaFinal = date("Y-m-d",strtotime("+ $dias days ",strtotime($fechaInicial)));

$fecha_rev = date("D",strtotime($FechaFinal));

if($fecha_rev=="Sat"||$fecha_rev=="Sun"){
$dias+=1;
}

return $FechaFinal = date("Y-m-d",strtotime("+ $dias days ",strtotime($fechaInicial)));      
    
}
function obtenerArregloLegalizadoTipo($lan,$link){

$usuario_canal_id=obtenerUsuarioCanalId($link);

$etiqueta_devolucion=trans('Instalación',$lan); 
$etiqueta_facturado=trans('Facturar',$lan); 
$etiqueta_garantia=trans('Garantía',$lan);
$etiqueta_pruebas=trans('Devolucion',$lan);
$etiqueta_salida_mercancia=trans('Salida mercancía',$lan);
$etiqueta_traslado=trans('Traslado',$lan);

if($usuario_canal_id!=346){
$arreglo_legalizado_tipo=["1"=>$etiqueta_facturado,"2"=>$etiqueta_garantia,"3"=>$etiqueta_pruebas,"0"=>$etiqueta_devolucion,"4"=>$etiqueta_salida_mercancia,"5"=>$etiqueta_traslado];
}else{
$arreglo_legalizado_tipo=["1"=>$etiqueta_facturado,"2"=>$etiqueta_garantia,"3"=>$etiqueta_pruebas];
}
return $arreglo_legalizado_tipo;
}
function obtenerArregloConsignacionPrioridad($lan){

$etiqueta_alta=trans('Alta',$lan); 
$etiqueta_media=trans('Media',$lan); 
$etiqueta_baja=trans('Baja',$lan);

$arreglo_consignacion_tipo=["1"=>$etiqueta_alta,"2"=>$etiqueta_media,"3"=>$etiqueta_baja];

return $arreglo_consignacion_tipo;
}
?>
