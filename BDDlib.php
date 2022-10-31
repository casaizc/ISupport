<?php 
//Conexion a la base de datos
include ("bdAct.php");

function conectarBDD(){
 $servidor = "127.0.0.1";

$usuario = "root";
$password = "123456";
$BDD = "ess_bd";


 //conecto con la base de datos 
 $enlace = mysqli_connect($servidor,$usuario,$password,'',3307); 

if (!mysqli_set_charset($enlace, "utf8")) {
   // printf("Error cargando el conjunto de caracteres utf8: %s\n", mysqli_error($enlace));
    exit();
} else {
    //printf("Conjunto de caracteres actual: %s\n", mysqli_character_set_name($enlace));
}
 //selecciono la BBDD 
 mysqli_select_db($enlace,$BDD);
 return($enlace); 
}

function conectarBDD2(){
$servidor = "127.0.0.1";

$usuario = "root";
$password = "123456";
$BDD = "ess_bd";


 //conecto con la base de datos 
 $enlace = mysqli_connect($servidor,$usuario,$password,'',3307); 

if (!mysqli_set_charset($enlace, "utf8")) {
    //printf("Error cargando el conjunto de caracteres utf8: %s\n", mysqli_error($enlace));
    exit();
} else {
    //printf("Conjunto de caracteres actual: %s\n", mysqli_character_set_name($enlace));
}
 //selecciono la BBDD 
 mysqli_select_db($enlace,$BDD);
 return($enlace); 
}

function conectarBDDAlertas(){
$servidor_alertas = "localhost";
$usuario_alertas = "root";
$password_alertas = "123456";
$BDD_alertas = "ess_bd";


 //conecto con la base de datos 
 $enlace_alertas = mysqli_connect($servidor_alertas,$usuario_alertas,$password_alertas,'',3307); 

 
if (!mysqli_set_charset($enlace_alertas, "utf8")) {
    //printf("Error cargando el conjunto de caracteres utf8: %s\n", mysqli_error($enlace));
    exit();
} else {
    //printf("Conjunto de caracteres actual: %s\n", mysqli_character_set_name($enlace));
}
 //selecciono la BBDD 
 mysqli_select_db($enlace_alertas,$BDD_alertas);
 return($enlace_alertas); 
}

function conectarBDDHeader(){
 $servidor = "localhost";

$usuario = "root";
$password = "123456";
$BDD = "ess_bd";


 //conecto con la base de datos 
 $enlace = mysqli_connect($servidor,$usuario,$password,'',3307); 

 
if (!mysqli_set_charset($enlace, "utf8")) {
    //printf("Error cargando el conjunto de caracteres utf8: %s\n", mysqli_error($enlace));
    exit();
} else {
    //printf("Conjunto de caracteres actual: %s\n", mysqli_character_set_name($enlace));
}
 //selecciono la BBDD 
 mysqli_select_db($enlace,$BDD);
 return($enlace); 
}

function conectarBDDInventario(){
 $servidor = "localhost";

$usuario = "root";
$password = "123456";
$BDD = "ess_bd";


 //conecto con la base de datos 
 $enlace = mysqli_connect($servidor,$usuario,$password,'',3307); 

 
if (!mysqli_set_charset($enlace, "utf8")) {
    //printf("Error cargando el conjunto de caracteres utf8: %s\n", mysqli_error($enlace));
    exit();
} else {
    //printf("Conjunto de caracteres actual: %s\n", mysqli_character_set_name($enlace));
}
 //selecciono la BBDD 
 mysqli_select_db($enlace,$BDD);
 return($enlace); 
}

function conectarBDDMensajes(){
 $servidor = "localhost";

$usuario = "root";
$password = "123456";
$BDD = "ess_bd";


 //conecto con la base de datos 
 $enlace = mysqli_connect($servidor,$usuario,$password,'',3307); 

 
if (!mysqli_set_charset($enlace, "utf8")) {
    //printf("Error cargando el conjunto de caracteres utf8: %s\n", mysqli_error($enlace));
    exit();
} else {
    //printf("Conjunto de caracteres actual: %s\n", mysqli_character_set_name($enlace));
}
 //selecciono la BBDD 
 mysqli_select_db($enlace,$BDD);
 return($enlace); 
}
function conectarBDDMts(){
 $servidor = "localhost";

$usuario = "root";
$password = "123456";
$BDD = "ess_bd";


 //conecto con la base de datos 
 $enlace = mysqli_connect($servidor,$usuario,$password,'',3307); 

 
if (!mysqli_set_charset($enlace, "utf8")) {
    //printf("Error cargando el conjunto de caracteres utf8: %s\n", mysqli_error($enlace));
    exit();
} else {
    //printf("Conjunto de caracteres actual: %s\n", mysqli_character_set_name($enlace));
}
 //selecciono la BBDD 
 mysqli_select_db($enlace,$BDD);
 return($enlace); 
}


?>
