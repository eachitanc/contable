<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
$data = isset($_POST['id_tercero_pd']) ? explode('|', $_POST['id_tercero_pd']) : 0;
$id_ter = isset($data[1]) ? $data[1] :  $data[0];
$id_fianza = isset($data[1]) ? $data[0] : 0;
if ($id_fianza != 0) {
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "SELECT `id_entrada`,`id_tercero_api`
                FROM `seg_entrada_almacen`
                WHERE  `id_entrada` = $id_fianza LIMIT 1";
        $rs = $cmd->query($sql);
        $fianza = $rs->fetch();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}
$acta_remision = $_POST['numActaRemDev'];
$fec_ac_re = $_POST['fecActRem'];
$tipo_salida = $_POST['id_tipo_sal'];
$observaciones = $_POST['txtaObservacionDev'];
$iduser = $_SESSION['id_user'];
$tipuser = 'user';
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT MAX(`consecutivo`) as `consecutivo`  FROM `seg_salida_dpdvo` WHERE `id_tipo_salida` = $tipo_salida";
    $rs = $cmd->query($sql);
    $consecutivo = $rs->fetch();
    $consec = $consecutivo['consecutivo'] == '' ? 1 : $consecutivo['consecutivo'] + 1;
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $estado = 1;
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `seg_salida_dpdvo` (`id_tercero_api`, `id_tipo_salida`,  `acta_remision`, `fec_acta_remision`, `observacion`, `vigencia`, `id_user_reg`, `fec_reg`, `consecutivo`, `estado`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_ter, PDO::PARAM_INT);
    $sql->bindParam(2, $tipo_salida, PDO::PARAM_INT);
    $sql->bindParam(3, $acta_remision, PDO::PARAM_STR);
    $sql->bindParam(4, $fec_ac_re, PDO::PARAM_STR);
    $sql->bindParam(5, $observaciones, PDO::PARAM_STR);
    $sql->bindParam(6, $vigencia, PDO::PARAM_STR);
    $sql->bindParam(7, $iduser, PDO::PARAM_INT);
    $sql->bindValue(8, $date->format('Y-m-d H:i:s'));
    $sql->bindParam(9, $consec, PDO::PARAM_INT);
    $sql->bindParam(10, $estado, PDO::PARAM_INT);
    $sql->execute();
    $id_salida = $cmd->lastInsertId();
    if ($id_salida > 0 && isset($id_fianza)) {
        $sql = "UPDATE `seg_entrada_almacen` SET `id_devolucion` = $id_salida WHERE `id_entrada` = $id_fianza";
        $sql = $cmd->prepare($sql);
        $sql->execute();
        echo 1;
    } else {
        echo '1', $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
