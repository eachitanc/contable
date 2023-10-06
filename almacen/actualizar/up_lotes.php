<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
$queda = isset($_POST['queda']) ? $_POST['queda'] : exit('Acceso denegado');
$ajuste = isset($_POST['ajuste']) ? $_POST['ajuste'] : exit('Acceso denegado');

$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$ajustados = 0;

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `seg_detalle_entrada_almacen` SET  `cant_ingresa` = `cant_ingresa` + ?, `id_user_act` = ?, `fec_act` = ? WHERE `id_entrada` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $ingresa, PDO::PARAM_INT);
    $sql->bindParam(2, $iduser, PDO::PARAM_INT);
    $sql->bindValue(3, $date->format('Y-m-d H:i:s'));
    $sql->bindParam(4, $id_entrada, PDO::PARAM_INT);
    foreach ($queda as $key => $value) {
        if ($value != $ajuste[$key]) {
            $ingresa = $ajuste[$key] - $value;
            $id_entrada = $key;
            $sql->execute();
            if (!($sql->rowCount() > 0)) {
                echo $sql->errorInfo()[2];
            } else {
                $ajustados++;
            }
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

if ($ajustados > 0) {
    echo 'ok';
} else {
    echo 'No se realizó ningún ajuste';
}
