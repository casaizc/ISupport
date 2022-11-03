<?php

require_once 'phpmailer/PHPMailerAutoload.php';


function sendemail($movimiento_id,$link,$bodega_nombre,$tipo,$fecha_salida,$hora,$tercero,$documento) {

    $cantidad=0;
    $nombre_completo="-";
    $nombreusuario="-";
    $articulo = array();
    $cant = array();

   
    $mail = new PHPMailer();

	//$mail->SMTPDebug = 3;                       // Activar o desactivar el modo debug
	$mail->isSMTP();                              // Indicar al mailer que use SMTP
	$mail->Host = 'mail.isupportess.com';           // Acá va el host SMTP
	$mail->SMTPAuth = true;                       // Activar la autenticación SMTP
	$mail->Username = 'desarrollo@isupportess.com';    // La cuenta de correos que vas a utilizar. Tiene que estar creada previamente en el cPanel
	$mail->Password = 'S1st3m@G3SS';             // La clave de de esa cuenta de correos
	$mail->SMTPSecure = 'tls';                    // Activar el cifrado TLS, "ssl" también es aceptado
	$mail->Port = 587;                           // El puerto de conexión SMTP
	
	$mail->setFrom('desarrollo@isupportess.com', 'Inventarios');            // El correo desde cual sale el correo y el "nombre" 
	$mail->addAddress('casaizc@correo.udistrital.edu.co', 'Joe User');  // Añadir el recipiente
	//$mail->addReplyTo('info@ejemplo.com', 'Informacion');            // Indicar una cuenta para responder (opcional)
	//$mail->addCC('cc@ejemplo.com');                                  // Indicar una cuenta de copia (opcional)
	//$mail->addBCC('bcc@ejemplo.com');                                // Indicar una cuenta de copia adicional (ocional)
	//$mail->addBCC('auxiliar.desarrollo@electrosignsupply.com');
	$mail->isHTML(true);                        

    $mail->Subject = utf8_decode("Movimiento Salida de Bodega. $bodega_nombre");// Asunto correo
    
    $mail->AddEmbeddedImage('recursos/emailsend.png','logo','logo.png');;
    
    $mail->Body = utf8_decode("<font face='Arial, Georgia'><strong>Cordial saludo, adjunto información correspondiente al movimiento de inventario.</strong></font>");
    $mail->Body .= utf8_decode("<br><br><font face='Arial, Georgia'>Movimiento de Ingreso - Bodega. <strong>$bodega_nombre.</strong><br><br><strong>REF/DOCUMENTO: </strong> $documento.
                    <br><strong>FECHA DE DOCUMENTO: </strong>$fecha_salida<br><br><br>");

	$mail->Body .= utf8_decode("<table border CELLPADDING=10 CELLSPACING=0><thead><tr><th width='100px'>REFERENCIA</th><th>CANTIDAD</th><th>DESCRIPCION</th><th>CLIENTE</th><th>TIPO MOVIMIENTO</th><th>FECHA MOVIMIENTO</th><th>USUARIO</th></tr></thead><tbody>");


	$buscar_ruta = $link->query('SELECT * FROM movimiento WHERE MOVIMIENTO_ID='.$movimiento_id);		
	if ($buscar_ruta->num_rows > 0) {
		while ($rowbuscar_ruta = $buscar_ruta->fetch_assoc()) {

			$tipomovimiento = $rowbuscar_ruta['MOVIMIENTO_TIPO'];//Se obtiene ID del movimiento
			$documento = $rowbuscar_ruta['DOCUMENTO'];//Se obtiene la fecha del documento
			$movimientousuario = $rowbuscar_ruta['MOVIMIENTO_USUARIO'];//Nombre de usuario
			$observacion = $rowbuscar_ruta['MOVIMIENTO_OBSERVACION'];
        }
	}

	$buscar_art = $link ->query('SELECT * FROM movimiento_item WHERE MOVIMIENTO_ID='.$movimiento_id);
	if($buscar_art->num_rows > 0) {
		while($rowbuscar_art = $buscar_art->fetch_assoc()){
			$arti = $rowbuscar_art['ARTICULO_REFERENCIA'];
			$articulo[] = $arti;
			$cantidad = $rowbuscar_art['CANTIDAD'];
			$cant[] = $cantidad;
        }
			$mail->Body .= utf8_decode("<tr><td>");
			foreach($articulo as $art){
			$mail->Body .= utf8_decode("$art<br>");
            }

			$mail->Body .= utf8_decode("</td><td>");
			foreach($cant as $can){
			$mail->Body .= utf8_decode("$can<br>");
			}

			$mail->Body .= utf8_decode("</td><td>");

    $resultado = array_unique($articulo);
	foreach($resultado as $nombre){
    $buscar_tipo = $link ->query('SELECT DISTINCT ARTICULO_NOMBRE, ARTICULO_REFERENCIA FROM articulo WHERE ARTICULO_REFERENCIA="'.strip_tags($nombre).'"');
			if ($buscar_tipo->num_rows > 0) {				
				while ($rowbuscar_tipo = $buscar_tipo->fetch_assoc()){
					$nombrearticulo = $rowbuscar_tipo['ARTICULO_NOMBRE'];
					$mail->Body .= utf8_decode("$nombrearticulo<br>");
                }

                    }
			}
		}
			$mail->Body .= utf8_decode("</td>");

	$nombre_completo = $tercero;
	$buscar_cli = $link ->query('SELECT * FROM cliente WHERE CLIENTE_ID=' .$tercero);						
	if ($buscar_cli->num_rows > 0) {						
		while ($rowbuscar_cli = $buscar_cli->fetch_assoc()){
			$nombre = $rowbuscar_cli['CLIENTE_NOMBRE_COMERCIAL'];//Nombre comercial del cliente
			$nom = $rowbuscar_cli['CLIENTE_NOMBRE'];//Nombre de pila del cliente
			if ($nombre=='')
				$nombre_completo = $nom;
			else
				$nombre_completo = $nombre.'-'.$nom;
		}
	}

	  $mail->Body .= utf8_decode("<td>$nombre_completo</td>");

	$buscar_mov = $link->query('SELECT * FROM movimiento_tipo WHERE MOVIMIENTO_ID='.$tipomovimiento);
	if ($buscar_mov->num_rows > 0) {						
		while ($rowbuscar_mov = $buscar_mov->fetch_assoc()){
			$nombremovimiento = $rowbuscar_mov['MOVIMIENTO_NOMBRE'];
		}
		$mail->Body .= utf8_decode("<td>$nombremovimiento</td>");
	}
	
	$mail->Body .= utf8_decode("<td>$hora</td>");

	$buscar_user = $link ->query('SELECT * FROM usuario WHERE USUARIO_LOGIN="'.strip_tags($movimientousuario).'"');
	if($buscar_user->num_rows > 0) {
		while($rowbuscar_user = $buscar_user->fetch_assoc()){
			$nombres = $rowbuscar_user['USUARIO_NOMBRES'];
			$apellidos = $rowbuscar_user['USUARIO_APELLIDOS'];
			$nombreusuario = $nombres.' '.$apellidos;
		}
		$mail->Body .= utf8_decode("<td>$nombreusuario</td></table>");
	}
	
   $mail->Body .= utf8_decode("<br><br><strong>Observaciones: </strong>$observacion<br><br>");
   $body='<br><br><img width="160px" src="cid:logo"><br><br>'; 
   $mail->Body .= utf8_decode("<br><strong>Atentamente,</font><br>$nombreusuario</strong><br>");
			

	if(!$mail->send()){// Error al enviar el correo
		$data['validar'] = 1;
		$data['mensaje'] = 'Mensaje no fue enviado<br>';
		$data['mensaje'] .= 'Mailer Error: ' . $mail->ErrorInfo;
		registrar("INVENTARIOS","inventario",7,"Error al enviar correo con ruta",$link);
		echo json_encode($data);//Se devuelven los datos de acuerdo a la acción ejecutada
		echo json_encode($movimiento_id,$link,$bodega_nombre);
	}else{// Correo enviado sin problemas
		$data['validar'] = 2;
		$data['mensaje'] = 'Archico actualizado y enviado correctamente';
		registrar("INVENTARIOS","inventario",4,"Se envía correo con ruta",$link);
	}
	
}