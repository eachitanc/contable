<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$busca = $_POST['term'];
$tercero = isset($_POST['tercero']) ? $_POST['tercero'] : exit('Acceso Denegado');
$condicion = $tercero == '0' ?  '' : "AND id_tercero_api = '$tercero'";
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_prod`,`id_entrada`,`lote`,`cant_ingresa` FROM `seg_detalle_entrada_almacen` WHERE lote LIKE '%$busca%' $condicion ORDER BY `lote` ASC";
    $rs = $cmd->query($sql);
    $lotes = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
foreach ($lotes as $ls) {
    $data[] = [
        'id' => $ls['id_prod'],
        'label' => $ls['lote'],
        'max' => $ls['cant_ingresa'],
        'id_entrada' => $ls['id_entrada'],
    ];
}
if (empty($data)) {
    $data[] = [
        'id' => '0',
        'label' => 'No hay coincidencias...',
    ];
}
echo json_encode($data);
