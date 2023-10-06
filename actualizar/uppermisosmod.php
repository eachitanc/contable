<?php

session_start();
include '../conexion.php';
$datos = isset($_POST['caden']) ? explode('|', $_POST['caden']) : exit('Acción no permitida');
$id_user = $datos[0];
$id_modulo = $datos[1];
$estado = $datos[2];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    if ($estado == '0') {
        $sql = "INSERT INTO `seg_permisos_modulos` (`id_usuario`, `id_modulo`) VALUES(? , ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_user);
        $sql->bindParam(2, $id_modulo);
        $sql->execute();
        $res = $cmd->lastInsertId();
    } else {
        $sql = "DELETE FROM `seg_permisos_modulos` WHERE `id_usuario` = ? AND `id_modulo` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_user);
        $sql->bindParam(2, $id_modulo);
        $sql->execute();
        $res = $sql->rowCount();
    }
    if ($res > 0) {
        echo '1';
    } else {
        echo $cmd->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
