<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
$id_bodega = isset($_POST['id_bodega']) ? $_POST['id_bodega'] : exit('Acción no permitida');
$cuentas = $_POST['numCuenta'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$contador = 0;
foreach ($cuentas as $key => $value) {
    $datos = explode("|", $key);
    $id_cta = $datos[0];
    $id_tipo = $datos[1];
    if ($id_cta == 0) {
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO `seg_ctas_gasto` (`id_bodega`, `id_tipo_bn_sv`, `id_user_reg`, `fec_reg`,`cuenta`)
                    VALUES (?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id_bodega, PDO::PARAM_INT);
            $sql->bindParam(2, $id_tipo, PDO::PARAM_INT);
            $sql->bindParam(3, $iduser, PDO::PARAM_INT);
            $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(5, $value, PDO::PARAM_STR);
            $sql->execute();
            if ($cmd->lastInsertId() > 0) {
                $contador++;
            } else {
                echo $sql->errorInfo()[2];
            }
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
    } else {
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "UPDATE `seg_ctas_gasto` SET `cuenta` = ? WHERE `id_cta` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $value, PDO::PARAM_INT);
            $sql->bindParam(2, $id_cta, PDO::PARAM_STR);
            if (!($sql->execute())) {
                echo $sql->errorInfo()[2];
            } else {
                if ($sql->rowCount() > 0) {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $sql = "UPDATE `seg_ctas_gasto` SET  `id_user_act` = ? ,`fec_act` = ?  WHERE `id_cta` = ?";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $iduser, PDO::PARAM_INT);
                    $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
                    $sql->bindParam(3, $id_cta, PDO::PARAM_INT);
                    $sql->execute();
                    if (!($sql->rowCount() > 0)) {
                        echo $sql->errorInfo()[2];
                    }
                    $contador++;
                }
            }
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
    }
}
if ($contador > 0) {
    echo 'ok';
} else {
    echo 'No se realizó ningún cambio';
}
