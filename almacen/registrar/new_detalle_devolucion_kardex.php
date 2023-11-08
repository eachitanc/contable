<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';

$id_prod = isset($_POST['id_prod']) ? $_POST['id_prod'] : exit('Acción no permitida');
$entradas = $_POST['canTrasforma'];
$id_dev = $_POST['id_dev_det'];
$vigencia = $_SESSION['vigencia'];
$id_user = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$ctrl = 0;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `seg_salidas_almacen`(`id_entrada`, `id_producto`, `id_devolucion`, `cantidad`, `vigencia`, `id_user_reg`, `fec_reg`)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_entrada, PDO::PARAM_INT);
    $sql->bindParam(2, $id_prod, PDO::PARAM_INT);
    $sql->bindParam(3, $id_dev, PDO::PARAM_INT);
    $sql->bindParam(4, $cantidad, PDO::PARAM_INT);
    $sql->bindParam(5, $vigencia, PDO::PARAM_STR);
    $sql->bindParam(6, $iduser, PDO::PARAM_INT);
    $sql->bindValue(7, $date->format('Y-m-d H:i:s'));
    foreach ($entradas as $key => $value) {
        if ($value > 0) {
            $id_entrada = $key;
            $cantidad = $value;
            $sql->execute();
            if ($cmd->lastInsertId() > 0) {
                $ctrl++;
            } else {
                echo $sql->errorInfo()[2];
            }
        }
    }
    if ($ctrl > 0) {
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
