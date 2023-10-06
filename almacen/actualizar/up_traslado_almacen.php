<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';

$id_traslado = isset($_POST['id_Uptraslado']) ? $_POST['id_Uptraslado'] : exit('Acción no permitida');
$slcSedeSalida = $_POST['slcSedeSalida'];
$slcBodegaSalida = $_POST['slcBodegaSalida'];
$slcSedeEntrada = $_POST['slcSedeEntrada'];
$slcBodegaEntrada = $_POST['slcBodegaEntrada'];
$numActaRemTrasl = $_POST['numActaRemTrasl'];
$fecActRemTrasl = $_POST['fecActRemTrasl'];
$txtaObservacionTrasl = $_POST['txtaObservacionTrasl'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `seg_traslados_almacen` SET `id_sede_sale`= ?, `id_bodega_sale`= ?, `id_sede_entra`= ?, `id_bodega_entra`= ?, `acta_remision`= ?, `fec_traslado`= ?, `observacion`= ? WHERE `id_trasl_alm` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $slcSedeSalida, PDO::PARAM_INT);
    $sql->bindParam(2, $slcBodegaSalida, PDO::PARAM_INT);
    $sql->bindParam(3, $slcSedeEntrada, PDO::PARAM_INT);
    $sql->bindParam(4, $slcBodegaEntrada, PDO::PARAM_INT);
    $sql->bindParam(5, $numActaRemTrasl, PDO::PARAM_STR);
    $sql->bindParam(6, $fecActRemTrasl, PDO::PARAM_STR);
    $sql->bindParam(7, $txtaObservacionTrasl, PDO::PARAM_STR);
    $sql->bindParam(8, $id_traslado, PDO::PARAM_INT);
    if (!($sql->execute())) {
        echo $sql->errorInfo()[2];
        exit();
    } else {
        if ($sql->rowCount() > 0) {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "UPDATE `seg_traslados_almacen` SET  `id_user_act` = ? ,`fec_act` = ?  WHERE `id_trasl_alm` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $iduser, PDO::PARAM_INT);
            $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(3, $id_traslado, PDO::PARAM_INT);
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
