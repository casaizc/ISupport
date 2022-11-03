<?php 
 require_once 'phpmailer/PHPMailerAutoload.php';// Se llama la libreria dpara poder configurar el correo

 include ("seguridad/seguridad.php");
 include ("lib/BDDlib.php");
 include ("lib/CFGlib.php"); 
 include ("lib/Lmensajes.php"); 
 include ('sendemail.php');

 //conecto con la base de datos 
$link = conectarBDD();

 //consultamos los permisos del usuario
$permiso=  consultaPermisoUsuario(69, $link);
$permisoAdd=$permiso[3];

if($permiso==0){
echo "Access denied";
exit();
}

 $arregloaux =  array();
 $contador=0;
 $control_reload = 0;
 $control_reload2 = 0;
 $control_reload3 = 0;
 $control_reload4 = 0;
 $control_reload5 = 0;
   
 $usuario_canal_id=obtenerUsuarioCanalId($link);

 $consecutivo= $_SESSION["consecutivo"];
 $usuario_crea = $_SESSION["nombreuser"];
 $date=  date("Y-m-d H:i:s");
 //Tomo un cliente 
 if(isset($_GET['proveedor'])){
 $proveedor=$_GET['proveedor'];
 }else{
 $proveedor='NULL';
 }
 if(isset($_GET['cliente'])){
 $cliente=$_GET['cliente'];
 }else{
 $cliente='NULL';
 }
 if(isset($_GET['arti'])){
  $arti=$_GET['arti'];
  }else{
  $arti='NULL';
  }
 //TOMO UN MOVIMIENTO 
 $tipo_movimiento=$_GET['movimiento'];
 $queryTipoMov="SELECT * FROM movimiento_tipo WHERE MOVIMIENTO_ID= $tipo_movimiento ";
 $resultTipoMov=  nmysql2_query($queryTipoMov,$link);
 $arregloTipoMov=  nmysql2_fetch_assoc($resultTipoMov);
 if($tipo_movimiento==24){
     if(isset($_GET["tecnico"])){
         $tecnico=$_GET["tecnico"];
         $queryTecnico="SELECT CONCAT(USUARIO_NOMBRES,' ',USUARIO_APELLIDOS) AS USUARIO FROM usuario WHERE USUARIO_LOGIN='$tecnico'";
         $resultTecnico=  nmysql2_query($queryTecnico,$link);        
         $arregloUsuario=  nmysql2_fetch_assoc($resultTecnico);
     }
 }
 else
     $tecnico=NULL;
 $bodega_filter=$_SESSION['bodega_filter'];
 //CONSULTAMOS EL id de la bodega
 $query_bodega="SELECT BODEGA_ID,BODEGA_CANAL FROM bodega WHERE BODEGA_NOMBRE='$bodega_filter'";
 $resul=  nmysql2_query($query_bodega,$link);
 $bodega;
 $articulo;
 $refer;
  // CAPTURO TODAS LOS INPUTS DEL FORMULARIO DE ENTRADA Y SE ASIGNA A UN ARREGLO
 //esto para tener los input de los artciculos
 foreach ($_GET as $clave => $valor) {   
    if($clave!="arti" && $clave!="cliente" && $clave!="proveedor" && $clave!="movimiento" && $clave!="documento" && $clave!="documento_fecha" && $clave!="observacion" && $clave!="tecnico" && $clave!="bodeg" && $clave!="id_aut" && $clave!="opc_tipo_tercero" && $clave!="no_requisicion"){
    $arregloaux[$contador]=$valor;
    $contador++; 
    }
}
 while ($row=  nmysql2_fetch_array($resul)){
     $bodega=$row['BODEGA_ID'];
     $bodega_canal_id=$row['BODEGA_CANAL'];
     $id_bodega=$bodega;
 }
 //insertamos el encabezado del movimiento
  if(isset($_GET["documento"])){
     $documento=  strtoupper($_GET["documento"]);
    
 }
 else{
     $documento=NULL;
     
 }
  if(isset($_GET["documento_fecha"])){
     $vdocumento_fecha= "'".$_GET["documento_fecha"]."'";
    
 }
 else{
     $vdocumento_fecha='NULL';
     
 }
   if(isset($_GET["observacion"])){    
     $observacion=  mb_strtoupper($_GET["observacion"]);
     }
 else{
     $observacion = NULL;
     
 } 
   if(isset($_GET["no_requisicion"])){    
     $requisicion_id= $_GET["no_requisicion"];
     }
 else{
     $requisicion_id = NULL;
     
 } 
 //valida el id de autorizacion utilizada
 if(isset($_GET["id_aut"])){
 $id_autorizacion=$_GET["id_aut"]; 
 }
 //obtiene el id del usuario
 $queryUsuario="SELECT USUARIO_ID,USUARIO_MOVIMIENTO_BODEGA_ID FROM usuario WHERE USUARIO_LOGIN='$usuario_crea'";
 $resultUsuario=nmysql2_query($queryUsuario,$link);
 $rowUsuario=nmysql2_fetch_array($resultUsuario);
 $id_usuario=$rowUsuario["USUARIO_ID"];
 $usuario_movimiento_bodega=$rowUsuario["USUARIO_MOVIMIENTO_BODEGA_ID"];
 //revisa si hay reenvio al guardar el registro
 if(isset($_SESSION["id_registro_revisado_plantilla"])){
  if(array_key_exists("inventario_add",$_SESSION["id_registro_revisado_plantilla"])){
  $consecutivo_revisado=$_SESSION['id_registro_revisado_plantilla']['inventario_add'];
  $query_consecutivo_movimiento="SELECT * FROM movimiento WHERE MOVIMIENTO_ID=$consecutivo_revisado"; 
  $consulta_consecutivo=  nmysql2_query($query_consecutivo_movimiento,$link);
   if( nmysql2_num_rows($consulta_consecutivo) >= 1 ){
   $control_reload = 1;
   $control_reload2 = 2;
   }
  }
 } 
 
 $arreglo_usuario_movimiento_bodega=array();
 $arreglo_usuario_movimiento_bodega=explode(",",$usuario_movimiento_bodega);

 $permiso_movimiento_bodega=false;

 for($i=0;$i<count($arreglo_usuario_movimiento_bodega);$i++){
    if($arreglo_usuario_movimiento_bodega[$i]==$id_bodega){
    $permiso_movimiento_bodega=true;
    }
 }
 
 if(!$permiso_movimiento_bodega){
 echo "Access denied";
 exit();
 }

 //valida si previamente se se ha utilizado la plantilla de agregar
 if(isset($_SESSION['deshabilitar_atras_inventario'])){
  if($_SESSION['deshabilitar_atras_inventario']==1){
  $control_reload=1;
  }
 }
 
 $_SESSION['deshabilitar_atras_inventario']=1;
 
 //if ( nmysql2_num_rows($consulta_consecutivo) >= 1 )
 //$control_reload = 1; Desahabilitado por id de movimiento que bloquea el registro pero se puede omitir
 if($control_reload==0){
     if(is_null($tecnico)){
         $query_insert_movimiento="INSERT INTO movimiento(DOCUMENTO,DOCUMENTO_FECHA,MOVIMIENTO_CLIENTE_ID,MOVIMIENTO_PROVEEDOR_ID,MOVIMIENTO_FECHA,MOVIMIENTO_TIPO,MOVIMIENTO_BODEGA,MOVIMIENTO_OBSERVACION,MOVIMIENTO_USUARIO)VALUES('".trim($documento)."',$vdocumento_fecha,$cliente,$proveedor,'$date',$tipo_movimiento,$bodega,'$observacion','$usuario_crea')";
     }else{
         $query_insert_movimiento="INSERT INTO movimiento(DOCUMENTO,DOCUMENTO_FECHA,MOVIMIENTO_CLIENTE_ID,MOVIMIENTO_PROVEEDOR_ID,MOVIMIENTO_FECHA,MOVIMIENTO_TIPO,MOVIMIENTO_BODEGA,MOVIMIENTO_OBSERVACION,MOVIMIENTO_USUARIO,MOVIMIENTO_USUARIO_TECNICO)VALUES('".trim($documento)."',$vdocumento_fecha,$cliente,$proveedor,'$date',$tipo_movimiento,$bodega,'$observacion','$usuario_crea','$tecnico')";
     }
$movimiento =  nmysql2_query($query_insert_movimiento,$link);
 //verificamos que se halla ralizad el insert
 $afecta = nmysql2_affected_rows($link);
 //consultamos el id que se acaba de generar 
 $maximo = nmysqli2_insert_id($link);
 
 if(!isset($_SESSION['id_registro_revisado_plantilla'])){
 $_SESSION['id_registro_revisado_plantilla']=array();
 $_SESSION['id_registro_revisado_plantilla']['inventario_add']=$maximo;
 }else{
 $_SESSION['id_registro_revisado_plantilla']['inventario_add']=$maximo;
 }
 
//insertamos los items de movimiento
$tamano=  count($arregloaux);
$registroExistencias=0;
$registroItemMovimiento=0;
$referencia=0;
$serial1=1;
$serial2=2;
$ftinta=3;
$estado=4;
$cantidad=5;
$cadena="";
if($afecta==1){

//chequea si los items existen si no los agrega 
/*Desahabilitado por falta de revisión de referencias
if(!is_null($requisicion_id)&&$requisicion_id!=""){

while($cantidad<$tamano){
        if($arregloaux[$referencia]!="0"){
            if($arregloaux[$cantidad]!=""){
            
            $query_validar_articulo="SELECT ARTICULO_ID,(FIND_IN_SET('$bodega_canal_id',ARTICULO_ARTICULO_VISIBILIDAD_CANAL_ID)>0) AS VISIBILIDAD_CANAL_ID FROM articulo WHERE ARTICULO_REFERENCIA='".nmysql2_escape_string($arregloaux[$referencia],$link)."' AND ARTICULO_BODEGA=$bodega ";//echo $query_validar_articulo;
            
            $resultado_query_validar_articulo=nmysql2_query($query_validar_articulo,$link);
            
             if($resultado_query_validar_articulo){
             
              if(nmysql2_num_rows($resultado_query_validar_articulo)<1){
              
              echo "Falta".$arregloaux[$referencia]."<br>";
              
              }else{
              
               while($row_valid=nmysql2_fetch_assoc($resultado_query_validar_articulo)){
               
               $validacion_visibilidad_canal_id=$row_valid["VISIBILIDAD_CANAL_ID"];
               
               if($validacion_visibilidad_canal_id=="0"){
               
               $query_ajustar_visibilidad="UPDATE articulo SET ARTICULO_ARTICULO_VISIBILIDAD_CANAL_ID= IF(ARTICULO_ARTICULO_VISIBILIDAD_CANAL_ID IS NULL,$bodega_canal_id,IF(ARTICULO_ARTICULO_VISIBILIDAD_CANAL_ID='',$bodega_canal_id,CONCAT(ARTICULO_ARTICULO_VISIBILIDAD_CANAL_ID,',$bodega_canal_id')))  WHERE ARTICULO_REFERENCIA='".nmysql2_escape_string($arregloaux[$referencia],$link)."' AND ARTICULO_BODEGA=$bodega ";
               
               $resultado_query_ajustar_visibilidad=nmysql2_query($query_ajustar_visibilidad,$link);
               
               }
               
               }
              
              }            
            
             }
            
            }
            
        }
        
    $referencia+=6;
    $serial1+=6;
    $serial2+=6;
    $ftinta+=6;
    $estado+=6;
    $cantidad+=6;
        
}

}

$registroExistencias=0;
$registroItemMovimiento=0;
$referencia=0;
$serial1=1;
$serial2=2;
$ftinta=3;
$estado=4;
$cantidad=5;
$cadena="";

*/

while($cantidad<$tamano){
        if($arregloaux[$referencia]!="0"){
            if($arregloaux[$cantidad]!=""){
                //if($arregloaux[$valor]!=""){ se comentarea por que no se esta manejando valor                                
                    $query_existencia_articulo="SELECT ARTICULO_CANTIDAD,ARTICULO_ID FROM articulo "
                            . "WHERE ARTICULO_REFERENCIA='".nmysql2_escape_string($arregloaux[$referencia],$link)."' AND "
                            ." ARTICULO_BODEGA=$bodega";
                    $result=  nmysql2_query($query_existencia_articulo,$link);                    
                    $row=  nmysql2_fetch_array($result);
                    $existencias=$row['ARTICULO_CANTIDAD'];
                    $articulo_id=$row['ARTICULO_ID'];
                    $existencias_nueva=$existencias+$arregloaux[$cantidad];
                    $cadena.=" ".$arregloaux[$referencia]."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$arregloaux[$cantidad]."<br>";
                    $actualiza_existencias="UPDATE articulo SET ARTICULO_CANTIDAD=ARTICULO_CANTIDAD+".$arregloaux[$cantidad]." "
                            . "             WHERE ARTICULO_REFERENCIA='".nmysql2_escape_string($arregloaux[$referencia],$link)."' AND ARTICULO_BODEGA=$bodega";
                    $query_actualiza_existencias=  nmysql2_query($actualiza_existencias,$link);
                    if(nmysql2_affected_rows($link)==1)
                    {
                        $registroExistencias++;                    
		      //Arreglo para colocar valores nulos a los campos que no son obligatorios
		      if(($arregloaux[$serial1]=="")||($arregloaux[$serial1]==NULL)){
		      $arregloaux[$serial1]="NULL";
		      }
		      if($arregloaux[$serial1]!="NULL"){
		      $arregloaux[$serial1]="'".nmysql2_escape_string($arregloaux[$serial1],$link)."'";
		      }
                    
		      if(($arregloaux[$serial2]=="")||($arregloaux[$serial2]==NULL)){
		      $arregloaux[$serial2]="NULL";
		      }
		      if($arregloaux[$serial2]!="NULL"){
		      $arregloaux[$serial2]="'".nmysql2_escape_string($arregloaux[$serial2],$link)."'";
		      }
                    
		      if(($arregloaux[$ftinta]=="")||($arregloaux[$ftinta]==NULL)){
		      $arregloaux[$ftinta]="NULL";
		      }
		      if($arregloaux[$ftinta]!="NULL"){
		      $arregloaux[$ftinta]="'".$arregloaux[$ftinta]."'";
		      }                                                                                

		      if(($arregloaux[$estado]=="")||($arregloaux[$estado]==NULL)){
		      $arregloaux[$estado]="NULL";
		      }
		      
		      
		      $query_movimiento_item="INSERT INTO movimiento_item(MOVIMIENTO_ID,ARTICULO_REFERENCIA,CANTIDAD,MOVIMIENTO_ITEM_BODEGA,MOVIMIENTO_ITEM_SERIAL1,MOVIMIENTO_ITEM_SERIAL2,MOVIMIENTO_ITEM_FECHA_TINTA)VALUES($maximo ,'".nmysql2_escape_string($arregloaux[$referencia],$link)."',$arregloaux[$cantidad],$bodega,".$arregloaux[$serial1].",".$arregloaux[$serial2].",".$arregloaux[$ftinta].")";//Modificada la consulta para guardar los seriales
		      $movimiento_item=  nmysql2_query($query_movimiento_item,$link);
                    
		      if(nmysql2_affected_rows($link)==1){		      
                        $registroItemMovimiento++;      
                        $movimiento_item_id=nmysqli2_insert_id($link);
                        //Registra serial si es necesario
                        if(($arregloaux[$serial1]!="NULL")||($arregloaux[$serial2]!="NULL")){                        
                        registrarSerial($articulo_id,$arregloaux[$serial1],$arregloaux[$serial2],'ENTRADA',$movimiento_item_id,$arregloaux[$cantidad],$link);    
                         if($usuario_canal_id==346){
                         ingresar_serial_aplicacion($link,$arregloaux[$referencia],$bodega,$arregloaux[$serial1]);
                         }
                        }
			//Registra fecha si es necesario                        
                        if($arregloaux[$ftinta]!="NULL"){  
                        registrarFechaTinta($articulo_id,$arregloaux[$ftinta],'ENTRADA',$movimiento_item_id,$arregloaux[$cantidad],$link);
                        }       
                        if($arregloaux[$estado]!="NULL"){
                        RegistrarEstadoArticulo($movimiento_item_id,$arregloaux[$referencia],$bodega,$arregloaux[$estado],'ENTRADA',$arregloaux[$cantidad],$link);
                        }
                        
		      }

                      if($arregloTipoMov["MOVIMIENTO_ID"]==27){
                      
                          $queryAutorizacion="SELECT AUTORIZACION_EJECUTADA,AUTORIZACION_CANTIDAD,AUTORIZACION_AUTORIZADO_USUARIO_ID FROM autorizacion WHERE AUTORIZACION_ITEM_ID=$articulo_id AND AUTORIZACION_ITEM_BODEGA_ID=$bodega";
			  $resultAutorizacion=nmysql2_query($queryAutorizacion,$link);
			  if(nmysql2_num_rows($resultAutorizacion)>0)
			  {    
			  while($row2=nmysql2_fetch_array($resultAutorizacion)){
			  $arreglo_usuarios=array();
			  $ejecutado=$row2["AUTORIZACION_EJECUTADA"];
			  $cantidad=$row2["AUTORIZACION_CANTIDAD"];
			  $arreglo_usuarios=explode("/",$row2["AUTORIZACION_AUTORIZADO_USUARIO_ID"]);
			    for($i=0;$i<count($arreglo_usuarios);$i++){
			     if(($ejecutado==0)&&($arreglo_usuarios[$i]==$id_usuario)){
			     $queryAutorizacionEjecutada="UPDATE autorizacion SET AUTORIZACION_EJECUTADA=1,AUTORIZACION_EJECUTADA_FECHA='$date' WHERE AUTORIZACION_ID=$id_autorizacion";
			     $resultAutorizacionEjecutada=nmysql2_query($queryAutorizacionEjecutada,$link);
			     }
			    }
			  }
			 }                      
                      }
                    }
                //}
            //Se registra el mensaje en una tabla para evitar duplicados en la mensajeria pediente por activar si se necesita    
            //r_mensaje_compras($maximo,nmysql2_escape_string($arregloaux[$referencia],$link),$id_bodega,$_SESSION['nombreuser'],$link);
            }
            
        }
    //Se modifica a 4 por agregada de seriales 6 por fecha de tinta   
    $referencia+=6;
    $serial1+=6;
    $serial2+=6;
    $ftinta+=6;
    $estado+=6;
    $cantidad+=6;
    //comentareo por que ya no se necesita el valor unitario
    //$valor+=3;    
}

//guarda las cantidades de los items en la tabla de movimiento item de entrada de inventario
if(($bodega==1)||($bodega==4)||($bodega==10)||($bodega==30)){
duplicarInventarioBarcode($maximo,$id_usuario);
}

}}

