<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';

$id_sede = isset($_POST['slcIdSede']) ? $_POST['slcIdSede'] : exit('Acción no permitida');
$bodega = mb_strtoupper($_POST['txtNewBodega']);
$responsable = $_POST['id_user_resp'];
$vigencia = $_SESSION['vigencia'];
$iduser = $_SESSION['id_user'];
$estado = 1;
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `seg_bodega_almacen`(`id_sede`,`nombre`) 
            VALUES (?,?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_sede, PDO::PARAM_INT);
    $sql->bindParam(2, $bodega, PDO::PARAM_STR);
    $sql->execute();
    $id_bodega = $cmd->lastInsertId();
    if ($id_bodega > 0) {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "INSERT INTO `seg_responsable_bodega` (`id_bodega`,`id_usuario`,`id_user_reg`,`fec_reg`)
                VALUES (?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_bodega, PDO::PARAM_INT);
        $sql->bindParam(2, $responsable, PDO::PARAM_INT);
        $sql->bindParam(3, $iduser, PDO::PARAM_INT);
        $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
        $sql->execute();
        if ($cmd->lastInsertId()) {
            echo 'ok';
        } else {
            echo $sql->errorInfo()[2];
        }
    } else {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
