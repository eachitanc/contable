<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
$id_sede = isset($_POST['slcIdSede']) ? $_POST['slcIdSede'] : exit('Acción no permitida');
$id_bodega = $_POST['id_bodega'];
$bodega = mb_strtoupper($_POST['txtNewBodega']);
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `seg_bodega_almacen` SET `id_sede` = ?,`nombre` = ? WHERE `id_bodega` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_sede, PDO::PARAM_INT);
    $sql->bindParam(2, $bodega, PDO::PARAM_STR);
    $sql->bindParam(3, $id_bodega, PDO::PARAM_INT);
    if (!($sql->execute())) {
        echo $sql->errorInfo()[2];
        exit();
    } else {
        if ($sql->rowCount() > 0) {
            echo 'ok';
        } else {
            echo 'No se registró ningún nuevo dato';
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
