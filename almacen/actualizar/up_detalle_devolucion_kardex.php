<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';

$id_proDev = isset($_POST['id_proDev']) ? $_POST['id_proDev'] : exit('Acción no permitida');
$id_proDev_ant = $_POST['id_proDev_ant'];
$id_salida = $_POST['id_salida'];
$id_dev = $_POST['id_dev'];
$id_entrada = $_POST['id_entradaDev'];
$id_entrada_ant = $_POST['id_entrada_ant'];
$cantidad = $_POST['numCantDev'];
$cantidad_ant = $_POST['numCantDev_ant'];
$exis_ant = $_POST['num_Existencia_Dev'];
$observaciones = $_POST['txtaObservacionDev'];
$iduser = $_SESSION['id_user'];
$tipuser = 'user';
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
if ($exis_ant == '0' && $cantidad > $cantidad_ant) {
    echo 'Cantidad ingresada supera el mámixo de existencias (' . $cantidad_ant . ')';
    exit();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_entrada` FROM `seg_salidas_almacen` WHERE `id_entrada` = '$id_entrada' AND `id_devolucion` = '$id_dev' LIMIT 1";
    $rs = $cmd->query($sql);
    $salida = $rs->fetch();
    if (!empty($salida)) {
        if ($id_entrada_ant != $salida['id_entrada']) {
            echo 'Numero de lote ya registrado';
            exit();
        }
    }
    if ($id_proDev == $id_proDev_ant) {
        if ($cantidad != $cantidad_ant) {
            $existe = $exis_ant + $cantidad_ant - $cantidad;
        } else {
            $existe = $exis_ant;
        }
    } else {
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
    }
    $sql = "UPDATE `seg_salidas_almacen` SET `id_entrada` = ?, `id_producto` = ?, `cantidad` = ?, `observacion` = ?, `existencia` = ? WHERE `id_salida` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_entrada, PDO::PARAM_INT);
    $sql->bindParam(2, $id_proDev, PDO::PARAM_INT);
    $sql->bindParam(3, $cantidad, PDO::PARAM_INT);
    $sql->bindParam(4, $observaciones, PDO::PARAM_STR);
    $sql->bindParam(5, $existe, PDO::PARAM_INT);
    $sql->bindParam(6, $id_salida, PDO::PARAM_INT);
    if (!($sql->execute())) {
        print_r($sql->errorInfo()[2]);
        exit();
    } else {
        if ($sql->rowCount() > 0) {
            $sql = "UPDATE `seg_salidas_almacen` SET `id_user_act` = ?, `fec_act` = ? WHERE `id_salida` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $iduser, PDO::PARAM_INT);
            $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(3, $id_salida, PDO::PARAM_INT);
            $sql->execute();
            if ($sql->rowCount() > 0) {
                echo '1';
            } else {
                print_r($sql->errorInfo()[2]);
            }
        } else {
            echo 'No se registró ningún nuevo dato';
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
