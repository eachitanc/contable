<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
$numActaRem = isset($_POST['numActaRem']) ? $_POST['numActaRem'] : exit('Acción no permitida');
$id_ter = $_POST['id_tercero_pd'];
$fecActRem = $_POST['fecActRem'];
$id_prdo = $_POST['id_prdo'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$observa = $_POST['txtObservaEntrada'];

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `seg_entrada_almacen`SET `id_tercero_api` = ?, `acta_remision` = ?,`fec_entrada` = ?, `observacion`= ? WHERE `id_entrada` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_ter, PDO::PARAM_INT);
    $sql->bindParam(2, $numActaRem, PDO::PARAM_STR);
    $sql->bindParam(3, $fecActRem, PDO::PARAM_STR);
    $sql->bindParam(4, $observa, PDO::PARAM_STR);
    $sql->bindParam(5, $id_prdo, PDO::PARAM_INT);
    if (!($sql->execute())) {
        print_r($sql->errorInfo()[2]);
        exit();
    } else {
        if ($sql->rowCount() > 0) {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "UPDATE `seg_entrada_almacen` SET  `id_user_act` = ? ,`fec_act` = ?  WHERE `id_entrada` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $iduser, PDO::PARAM_INT);
            $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(3, $id_prdo, PDO::PARAM_INT);
            $sql->execute();
            if ($sql->rowCount() > 0) {
                echo '1';
            } else {
                print_r($sql->errorInfo()[2]);
            }
        } else {
            echo 'No se registró ningún nuevo dato';
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
