<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
$id_prod1 = isset($_POST['id_prod1']) ? $_POST['id_prod1'] : exit('Acceso denegado');
$id_prod2 = isset($_POST['id_prod2']) ? $_POST['id_prod2'] : exit('Acceso denegado');
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
try {
    $sql = "SELECT `id_b_s`,`id_tipo_bn_sv` FROM `seg_bien_servicio` WHERE `id_b_s` IN ($id_prod1,$id_prod2)";
    $res = $cmd->query($sql);
    $grupo = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$iguales = true;
foreach ($grupo as $gp) {
    if ($gp['id_tipo_bn_sv'] != $grupo[0]['id_tipo_bn_sv']) {
        $iguales = false;
        break;
    }
}
if (!$iguales) {
    echo 'No se puede unificar los productos, no pertenecen al mismo grupo de bienes y servicios';
    exit();
} else {
    try {
        $sql = "UPDATE `seg_detalle_entrada_almacen` SET `id_prod_unificado` = `id_prod` WHERE `id_prod` = ? AND `id_prod_unificado` IS NULL";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_prod2, PDO::PARAM_INT);
        if (!($sql->execute())) {
            echo $sql->errorInfo()[2];
            exit();
        } else {
            if ($sql->rowCount() > 0) {
                $query = "UPDATE `seg_detalle_entrada_almacen` SET `id_prod` = ? WHERE `id_prod` = ?";
                $query = $cmd->prepare($query);
                $query->bindParam(1, $id_prod1, PDO::PARAM_INT);
                $query->bindParam(2, $id_prod2, PDO::PARAM_INT);
                $query->execute();
                if ($query->rowCount() > 0) {
                    echo 'ok';
                } else {
                    echo $query->errorInfo()[2];
                }
            } else {
                echo 'No se realizo ninguna actualización';
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}
