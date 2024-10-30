<?php
session_start();
require('routeros_api.class.php');

$API = new RouterosAPI();

if (isset($_POST['new_ip']) && isset($_POST['new_interface']) && isset($_POST['old_ip'])) {
    $new_ip = $_POST['new_ip'];
    $new_interface = $_POST['new_interface'];
    $old_ip = $_POST['old_ip'];

    // Intentar conectar a MikroTik
    if ($API->connect('192.168.3.155', 'admin', 'admin')) {
        // Obtener el ID de la IP actual
        $currentData = $API->comm('/ip/address/print', ['?address' => $old_ip]);
        
        if (!empty($currentData)) {
            $id = $currentData[0]['.id'];

            // Preparar los datos para actualizar según los campos no vacíos
            $data = ['.id' => $id];
            if (!empty($new_ip)) {
                $data['address'] = $new_ip;
            }
            if (!empty($new_interface)) {
                $data['interface'] = $new_interface;
            }

            // Ejecutar el comando para actualizar la IP
            $API->comm('/ip/address/set', $data);

            // Verificar si hubo error
            $error = $API->comm('/log/print', ['?topics' => 'error']);
            if (empty($error)) {
                // Si no hay errores, mostramos éxito
                $_SESSION['message'] = "La IP $old_ip ha sido actualizada a $new_ip en la interfaz $new_interface.";
                $_SESSION['alert_class'] = 'alert-success';
            } else {
                $_SESSION['message'] = "Error al actualizar la IP $old_ip.";
                $_SESSION['alert_class'] = 'alert-error';
            }

        } else {
            $_SESSION['message'] = "No se encontró la IP $old_ip en el dispositivo.";
            $_SESSION['alert_class'] = 'alert-error';
        }

        $API->disconnect();
    } else {
        $_SESSION['message'] = "No se pudo conectar a MikroTik.";
        $_SESSION['alert_class'] = 'alert-error';
    }

    header("Location: index.php");
    exit();
}
?>
