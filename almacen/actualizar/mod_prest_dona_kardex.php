<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
//API URL
$id_entradaK = isset($_POST['id_entradaK']) ? $_POST['id_entradaK'] : exit('Acción no permitida');
$idProd = $_POST['id_bnsvc'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
if ($id_entradaK == 0) {
    $datas = explode('|', $_POST['id_prestdonac']);
    $id_ent = $datas[0];
    $tip_entrada = $datas[1];
    $sede = $bodega = 1;
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "INSERT INTO `seg_detalle_entrada_almacen`(`id_prod`,`id_sede`,`id_bodega`,`id_tipo_entrada`,`id_user_reg`,`fec_reg`, `id_entra`) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $idProd, PDO::PARAM_INT);
        $sql->bindParam(2, $sede, PDO::PARAM_INT);
        $sql->bindParam(3, $bodega, PDO::PARAM_INT);
        $sql->bindParam(4, $tip_entrada, PDO::PARAM_INT);
        $sql->bindParam(5, $iduser, PDO::PARAM_INT);
        $sql->bindValue(6, $date->format('Y-m-d H:i:s'));
        $sql->bindParam(7, $id_ent, PDO::PARAM_INT);
        $sql->execute();
        $id_entradaK = $cmd->lastInsertId();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}
$existe = NULL;
$idant = $_POST['id_bnsvc_ant'];
$cntdd = $_POST['numCantRecb'];
$cnt_ant = $_POST['numCantRecb_ant'];
$valunit = $_POST['numValUnita'];
$iva = $_POST['numIvaProd'];
$lote = $_POST['lote'];
$vence = $_POST['fec_vence'] == '' ? null : $_POST['fec_vence'];
$invima = $_POST['invima'];
$marca = $_POST['txtMarcaI'];
$id_marca = $_POST['idMarcaI'];
$exis_ant = $_POST['numCantExistencia'];
if ($marca == '' || $id_marca == 0) {
    $id_marca = NULL;
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `val_prom` FROM `seg_detalle_entrada_almacen` WHERE  `id_prod` = '$idProd' ORDER BY `fec_reg` DESC LIMIT 2";
    $rs = $cmd->query($sql);
    $valpromedio = $rs->fetchAll();
    $vprom = isset($valpromedio[1]['val_prom']) ? $valpromedio[1]['val_prom'] : '';
    if ($valunit == '' || $valunit == 0) {
        if ($vprom == '') {
            echo 'Debe ingresar el Valor Unitario de este elemento';
            exit();
        } else {
            $valunit = $valprom = $vprom;
        }
    } else {
        if ($vprom == '') {
            $valprom = $valunit;
        } else {
            $valprom = ($vprom + $valunit) / 2;
        }
    }
    $sql = "UPDATE `seg_detalle_entrada_almacen` SET `id_prod` = ?, `cant_ingresa` = ?,`valu_ingresa` = ?,`val_prom` = ?,`lote` = ?,`fecha_vence` = ?, `invima` = ?, `id_marca` = ?,`existencia` = ?, `iva` = ?  WHERE `id_entrada` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $idProd, PDO::PARAM_INT);
    $sql->bindParam(2, $cntdd, PDO::PARAM_INT);
    $sql->bindParam(3, $valunit, PDO::PARAM_STR);
    $sql->bindParam(4, $valprom, PDO::PARAM_STR);
    $sql->bindParam(5, $lote, PDO::PARAM_STR);
    $sql->bindParam(6, $vence, PDO::PARAM_STR);
    $sql->bindParam(7, $invima, PDO::PARAM_STR);
    $sql->bindParam(8, $id_marca, PDO::PARAM_STR);
    $sql->bindParam(9, $existe, PDO::PARAM_INT);
    $sql->bindParam(10, $iva, PDO::PARAM_INT);
    $sql->bindParam(11, $id_entradaK, PDO::PARAM_INT);
    if (!($sql->execute())) {
        print_r($sql->errorInfo()[2]);
        exit();
    } else {
        if ($sql->rowCount() > 0) {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "UPDATE `seg_detalle_entrada_almacen` SET  `id_user_act` = ? ,`fec_act` = ?  WHERE `id_entrada` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $iduser, PDO::PARAM_INT);
            $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(3, $id_entradaK, PDO::PARAM_INT);
            $sql->execute();
            if ($sql->rowCount() > 0) {
                echo '1';
            } else {
                print_r($sql->errorInfo()[2]);
            }
        } else {
            echo 'No se registró ningún nuevo dato';
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
