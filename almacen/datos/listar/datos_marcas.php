<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_marca` , `descripcion` , `fec_reg` FROM `seg_marcas`";
    $rs = $cmd->query($sql);
    $marcas = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$data = [];
if (!empty($marcas)) {
    foreach ($marcas as $mc) {
        $id_mc = $mc['id_marca'];
        $data[] = [
            "id" => $id_mc,
            "marca" => mb_strtoupper($mc['descripcion']),
            "fecha" => '<div class="text-center centro-vertical">' . $mc['fec_reg'] . '</div>',
            "botones" => '<div class="text-center centro-vertical"></div>',
        ];
    }
}
$datos = ['data' => $data];
echo json_encode($datos);
