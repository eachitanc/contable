<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
$id_prod1 = isset($_POST['idArtc3']) ? $_POST['idArtc3'] : exit('Acceso denegado');
$id_prod2 = isset($_POST['idArtc4']) ? $_POST['idArtc4'] : exit('Acceso denegado');
$tipo = $_POST['radTransfor'];
$cantidad = $_POST['numArt4'];
$entradas = $_POST['canTrasforma'];
$id_entra = $_POST['id_entra'];
$bodega_sale = 1;
$ids = [];
foreach ($entradas as $key => $value) {
    $ids[] = $key;
}
$ids = implode(', ', $ids);
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
try {
    $sql = "SELECT
                `id_entrada`, `id_entra`, `id_prod`, `id_sede`, `id_bodega`, `id_tercero_api`, `id_tipo_entrada`, `cant_ingresa`, `iva`
                , `valu_ingresa`, `val_prom`, `id_lote`, `lote`, `id_marca`, `marca`, `invima`, `fecha_vence`, `id_user_reg`, `fec_reg`
            FROM
                `seg_detalle_entrada_almacen`
            WHERE `id_entrada` IN ($ids)";
    $res = $cmd->query($sql);
    $detalles = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `id_entrada`, `id_tercero_api`, `id_devolucion`, `fec_entrada`, `acta_remision`, `consecutivo`
            FROM
                `seg_entrada_almacen`
            WHERE `id_entrada` =  $id_entra";
    $res = $cmd->query($sql);
    $entrada = $res->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$vigencia = $_SESSION['vigencia'];
$id_user = $_SESSION['id_user'];
if ($entrada['id_devolucion'] == '') {
    $tipo_salida = 10;
    $id_ter = $entrada['id_tercero_api'];
    $fecmv = $entrada['fec_entrada'];
    $observ = "SALIDA DEL RESULTADO DE LA ENTRADA TRANSFORMACIÓN No. " . $entrada['consecutivo'];
    $acta_remision = $entrada['acta_remision'];
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
        $estado = 3;
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "INSERT INTO `seg_salida_dpdvo` (`id_tercero_api`, `id_tipo_salida`,  `acta_remision`, `fec_acta_remision`, `observacion`, `vigencia`, `id_user_reg`, `fec_reg`, `consecutivo`, `estado`, `id_bodega`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_ter, PDO::PARAM_INT);
        $sql->bindParam(2, $tipo_salida, PDO::PARAM_INT);
        $sql->bindParam(3, $acta_remision, PDO::PARAM_STR);
        $sql->bindParam(4, $fecmv, PDO::PARAM_STR);
        $sql->bindParam(5, $observ, PDO::PARAM_STR);
        $sql->bindParam(6, $vigencia, PDO::PARAM_STR);
        $sql->bindParam(7, $id_user, PDO::PARAM_INT);
        $sql->bindValue(8, $date->format('Y-m-d H:i:s'));
        $sql->bindParam(9, $consec, PDO::PARAM_INT);
        $sql->bindParam(10, $estado, PDO::PARAM_INT);
        $sql->bindParam(11, $bodega_sale, PDO::PARAM_INT);
        $sql->execute();
        $id_salida = $cmd->lastInsertId();
        if (!($id_salida > 0)) {
            exit($sql->errorInfo()[2]);
        } else {
            $estado = 2;
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "UPDATE `seg_entrada_almacen` SET  `id_user_act` = ? ,`fec_act` = ?, `id_devolucion` = ?, `estado`= ?  WHERE `id_entrada` =  ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id_user, PDO::PARAM_INT);
            $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(3, $id_salida, PDO::PARAM_INT);
            $sql->bindParam(4, $estado, PDO::PARAM_INT);
            $sql->bindParam(5, $id_entra, PDO::PARAM_INT);
            $sql->execute();
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
} else {
    $id_salida = $entrada['id_devolucion'];
}
$transformados = 0;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `seg_detalle_entrada_almacen`(`id_entra`, `id_prod`, `id_sede`, `id_bodega`, `id_tercero_api`, `id_tipo_entrada`, `cant_ingresa`, `iva`, `valu_ingresa`, `val_prom`, `id_lote`, `lote`, `id_marca`, `marca`, `invima`, `fecha_vence`, `id_user_reg`, `fec_reg`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_entra, PDO::PARAM_INT);
    $sql->bindParam(2, $id_prod, PDO::PARAM_INT);
    $sql->bindParam(3, $id_sede, PDO::PARAM_INT);
    $sql->bindParam(4, $id_bodega, PDO::PARAM_INT);
    $sql->bindParam(5, $id_tercero_api, PDO::PARAM_INT);
    $sql->bindParam(6, $id_tipo_entrada, PDO::PARAM_INT);
    $sql->bindParam(7, $cant_total, PDO::PARAM_INT);
    $sql->bindParam(8, $iva, PDO::PARAM_INT);
    $sql->bindParam(9, $valu_ingresa, PDO::PARAM_STR);
    $sql->bindParam(10, $val_prom, PDO::PARAM_STR);
    $sql->bindParam(11, $id_lote, PDO::PARAM_INT);
    $sql->bindParam(12, $lote, PDO::PARAM_STR);
    $sql->bindParam(13, $id_marca, PDO::PARAM_INT);
    $sql->bindParam(14, $marca, PDO::PARAM_STR);
    $sql->bindParam(15, $invima, PDO::PARAM_STR);
    $sql->bindParam(16, $fecha_vence, PDO::PARAM_STR);
    $sql->bindParam(17, $iduser, PDO::PARAM_INT);
    $sql->bindValue(18, $date->format('Y-m-d H:i:s'));
    foreach ($detalles as $dl) {
        $id_entrada = $dl['id_entrada'];
        if (array_key_exists($id_entrada, $entradas)) {
            $cant_p3 = $entradas[$id_entrada];
            $cant_total = $tipo == 2 ? $cant_p3 * $cantidad : $cant_p3 / $cantidad;
            $id_prod = $id_prod2;
            $id_sede = $dl['id_sede'];
            $id_bodega = $dl['id_bodega'];
            $id_tercero_api = $dl['id_tercero_api'];
            $id_tipo_entrada = $dl['id_tipo_entrada'];
            $iva = $dl['iva'];
            $valu_ingresa = $tipo == 2 ? $dl['valu_ingresa'] / $cantidad : $dl['valu_ingresa'] * $cantidad;
            $val_prom = $tipo == 2 ? $dl['val_prom'] / $cantidad : $dl['val_prom'] * $cantidad;
            $id_lote = $dl['id_lote'];
            $lote = $dl['lote'];
            $id_marca = $dl['id_marca'];
            $marca = $dl['marca'];
            $invima = $dl['invima'];
            $fecha_vence = $dl['fecha_vence'];
        }
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            $transformados++;
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $query = "INSERT INTO `seg_salidas_almacen` (`id_entrada`,`id_producto`,`id_devolucion`,`cantidad`,`vigencia`,`id_user_reg`,`fec_reg`) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $query = $cmd->prepare($query);
                $query->bindParam(1, $id_entrada, PDO::PARAM_INT);
                $query->bindParam(2, $id_prod1, PDO::PARAM_INT);
                $query->bindParam(3, $id_salida, PDO::PARAM_INT);
                $query->bindParam(4, $cant_p3, PDO::PARAM_INT);
                $query->bindParam(5, $vigencia, PDO::PARAM_INT);
                $query->bindParam(6, $id_user, PDO::PARAM_INT);
                $query->bindValue(7, $date->format('Y-m-d H:i:s'));
                $query->execute();
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
        } else {
            echo 'Error:' . $sql->errorInfo()[2];
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if ($transformados > 0) {
    echo 'ok';
} else {
    echo 'Error: No se realizó ninguna transformación';
}