function duplicarInventarioBarcode($movimiento_id,$usuario_id){
global $link,$control_reload3,$control_reload4,$control_reload5;
		   
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
	."MOVIMIENTO_FECHA,"
	."1,"
	."MOVIMIENTO_BODEGA,"
	."MOVIMIENTO_USUARIO,"
	."$usuario_id,"
	."0,"
	."MOVIMIENTO_CLIENTE_ID,"
	."MOVIMIENTO_PROVEEDOR_ID,"
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
      $control_reload3 = 1;
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
							  ."WHERE ARTICULO_CODIGO_BARRAS_REFERENCIA_TEMPORAL='$articulo_referencia_revisada' "
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
				  $control_reload4=1;
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
				  $control_reload5=1;
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
				  $control_reload5=1;
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
				  $control_reload5=1;
				  }				  
				  
				  }
				  
				  }

				}
				else{
				$control_reload4=1;
				}
			  
			} else{
	        $control_reload4=1;
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
    $control_reload3 = 1;
    }
				  
}

?>
<!-- otra pagina -->
<html>
<head>
<title>iSupport - Customized for ESS ElectroSignSupply Corp.</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
<!--
body {
	background-color: #E0EBED;
}
-->
</style>
<link href="recursos/css/titulos.css" rel="stylesheet" type="text/css">
<link href="recursos/css/textos.css" rel="stylesheet" type="text/css">
<link href="recursos/css/input_type.css" rel="stylesheet" type="text/css">
<style type="text/css">
<!--
.style2 {
	color: #648891;
	font-weight: bold;
}
-->
</style>
</head>
<body>
<script language="JavaScript">
var salir_ventana=0;
window.onload = function () {
    if (typeof history.pushState === "function") {
        history.pushState("jibberish", null, null);
        window.onpopstate = function () {
            history.pushState('newjibberish', null, null);
            // Handle the back (or forward) buttons here
            // Will NOT handle refresh, use onbeforeunload for this.
        };
    }
    else {
        var ignoreHashChange = true;
        window.onhashchange = function () {
            if (!ignoreHashChange) {
                ignoreHashChange = true;
                window.location.hash = Math.random();
                // Detect and redirect change here
                // Works in older FF and IE9
                // * it does mess with your hash symbol (anchor?) pound sign
                // delimiter on the end of the URL
            }
            else {
                ignoreHashChange = false;   
            }
        };
    }
}
function esok(valor) {
 if ( valor == 1 ) { 
  document.formu.action = "bodega_add.php";
 }
 else {
  salir_ventana=1;
  document.formu.action = "inventario_list.php"; 
 }
 document.formu.submit();
return;
}
window.onbeforeunload=function(){
if(salir_ventana==0)
return 'Exit page';
};
window.location.hash="no-back-button";
window.location.hash="Again-No-back-button";
window.onhashchange=function(){window.location.hash="no-back-button";};
</script>
<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
  <tr>
    <td width="10" height="10"><img src="recursos/bordblan_left-top.gif" width="10" height="10"></td>
    <td><img src="recursos/transp.gif" height="1"></td>
    <td width="10"><img src="recursos/bordblan_der-top.gif" width="10" height="10"></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td height="485" valign="top">
	  <table width="90%"  border="0" align="center" cellpadding="0" cellspacing="0">
        <tr>
          <td align="right"><span class="style2"><font face="Arial" size="2">Registro de Entradas : Adicionar</font></span></td>
        </tr>
      </table>
