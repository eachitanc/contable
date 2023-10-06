<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
//API URL
$id_pdo = isset($_POST['idPedido']) ? $_POST['idPedido'] : exit('Acción no permitida');
$id_area = isset($_POST['idAreaPide']) ? $_POST['idAreaPide'] : 0;
$bod_entrega = isset($_POST['idAreaEntrega']) ? $_POST['idAreaEntrega'] : 0;
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
if ($id_area > 0) {
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "UPDATE `seg_pedidos_almacen` SET `id_bodega` = ?, `bod_entrega` = ? WHERE `id_pedido` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_area, PDO::PARAM_INT);
        $sql->bindParam(2, $bod_entrega, PDO::PARAM_INT);
        $sql->bindParam(3, $id_pdo, PDO::PARAM_INT);
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
                $sql->bindParam(3, $id_pdo, PDO::PARAM_INT);
                $sql->execute();
                if ($sql->rowCount() > 0) {
                    echo '1';
                } else {
                    echo $sql->errorInfo()[2];
                }
            } else {
                echo 'No se registró ningún nuevo dato';
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
} else {
    $estado = 0;
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "UPDATE `seg_pedidos_almacen` SET `estado` = ? WHERE `id_pedido` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $estado, PDO::PARAM_INT);
        $sql->bindParam(2, $id_pdo, PDO::PARAM_INT);
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
                $sql->bindParam(3, $id_pdo, PDO::PARAM_INT);
                $sql->execute();
                if ($sql->rowCount() > 0) {
                    echo '1';
                } else {
                    echo $sql->errorInfo()[2];
                }
            } else {
                echo 'No se registró ningún nuevo dato';
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}
