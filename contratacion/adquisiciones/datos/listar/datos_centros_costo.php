<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
$id_sede = isset($_POST['id_sede']) ? $_POST['id_sede'] : exit('Acción no permitida');
include '../../../../conexion.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_centro_costo_x_sede`.`id_x_sede`, `seg_centros_costo`.`descripcion`
            FROM
                `seg_centro_costo_x_sede`
                INNER JOIN `seg_centros_costo` 
                    ON (`seg_centro_costo_x_sede`.`id_centro_c` = `seg_centros_costo`.`id_centro`)
            WHERE `seg_centro_costo_x_sede`.`id_sede` = '$id_sede' 
            ORDER BY `seg_centros_costo`.`descripcion` ASC";
    $rs = $cmd->query($sql);
    $centros_costo = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$res = '';
if (!empty($centros_costo)) {
    $res .=  '<option value="0">--Seleccione--</option>';
    foreach ($centros_costo as $centro_costo) {
        $res .= '<option value="' . $centro_costo['id_x_sede'] . '">' . $centro_costo['descripcion'] . '</option>';
    }
} else {
    $res .=  '<option value="0">--Sede sin centros de costo--</option>';
}

echo $res;
