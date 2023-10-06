<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';

$id_pdo = isset($_POST['id_pdo']) ? $_POST['id_pdo'] : exit('Acción no permitida');
$iduser = $_SESSION['id_user'];
$id_prod = $_POST['id_prod'];
$cant = $_POST['numCanProd'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT * FROM `seg_detalle_pedido` WHERE `id_pedido` = ? AND `id_producto` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_pdo, PDO::PARAM_INT);
    $sql->bindParam(2, $id_prod, PDO::PARAM_INT);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        echo 'El producto ya se encuentra registrado en el pedido';
        exit();
    }
    $sql = "INSERT INTO `seg_detalle_pedido`(`id_pedido`, `id_producto`, `cantidad`, `id_user_reg`, `fec_reg`) VALUES (?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_pdo, PDO::PARAM_INT);
    $sql->bindParam(2, $id_prod, PDO::PARAM_INT);
    $sql->bindParam(3, $cant, PDO::PARAM_INT);
    $sql->bindParam(4, $iduser, PDO::PARAM_INT);
    $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
    $sql->execute();
    if ($cmd->lastInsertId() > 0) {
        $query = "SELECT `id_pedido` FROM `seg_detalle_pedido` WHERE `id_pedido` = $id_pdo";
        $rs = $cmd->query($query);
        $res = $rs->fetchAll(PDO::FETCH_ASSOC);
        if (count($res) == 1) {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sq = "UPDATE `seg_pedidos_almacen` SET `estado` = 2 WHERE `id_pedido` = ?";
            $sq = $cmd->prepare($sq);
            $sq->bindParam(1, $id_pdo, PDO::PARAM_INT);
            $sq->execute();
        }
        echo '1';
    } else {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
