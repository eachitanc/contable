<?php

session_start();
include '../conexion.php';
$id_permiso = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
$id_opcion = $_POST['opcion'];
$iduser = $_POST['iduser'];
if ($id_permiso != '0') {
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "DELETE FROM `seg_permiso_opciones`  WHERE `id_permiso` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_permiso, PDO::PARAM_INT);
        $sql->execute();
        if ($sql->rowCount() > 0) {
            echo 1;
        } else {
            echo $cmd->errorInfo()[2];
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
} else {
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "INSERT INTO `seg_permiso_opciones` (`id_usuario`, `id_opcion`) VALUES (?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $iduser, PDO::PARAM_INT);
        $sql->bindParam(2, $id_opcion, PDO::PARAM_INT);
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            echo $cmd->lastInsertId();
        } else {
            echo $cmd->errorInfo()[2];
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}
