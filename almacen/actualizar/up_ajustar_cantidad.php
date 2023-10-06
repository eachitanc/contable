<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';

$id_entrada = isset($_POST['id_Enlote_ajuste']) ? $_POST['id_Enlote_ajuste'] : exit('Acción no permitida');
$id_prod = $_POST['id_prod_ajuste'];
$lote = $_POST['desc_lote'];
$existencia_lote = $_POST['existencia_lote'];
$existe_lote_ant = $_POST['existe_lote_ant'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$actualiza = $existencia_lote - $existe_lote_ant;

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `seg_detalle_entrada_almacen` SET `cant_ingresa` = `cant_ingresa`+ ? WHERE `lote` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $actualiza, PDO::PARAM_INT);
    $sql->bindParam(2, $lote, PDO::PARAM_STR);
    if (!($sql->execute())) {
        echo $sql->errorInfo()[2];
        exit();
    } else {
        if ($sql->rowCount() > 0) {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "UPDATE `seg_detalle_entrada_almacen` SET  `id_user_act` = ? ,`fec_act` = ?  WHERE `lote` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $iduser, PDO::PARAM_INT);
            $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(3, $lote, PDO::PARAM_STR);
            $sql->execute();
            if ($sql->rowCount() > 0) {
                echo '1';
            } else {
                echo $sql->errorInfo()[2];
            }
        } else {
            echo 'No se registró ningún nuevo dato';
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
