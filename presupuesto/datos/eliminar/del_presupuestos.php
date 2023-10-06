<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';

$idpto = $_SESSION['del'];

// Comprobar si en la tabla seg_pto_cargue hay registros con el id_pto_presupuestos
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT cod_pptal FROM seg_pto_cargue  WHERE id_pto_presupuestos=$idpto";
    $rs = $cmd->query($sql);
    $res = $rs->rowCount();
    if ($res > 0) {
        echo 'El presupuesto tiene rubros cargados';
    } else {
        // Procedo a realizar la eliminación
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "DELETE FROM seg_pto_presupuestos  WHERE id_pto_presupuestos = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $idpto, PDO::PARAM_INT);
            $sql->execute();
            if ($sql->rowCount() > 0) {
                echo '1';
            } else {
                print_r($sql->errorInfo()[2]);
            }
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
