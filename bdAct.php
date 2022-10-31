<?php
//obtiene el resultado
function nmysql2_select_db($db,$link){	
mysqli_select_db ($link,$db);
}
//consulta $query,$link
function nmysql2_query($query,$link){    
mysqli_query($link, "SET SESSION sql_mode = 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");
return(mysqli_query($link,$query));
}
//retorna el numero de registros
//$query consulta
function nmysql2_num_rows($query){
return(mysqli_num_rows($query));		
}
//retorna array
//$query consulta
function nmysql2_fetch_array($query){	
return(mysqli_fetch_array($query));
}
//free_result de consutla
//$query consulta
function nmysql2_free_result($query){	
mysqli_free_result($query);
}
//cierra la conexion
//$link conexion
function nmysql2_close($link){	
mysqli_close($link);
}
//obtiene array de consulta
//$link conexion
function nmysql2_fetch_assoc($query){
return(mysqli_fetch_assoc($query));	
}
//obtiene el resultado de un campo en determinada fila de la consulta
//$query consulta
//$fila  fila de la consulta
//$campo campo del resultado de la consulta en fetch_assoc
function nmysql2_result($query, $fila, $campo){	
mysqli_data_seek($query,$fila);
$res = nmysql2_fetch_assoc($query);	
return($res[$campo]);
}
//cambia la posicion del cursor en una consulta
//$query consulta 
//$fila fila
function nmysql2_data_seek ($query, $fila){
return(mysqli_data_seek($query,$fila));	
}

//retorna el numero de registros afectados
//$link conexion mysqli
function nmysql2_affected_rows($link){
return(mysqli_affected_rows($link));	
}
//retorna el error de una conexión
//$lin conexion mysqli
function nmysql2_error($link){
return(mysqli_error($link));    
}
//Es una funcion que escapa a los caracteres especiales de una cadena 
function nmysql2_escape_string($string,$link)
{        
return(mysqli_real_escape_string($link,$string));    
}
//Retorna el id insertado en una tabla
function nmysqli2_insert_id($link){
return(mysqli_insert_id($link));
}
//Retorna el id insertado en una tabla
function nmysql2_real_escape_string($string,$link){
return(mysqli_real_escape_string($link,$string));
}

function nutf8_decode($texto)
{
return utf8_encode($texto);
}

function mysql2_fetch_field($query){
return mysqli_fetch_field($query);
}

function mysql2_num_fields($query){
return mysqli_num_fields($query);
}

?>
