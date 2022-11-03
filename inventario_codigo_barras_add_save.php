<?php 
 require_once 'phpmailer/PHPMailerAutoload.php';// Se llama la libreria dpara poder configurar el correo

 include ("seguridad/seguridad.php");
 include ("lib/BDDlib.php");
 include ("lib/CFGlib.php"); 
 include ('sendemail.php');

 $link = conectarBDD();
  
 $permiso=consultaPermisoUsuario(111,$link);

 $permisoAdd=$permiso[3];

 if($permisoAdd==0){
 echo "Conection error";
 exit();	 
 }

 if (isset($_POST["num3"])){
	$movimiento_codigo_barras_temporal_id = $_POST["num3"];
	}
 
 if(isset($_POST["vnp"])){
 $bodega_id=$_POST["vnp"];
 }
 
 if(isset($_POST["opc_tipo_tercero_t"])){
 $tercero_tipo = $_POST["opc_tipo_tercero_t"];
 } 
 
 if(isset($_POST["opc_tipo_tercero_v"])){
 $tercero_version_busq = $_POST["opc_tipo_tercero_v"];
 } 
 
 if(isset($_POST["opc_tipo_tercero_s"])){
 $tercero = $_POST["opc_tipo_tercero_s"];
 }
 else{
 $tercero=null;
 } 
 if(($tercero=="")||($tercero==null)){
 $vtercero="NULL";
 }
 else{
 $vtercero=$tercero;
 }  

 if(isset($_POST["movimiento"])){
 $movimiento_tipo = $_POST["movimiento"];
 }
 else{
 $movimiento_tipo=null;
 } 
 if(($movimiento_tipo=="")||($movimiento_tipo==null)){
 $vmovimiento_tipo="NULL";
 }
 else{
 $vmovimiento_tipo=$movimiento_tipo;
 }  
 
 if(isset($_POST["documento"])){
 $documento = $_POST["documento"];
 }
 else{
 $documento=null;
 } 
 if(($documento=="")||($documento==null)){
 $vdocumento="'".$documento."'";
 }
 else{
 $vdocumento="'".trim(FormatearDatosEscStringT($documento,$link))."'";
 }  

 if(isset($_POST["documento_fecha"])){
 $documento_fecha = $_POST["documento_fecha"];
 }
 else{
 $documento_fecha=null;
 } 
 if(($documento_fecha=="")||($documento_fecha==null)){
 $vdocumento_fecha="NULL";
 }
 else{
 $vdocumento_fecha="'".$documento_fecha."'";
 }  

 if(isset($_POST["observaciones"])){
 $observaciones = $_POST["observaciones"];
 }
 else{
 $observaciones=null;
 } 
 if(($observaciones=="")||($observaciones==null)){
 $vobservaciones="NULL";
 }
 else{
 $vobservaciones="'".FormatearDatosEscStringT($observaciones,$link)."'";
 }

 $bodega_nombre=obtenerNombreBodegaCodigoBarras($bodega_id,$link);
 
 //variable de inventario add, si se registra el tecnico relacionado con el movimiento de inventario
 $tecnico=null;
 
 $control_reload=array(); 
 
 $control_reload[0]=0;
 $control_reload[1]=0;
 $control_reload[2]=0;
 $control_reload[3]=0;
 $control_reload[4]=0;
 $control_reload[5]=0;
 $control_reload[6]=0;
 $control_reload[7]=0;
 $control_reload[8]=0;

 $mensaje_error="";
 
  $rusuario=$_SESSION['nombreuser'];
  $query = "SELECT USUARIO_ID FROM usuario WHERE USUARIO_LOGIN='$rusuario'";
  $query = nmysql2_query($query,$link);    

  if(nmysql2_num_rows($query)>0){ 
  $row = nmysql2_fetch_array($query);
  $usuario_id=$row["USUARIO_ID"];
  }   
  
 $nombre_revplantilla="inventario_codigo_barras_add";
 
 if(isset($_SESSION["$nombre_revplantilla"])){
 
 $consecutivo_revisado_plantilla=ObtenerIdRevPlantilla("$nombre_revplantilla");

 if($consecutivo_revisado_plantilla>0){  
 
    $query_consecutivo_plantilla="SELECT MOVIMIENTO_CODIGO_BARRAS_ID FROM movimiento_codigo_barras WHERE MOVIMIENTO_CODIGO_BARRAS_ID=$consecutivo_revisado_plantilla; ";
    $consulta_consecutivo=nmysql2_query($query_consecutivo_plantilla,$link);
      if( nmysql2_num_rows($consulta_consecutivo) >= 1 ){
      $control_reload[0] = 1;
      $control_reload[2] = 1;
      }
   }
 
 }

 //valida si previamente se se ha utilizado la plantilla de agregar
 if(ObtenerEstRetornoPlantilla("$nombre_revplantilla")==1){
  $control_reload[0] = 1;
  $control_reload[2] = 1;
 }
 
 RetornoPlantilla("$nombre_revplantilla",1);
 
 if($control_reload[0]==0){
	 
 $fecha=date("Y-m-d H:i:s");
 
 $vtercero_cliente_id='NULL';
 $vtercero_proveedor_id='NULL';
 
 if($tercero_tipo=="1"){
 $vtercero_cliente_id=$vtercero;
 }
 
 if($tercero_tipo=="2"){
 $vtercero_proveedor_id=$vtercero;
 }
 
 if($tercero_version_busq=="1"){
 $vtercero_cliente_id=$vtercero; 
 $vtercero_proveedor_id='NULL';
 }
 
     if(is_null($tecnico))
         $query_insert_movimiento="INSERT INTO movimiento(DOCUMENTO,DOCUMENTO_FECHA,MOVIMIENTO_CLIENTE_ID,MOVIMIENTO_PROVEEDOR_ID,MOVIMIENTO_FECHA,MOVIMIENTO_TIPO,MOVIMIENTO_BODEGA,MOVIMIENTO_OBSERVACION,MOVIMIENTO_USUARIO)VALUES($vdocumento,$vdocumento_fecha,$vtercero_cliente_id,$vtercero_proveedor_id,'$fecha',$vmovimiento_tipo,$bodega_id,$vobservaciones,'$rusuario')";
     else
         $query_insert_movimiento="INSERT INTO movimiento(DOCUMENTO,DOCUMENTO_FECHA,MOVIMIENTO_CLIENTE_ID,MOVIMIENTO_PROVEEDOR_ID,MOVIMIENTO_FECHA,MOVIMIENTO_TIPO,MOVIMIENTO_BODEGA,MOVIMIENTO_OBSERVACION,MOVIMIENTO_USUARIO,MOVIMIENTO_USUARIO_TECNICO)VALUES($vdocumento,$vdocumento_fecha,$vtercero_cliente_id,$vtercero_proveedor_id,'$fecha',$vmovimiento_tipo,$bodega_id,$vobservaciones,'$rusuario','$tecnico')";
 //echo $query_insert_movimiento;
 $resultado_movimiento_id_tabla_origen= nmysql2_query($query_insert_movimiento,$link);
 
 if($resultado_movimiento_id_tabla_origen){

 //verificamos que se halla ralizado el insert
 if(nmysql2_affected_rows($link)>0){
 
 //consultamos el id que se acaba de generar 
 $movimiento_id=nmysqli2_insert_id($link);

 RegistrarIdRevPlantilla("$nombre_revplantilla",$movimiento_id);
		   
    $query_codigo_barras="INSERT INTO movimiento_codigo_barras "
    ."(MOVIMIENTO_CODIGO_BARRAS_ID,"
	."MOVIMIENTO_CODIGO_BARRAS_FECHA,"
    ."MOVIMIENTO_CODIGO_BARRAS_TIPO,"
    ."MOVIMIENTO_CODIGO_BARRAS_BODEGA_ID,"
    ."MOVIMIENTO_CODIGO_BARRAS_USUARIO,"
	."MOVIMIENTO_CODIGO_BARRAS_USUARIO_ID,"
	."MOVIMIENTO_CODIGO_BARRAS_MOVIMIENTO_CODIGO_BARRAS_TEMPORAL_ID,"
	."MOVIMIENTO_CODIGO_BARRAS_CLIENTE_ID,"
	."MOVIMIENTO_CODIGO_BARRAS_PROVEEDOR_ID,"
	."MOVIMIENTO_CODIGO_BARRAS_TIPO_DETALLE,"
	."MOVIMIENTO_CODIGO_BARRAS_DOCUMENTO,"
	."MOVIMIENTO_CODIGO_BARRAS_DOCUMENTO_FECHA,"
	."MOVIMIENTO_CODIGO_BARRAS_OBSERVACION)"
    ."SELECT $movimiento_id,"
	."'$fecha',"
	."MOVIMIENTO_CODIGO_BARRAS_TEMPORAL_TIPO,"
	."MOVIMIENTO_CODIGO_BARRAS_TEMPORAL_BODEGA,"
	."MOVIMIENTO_CODIGO_BARRAS_TEMPORAL_USUARIO,"
	."$usuario_id,"
	."'$movimiento_codigo_barras_temporal_id',"
	."$vtercero_cliente_id,"
	."$vtercero_proveedor_id,"
	."$vmovimiento_tipo,"
	."$vdocumento,"
	."$vdocumento_fecha,"
	."$vobservaciones "
	."FROM movimiento_codigo_barras_temporal "
	."WHERE MOVIMIENTO_CODIGO_BARRAS_TEMPORAL_ID=$movimiento_codigo_barras_temporal_id;";
	 
    //echo $query_codigo_barras;
    $resultado_query_codigo_barras=nmysql2_query($query_codigo_barras,$link);
    
    if($resultado_query_codigo_barras){
      if(nmysql2_affected_rows($link)==0)
      {
      $control_reload[0] = 1;
      $mensaje_error.="No se pudo guardar\n";
      }
      if(nmysql2_affected_rows($link)>0){     
	  	  	  
		$query_codigo_barras_item="SELECT MOVIMIENTO_ITEM_CODIGO_BARRAS_TEMPORAL_ID,"
								."MOVIMIENTO_ITEM_CODIGO_BARRAS_TEMPORAL_ARTICULO_CODIGO_BARRAS_ID,"
								."MOVIMIENTO_ITEM_CODIGO_BARRAS_TEMPORAL_ARTICULO_CODIGO_BARRAS_IN,"
								."MOVIMIENTO_ITEM_CODIGO_BARRAS_TEMPORAL_CANTIDAD "
								."FROM movimiento_item_codigo_barras_temporal "
								."WHERE MOVIMIENTO_ITEM_CODIGO_BARRAS_TEMPORAL_MOVIMIENTO_TEMPORAL_ID=$movimiento_codigo_barras_temporal_id "
								."AND MOVIMIENTO_ITEM_CODIGO_BARRAS_TEMPORAL_USUARIO='$rusuario';";
		
		//echo $query_codigo_barras_item;
		
		$resultado_query_codigo_barras_item=nmysql2_query($query_codigo_barras_item,$link);
		
		if($resultado_query_codigo_barras_item){
		
		if(nmysql2_num_rows($resultado_query_codigo_barras_item)>0){
		
          while($row=nmysql2_fetch_assoc($resultado_query_codigo_barras_item)){
		
		  $articulo_item_movimiento_id=$row["MOVIMIENTO_ITEM_CODIGO_BARRAS_TEMPORAL_ID"];
		  $articulo_id=$row["MOVIMIENTO_ITEM_CODIGO_BARRAS_TEMPORAL_ARTICULO_CODIGO_BARRAS_ID"];		
		  $articulo_indice=$row["MOVIMIENTO_ITEM_CODIGO_BARRAS_TEMPORAL_ARTICULO_CODIGO_BARRAS_IN"];
		  $articulo_cantidad=$row["MOVIMIENTO_ITEM_CODIGO_BARRAS_TEMPORAL_CANTIDAD"];
		  
			$query_codigo_barras_item_cantidad="UPDATE articulo_codigo_barras_$articulo_indice art "
											  ."SET art.ARTICULO_CODIGO_BARRAS_CANTIDAD=(art.ARTICULO_CODIGO_BARRAS_CANTIDAD+$articulo_cantidad) "
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
				."MOVIMIENTO_ITEM_CODIGO_BARRAS_NUMERO_R,"
				."MOVIMIENTO_ITEM_CODIGO_BARRAS_ESTADO,"
				."MOVIMIENTO_ITEM_CODIGO_BARRAS_USUARIO_ID,"
				."MOVIMIENTO_ITEM_CODIGO_BARRAS_MOVIMIENTO_ITEM_CODIGO_BARRAS_T_ID) "				
				."SELECT '$movimiento_id',"
				."MOVIMIENTO_ITEM_CODIGO_BARRAS_TEMPORAL_ARTICULO_CODIGO_BARRAS_ID,"
				."MOVIMIENTO_ITEM_CODIGO_BARRAS_TEMPORAL_ARTICULO_CODIGO_BARRAS_IN,"
				."MOVIMIENTO_ITEM_CODIGO_BARRAS_TEMPORAL_BODEGA,"
				."MOVIMIENTO_ITEM_CODIGO_BARRAS_TEMPORAL_CANTIDAD,"
				."MOVIMIENTO_ITEM_CODIGO_BARRAS_TEMPORAL_NUMERO_R,"
				."MOVIMIENTO_ITEM_CODIGO_BARRAS_TEMPORAL_ESTADO,"
				."'$usuario_id', "
				."MOVIMIENTO_ITEM_CODIGO_BARRAS_TEMPORAL_ID "
				."FROM movimiento_item_codigo_barras_temporal "
				."WHERE MOVIMIENTO_ITEM_CODIGO_BARRAS_TEMPORAL_ID=$articulo_item_movimiento_id "
				."AND MOVIMIENTO_ITEM_CODIGO_BARRAS_TEMPORAL_USUARIO='$rusuario';";


				//echo $query_movimiento_item_codigo_barras;
				
				$resultado_query_codigo_barras=nmysql2_query($query_movimiento_item_codigo_barras,$link);			
				
				if($resultado_query_codigo_barras){
				
				$num_registros_afectados=nmysql2_affected_rows($link);					
				
				  if($num_registros_afectados==0){
				  $mensaje_error.="Conection error registering item $articulo_item_movimiento_id \n";
				  $control_reload[1]=1;
				  }

				  if($num_registros_afectados>0){

				  $query_movimiento_item_guardados="INSERT INTO movimiento_item_codigo_barras_fecha_vencimiento "
					."(MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_V_MOVIMIENTO_ITEM_COD_BAR_ID,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_V_ARTICULO_CODIGO_BARRAS_IN,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_V_ARTICULO_CODIGO_BARRAS_ID,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_V_ITEM_FECHA,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_V_MOVIMIENTO_TIPO,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_V_MOVIMIENTO_ITEM_F_VT_ID) "
					."SELECT MOVIMIENTO_ITEM_CODIGO_BARRAS_ID,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_F_VT_ARTICULO_CODIGO_BARRAS_IN,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_F_VT_ARTICULO_CODIGO_BARRAS_ID,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_F_VT_ITEM_FECHA,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_F_VT_MOVIMIENTO_TIPO, "
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_F_VT_ID "
					."FROM movimiento_item_codigo_barras_fecha_vencimiento_temporal "
					."JOIN movimiento_item_codigo_barras ON MOVIMIENTO_ITEM_CODIGO_BARRAS_MOVIMIENTO_ITEM_CODIGO_BARRAS_T_ID=MOVIMIENTO_ITEM_CODIGO_BARRAS_F_VT_MOVIMIENTO_ITEM_COD_BAR_T_ID "
					."WHERE MOVIMIENTO_ITEM_CODIGO_BARRAS_MOVIMIENTO_CODIGO_BARRAS_ID=$movimiento_id "
					."AND MOVIMIENTO_ITEM_CODIGO_BARRAS_MOVIMIENTO_ITEM_CODIGO_BARRAS_T_ID=$articulo_item_movimiento_id; ";
					
				  $resultado_query_movimiento_item_guardados=nmysql2_query($query_movimiento_item_guardados,$link);
				  
				  if($resultado_query_movimiento_item_guardados){
				 
				  }
				  else{
				  $control_reload[4]=1;
				  }

				  	$query_movimiento_item_guardados="INSERT INTO movimiento_item_codigo_barras_serial "
					."(MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_MOVIMIENTO_ITEM_COD_BAR_ID,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_ARTICULO_CODIGO_BARRAS_IN,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_ARTICULO_CODIGO_BARRAS_ID,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_ITEM_SERIAL,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_ITEM_SERIAL_NUMERO,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_MOVIMIENTO_TIPO,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_SERIAL_MOVIMIENTO_ITEM_S_T_ID) "
					."SELECT MOVIMIENTO_ITEM_CODIGO_BARRAS_ID,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_S_T_ARTICULO_CODIGO_BARRAS_IN,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_S_T_ARTICULO_CODIGO_BARRAS_ID,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_S_T_ITEM_SERIAL,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_S_T_ITEM_SERIAL_NUMERO, "
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_S_T_MOVIMIENTO_TIPO, "
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_S_T_ID "
					."FROM movimiento_item_codigo_barras_serial_temporal "
					."JOIN movimiento_item_codigo_barras ON MOVIMIENTO_ITEM_CODIGO_BARRAS_MOVIMIENTO_ITEM_CODIGO_BARRAS_T_ID=MOVIMIENTO_ITEM_CODIGO_BARRAS_S_T_MOVIMIENTO_ITEM_COD_BAR_T_ID "
					."WHERE MOVIMIENTO_ITEM_CODIGO_BARRAS_MOVIMIENTO_CODIGO_BARRAS_ID=$movimiento_id "
					."AND MOVIMIENTO_ITEM_CODIGO_BARRAS_MOVIMIENTO_ITEM_CODIGO_BARRAS_T_ID=$articulo_item_movimiento_id; ";
					
				  //echo $query_movimiento_item_guardados;
				  $resultado_query_movimiento_item_guardados=nmysql2_query($query_movimiento_item_guardados,$link);
				  
				  if($resultado_query_movimiento_item_guardados){
				 
				  }
				  else{
				  $control_reload[5]=1;
				  }				  

				  	$query_movimiento_item_guardados="INSERT INTO movimiento_item_codigo_barras_fecha_ingreso "
					."(MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_I_MOVIMIENTO_ITEM_COD_BAR_ID,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_I_ARTICULO_CODIGO_BARRAS_IN,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_I_ARTICULO_CODIGO_BARRAS_ID,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_I_ITEM_FECHA,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_I_MOVIMIENTO_TIPO,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_I_MOVIMIENTO_ITEM_F_IT_ID) "
					."SELECT MOVIMIENTO_ITEM_CODIGO_BARRAS_ID,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_F_IT_ARTICULO_CODIGO_BARRAS_IN,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_F_IT_ARTICULO_CODIGO_BARRAS_ID,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_F_IT_ITEM_FECHA,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_F_IT_MOVIMIENTO_TIPO, "
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_F_IT_ID "
					."FROM movimiento_item_codigo_barras_fecha_ingreso_temporal "
					."JOIN movimiento_item_codigo_barras ON MOVIMIENTO_ITEM_CODIGO_BARRAS_MOVIMIENTO_ITEM_CODIGO_BARRAS_T_ID=MOVIMIENTO_ITEM_CODIGO_BARRAS_F_IT_MOVIMIENTO_ITEM_COD_BAR_T_ID "
					."WHERE MOVIMIENTO_ITEM_CODIGO_BARRAS_MOVIMIENTO_CODIGO_BARRAS_ID=$movimiento_id "
					."AND MOVIMIENTO_ITEM_CODIGO_BARRAS_MOVIMIENTO_ITEM_CODIGO_BARRAS_T_ID=$articulo_item_movimiento_id; ";
					
				  //echo $query_movimiento_item_guardados;
				  $resultado_query_movimiento_item_guardados=nmysql2_query($query_movimiento_item_guardados,$link);
				  
				  if($resultado_query_movimiento_item_guardados){
				 
				  }
				  else{
				  $control_reload[6]=1;
				  }				  

				  	$query_movimiento_item_guardados="INSERT INTO movimiento_item_codigo_barras_lote "
					."(MOVIMIENTO_ITEM_CODIGO_BARRAS_LOTE_MOVIMIENTO_ITEM_COD_BAR_ID,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_LOTE_ARTICULO_CODIGO_BARRAS_IN,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_LOTE_ARTICULO_CODIGO_BARRAS_ID,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_LOTE_ITEM_LOTE,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_LOTE_MOVIMIENTO_TIPO,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_LOTE_MOVIMIENTO_ITEM_L_T_ID) "
					."SELECT MOVIMIENTO_ITEM_CODIGO_BARRAS_ID,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_L_T_ARTICULO_CODIGO_BARRAS_IN,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_L_T_ARTICULO_CODIGO_BARRAS_ID,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_L_T_ITEM_LOTE,"
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_L_T_MOVIMIENTO_TIPO, "
					."MOVIMIENTO_ITEM_CODIGO_BARRAS_L_T_ID "
					."FROM movimiento_item_codigo_barras_lote_temporal "
					."JOIN movimiento_item_codigo_barras ON MOVIMIENTO_ITEM_CODIGO_BARRAS_MOVIMIENTO_ITEM_CODIGO_BARRAS_T_ID=MOVIMIENTO_ITEM_CODIGO_BARRAS_L_T_MOVIMIENTO_ITEM_COD_BAR_T_ID "
					."WHERE MOVIMIENTO_ITEM_CODIGO_BARRAS_MOVIMIENTO_CODIGO_BARRAS_ID=$movimiento_id "
					."AND MOVIMIENTO_ITEM_CODIGO_BARRAS_MOVIMIENTO_ITEM_CODIGO_BARRAS_T_ID=$articulo_item_movimiento_id; ";
					
				  //echo $query_movimiento_item_guardados;
				  $resultado_query_movimiento_item_guardados=nmysql2_query($query_movimiento_item_guardados,$link);
				  
				  if($resultado_query_movimiento_item_guardados){
					
				  }
				  else{
				  $control_reload[8]=1;
				  }	
				  
				  }
										  
				}
				else{
				$control_reload[1]=1;
				}
			  
			} else{
	        $control_reload[1]=1;
			}			
		
		}

			  //guarda las cantidades de los items en la tabla de movimiento item
			  //se deja una función SQL Script con indice 5, si se agrega un indice superior genera error
                

			 
			  duplicarInventarioBarcode($movimiento_id,$bodega_id);
			  //----------------------------

	   }
	   
	   }
	  
      }
	  

    }
	
    else{
    $control_reload[0] = 1;
    }
	
}

}
	  

}

