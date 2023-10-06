<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';

$id_dev = isset($_POST['id_devolucion']) ? $_POST['id_devolucion'] : exit('Acción no permitida');
$id_ter = $_POST['id_tercero_pd'];
$acta_remision = $_POST['numActaRemDev'];
$fec_ac_re = $_POST['fecActRem'];
$observaciones = $_POST['txtaObservacionDev'];
$iduser = $_SESSION['id_user'];
$tipuser = 'user';
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `seg_salida_dpdvo` SET `id_tercero_api`= ?, `acta_remision`= ?, `fec_acta_remision` = ?, `observacion` = ? WHERE `id_devolucion` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_ter, PDO::PARAM_INT);
    $sql->bindParam(2, $acta_remision, PDO::PARAM_STR);
    $sql->bindParam(3, $fec_ac_re, PDO::PARAM_STR);
    $sql->bindParam(4, $observaciones, PDO::PARAM_STR);
    $sql->bindParam(5, $id_dev, PDO::PARAM_INT);
    if (!($sql->execute())) {
        echo $sql->errorInfo()[2];
        exit();
    } else {
        if ($sql->rowCount() > 0) {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "UPDATE `seg_salida_dpdvo` SET  `id_user_act` = ? ,`fec_act` = ?  WHERE `id_devolucion` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $iduser, PDO::PARAM_INT);
            $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(3, $id_dev, PDO::PARAM_INT);
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
