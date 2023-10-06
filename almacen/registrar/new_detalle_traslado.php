<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';

$id_proTrasl = isset($_POST['id_proTras']) ? $_POST['id_proTras'] : exit('Acción no permitida');
$id_entrada = $_POST['id_entradaTras'];
$id_tra_alm = $_POST['id_up_tra_alm'];
$cantidad = $_POST['numCantTras'];
$observaciones = $_POST['txtaObservacionTras'];
$iduser = $_SESSION['id_user'];
$tipuser = 'user';
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_entrada` FROM `seg_detalles_traslado` WHERE `id_entrada` = '$id_entrada' AND `id_traslado` = '$id_tra_alm' LIMIT 1";
    $rs = $cmd->query($sql);
    $traslado = $rs->fetch();
    if (!empty($traslado)) {
        echo 'Numero de lote ya registrado';
        exit();
    }
    $sql = "INSERT INTO `seg_detalles_traslado` (`id_entrada`, `id_producto`, `id_traslado`, `cantidad`, `vigencia`, `observacion`, `id_user_reg`, `fec_reg`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_entrada, PDO::PARAM_INT);
    $sql->bindParam(2, $id_proTrasl, PDO::PARAM_INT);
    $sql->bindParam(3, $id_tra_alm, PDO::PARAM_INT);
    $sql->bindParam(4, $cantidad, PDO::PARAM_INT);
    $sql->bindParam(5, $vigencia, PDO::PARAM_STR);
    $sql->bindParam(6, $observaciones, PDO::PARAM_STR);
    $sql->bindParam(7, $iduser, PDO::PARAM_INT);
    $sql->bindValue(8, $date->format('Y-m-d H:i:s'));
    $sql->execute();
    if ($cmd->lastInsertId() > 0) {
        $estado = 2;
        $sql = "UPDATE `seg_traslados_almacen` SET `estado` = ?, `id_user_act` = ?, `fec_act` = ? WHERE `id_trasl_alm` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $estado, PDO::PARAM_INT);
        $sql->bindParam(2, $iduser, PDO::PARAM_INT);
        $sql->bindValue(3, $date->format('Y-m-d H:i:s'));
        $sql->bindParam(4, $id_tra_alm, PDO::PARAM_INT);
        $sql->execute();
        echo 1;
    } else {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