function duplicarInventarioBarcode($movimiento_id,$bodega_id){
global $link;
	
			$query_item_agregados="SELECT "
			."MOVIMIENTO_ITEM_CODIGO_BARRAS_ARTICULO_CODIGO_BARRAS_IN,"
			."MOVIMIENTO_ITEM_CODIGO_BARRAS_ARTICULO_CODIGO_BARRAS_ID,"
			."getItemRefOrigen(MOVIMIENTO_ITEM_CODIGO_BARRAS_ARTICULO_CODIGO_BARRAS_IN,MOVIMIENTO_ITEM_CODIGO_BARRAS_ARTICULO_CODIGO_BARRAS_ID) AS ARTICULO_REFERENCIA_ORIGEN,"
			."MOVIMIENTO_ITEM_CODIGO_BARRAS_CANTIDAD, "
			."MOVIMIENTO_ITEM_CODIGO_BARRAS_ID "
			."FROM movimiento_item_codigo_barras WHERE MOVIMIENTO_ITEM_CODIGO_BARRAS_MOVIMIENTO_CODIGO_BARRAS_ID=$movimiento_id;";
			
			//echo $query_item_agregados;
			$resultado_query_item_agregados=nmysql2_query($query_item_agregados,$link);
			
			if($resultado_query_item_agregados){
			$numero_item_agregados=nmysql2_num_rows($resultado_query_item_agregados);
				
			 if($numero_item_agregados>0){
				
			  while($row3=nmysql2_fetch_assoc($resultado_query_item_agregados)){
				//la referencia es de la tabla articulo
				$articulo_agregado_indice=$row3["MOVIMIENTO_ITEM_CODIGO_BARRAS_ARTICULO_CODIGO_BARRAS_IN"];
				$articulo_agregado_id=$row3["MOVIMIENTO_ITEM_CODIGO_BARRAS_ARTICULO_CODIGO_BARRAS_ID"];
				$articulo_agregado_referencia=$row3["ARTICULO_REFERENCIA_ORIGEN"];
				$articulo_agregado_cantidad=$row3["MOVIMIENTO_ITEM_CODIGO_BARRAS_CANTIDAD"];
				$articulo_agregado_movimiento_item_id=$row3["MOVIMIENTO_ITEM_CODIGO_BARRAS_ID"];
				
			$query_movimiento_item_cantidad="UPDATE articulo "
											."SET ARTICULO_CANTIDAD=(ARTICULO_CANTIDAD+$articulo_agregado_cantidad) "
											."WHERE ARTICULO_REFERENCIA='$articulo_agregado_referencia' "
											."AND ARTICULO_BODEGA=$bodega_id;";			
			  
			//echo $query_movimiento_item_cantidad;
			
			$resultado_query_movimiento_item_cantidad=nmysql2_query($query_movimiento_item_cantidad,$link);
			 
			if($resultado_query_movimiento_item_cantidad){
			
		      $query_movimiento_item="INSERT INTO movimiento_item "
			  ."(MOVIMIENTO_ID,"
			  ."ARTICULO_REFERENCIA,"
			  ."CANTIDAD,"
			  ."MOVIMIENTO_ITEM_BODEGA,"
			  ."MOVIMIENTO_ITEM_SERIAL1,"
			  ."MOVIMIENTO_ITEM_SERIAL2,"
			  ."MOVIMIENTO_ITEM_FECHA_TINTA,"
			  ."MOVIMIENTO_ITEM_FECHA_INGRESO) "
			  ."SELECT MOVIMIENTO_ITEM_CODIGO_BARRAS_MOVIMIENTO_CODIGO_BARRAS_ID,"
			  ."getItemRefOrigen(MOVIMIENTO_ITEM_CODIGO_BARRAS_ARTICULO_CODIGO_BARRAS_IN, MOVIMIENTO_ITEM_CODIGO_BARRAS_ARTICULO_CODIGO_BARRAS_ID) AS REFERENCIA,"
			  ."MOVIMIENTO_ITEM_CODIGO_BARRAS_CANTIDAD,"
			  ."MOVIMIENTO_ITEM_CODIGO_BARRAS_BODEGA_ID," 
			  ."getItemSerial(MOVIMIENTO_ITEM_CODIGO_BARRAS_ID,0) AS SERIAL1,"
			  ."getItemSerial(MOVIMIENTO_ITEM_CODIGO_BARRAS_ID,1) AS SERIAL2,"
			  ."MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_V_ITEM_FECHA, "
			  ."MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_I_ITEM_FECHA "
			  ."FROM movimiento_item_codigo_barras "
			  ."LEFT JOIN movimiento_item_codigo_barras_fecha_vencimiento "
			  ."ON MOVIMIENTO_ITEM_CODIGO_BARRAS_ID=MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_V_MOVIMIENTO_ITEM_COD_BAR_ID "
			  ."LEFT JOIN movimiento_item_codigo_barras_fecha_ingreso "
			  ."ON MOVIMIENTO_ITEM_CODIGO_BARRAS_ID=MOVIMIENTO_ITEM_CODIGO_BARRAS_FECHA_I_MOVIMIENTO_ITEM_COD_BAR_ID "			  
			  ."WHERE MOVIMIENTO_ITEM_CODIGO_BARRAS_MOVIMIENTO_CODIGO_BARRAS_ID=$movimiento_id "
			  ."AND MOVIMIENTO_ITEM_CODIGO_BARRAS_ARTICULO_CODIGO_BARRAS_IN=$articulo_agregado_indice "
			  ."AND MOVIMIENTO_ITEM_CODIGO_BARRAS_ARTICULO_CODIGO_BARRAS_ID=$articulo_agregado_id "
			  ."AND MOVIMIENTO_ITEM_CODIGO_BARRAS_ID=$articulo_agregado_movimiento_item_id;";
			  
			  //echo $query_movimiento_item;
			  
			  $resultado_query_movimiento_item=nmysql2_query($query_movimiento_item,$link);
			  
			  if($resultado_query_movimiento_item){
			   if(nmysql2_affected_rows($link)==0){
               $control_reload[6]=1;
			   }		
			  }
			  else{
			  $control_reload[6]=1;
			  }			  			   
			   
			   }
			   else{
			   $control_reload[6]=1;   
			   }			   
			  
			   }
			   
			   }
			  
			  }	
} 

