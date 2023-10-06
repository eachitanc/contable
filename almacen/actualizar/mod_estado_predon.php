<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
//API URL
$id_pd = isset($_POST['id_pd']) ? $_POST['id_pd'] : exit('Acción no permitida');
$estado = 3;
$vigencia  = $_SESSION['vigencia'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_entrada_almacen`.`id_entrada`
                , `seg_entrada_almacen`.`id_cronhis`
                , `seg_detalle_entrada_almacen`.`id_prod`
                , `seg_detalle_entrada_almacen`.`cant_ingresa`
            FROM
                `seg_detalle_entrada_almacen`
                INNER JOIN `seg_entrada_almacen` 
                    ON (`seg_detalle_entrada_almacen`.`id_entra` = `seg_entrada_almacen`.`id_entrada`)
            WHERE (`seg_entrada_almacen`.`id_entrada` = $id_pd)";
    $rs = $cmd->query($sql);
    $cronhis = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if ($cronhis[0]['id_cronhis'] > 0) {
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "SELECT
                    `id_usuario`
                FROM
                    `seg_responsable_bodega`
                WHERE `id_resp` = (SELECT MAX(`id_resp`) FROM `seg_responsable_bodega` WHERE `id_bodega` = 40)";
        $rs = $cmd->query($sql);
        $userbg = $rs->fetch();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    $usuario_farmacia = $userbg['id_usuario'];
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "SELECT MAX(`consecutivo`) AS `consecutivo` FROM `seg_pedidos_almacen` WHERE `id_bodega` =  40";
        $rs = $cmd->query($sql);
        $consecutivo = $rs->fetch();
        $consec = $consecutivo['consecutivo'] == '' ? 1 : $consecutivo['consecutivo'] + 1;
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    if (!empty($cronhis)) {
        $agrupar = [];
        foreach ($cronhis as $row) {
            $agrupar[$row['id_prod']] =  isset($agrupar[$row['id_prod']]) ? $agrupar[$row['id_prod']] + $row['cant_ingresa'] : $row['cant_ingresa'];
        }
        $estado = 3;
        $id_area = 40; //Bodega Farmacia
        $entrega = 1;
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO `seg_pedidos_almacen`(`id_bodega`, `estado`, `vigencia`, `id_user_reg`, `fec_reg`, `id_entrada`, `consecutivo`,`bod_entrega`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id_area, PDO::PARAM_INT);
            $sql->bindParam(2, $estado, PDO::PARAM_INT);
            $sql->bindParam(3, $vigencia, PDO::PARAM_STR);
            $sql->bindParam(4, $usuario_farmacia, PDO::PARAM_INT);
            $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(6, $id_pd, PDO::PARAM_INT);
            $sql->bindParam(7, $consec, PDO::PARAM_INT);
            $sql->bindParam(7, $entrega, PDO::PARAM_INT);
            $sql->execute();
            $id_pedido = $cmd->lastInsertId();
            if ($id_pedido > 0) {
                try {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $sql = "INSERT INTO `seg_detalle_pedido`(`id_pedido`, `id_producto`, `cantidad`, `id_user_reg`, `fec_reg`) VALUES (?, ?, ?, ?, ?)";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $id_pedido, PDO::PARAM_INT);
                    $sql->bindParam(2, $id_prod, PDO::PARAM_INT);
                    $sql->bindParam(3, $cant, PDO::PARAM_INT);
                    $sql->bindParam(4, $usuario_farmacia, PDO::PARAM_INT);
                    $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
                    foreach ($agrupar as $key => $c) {
                        $id_prod = $key;
                        $cant = $c;
                        $sql->execute();
                        if (!($cmd->lastInsertId() > 0)) {
                            echo $sql->errorInfo()[2] . substr($sql->queryString, 0, 50);
                        }
                    }
                    $cmd = null;
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                }
            } else {
                echo $sql->errorInfo()[2];
            }
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
    }
}
try {
    $estado = 3;
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `seg_entrada_almacen` SET `estado` = ?, `id_user_act` = ?, `fec_act` = ? WHERE `id_entrada` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $estado, PDO::PARAM_INT);
    $sql->bindParam(2, $iduser, PDO::PARAM_INT);
    $sql->bindValue(3, $date->format('Y-m-d H:i:s'));
    $sql->bindParam(4, $id_pd, PDO::PARAM_INT);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        echo '1';
    } else {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
