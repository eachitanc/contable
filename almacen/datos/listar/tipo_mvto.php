<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$tipoMv = isset($_POST['tipo']) ? $_POST['tipo'] : exit('Acción no permitida');
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
try {
    if ($tipoMv == 1) {
        $sql = "SELECT `id_entrada` AS `id`, `descripcion` FROM `seg_tipo_entrada`";
    } else if ($tipoMv == 2) {
        $sql = "SELECT `id_salida` AS `id`, `descripcion` FROM `seg_tipo_salidas`";
    } else {
        $sql = "SELECT `id_entrada` FROM `seg_tipo_entrada` WHERE `id_entrada` = 0";
    }
    echo $sql;
    $res = $cmd->query($sql);
    $movimiento = $res->fetchAll();
    $response = '<option value="0">--Seleccione--</option>';
    if (!empty($movimiento)) {
        foreach ($movimiento as $mv) {
            $response .= '<option value="' . $mv['id'] . '">' . $mv['descripcion'] . '</option>';
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
echo $response;
