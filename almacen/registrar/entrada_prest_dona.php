<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
$numActaRem = isset($_POST['numActaRem']) ? $_POST['numActaRem'] : exit('Acción no permitida');
$tipoEntrada = $_POST['tipoEntrada'];
if ($tipoEntrada == 2 || $tipoEntrada == 7) {
    $data = explode('|', $_POST['id_tercero_pd']);
    $idta = $data[1];
    $id_salida = $data[0];
} else {
    $idta = $_POST['id_tercero_pd'];
    $id_salida = NULL;
}
$fecActRem = $_POST['fecActRem'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$vigencia = $_SESSION['vigencia'];
$observa = $_POST['txtObservaEntrada'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT MAX(`consecutivo`) as `consecutivo`  FROM `seg_entrada_almacen` WHERE `id_tipo_entrada` = $tipoEntrada";
    $rs = $cmd->query($sql);
    $consecutivo = $rs->fetch();
    $consec = $consecutivo['consecutivo'] == '' ? 1 : $consecutivo['consecutivo'] + 1;
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `seg_entrada_almacen`(`id_tipo_entrada`,`id_tercero_api`,`acta_remision`,`fec_entrada`,`vigencia`,`id_user_reg`,`fec_reg`, `observacion`, `consecutivo`, `id_devolucion`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $tipoEntrada, PDO::PARAM_INT);
    $sql->bindParam(2, $idta, PDO::PARAM_INT);
    $sql->bindParam(3, $numActaRem, PDO::PARAM_STR);
    $sql->bindParam(4, $fecActRem, PDO::PARAM_STR);
    $sql->bindParam(5, $vigencia, PDO::PARAM_STR);
    $sql->bindParam(6, $iduser, PDO::PARAM_INT);
    $sql->bindValue(7, $date->format('Y-m-d H:i:s'));
    $sql->bindParam(8, $observa, PDO::PARAM_STR);
    $sql->bindParam(9, $consec, PDO::PARAM_INT);
    $sql->bindParam(10, $id_salida, PDO::PARAM_INT);
    $sql->execute();
    if ($cmd->lastInsertId() > 0) {
        echo 1;
    } else {
        print_r($sql->errorInfo()[2]);
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
