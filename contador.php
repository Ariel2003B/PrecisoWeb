<?php
$file = 'contador.txt';

// Comprobar si la cookie 'visitado' está configurada
if (!isset($_COOKIE['visitado'])) {
    // Leer el contador
    $visitas = (int) file_get_contents($file);

    // Incrementar el contador
    $visitas++;

    // Guardar el nuevo valor
    file_put_contents($file, $visitas);

    // Configurar la cookie para evitar que se incremente en próximas visitas
    setcookie('visitado', '1', time() + 3600 * 24); // Cookie válida por 24 horas
} else {
    // Si la cookie existe, solo leer el contador
    $visitas = (int) file_get_contents($file);
}

// Mostrar el contador
echo $visitas;
?>
