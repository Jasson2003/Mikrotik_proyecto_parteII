<?php
session_start();
require('routeros_api.class.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $ip_bandwidth = $_POST['ip_bandwidth'];
    $download_limit = $_POST['download_limit'];
    $upload_limit = $_POST['upload_limit'];

    $API = new RouterosAPI();

    if ($API->connect('192.168.3.155', 'admin', 'admin')) {
        // Obtener la lista de colas simples
        $queues = $API->comm('/queue/simple/print');

        // Verificar si existe una cola para la IP especificada
        $queueId = null;
        foreach ($queues as $queue) {
            if ($queue['target'] === $ip_bandwidth) {
                $queueId = $queue['.id']; // Guardar el ID de la cola
                break;
            }
        }

        // Si no se encontró la cola, mostrar mensaje de error
        if ($queueId === null) {
            $_SESSION['message'] = "No se encontró una cola de ancho de banda para la IP proporcionada.";
            $_SESSION['alert_class'] = 'alert-danger';
        } else {
            // Actualizar el límite de ancho de banda
            $API->comm('/queue/simple/set', [
                '.id' => $queueId,
                'max-limit' => $download_limit . '/' . $upload_limit
            ]);
            $_SESSION['message'] = "Límite de ancho de banda actualizado exitosamente.";
            $_SESSION['alert_class'] = "alert-success";
        }

        $API->disconnect();
    } else {
        $_SESSION['message'] = "Error al conectar a MikroTik.";
        $_SESSION['alert_class'] = "alert-danger";
    }
}

header("location: index.php");
?>
