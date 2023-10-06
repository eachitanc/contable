<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
$id_entrada = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base_f;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `id_ingreso` AS `id_entrada`
                ,`tipo_ingreso` AS`id_tipo_entrada`
                ,`nit_tercero` AS`id_tercero_api`
                ,`num_factura` AS `no_factura`
                ,'' AS `acta_remision`
                ,`fec_factura` AS `fec_entrada`
                ,`estado`
                ,`detalle`
                ,'CRHON' AS `procede` 
            FROM `vista_entrada_farmacia`
            WHERE  `id_ingreso` = '$id_entrada'";
    $rs = $cmd->query($sql);
    $listentradas = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$tipoIngreso  = $listentradas['id_tipo_entrada'] == 2 ? 8 : $listentradas['id_tipo_entrada'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT MAX(`consecutivo`) AS `consecutivo` FROM `seg_entrada_almacen` WHERE `id_tipo_entrada` = $tipoIngreso";
    $rs = $cmd->query($sql);
    $ultimo = $rs->fetch(PDO::FETCH_ASSOC);
    $consecutivo = !empty($ultimo['consecutivo']) ? $ultimo['consecutivo'] + 1 : 1;
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$ced = explode('-', $listentradas['id_tercero_api']);
//API
$url = $api . 'terceros/datos/res/lista/' . $ced[0];
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
$terceros = json_decode($result, true);
if ($terceros != '0') {
    $idta = isset($terceros[0]['id_tercero']) ? $terceros[0]['id_tercero'] : 0;
} else {
    $idta = 0;
}
$tipoEntrada = $tipoIngreso;
$numActaRem = $listentradas['no_factura'];
$fecActRem = $listentradas['fec_entrada'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$vigencia = $_SESSION['vigencia'];
$estado = 2;
$observa = $listentradas['detalle'];
$ingresados = 0;

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `seg_entrada_almacen`(`id_tipo_entrada`,`id_tercero_api`,`no_factura`,`fec_entrada`,`vigencia`, `estado`, `id_user_reg`,`fec_reg`, `observacion`, `id_cronhis`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $tipoEntrada, PDO::PARAM_INT);
    $sql->bindParam(2, $idta, PDO::PARAM_INT);
    $sql->bindParam(3, $numActaRem, PDO::PARAM_STR);
    $sql->bindParam(4, $fecActRem, PDO::PARAM_STR);
    $sql->bindParam(5, $vigencia, PDO::PARAM_STR);
    $sql->bindParam(6, $estado, PDO::PARAM_INT);
    $sql->bindParam(7, $iduser, PDO::PARAM_INT);
    $sql->bindValue(8, $date->format('Y-m-d H:i:s'));
    $sql->bindParam(9, $observa, PDO::PARAM_STR);
    $sql->bindParam(10, $id_entrada, PDO::PARAM_INT);
    $sql->execute();
    $id_ins = $cmd->lastInsertId();
    if ($id_ins > 0) {
        $slq = "UPDATE `seg_entrada_almacen` SET `consecutivo` = ? WHERE `id_entrada` = ?";
        $sql = $cmd->prepare($slq);
        $sql->bindParam(1, $consecutivo, PDO::PARAM_INT);
        $sql->bindParam(2, $id_ins, PDO::PARAM_INT);
        $sql->execute();
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base_f;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $sql = "SELECT 
                        `id_ingreso` AS `id_entrada`
                        ,`tipo_ingreso` AS`id_tipo_entrada`
                        ,`nit_tercero` AS`id_tercero_api`
                        ,`num_factura` AS `no_factura`
                        ,'' AS `acta_remision`
                        ,`fec_factura` AS `fec_entrada`
                        ,`estado`
                        ,`detalle`
                        ,'CRHON' AS `procede` 
                    FROM `vista_entrada_farmacia`
                    WHERE  `id_ingreso` = '$id_entrada'";
            $rs = $cmd->query($sql);
            $listmedic = $rs->fetch(PDO::FETCH_ASSOC);
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        if (!empty($listentradas)) {
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base_f;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                $sql = "SELECT
                            `id_ingreso`
                            , `id_med`
                            , `nom_medicamento`
                            , `cantidad_ini`
                            , `valor_sin_iva`
                            , `iva`
                            , `id_lote`
                            , `lote`
                            , `fec_vencimiento`
                            , `reg_invima`
                            , `nom_laboratorio`
                            , `cod_tipo`
                        FROM
                            `vista_entrada_farmacia_detalles`
                        WHERE (`id_ingreso` = $id_entrada)";
                $rs = $cmd->query($sql);
                $productos = $rs->fetchAll(PDO::FETCH_ASSOC);
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
            $sede = 1;
            $bodega = 1;
            $iduser = $_SESSION['id_user'];
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                $sql = "INSERT INTO `seg_detalle_entrada_almacen`(`id_prod`,`id_sede`,`id_bodega`,`id_tipo_entrada`, `cant_ingresa`
                                        ,`valu_ingresa`,`lote`,`fecha_vence`, `marca`, `invima`,`id_user_reg`,`fec_reg`, `id_entra`, `iva`,`id_lote`) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $idProd, PDO::PARAM_INT);
                $sql->bindParam(2, $sede, PDO::PARAM_INT);
                $sql->bindParam(3, $bodega, PDO::PARAM_INT);
                $sql->bindParam(4, $tipoEntrada, PDO::PARAM_INT);
                $sql->bindParam(5, $cntdd, PDO::PARAM_INT);
                $sql->bindParam(6, $valunit, PDO::PARAM_STR);
                $sql->bindParam(7, $lote, PDO::PARAM_STR);
                $sql->bindParam(8, $vence, PDO::PARAM_STR);
                $sql->bindParam(9, $marca, PDO::PARAM_STR);
                $sql->bindParam(10, $invima, PDO::PARAM_STR);
                $sql->bindParam(11, $iduser, PDO::PARAM_INT);
                $sql->bindValue(12, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(13, $id_pre_don, PDO::PARAM_INT);
                $sql->bindParam(14, $iva, PDO::PARAM_INT);
                $sql->bindParam(15, $id_lote, PDO::PARAM_INT);
                foreach ($productos as $prod) {
                    $id_med = $prod['id_med'];
                    $cod_tipo = $prod['cod_tipo'];
                    $nom_medicamento = $prod['nom_medicamento'];
                    $idProd = ParamProducto($id_med, $cod_tipo, $nom_medicamento);
                    $cntdd = $prod['cantidad_ini'];
                    $valunit = $prod['valor_sin_iva'];
                    $valprom = $prod['valor_sin_iva'];
                    $lote = $prod['lote'];
                    $vence = $prod['fec_vencimiento'];
                    $marca = $prod['nom_laboratorio'];
                    $invima = $prod['reg_invima'];
                    $iva = $prod['iva'];
                    $id_pre_don = $id_ins;
                    $id_lote = $prod['id_lote'];
                    $sql->execute();
                    if ($cmd->lastInsertId() > 0) {
                        $ingresados++;
                    } else {
                        echo $sql->errorInfo()[2];
                    }
                }
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
        }
    } else {
        echo $sql->errorInfo()[2] . substr($sql->queryString, 0, 50);
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if ($ingresados > 0) {
    $estado = 3;
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base_f;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "UPDATE `far_orden_ingreso` SET `estado_fnc` = ? WHERE `id_ingreso` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $estado, PDO::PARAM_INT);
        $sql->bindParam(2, $id_entrada, PDO::PARAM_INT);
        $sql->execute();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    echo 'ok';
} else {
    echo 'error';
}
function ParamProducto($id_prod, $tipo, $nombre)
{
    //TIPO DE BIEN 27 MEDICAMENTOS, 43 INSUMOS
    include '../../../conexion.php';
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "SELECT
                    `id_med`
                    , `id_prod`
                FROM
                    `seg_ids_farmacia`
                WHERE (`id_med` = $id_prod)";
        $rs = $cmd->query($sql);
        $prod = $rs->fetch();
        if (!empty($prod)) {
            return $prod['id_prod'];
        } else {
            if ($tipo == '09') {
                $tipo = 43;
            } else {
                $tipo = 27;
            }
            $nombre = strtoupper($nombre);
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "INSERT INTO `seg_bien_servicio` (`id_tipo_bn_sv`, `bien_servicio`) VALUES (?, ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $tipo, PDO::PARAM_INT);
                $sql->bindParam(2, $nombre, PDO::PARAM_STR);
                $sql->execute();
                $id_pd = $cmd->lastInsertId();
                if ($id_pd > 0) {
                    try {
                        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                        $sql = "INSERT INTO `seg_ids_farmacia` (`id_med`, `id_prod`) VALUES (?, ?)";
                        $sql = $cmd->prepare($sql);
                        $sql->bindParam(1, $id_prod, PDO::PARAM_INT);
                        $sql->bindParam(2, $id_pd, PDO::PARAM_INT);
                        $sql->execute();
                        return $id_pd;
                    } catch (PDOException $e) {
                        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                    }
                }
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}