<form name="formu" method="post" action="">
<div style="margin-left:36px"></div>
        <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
          <tr>
            <td width="10" height="10"><img src="recursos/bordform_left-top.gif" width="10" height="10"></td>
            <td width="100%" background="recursos/bordform_topfon-vert.gif"><img src="recursos/transp.gif" height="1"></td>
            <td width="10"><img src="recursos/bordform_der-top.gif" width="10" height="10"></td>
          </tr>
          <tr>
            <td background="recursos/bordform_leftfon-hori.gif">&nbsp;</td>
            <td valign="top">			<table width="99%"  border="0" cellpadding="0" cellspacing="0" bgcolor="#C1D0D4">
              <tr>
                <td><table width="100%" cellpadding="4" cellspacing="0">
              		<tr bgcolor="#FFFFFF">
                		<td colspan="2" class="infoMsg" align="center"><img src="recursos/ico_info_mess.gif" width="33" height="28" align="absmiddle">&nbsp;Confirmaci&oacute;n de Entrada <?php echo $maximo; ?></td>
              		</tr>
                  <tr bgcolor="#FFFFFF" class="moduloDown">
                    <td colspan="2">&nbsp;</td>
                  </tr>
                  <tr bgcolor="#FFFFFF" class="moduloDown">
                      <td colspan="2" align="center">Tipo Movimiento :&nbsp;<?php  echo $arregloTipoMov["MOVIMIENTO_NOMBRE"]?></td>
                  </tr>
                  <?php
                    if($tipo_movimiento==24)
                        echo " <tr bgcolor='#FFFFFF' class='moduloDown'>
                                    <td colspan='2' align='center' class='titleNegrillaTabla' >T&eacute;nico :  $arregloUsuario[USUARIO]</td>
                              </tr>";
                  ?>
                  <tr bgcolor="#FFFFFF" class="moduloDown">
                    <td colspan="2" align="center" class="titleNegrillaTabla" ><?php echo "Se actualizo la existencias de la bodega ".$bodega_filter."<br>Entrada de Almacen $maximo" ?></td>
                  </tr>
                  <?php
                  $tamano=  count($arregloaux);
                  $referencia=0;
                  $cantidad=5;                 
                  while($cantidad<$tamano){
                      
                  ?>
                  <tr bgcolor="#FFFFFF" class="moduloDown">
                      <td colspan="2"  class="titleNegrillaTabla" align="center">Art&iacute;culo :&nbsp;<?php echo $arregloaux[$referencia] ?></td>
                    
                  
                  </tr>
                  <tr bgcolor="#FFFFFF" class="moduloDown">
                      <td colspan="2" align="center" class="titleNegrillaTabla" >Cantidad :&nbsp;<?php echo $arregloaux[$cantidad] ?></td>
                  </tr>
                  <?php
                  $referencia+=6;
                  $cantidad+=6;
                  
                  
                  ?>
                  
                  
                  
                  <tr bgcolor="#FFFFFF" class="moduloDown">
                    <td colspan="2" class="titleNegrillaTabla" align="center">
            				<?php
                    
                    }
            				 if ($control_reload == 0){//Se modifica a 5 la division de registros                                            
            				  if ($afecta== 1 && $registroExistencias==$tamano/6 && $registroItemMovimiento==$tamano/6){
            				   echo "Almacenado correctamente";
                       //registro de actividades(Servicio,Tabla,Accion,Observ,Link)
                       registrar("Inventario","articulo",4,"Se actualizo existencia Bodega $bodega_filter Entrada de Almacen Movimiento N&uacute;mero $maximo <br>Articulo&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Cantidad<br>$cadena ",$link);
                       //revisa si afecta a las notificaciones activas de inventario
                       revisarAlertasAtendidasItem();
            				  }
            				  else{
                                              if($afecta!= 1)
                                                   echo "No se pudo almacenar(error:Encabezado Movimiento)";
                                              if($registroExistencias!=$tamano/6)//Modificado->Se cambia a 6 por ser 6 registros por articulo
                                                   echo "No se pudo almacenar(error:Al actualizar las existencias de Inventario)";
                                                if($registroItemMovimiento!=$tamano/6)//Modificado->Se cambia a 6 por ser 6 registros por articulo
                                                   echo "No se pudo almacenar(error:Al registrar los items del Movimiento)";
                       //registro de actividades(Servicio,Tabla,Accion,Observ,Link)
                       registrar("Inventario","articulo",7,"No se pudo actulizar existencia  Bodega $bodega_filter Entrada de Almacen Movimiento N&uacute;mero $maximo <br>Articulo&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Cantidad<br>$cadena ",$link);
            				  }
            				 }
            				 else{
            				  if($control_reload2 == 0){
            				  echo "Error al guardar:$control_reload";
            				  }
            				  if($control_reload2 == 2){
            				  echo "Por error de conexión, ya se almaceno el documento previamente:$control_reload";
            				  }
                      //registro de actividades(Servicio,Tabla,Accion,Observ,Link)
                      registrar("Bodega","bodega",7,"[Reload]No se pudo almacenar nueva entrada(nombre=$tipo_movimiento - canal=$bodega_filter ) <br>Movimiento N&uacute;mero $maximo <br>$cadena",$link);
                      
										 }
                     if($cliente!='NULL'){
                      $clienteaux = $cliente;
                    }
                    else{
                      $clienteaux = $proveedor;
                    }
                    // $result = $arregloaux[$referencia];
                    sendemail($maximo,$link,$bodega_filter,$tipo_movimiento,$vdocumento_fecha,$date,$clienteaux,$documento);
										 nmysql2_close($link);
            				?>
										</td>
                  </tr>
                </table></td>
              </tr>
            </table>
		    		<br>
						</td>
            <td background="recursos/bordform_derfon-hori.gif">&nbsp;</td>
					</tr>
					<tr>
            <td height="10"><img src="recursos/bordform_left-aba.gif" width="10" height="10"></td>
            <td width="100%" background="recursos/bordform_abafon-vert.gif"><img src="recursos/transp.gif" width="8" height="8"></td>
            <td height="10"><img src="recursos/bordform_der-aba.gif" width="10" height="10"></td>
          </tr>
        </table>
		<table width="93%"  border="0" align="center">
                    <tr>
            <td width="30%" height="35" valign="bottom">
		
                <a onmousedown="javascript:salir_ventana=1;" href="movimiento_report.php?maximo=<?php echo $maximo;?>"><img src="recursos/ico_pdf.png" width="38" height="38" title="Descargar Reporte"></a>
                    

	     </td>
          </tr>
          <tr>
            <td width="30%" height="35" valign="bottom">
		
                <input type="button" class="button" name="regresar" value="Aceptar" onclick="javascript:esok(2);">

						</td>
          </tr>
        </table>
			</form>
    <br></td>
    <td background="recursos/bordblan_derfon-hori.gif">&nbsp;</td>
  </tr>
  <tr>
    <td height="10"><img src="recursos/bordblan_left-aba.gif" width="10" height="10"></td>
    <td background="recursos/bordblan_abafon-vert.gif"><img src="recursos/transp.gif" width="1"></td>
    <td height="10"><img src="recursos/bordblan_der-aba.gif" width="10" height="10"></td>
  </tr>
</table>
<?php

$tipo_de_usuario =  $_SESSION["tipouser"];
$nombre_de_usuario = $_SESSION["nombreuser"];
$lan2=$_SESSION["lan"];
$salida_js =<<<TALES
<script>
window.parent.frames[0].location="header.php?modulo_up=3&tipo=$tipo_de_usuario&usuario=$nombre_de_usuario&lan=$lan2";
</script>
TALES;
echo $salida_js;
?>
</body>
</html>



