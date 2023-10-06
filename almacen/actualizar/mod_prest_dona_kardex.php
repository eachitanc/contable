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
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
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
    if ($idProd == $idant) {
        if ($cntdd != $cnt_ant) {
            $existe = $exis_ant + $cntdd - $cnt_ant;
        } else {
            $existe = $exis_ant;
        }
    } else {
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
