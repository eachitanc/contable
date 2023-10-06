<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';

$id_tipo_traslado = isset($_POST['id_tipo_traslado']) ? $_POST['id_tipo_traslado'] : exit('Acción no permitida');
$slcSedeSalida = $_POST['slcSedeSalida'];
$slcBodegaSalida = $_POST['slcBodegaSalida'];
$slcSedeEntrada = $_POST['slcSedeEntrada'];
$slcBodegaEntrada = $_POST['slcBodegaEntrada'];
$numActaRemTrasl = $_POST['numActaRemTrasl'];
$fecActRemTrasl = $_POST['fecActRemTrasl'];
$txtaObservacionTrasl = $_POST['txtaObservacionTrasl'];
$vigencia = $_SESSION['vigencia'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `seg_traslados_almacen` (`id_tipo_trasl`, `id_sede_sale`, `id_bodega_sale`, `id_sede_entra`, `id_bodega_entra`, `acta_remision`, `fec_traslado`, `observacion`, `vigencia`, `id_user_reg`, `fec_reg`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_tipo_traslado, PDO::PARAM_INT);
    $sql->bindParam(2, $slcSedeSalida, PDO::PARAM_INT);
    $sql->bindParam(3, $slcBodegaSalida, PDO::PARAM_INT);
    $sql->bindParam(4, $slcSedeEntrada, PDO::PARAM_INT);
    $sql->bindParam(5, $slcBodegaEntrada, PDO::PARAM_INT);
    $sql->bindParam(6, $numActaRemTrasl, PDO::PARAM_STR);
    $sql->bindParam(7, $fecActRemTrasl, PDO::PARAM_STR);
    $sql->bindParam(8, $txtaObservacionTrasl, PDO::PARAM_STR);
    $sql->bindParam(9, $vigencia, PDO::PARAM_STR);
    $sql->bindParam(10, $iduser, PDO::PARAM_INT);
    $sql->bindValue(11, $date->format('Y-m-d H:i:s'));
    $sql->execute();
    if ($cmd->lastInsertId() > 0) {
        echo 1;
    } else {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
