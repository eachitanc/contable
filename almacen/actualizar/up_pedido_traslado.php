<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
$cantidades = isset($_POST['cantxprod']) ? $_POST['cantxprod'] : exit('Acción no permitida');
$entrega = [];
$ids = [];
foreach ($cantidades as $key => $value) {
    $llave = explode('|', $key);
    $entrega[] = [
        'id_detalle' => $llave[0],
        'id_entrada' => $llave[1],
        'id_prod' => $llave[2],
        'cantidad' => $value
    ];
    $ids[] = $llave[1];
}
$ids = implode(',', $ids);
$tipo_trasl = 2;
$sede_sale = $_POST['id_sede_sale'];
$bodega_sale = $_POST['id_bodega_sale'];
$sede_entra = $_POST['id_sede_entra'];
$bodega_entra =  $_POST['id_bodega_entra'];
$id_pedido =  $_POST['id_pedido'];
$iduser = $_SESSION['id_user'];
$vigencia = $_SESSION['vigencia'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fec_traslado = $date->format('Y-m-d');
$entregado = 0;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `seg_detalle_entrada_almacen`.`id_entrada`
                , `seg_detalle_entrada_almacen`.`id_lote`
                , `seg_detalle_entrada_almacen`.`lote`
                , `seg_marcas`.`descripcion` AS `marca`
                , `seg_detalle_entrada_almacen`.`invima`
                , `seg_detalle_entrada_almacen`.`fecha_vence`
            FROM
                `seg_detalle_entrada_almacen`
                LEFT JOIN `seg_marcas` 
                    ON (`seg_detalle_entrada_almacen`.`id_marca` = `seg_marcas`.`id_marca`)
            WHERE (`seg_detalle_entrada_almacen`.`id_entrada` IN ($ids))";
    $res = $cmd->query($sql);
    $info_entrada = $res->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `seg_pedidos_almacen`.`id_bodega`
                , `seg_pedidos_almacen`.`id_entrada`
                , `seg_pedidos_almacen`.`id_pedido`
                , `seg_entrada_almacen`.`id_cronhis`
            FROM
                `seg_pedidos_almacen`
                INNER JOIN `seg_entrada_almacen` 
                    ON (`seg_pedidos_almacen`.`id_entrada` = `seg_entrada_almacen`.`id_entrada`)
            WHERE (`seg_pedidos_almacen`.`id_pedido` = $id_pedido)";
    $res = $cmd->query($sql);
    $bodega = $res->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$id_cronhis = isset($bodega['id_cronhis']) ? $bodega['id_cronhis'] : 0;
if (!empty($entrega)) {
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "INSERT INTO `seg_traslados_almacen` (`id_tipo_trasl`, `id_sede_sale`, `id_bodega_sale`, `id_sede_entra`, `id_bodega_entra`, `fec_traslado`, `id_pedido`,`vigencia`, `id_user_reg`, `fec_reg`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $tipo_trasl, PDO::PARAM_INT);
        $sql->bindParam(2, $sede_sale, PDO::PARAM_INT);
        $sql->bindParam(3, $bodega_sale, PDO::PARAM_INT);
        $sql->bindParam(4, $sede_entra, PDO::PARAM_INT);
        $sql->bindParam(5, $bodega_entra, PDO::PARAM_INT);
        $sql->bindParam(6, $fec_traslado, PDO::PARAM_STR);
        $sql->bindParam(7, $id_pedido, PDO::PARAM_INT);
        $sql->bindParam(8, $vigencia, PDO::PARAM_STR);
        $sql->bindParam(9, $iduser, PDO::PARAM_INT);
        $sql->bindValue(10, $date->format('Y-m-d H:i:s'));
        $sql->execute();
        $id_traslado = $cmd->lastInsertId();
        if (!($id_traslado > 0)) {
            echo $sql->errorInfo()[2];
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    foreach ($entrega as $value) {
        $id_detalle = $value['id_detalle'];
        $id_entrada = $value['id_entrada'];
        $id_prod = $value['id_prod'];
        $cantidad = $value['cantidad'];
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "UPDATE `seg_detalle_pedido` SET `entrega` = `entrega` + ? WHERE `id_detalle` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $cantidad, PDO::PARAM_INT);
            $sql->bindParam(2, $id_detalle, PDO::PARAM_INT);
            if (!($sql->execute())) {
                echo $sql->errorInfo()[2];
            } else {
                if ($sql->rowCount() > 0) {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $sql = "UPDATE `seg_detalle_pedido` SET  `id_user_act` = ? ,`fec_act` = ?  WHERE `id_detalle` = ?";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $iduser, PDO::PARAM_INT);
                    $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
                    $sql->bindParam(3, $id_detalle, PDO::PARAM_INT);
                    $sql->execute();
                    if (!($sql->rowCount() > 0)) {
                        echo $sql->errorInfo()[2];
                    }
                }
            }
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        $observacion = NULL;
        $id_lote = NULL;
        if ($cantidad > 0) {
            $key = array_search($id_entrada, array_column($info_entrada, 'id_entrada'));
            $observacion = $info_entrada[$key]['invima'] . ', ' . $info_entrada[$key]['fecha_vence'] . ', ' . $info_entrada[$key]['lote'] . ', ' . $info_entrada[$key]['marca'];
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "INSERT INTO `seg_detalles_traslado` (`id_entrada`, `id_producto`, `id_traslado`, `cantidad`, `vigencia`, `id_user_reg`, `fec_reg`, `observacion`) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $id_entrada, PDO::PARAM_INT);
                $sql->bindParam(2, $id_prod, PDO::PARAM_INT);
                $sql->bindParam(3, $id_traslado, PDO::PARAM_INT);
                $sql->bindParam(4, $cantidad, PDO::PARAM_INT);
                $sql->bindParam(5, $vigencia, PDO::PARAM_STR);
                $sql->bindParam(6, $iduser, PDO::PARAM_INT);
                $sql->bindValue(7, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(8, $observacion, PDO::PARAM_STR);
                $sql->execute();
                if ($cmd->lastInsertId() > 0) {
                    $entregado++;
                    $estado = 3;
                    $sql = "UPDATE `seg_traslados_almacen` SET `estado` = ?, `id_user_act` = ?, `fec_act` = ? WHERE `id_trasl_alm` = ?";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $estado, PDO::PARAM_INT);
                    $sql->bindParam(2, $iduser, PDO::PARAM_INT);
                    $sql->bindValue(3, $date->format('Y-m-d H:i:s'));
                    $sql->bindParam(4, $id_traslado, PDO::PARAM_INT);
                    $sql->execute();
                    if ($id_cronhis > 0) {
                        $keylt = array_search($id_entrada, array_column($info_entrada, 'id_entrada'));
                        $id_lote = $info_entrada[$keylt]['id_lote'];
                        try {
                            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base_f;$charset", $bd_usuario, $bd_clave);
                            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                            $sql = "UPDATE `far_orden_ingreso_detalle` SET `cantidad` = ? WHERE `id_lote` = ? AND `id_ingreso` = ?";
                            $sql = $cmd->prepare($sql);
                            $sql->bindParam(1, $cantidad, PDO::PARAM_INT);
                            $sql->bindParam(2, $id_lote, PDO::PARAM_INT);
                            $sql->bindParam(3, $id_cronhis, PDO::PARAM_INT);
                            $sql->execute();
                            if (!($sql->rowCount() > 0)) {
                                echo $sql->errorInfo()[2];
                            }
                        } catch (PDOException $e) {
                            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                        }
                        if ($entregado == 1) {
                            $estado = 4;
                            try {
                                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base_f;$charset", $bd_usuario, $bd_clave);
                                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                                $sql = "UPDATE `far_orden_ingreso` SET `estado_fnc` = ? WHERE `id_ingreso` = ?";
                                $sql = $cmd->prepare($sql);
                                $sql->bindParam(1, $estado, PDO::PARAM_INT);
                                $sql->bindParam(2, $id_cronhis, PDO::PARAM_INT);
                                $sql->execute();
                                if (!($sql->rowCount() > 0)) {
                                    echo $sql->errorInfo()[2];
                                }
                                $cmd = null;
                            } catch (PDOException $e) {
                                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                            }
                        }
                    }
                } else {
                    echo $cmd->errorInfo()[2];
                }
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
        }
    }
}
if ($entregado > 0) {
    $estado = 4;
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "UPDATE `seg_pedidos_almacen` SET `estado` = ? WHERE `id_pedido` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $estado, PDO::PARAM_INT);
        $sql->bindParam(2, $id_pedido, PDO::PARAM_INT);
        if (!($sql->execute())) {
            echo $sql->errorInfo()[2];
            exit();
        } else {
            if ($sql->rowCount() > 0) {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "UPDATE `seg_pedidos_almacen` SET  `id_user_act` = ? ,`fec_act` = ?  WHERE `id_pedido` = ?";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $iduser, PDO::PARAM_INT);
                $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(3, $id_pedido, PDO::PARAM_INT);
                $sql->execute();
                if ($sql->rowCount() > 0) {
                    echo 'ok';
                } else {
                    echo $sql->errorInfo()[2];
                }
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
} else {
    echo 'No se pudo realizar el traslado';
}
