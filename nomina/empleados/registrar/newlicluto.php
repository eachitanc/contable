<?php

session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$idemple = isset($_POST['idEmpLicLuto']) ? $_POST['idEmpLicLuto'] : exit('Acción no permitida');
$inicio = date('Y-m-d', strtotime($_POST['datFecInicioLicLuto']));
$fin = date('Y-m-d', strtotime($_POST['datFecFinLicLuto']));
$dinac = $_POST['numCantDiasLicLuto'];
$dhab = $_POST['numCantDiasHabLicLuto'];
$id_user = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `seg_licencia_luto` (`id_empleado`, `fec_inicio`, `fec_fin`, `dias_inactivo`, `dias_habiles`, `id_user_reg`, `fec_reg`) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $idemple, PDO::PARAM_INT);
    $sql->bindParam(2, $inicio, PDO::PARAM_STR);
    $sql->bindParam(3, $fin, PDO::PARAM_STR);
    $sql->bindParam(4, $dinac, PDO::PARAM_STR);
    $sql->bindParam(5, $dhab, PDO::PARAM_STR);
    $sql->bindParam(6, $id_user, PDO::PARAM_INT);
    $sql->bindValue(7, $date->format('Y-m-d H:i:s'));
    $sql->execute();
    if ($cmd->lastInsertId() > 0) {
        echo 'ok';
    } else {
        echo $sql->errorInfo()[2] . $sql->queryString;
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