?>
<html>
<head>
<title>iSupport - Customized for ESS ElectroSignSupply Corp.</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
body {
	background-color: #FFFFFF;
}
</style>
<link href="recursos/css2/titulos.css" rel="stylesheet" type="text/css">
<link href="recursos/css2/textos.css" rel="stylesheet" type="text/css">
<link href="recursos/css2/input_type.css" rel="stylesheet" type="text/css">
<style type="text/css">
<!--
.style2 {
	color: #648891;
	font-weight: bold;
}
-->
</style>
</head>
<script src="recursos/js/jquery-1.8.3.min.js"></script>
<script src="recursos/js/B_code128.js"></script>
<script language="JavaScript">
var salir_ventana=0;
    window.onload = function () {
      
}
    function esok(){
    salir_ventana=1;
    document.formu.action = "inventario_codigo_barras_add.php?vrevselr=<?php echo $bodega_id;?>"; 
    document.formu.submit();
	
    }
window.onbeforeunload=function(){
if(salir_ventana==0)
return 'Exit page';
};
</script>

<body>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td></td>
    <td valign="top">
	  <table width="100%"  border="0" align="center" cellpadding="0" cellspacing="0">
        <tr>
            <td align="right"><div style="margin-top:-0.1em;margin-left:2%;" class="mensaje_bodega_nombre"><b><?php echo $bodega_nombre;?>&nbsp;STORE</b></div><span class="textEncabezadoPagina">Inventory Article Store Input: Add Save</span></td>
        </tr>
      </table>
        <form name="formu" id="formu" action="" method="post" >
