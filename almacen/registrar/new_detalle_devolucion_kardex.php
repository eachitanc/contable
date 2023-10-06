<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';

$id_proDev = isset($_POST['id_proDev']) ? $_POST['id_proDev'] : exit('Acción no permitida');
$id_entrada = $_POST['id_entradaDev'];
$id_dev = $_POST['id_dev_det'];
$cantidad = $_POST['numCantDev'];
$observaciones = $_POST['txtaObservacionDev'];
$iduser = $_SESSION['id_user'];
$tipuser = 'user';
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_entrada` FROM `seg_salidas_almacen` WHERE `id_entrada` = '$id_entrada' AND `id_devolucion` = '$id_dev' LIMIT 1";
    $rs = $cmd->query($sql);
    $salida = $rs->fetch();
    if (!empty($salida)) {
        echo 'Numero de lote ya registrado';
        exit();
    }
    $query = "SELECT `existencia`, `fec_reg` FROM `seg_detalle_entrada_almacen` WHERE  `id_prod` = '$id_proDev' ORDER BY `id_entrada` DESC LIMIT 1";
    $rs = $cmd->query($query);
    $existencia_entrada = $rs->fetch();
    $e_entrada = $existencia_entrada['fec_reg'] == '' ?  '0' : $existencia_entrada['fec_reg'];
    $query = "SELECT `existencia`, `fec_reg` FROM `seg_salidas_almacen` WHERE  `id_producto` = '$id_proDev' ORDER BY `id_entrada` DESC LIMIT 1";
    $rs = $cmd->query($query);
    $existencia_salida = $rs->fetch();
    $e_salida = $existencia_salida['fec_reg'] == '' ?  '0' : $existencia_salida['fec_reg'];
    if ($e_salida == '0' && $e_entrada == '0') {
        $existe = $cantidad;
    } else if ($e_entrada > $e_salida) {
        $existe = $existencia_entrada['existencia'] - $cantidad;
    } else {
        $existe = $existencia_salida['existencia'] - $cantidad;
    }
    $sql = "INSERT INTO `seg_salidas_almacen`(`id_entrada`, `id_producto`, `id_devolucion`, `cantidad`, `vigencia`, `observacion`, `existencia`, `id_user_reg`, `fec_reg`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_entrada, PDO::PARAM_INT);
    $sql->bindParam(2, $id_proDev, PDO::PARAM_INT);
    $sql->bindParam(3, $id_dev, PDO::PARAM_INT);
    $sql->bindParam(4, $cantidad, PDO::PARAM_INT);
    $sql->bindParam(5, $vigencia, PDO::PARAM_STR);
    $sql->bindParam(6, $observaciones, PDO::PARAM_STR);
    $sql->bindParam(7, $existe, PDO::PARAM_INT);
    $sql->bindParam(8, $iduser, PDO::PARAM_INT);
    $sql->bindValue(9, $date->format('Y-m-d H:i:s'));
    $sql->execute();
    if ($cmd->lastInsertId() > 0) {
        $estado = 2;
        $sql = "UPDATE `seg_salida_dpdvo` SET `estado` = ?, `id_user_act` = ?, `fec_act` = ? WHERE `id_devolucion` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $estado, PDO::PARAM_INT);
        $sql->bindParam(2, $iduser, PDO::PARAM_INT);
        $sql->bindValue(3, $date->format('Y-m-d H:i:s'));
        $sql->bindParam(4, $id_dev, PDO::PARAM_INT);
        $sql->execute();
        echo 1;
    } else {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
