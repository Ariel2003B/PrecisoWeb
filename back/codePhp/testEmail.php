<?php
$para = 'elvisguato02@gmail.com';
$asunto = 'Correo de prueba con HTML';
$mensaje = '<html><body><h1>Hola, este es un correo en HTML</h1><p>Enviado desde PHP</p></body></html>';
$cabeceras  = "MIME-Version: 1.0\r\n";
$cabeceras .= "Content-type: text/html; charset=UTF-8\r\n";
$cabeceras .= "From: suscripciones@soporte.precisogps.com\r\n";
$cabeceras .= "Reply-To: suscripciones@soporte.precisogps.com\r\n";

if(mail($para, $asunto, $mensaje, $cabeceras)) {
    echo 'Correo enviado con éxito';
} else {
    echo 'Error al enviar el correo';
}
?>
