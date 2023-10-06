<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
$id_pdo = isset($_POST['pedido']) ? $_POST['pedido'] : exit('Acción no permitida');
$data = explode('|', $_POST['id']);
$id_detalle = $data[0];
$id_producto = $data[1];
$cantidad = $_POST['valor'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_devolucion` FROM  `seg_salida_dpdvo` WHERE `id_pedido` = $id_pdo";
    $rs = $cmd->query($sql);
    $salida = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$tipo_salida = 7;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT MAX(`consecutivo`) AS `consecutivo`  FROM `seg_salida_dpdvo` WHERE `id_tipo_salida` = $tipo_salida";
    $rs = $cmd->query($sql);
    $consec = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$consecutivo = $consec['consecutivo'] > 0 ? $consec['consecutivo'] + 1 : 1;
if (!empty($salida)) {
    $id_salida = $salida['id_devolucion'];
} else {
    $estado = 2;
    $fecha = $date->format('Y-m-d');
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "INSERT INTO `seg_salida_dpdvo` (`id_tipo_salida`, `fec_acta_remision`, `vigencia`, `id_user_reg`, `fec_reg`, `id_pedido`,`estado`, `consecutivo`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $tipo_salida, PDO::PARAM_INT);
        $sql->bindParam(2, $fecha, PDO::PARAM_STR);
        $sql->bindParam(3, $vigencia, PDO::PARAM_STR);
        $sql->bindParam(4, $iduser, PDO::PARAM_INT);
        $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
        $sql->bindParam(6, $id_pdo, PDO::PARAM_INT);
        $sql->bindParam(7, $estado, PDO::PARAM_INT);
        $sql->bindParam(8, $consecutivo, PDO::PARAM_INT);
        $sql->execute();
        $id_salida = $cmd->lastInsertId();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `t3`.`id_entrada`, `t3`.`cantidad`- IFNULL(`t2`.`sale`,0) AS `disponible` FROM 
                (SELECT 
                    `id_entrada`, `id_producto`, SUM(`cantidad`) AS `sale`
                FROM
                    (SELECT
                        `seg_salida_dpdvo`.`id_pedido`
                        , `seg_salidas_almacen`.`id_entrada`
                        , `seg_salidas_almacen`.`id_producto`
                        , `seg_salidas_almacen`.`cantidad`
                    FROM
                        `seg_salidas_almacen`
                        INNER JOIN `seg_salida_dpdvo` 
                        ON (`seg_salidas_almacen`.`id_devolucion` = `seg_salida_dpdvo`.`id_devolucion`)
                    WHERE `seg_salida_dpdvo`.`id_pedido` = $id_pdo AND `seg_salidas_almacen`.`id_producto` = $id_producto) AS `t1`
                    GROUP BY `id_entrada`)AS `t2`
            RIGHT JOIN
                (SELECT
                    `seg_traslados_almacen`.`id_pedido`
                    , `seg_detalles_traslado`.`id_entrada`
                    , `seg_detalles_traslado`.`id_producto`
                    , `seg_detalles_traslado`.`cantidad`
                    , `seg_traslados_almacen`.`id_trasl_alm`
                FROM
                    `seg_detalles_traslado`
                INNER JOIN `seg_traslados_almacen` 
                    ON (`seg_detalles_traslado`.`id_traslado` = `seg_traslados_almacen`.`id_trasl_alm`)
                WHERE (`seg_traslados_almacen`.`id_pedido` = $id_pdo AND `seg_detalles_traslado`.`id_producto` = $id_producto)
                GROUP BY `seg_traslados_almacen`.`id_trasl_alm`) AS `t3` 
            ON (`t2`.`id_entrada` = `t3`.`id_entrada`)";
    $rs = $cmd->query($sql);
    $traslados = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `consumo`, `entrega` FROM `seg_detalle_pedido` WHERE `id_detalle` = $id_detalle";
    $rs = $cmd->query($sql);
    $consumo = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if ($consumo['consumo'] + $cantidad <= $consumo['entrega']) {
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "UPDATE `seg_detalle_pedido` SET `consumo` = `consumo` + ? WHERE `id_detalle` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $cantidad, PDO::PARAM_INT);
        $sql->bindParam(2, $id_detalle, PDO::PARAM_INT);
        if (!($sql->execute())) {
            echo $sql->errorInfo()[2];
            exit();
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
                if ($sql->rowCount() > 0) {
                    try {
                        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                        $sql = "INSERT INTO `seg_salidas_almacen`(`id_producto`, `id_devolucion`, `cantidad`, `vigencia`, `id_user_reg`, `fec_reg`,`id_entrada`)
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                        $sql = $cmd->prepare($sql);
                        $sql->bindParam(1, $id_producto, PDO::PARAM_INT);
                        $sql->bindParam(2, $id_salida, PDO::PARAM_INT);
                        $sql->bindParam(3, $cantidad, PDO::PARAM_INT);
                        $sql->bindParam(4, $vigencia, PDO::PARAM_STR);
                        $sql->bindParam(5, $iduser, PDO::PARAM_INT);
                        $sql->bindValue(6, $date->format('Y-m-d H:i:s'));
                        $sql->bindParam(7, $id_intra, PDO::PARAM_INT);
                        $lotes = count($traslados);
                        if ($lotes == 1) {
                            $id_intra = $traslados[0]['id_entrada'];
                            $sql->execute();
                            if ($cmd->lastInsertId() > 0) {
                                echo 'ok';
                            } else {
                                echo $sql->errorInfo()[2];
                            }
                        } else {
                            $consumir = $cantidad;
                            foreach ($traslados as $traslado) {
                                if ($traslado['disponible'] > 0) {
                                    $cantidad = $traslado['disponible'];
                                    if ($consumir <= $cantidad) {
                                        $cantidad = $consumir;
                                        $id_intra = $traslado['id_entrada'];
                                        $sql->execute();
                                        if ($cmd->lastInsertId() > 0) {
                                            echo 'ok';
                                            exit();
                                        } else {
                                            echo $sql->errorInfo()[2];
                                        }
                                    } else {
                                        $consumir = $consumir - $cantidad;
                                        $id_intra = $traslado['id_entrada'];
                                        $sql->execute();
                                        if (!($cmd->lastInsertId() > 0)) {
                                            echo $sql->errorInfo()[2];
                                        }
                                    }
                                }
                            }
                        }
                        $cmd = null;
                    } catch (PDOException $e) {
                        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                    }
                }
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
} else {
    echo '!¿Qué intentas hacer?¡, Con un click es suficiente.';
}
