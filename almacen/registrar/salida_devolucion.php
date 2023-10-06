<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';

$cantidades = isset($_REQUEST['cantDev']) ? $_REQUEST['cantDev'] : exit('Acción no permitida');
$id_ter = $_POST['idTerceroDev'];
$acta_remision = $_POST['numActaRemDev'];
$fec_ac_re = $_POST['fecActRem'];
$tipo_salida = 1;
$observaciones = $_POST['txtaObservacionDev'];
$iduser = $_SESSION['id_user'];
$tipuser = 'user';
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$vigencia = $_SESSION['vigencia'];
$c = 0;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `seg_salidas_almacen` (`id_entrada`, `id_producto`, `id_tipo_salida`, `id_tercero_api`, `acta_remision`, `cantidad`, `fecha`, `vigencia`, `observaciones`, `id_user_reg`, `fec_reg`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_entrada, PDO::PARAM_INT);
    $sql->bindParam(2, $id_prod, PDO::PARAM_INT);
    $sql->bindParam(3, $tipo_salida, PDO::PARAM_INT);
    $sql->bindParam(4, $id_ter, PDO::PARAM_INT);
    $sql->bindParam(5, $acta_remision, PDO::PARAM_STR);
    $sql->bindParam(6, $cant, PDO::PARAM_INT);
    $sql->bindParam(7, $fec_ac_re, PDO::PARAM_STR);
    $sql->bindParam(8, $vigencia, PDO::PARAM_STR);
    $sql->bindParam(9, $observaciones, PDO::PARAM_STR);
    $sql->bindParam(10, $iduser, PDO::PARAM_INT);
    $sql->bindValue(11, $date->format('Y-m-d H:i:s'));
    foreach ($cantidades as $key => $value) {
        $id_entrada = $key;
        $id_prod = $_POST['idProd_' . $key];
        $cant = $value;
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            $c++;
        } else {
            echo $sql->errorInfo()[2];
        }
    }
    if ($c > 0) {
        echo 1;
    } else {
        echo 'No se ingresó ningún elemento a la devolución';
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
