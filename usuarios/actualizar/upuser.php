<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit;
}
include '../../conexion.php';

$idus = isset($_POST['idUpUser']) ? $_POST['idUpUser'] : exit('Acción no permitida');
$cc = $_POST['txtccUpUser'];
$nomb1 = $_POST['txtNomb1Upuser'];
$nomb2 = $_POST['txtNomb2Upuser'];
$ape1 = $_POST['txtApe1Upuser'];
$ape2 = $_POST['txtApe2Upuser'];
$login = $_POST['txtUplogin'];
$mail = $_POST['mailUpuser'];
$pass = $_POST['passUp'];
$roluser = $_POST['slcRolUpUser'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE seg_usuarios SET documento = ?, nombre1= ?, nombre2 = ?, apellido1 = ?, apellido2 = ?, login = ?, correo = ?, clave = ?, id_rol = ? WHERE id_usuario = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $cc, PDO::PARAM_STR);
    $sql->bindParam(2, $nomb1, PDO::PARAM_STR);
    $sql->bindParam(3, $nomb2, PDO::PARAM_STR);
    $sql->bindParam(4, $ape1, PDO::PARAM_STR);
    $sql->bindParam(5, $ape2, PDO::PARAM_STR);
    $sql->bindParam(6, $login, PDO::PARAM_STR);
    $sql->bindParam(7, $mail, PDO::PARAM_STR);
    $sql->bindParam(8, $pass, PDO::PARAM_STR);
    $sql->bindParam(9, $roluser, PDO::PARAM_INT);
    $sql->bindParam(10, $idus, PDO::PARAM_INT);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        $updata = 1;
    } else {
        $updata = 0;
    }
    if (!($sql->execute())) {
        print_r($sql->errorInfo()[2]);
        exit();
    }
    if ($updata > 0) {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "UPDATE seg_usuarios SET fec_act = ? WHERE id_usuario = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindValue(1, $date->format('Y-m-d H:i:s'));
        $sql->bindParam(2, $idus, PDO::PARAM_INT);
        $sql->execute();
        if ($sql->rowCount() > 0) {
            echo '1';
        } else {
            print_r($cmd->errorInfo()[2]);
        }
    } else {
        echo 'No se registró ningún dato nuevo.';
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
