<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_pto = $_POST['id_pto'];
$fecha = $_POST['fecha'];
$id_empresa = '2';
$id_sede = '1';
$numCdp = $_POST['numCdp'];
$Objeto = $_POST['Objeto'];
$datFecVigencia = $_POST['datFecVigencia'];
$iduser = $_SESSION['id_user'];
$tipo_mov = 'CDP';
$date = new DateTime('now', new DateTimeZone('America/Bogota'));

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `seg_pto_documento` (`id_pto_presupuestos`, `id_sede`, `id_manu`, `fecha`, `objeto`,id_user_reg, fec_reg,tipo_doc) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_pto, PDO::PARAM_INT);
    $sql->bindParam(2, $id_sede, PDO::PARAM_INT);
    $sql->bindParam(3, $numCdp, PDO::PARAM_STR);
    $sql->bindParam(4, $fecha, PDO::PARAM_STR);
    $sql->bindParam(5, $Objeto, PDO::PARAM_STR);
    $sql->bindParam(6, $iduser, PDO::PARAM_INT);
    $sql->bindValue(7, $date->format('Y-m-d H:i:s'));
    $sql->bindParam(8, $tipo_mov, PDO::PARAM_STR);
    $sql->execute();
    if ($cmd->lastInsertId() > 0) {
        // Obtengo el ultimo id registrado  
        $id_pto_documento = $cmd->lastInsertId();
        echo 'ok' . '-' . $id_pto_documento;
    } else {
        print_r($sql->errorInfo()[2]);
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
