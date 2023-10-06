<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$sede = isset($_POST['sede']) ? $_POST['sede'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_bodega`,`id_sede`, `nombre` FROM `seg_bodega_almacen` WHERE `id_sede`= '$sede'";
    $rs = $cmd->query($sql);
    $bodegas = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$res = '';
$res .= '<option value="0">--Seleccionar--</option>';
foreach ($bodegas as $b) {
    $res .= '<option value="' . $b['id_bodega'] . '">' . $b['nombre'] . '</option>';
}
echo $res;
