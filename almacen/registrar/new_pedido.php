<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';

$id_area = isset($_POST['idAreaEntrega']) ? $_POST['idAreaEntrega'] : exit('Acción no permitida');
$id_area_pide = isset($_POST['idAreaPide']) ? $_POST['idAreaPide'] : exit('Acción no permitida');
$vigencia = $_SESSION['vigencia'];
$iduser = $_SESSION['id_user'];
$estado = 1;
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `seg_pedidos_almacen`(`id_bodega`, `bod_entrega`, `estado`, `vigencia`, `id_user_reg`, `fec_reg`) VALUES (?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_area_pide, PDO::PARAM_INT);
    $sql->bindParam(2, $id_area, PDO::PARAM_INT);
    $sql->bindParam(3, $estado, PDO::PARAM_INT);
    $sql->bindParam(4, $vigencia, PDO::PARAM_STR);
    $sql->bindParam(5, $iduser, PDO::PARAM_INT);
    $sql->bindValue(6, $date->format('Y-m-d H:i:s'));
    $sql->execute();
    $consec = $cmd->lastInsertId();
    if ($consec > 0) {
        $slq = "UPDATE `seg_pedidos_almacen` SET `consecutivo` = ? WHERE `id_pedido` = ?";
        $sql = $cmd->prepare($slq);
        $sql->bindParam(1, $consec, PDO::PARAM_INT);
        $sql->bindParam(2, $consec, PDO::PARAM_INT);
        $sql->execute();
        echo 'ok';
    } else {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
