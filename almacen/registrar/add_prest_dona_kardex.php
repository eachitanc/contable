<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
//API URL
$id_bnsvc = isset($_POST['id_bnsvc']) ? $_POST['id_bnsvc'] : exit('Acción no permitida');
$idProd = $id_bnsvc;
$tip_entrada = $_POST['tipoEntrada'];
$acta_remision = $_POST['numActaRem'];
$sede = $_POST['slcSede'];
$bodega = $_POST['slcBodega'];
$cntdd = $_POST['numCantRecb'];
$id_tercero = $_POST['id_tercero_pd'];
$id_pre_don = $_POST['id_pre_don'];
$valunit = $_POST['numValUnita'];
$lote = $_POST['lote'];
$vence = $_POST['fec_vence'] == '' ? null : $_POST['fec_vence'];
$invima = $_POST['invima'];
$marca = $_POST['txtMarcaI'];
$id_marca = $_POST['idMarcaI'];
$iva = $_POST['numIvaProd'];
$iduser = $_SESSION['id_user'];
$tipuser = 'user';
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
if ($marca == '' || $id_marca == 0) {
    $id_marca = NULL;
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `val_prom` FROM `seg_detalle_entrada_almacen` WHERE  `id_prod` = '$idProd' ORDER BY fec_reg DESC LIMIT 1";
    $rs = $cmd->query($sql);
    $valpromedio = $rs->fetch();
    if ($valunit == '' || $valunit == 0) {
        if ($valpromedio['val_prom'] == '') {
            echo 'Debe ingresar el Valor Unitario de este elemento';
            exit();
        } else {
            $valunit = $valprom = $valpromedio['val_prom'];
        }
    } else {
        if ($valpromedio['val_prom'] == '') {
            $valprom = $valunit;
        } else {
            $valprom = ($valpromedio['val_prom'] + $valunit) / 2;
        }
    }
    $query = "SELECT `existencia`, `fec_reg` FROM `seg_detalle_entrada_almacen` WHERE  `id_prod` = '$idProd' ORDER BY `id_entrada` DESC LIMIT 1";
    $rs = $cmd->query($query);
    $existencia_entrada = $rs->fetch();
    $e_entrada = $existencia_entrada['fec_reg'] == '' ?  '0' : $existencia_entrada['fec_reg'];
    $query = "SELECT `existencia`, `fec_reg` FROM `seg_salidas_almacen` WHERE  `id_producto` = '$idProd' ORDER BY `id_entrada` DESC LIMIT 1";
    $rs = $cmd->query($query);
    $existencia_salida = $rs->fetch();
    $e_salida = $existencia_salida['fec_reg'] == '' ?  '0' : $existencia_salida['fec_reg'];
    if ($e_salida == '0' && $e_entrada == '0') {
        $existe = $cntdd;
    } else if ($e_entrada > $e_salida) {
        $existe = $existencia_entrada['existencia'] + $cntdd;
    } else {
        $existe = $existencia_salida['existencia'] + $cntdd;
    }
    $sql = "INSERT INTO `seg_detalle_entrada_almacen`(`id_prod`,`id_sede`,`id_bodega`,`id_tercero_api`,`id_tipo_entrada` ,`cant_ingresa`,`valu_ingresa`,`val_prom`,`lote`,`fecha_vence`, `id_marca`, `invima`,`existencia`,`id_user_reg`,`fec_reg`, `id_entra`,`iva`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $idProd, PDO::PARAM_INT);
    $sql->bindParam(2, $sede, PDO::PARAM_INT);
    $sql->bindParam(3, $bodega, PDO::PARAM_INT);
    $sql->bindParam(4, $id_tercero, PDO::PARAM_INT);
    $sql->bindParam(5, $tip_entrada, PDO::PARAM_INT);
    $sql->bindParam(6, $cntdd, PDO::PARAM_INT);
    $sql->bindParam(7, $valunit, PDO::PARAM_STR);
    $sql->bindParam(8, $valprom, PDO::PARAM_STR);
    $sql->bindParam(9, $lote, PDO::PARAM_STR);
    $sql->bindParam(10, $vence, PDO::PARAM_STR);
    $sql->bindParam(11, $id_marca, PDO::PARAM_STR);
    $sql->bindParam(12, $invima, PDO::PARAM_STR);
    $sql->bindParam(13, $existe, PDO::PARAM_INT);
    $sql->bindParam(14, $iduser, PDO::PARAM_INT);
    $sql->bindValue(15, $date->format('Y-m-d H:i:s'));
    $sql->bindParam(16, $id_pre_don, PDO::PARAM_INT);
    $sql->bindParam(17, $iva, PDO::PARAM_INT);
    $sql->execute();
    if ($cmd->lastInsertId() > 0) {
        $sql = "UPDATE `seg_entrada_almacen` SET `estado`= 2 WHERE `id_entrada` = $id_pre_don";
        $sql = $cmd->prepare($sql);
        $sql->execute();
        echo 1;
    } else {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
