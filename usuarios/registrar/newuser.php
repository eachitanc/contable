<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit;
}
include '../../conexion.php';

$cc = isset($_POST['txtCCuser']) ? $_POST['txtCCuser'] : exit('Acción no permitida');
$nomb1 = $_POST['txtNomb1user'];
$nomb2 = $_POST['txtNomb2user'];
$ape1 = $_POST['txtApe1user'];
$ape2 = $_POST['txtApe2user'];
$login = $_POST['txtlogin'];
$mail = $_POST['mailuser'];
$pass = $_POST['passu'];
$est = $_POST['numEstUser'];
$roluser = $_POST['slcRolUser'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT * FROM seg_usuarios WHERE login = '$login'";
    $r = $cmd->query($sql);
    $sql = "SELECT * FROM seg_usuarios WHERE documento = '$cc'";
    $s = $cmd->query($sql);
    if ($r->rowCount() > 0 || $s->rowCount() > 0) {
        echo '0';
    } else {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "INSERT INTO seg_usuarios(documento, nombre1, nombre2, apellido1, apellido2, login, correo, clave, estado, id_rol, fec_reg) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $cc, PDO::PARAM_STR);
        $sql->bindParam(2, $nomb1, PDO::PARAM_STR);
        $sql->bindParam(3, $nomb2, PDO::PARAM_STR);
        $sql->bindParam(4, $ape1, PDO::PARAM_STR);
        $sql->bindParam(5, $ape2, PDO::PARAM_STR);
        $sql->bindParam(6, $login, PDO::PARAM_STR);
        $sql->bindParam(7, $mail, PDO::PARAM_STR);
        $sql->bindParam(8, $pass, PDO::PARAM_STR);
        $sql->bindParam(9, $est, PDO::PARAM_INT);
        $sql->bindParam(10, $roluser, PDO::PARAM_INT);
        $sql->bindValue(11, $date->format('Y-m-d H:i:s'));
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            $iduser = $cmd->lastInsertId();
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO seg_permisos_usuario(id_usuario, listar, registrar, editar, borrar, fec_reg) VALUES (?, ?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $iduser, PDO::PARAM_INT);
            $sql->bindParam(2, $list, PDO::PARAM_INT);
            $sql->bindParam(3, $reg, PDO::PARAM_INT);
            $sql->bindParam(4, $edit, PDO::PARAM_INT);
            $sql->bindParam(5, $del, PDO::PARAM_INT);
            $sql->bindValue(6, $date->format('Y-m-d H:i:s'));
            if ($roluser === 1) {
                $list = 1;
                $reg = 1;
                $edit = 1;
                $del = 0;
            } else {
                $list = 1;
                $reg = 0;
                $edit = 0;
                $del = 0;
            }
            $sql->execute();
            if ($cmd->lastInsertId() > 0) {
                echo '1';
            } else {
                print_r($cmd->errorInfo()[2]);
            }
        } else {
            print_r($cmd->errorInfo()[2]);
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
