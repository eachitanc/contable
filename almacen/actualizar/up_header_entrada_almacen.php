<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';

$id_entrda = isset($_POST['id_inalmacen']) ? $_POST['id_inalmacen'] : exit('Acción no permitida');
$id_tercero = $_POST['id_tercero_pd'];
$tipo_entrada = $_POST['id_tipo_entrada'];
$identificador = $_POST['id_c'];
$factura = $_POST['numFactura'];
$acta_remision = $_POST['numActaRem'];
$fecha = $_POST['fecActRem'];
$observacion = $_POST['txtObserva'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$vigencia = $_SESSION['vigencia'];
$res = [];
$res['status'] = 0;
$res['msg'] = 'Sin procesar';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT MAX(`consecutivo`) as `consecutivo`  FROM `seg_entrada_almacen` WHERE `id_tipo_entrada` = $tipo_entrada";
    $rs = $cmd->query($sql);
    $consecutivo = $rs->fetch();
    $consec = $consecutivo['consecutivo'] == '' ? 1 : $consecutivo['consecutivo'] + 1;
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if ($id_entrda == 0) {
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "INSERT INTO `seg_entrada_almacen`
                    (`id_tercero_api`, `id_tipo_entrada`, `no_factura`, `acta_remision`, `fec_entrada`, `observacion`, `identificador`, `vigencia`, `id_user_reg`, `fec_reg`, `consecutivo`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_tercero, PDO::PARAM_INT);
        $sql->bindParam(2, $tipo_entrada, PDO::PARAM_INT);
        $sql->bindParam(3, $factura, PDO::PARAM_STR);
        $sql->bindParam(4, $acta_remision, PDO::PARAM_STR);
        $sql->bindParam(5, $fecha, PDO::PARAM_STR);
        $sql->bindParam(6, $observacion, PDO::PARAM_STR);
        $sql->bindParam(7, $identificador, PDO::PARAM_STR);
        $sql->bindParam(8, $vigencia, PDO::PARAM_STR);
        $sql->bindParam(9, $iduser, PDO::PARAM_INT);
        $sql->bindValue(10, $date->format('Y-m-d H:i:s'));
        $sql->bindParam(11, $consec, PDO::PARAM_INT);
        $sql->execute();
        $lastID = $cmd->lastInsertId();
        if ($lastID > 0) {
            $res['status'] = 1;
            $res['msg'] = $lastID;
        } else {
            $res['status'] = 0;
            $res['msg'] = $sql->errorInfo()[2];
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
} else {
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "UPDATE `seg_entrada_activo_fijo` SET  `factura` = ?, `acta_remision` = ?, `fec_acta_remision` = ?, `observacion` = ? WHERE `id_entra_af` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $factura, PDO::PARAM_STR);
        $sql->bindParam(2, $acta_remision, PDO::PARAM_STR);
        $sql->bindParam(3, $fecha, PDO::PARAM_STR);
        $sql->bindParam(4, $observacion, PDO::PARAM_STR);
        $sql->bindParam(5, $id_entrda, PDO::PARAM_INT);
        if ($sql->execute()) {
            if ($sql->rowCount() > 0) {
                $sql = "UPDATE `seg_entrada_activo_fijo` SET  `id_user_act` = ?, `fec_act` = ? WHERE `id_entra_af` = ?";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $iduser, PDO::PARAM_INT);
                $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(3, $id_entrda, PDO::PARAM_INT);
                $sql->execute();
            }
            $res['status'] = 1;
            $res['msg'] = $id_entrda;
        } else {
            $res['status'] = 0;
            $res['msg'] = $sql->errorInfo()[2];
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}

echo json_encode($res);
