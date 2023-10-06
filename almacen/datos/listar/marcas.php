<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_detalle_entrada_almacen`.`id_prod`
                , `seg_marcas`.`id_marca`
                , `seg_marcas`.`descripcion`
            FROM
                `seg_detalle_entrada_almacen`
                INNER JOIN `seg_marcas` 
                    ON (`seg_detalle_entrada_almacen`.`id_marca` = `seg_marcas`.`id_marca`)
            WHERE `seg_detalle_entrada_almacen`.`id_prod` = $id
            GROUP BY `seg_marcas`.`id_marca` ORDER BY `seg_marcas`.`descripcion` ASC";
    $rs = $cmd->query($sql);
    $marcas = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$options = '<option value="0">--Seleccionar--</option>';
foreach ($marcas as $m) {
    $options .= '<option value="' . $m['id_marca'] . '">' . $m['descripcion'] . '</option>';
}
echo $options;
