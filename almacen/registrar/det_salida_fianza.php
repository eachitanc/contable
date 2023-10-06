<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';

$cantidades = isset($_POST['cantxprod']) ? $_POST['cantxprod'] : exit('Acción no permitida');
$iduser = $_SESSION['id_user'];
$vigencia = $_SESSION['vigencia'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$id_devolucion = $_POST['id_dev_det'];
$entregado = 0;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `seg_salidas_almacen` (`id_entrada`,`id_producto`,`id_devolucion`,`cantidad`,`vigencia`,`id_user_reg`,`fec_reg`) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_entrada, PDO::PARAM_INT);
    $sql->bindParam(2, $id_producto, PDO::PARAM_INT);
    $sql->bindParam(3, $id_devolucion, PDO::PARAM_INT);
    $sql->bindParam(4, $cantidad, PDO::PARAM_INT);
    $sql->bindParam(5, $vigencia, PDO::PARAM_INT);
    $sql->bindParam(6, $iduser, PDO::PARAM_INT);
    $sql->bindValue(7, $date->format('Y-m-d H:i:s'));
    foreach ($cantidades as $key => $cantidad) {
        $data = explode('|', $key);
        $id_entrada = $data[0];
        $id_producto = $data[1];
        if ($cantidad > 0) {
            $sql->execute();
            if (!($cmd->lastInsertId() > 0)) {
                echo $sql->errorInfo()[2];
            } else {
                $entregado++;
            }
        }
    }
    if ($entregado > 0) {
        $sql = "UPDATE `seg_salida_dpdvo` SET `estado` = 3 WHERE `id_devolucion` = $id_devolucion";
        $sql = $cmd->prepare($sql);
        $sql->execute();
        echo 'ok';
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
