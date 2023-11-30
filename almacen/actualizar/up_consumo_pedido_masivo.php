<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
$id_pdo = isset($_POST['id_pedido']) ? $_POST['id_pedido'] : exit('Acción no permitida');
$data = $_POST['cantxprod'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$vigencia = $_SESSION['vigencia'];
$consumidos = 0;
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
foreach ($data as $d => $v) {
    if ($v > 0) {
        $dt = explode('|', $d);
        $id_detalle = $dt[0];
        $id_producto = $dt[1];
        $id_entrada = $dt[2];
        $cantidad = $v;
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
                $sql->execute();
                $cmd = null;
                try {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $sql = "UPDATE `seg_detalle_pedido` SET  `id_user_act` = ? ,`fec_act` = ?  WHERE `id_detalle` = ?";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $iduser, PDO::PARAM_INT);
                    $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
                    $sql->bindParam(3, $id_detalle, PDO::PARAM_INT);
                    $sql->execute();
                    $cmd = null;
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
                        $sql->bindParam(7, $id_entrada, PDO::PARAM_INT);
                        $sql->execute();
                        if ($cmd->lastInsertId() > 0) {
                            $consumidos++;
                        } else {
                            echo $sql->errorInfo()[2];
                        }
                        $cmd = null;
                    } catch (PDOException $e) {
                        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                    }
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                }
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
        } else {
            echo '!¿Qué intentas hacer?¡, Con un click es suficiente.';
        }
    }
}
if ($consumidos > 0) {
    echo 'ok';
} else {
    echo 'No se consumió ningún producto';
}
