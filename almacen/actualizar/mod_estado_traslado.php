<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
//API URL
$id_tras = isset($_POST['id_tras']) ? $_POST['id_tras'] : exit('Acción no permitida');
$estado = 3;
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `seg_traslados_almacen` SET `estado` = ?, `id_user_act` = ?, `fec_act` = ? WHERE `id_trasl_alm` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $estado, PDO::PARAM_INT);
    $sql->bindParam(2, $iduser, PDO::PARAM_INT);
    $sql->bindValue(3, $date->format('Y-m-d H:i:s'));
    $sql->bindParam(4, $id_tras, PDO::PARAM_INT);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        echo '1';
    } else {
        print_r($sql->errorInfo()[2]);
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