<div style="margin-left:36px"></div>
        <table width="98%" border="0" align="center" cellpadding="0" cellspacing="0">
          <!--<tr>
            <td width="10" height="10"><img src="recursos/bordform_left-top.gif" width="10" height="10"></td>
            <td width="100%" background="recursos/bordform_topfon-vert.gif"><img src="recursos/transp.gif" height="1"></td>
            <td width="10"><img src="recursos/bordform_der-top.gif" width="10" height="10"></td>
          </tr>-->
          <tr>
            <td></td>
            <td valign="top">			<table width="99%"  border="0" cellpadding="0" cellspacing="0" bgcolor="#C1D0D4">
              <tr>
                <td bgcolor="#FFFFFF">
                <!-- Resultado -->
                <table width="100%" cellpadding="4" cellspacing="0" style="border-collapse: collapse;" class="borde_tabla">
              		<tr bgcolor="#FFFFFF" class="borde_tabla">
                		<td class="infoMsg borde_tabla" align="center"><img src="recursos/ico_info_mess.gif" width="33" height="28" align="absmiddle">Data entry confirmation</td>
              		</tr>
                  <tr bgcolor="#FFFFFF" class="tabla_separador">
                    <td class="borde_tabla">&nbsp;</td>
                  </tr>
                  <tr bgcolor="#FFFFFF" class="moduloDown borde_tabla">
                    <td align="left" class="borde_tabla">Inventory entry:&nbsp;</td>
				  </tr>
				  <tr bgcolor="#FFFFFF" class="moduloDown borde_tabla">
                    <td class="textConNegrilla borde_tabla">&nbsp;<?php echo $movimiento_id; ?></td>
                  </tr>
                  <tr bgcolor="#FFFFFF" class="moduloDown borde_tabla">
                    <td class="titleNegrillaTabla" align="center">
            				<?php
                                          if($control_reload[0]==0){
            				   echo "Saved correctly !!";
                               
							  
					  }
                                          else{
                                             echo "Can not saved correctly !";
                                             registrar("inventario articulo barcode","inventario articulo barcode",7,"[Reload]No se pudo agregar ",$link); 
                                             }
                                             
								 if($control_reload[1]==1){									 
								 echo "Conection error saved items ".$mensaje_error; 
								 }
								 
            				     if($control_reload[2]==1){
            				     echo "<br>By conection error, this article already saved";
            				     registrar("Articulo barcode","articulo barcode",7,"[Reload]Error el artículo ya fue guardado ",$link); 
            				     }                                                                                                      				    

								 if($control_reload[4]==1){									 
								 echo "Conection error saved expire date "; 
								 }								 
								 
								 if($control_reload[5]==1){									 
								 echo "Conection error saved serial "; 
								 }

								 if($control_reload[6]==1){
								 echo "Conection error saved system backup"; 
								 }
								 
            				?>
										</td>
                  </tr>                
                </table>
				<br>
				<table width="100">
				
				<?php
 				$contador_color=0;				
				$res="";
				$nfila=0;
				$registro_articulos_agregados="";
				//carga el listado de items guardados
				
	$query_codigo_barras_listado = "SELECT MOVIMIENTO_ITEM_CODIGO_BARRAS_ARTICULO_CODIGO_BARRAS_ID,"
							."MOVIMIENTO_ITEM_CODIGO_BARRAS_ARTICULO_CODIGO_BARRAS_IN,"
							."MOVIMIENTO_ITEM_CODIGO_BARRAS_CANTIDAD,"
							."MOVIMIENTO_ITEM_CODIGO_BARRAS_ESTADO "
							."FROM movimiento_item_codigo_barras "					  
							."WHERE MOVIMIENTO_ITEM_CODIGO_BARRAS_MOVIMIENTO_CODIGO_BARRAS_ID=$movimiento_id "
							."AND MOVIMIENTO_ITEM_CODIGO_BARRAS_USUARIO_ID=$usuario_id;";		
	//echo $query_codigo_barras_listado;
	
	$resultado_codigo_barras_listado = nmysql2_query($query_codigo_barras_listado,$link);

	if($resultado_codigo_barras_listado){
		
	if(nmysql2_num_rows($resultado_codigo_barras_listado)>0){

/*	
	<td class='moduloDown borde_tabla' width='4%'>
	State
	</td>	
*/

	$res="<table width='100%' class='borde_tabla' style='border-collapse: collapse;padding:0.5em;margin-top:0.5em;' cellpadding='10'>
	<tr bgcolor='#FFFFFF' class='borde_tabla'>
	<td class='moduloDown borde_tabla' width='48%'>
	Description
	</td>
	<td class='moduloDown borde_tabla' width='48%'>
	Quantity
	</td>
	</tr>
	";
	
	  while($row=nmysql2_fetch_assoc($resultado_codigo_barras_listado)){	

		$articulo_id=$row["MOVIMIENTO_ITEM_CODIGO_BARRAS_ARTICULO_CODIGO_BARRAS_ID"];
		$articulo_indice=$row["MOVIMIENTO_ITEM_CODIGO_BARRAS_ARTICULO_CODIGO_BARRAS_IN"];
        $articulo_cantidad_registrada=$row["MOVIMIENTO_ITEM_CODIGO_BARRAS_CANTIDAD"];
		$articulo_estado=$row["MOVIMIENTO_ITEM_CODIGO_BARRAS_ESTADO"];
		
		if($articulo_estado==1){
		$articulo_estado_nombre="New";
		}
		if($articulo_estado==2){
		$articulo_estado_nombre="Used";
		}
		if($articulo_estado==3){
		$articulo_estado_nombre="Existent";
		}
		
		$query_item_codigo_barras = "SELECT ARTICULO_CODIGO_BARRAS_REFERENCIA,"		
									."ARTICULO_CODIGO_BARRAS_CODIGO "
									."FROM articulo_codigo_barras_$articulo_indice "
									."WHERE ARTICULO_CODIGO_BARRAS_ID=$articulo_id;";
		
        //echo $query_item_codigo_barras;
 		
		$resultado_item_codigo_barras = nmysql2_query($query_item_codigo_barras,$link);

		if($resultado_item_codigo_barras){
		if(nmysql2_num_rows($resultado_item_codigo_barras)>0){
		  while($row2=nmysql2_fetch_assoc($resultado_item_codigo_barras)){
			  
		  $articulo_codigo_barras_referencia=$row2["ARTICULO_CODIGO_BARRAS_REFERENCIA"];
		  $articulo_codigo_barras_barcode=$row2["ARTICULO_CODIGO_BARRAS_CODIGO"];
		
		  $registro_articulos_agregados.="indice=$articulo_indice id=$articulo_id barcode=".FormatearDatosEscTextoConFormat($articulo_codigo_barras_barcode,$link)." cantidad=$articulo_cantidad_registrada estado=$articulo_estado_nombre\n";
		  
		  if($nfila!=0){
		  $separador="<tr bgcolor='#FFFFFF' class='tabla_separador'>
                <td colspan='2' class='borde_tabla' style='font-size:3px;'>&nbsp;</td>
                </tr>";
		  }
		  if($nfila==0){
		  $separador="";
		  $nfila=1;
		  }

/*
				<td class='textSinNegrilla borde_tabla' valign='top'>
				$articulo_estado_nombre
				</td>
*/		  
		  $res.="$separador
				<tr bgcolor='#FFFFFF'>
				<td class='textSinNegrilla borde_tabla' valign='top'>
				<b>Code:</b> $articulo_codigo_barras_referencia<br>
				<b>Barcode:</b> $articulo_codigo_barras_barcode
				</td>
				<td class='textSinNegrilla borde_tabla' valign='top'>
				$articulo_cantidad_registrada
				</td>
				</tr>
				";
		  
		  }  
		 }
		
	    }
	   
	  }
	  
	  $res.="</table>";
  	  
	  echo $res;
		 
	 }
	 else{
	  if(nmysql2_num_rows($resultado_codigo_barras_listado)==0){
	  $control_reload[3]=1; 
	  }	  
	
	 }	
	
    }
	else{		
	$control_reload[3]=1; 	
	}

    if($control_reload[0]==0){
    //registro de actividades(Servicio,Tabla,Accion,Observ,Link)
	
    registrar("Inventario articulo barcode","inventario articulo barcode",4,"Bodega=$bodega_nombre\nEntrada inventario\nid_t=$movimiento_codigo_barras_temporal_id movimiento=$movimiento_id\n$registro_articulos_agregados",$link);	
	
	sendemail($movimiento_id ,$link,$bodega_nombre,$movimiento_tipo,$documento_fecha,$fecha,$tercero,$documento);	
}
	
	nmysql2_close($link);
				?>
                </table>  				
				</td>
              </tr>
            </table>
						</td>
            <td>&nbsp;</td>
					</tr>
					<!--<tr>
            <td height="10"><img src="recursos/bordform_left-aba.gif" width="10" height="10"></td>
            <td width="100%" background="recursos/bordform_abafon-vert.gif"><img src="recursos/transp.gif" width="8" height="8"></td>
            <td height="10"><img src="recursos/bordform_der-aba.gif" width="10" height="10"></td>
          </tr>-->
        </table>
		<table width="93%"  border="0" align="center">
          <tr>
            <td width="100%" valign="top">	
            <input type="button" class="button" name="aceptar" value="Accept" onclick="javascript:esok();">		
			
<?php
if($control_reload[0]==0){
  $salida_reporte_pdf =<<<TALES
  <script language="JavaScript">
  function prep(valor) {
	 if(valor==1){
	 salir_ventana=1;
	 }
  }
 </script>  
 <a href="movimiento_articulo_codigo_barras_report_det.php?retorno_lista=0/-/$movimiento_id"><input type="button" id="cppdf" class="button" style="margin-left:1em;" value="Print PDF" onclick="javascript:prep(1);"></a> 
TALES;
echo $salida_reporte_pdf;
}
?>
			</td>
          </tr>
        </table>
    <br></td>
    <td>&nbsp;</td>
  </tr>
</table>     
</body>
</html>
