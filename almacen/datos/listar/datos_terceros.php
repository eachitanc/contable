<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$buscar = isset($_GET['term']) ? $_GET['term'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_tercero_api` FROM `seg_terceros`";
    $rs = $cmd->query($sql);
    $terceros_api = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$id_t = [];
foreach ($terceros_api as $l) {
    $id_t[] = $l['id_tercero_api'];
}
$payload = json_encode($id_t);
//API URL
$url = $api . 'terceros/datos/res/lista/terceros';
$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
$terceros = json_decode($result, true);
$tercero = [];
foreach ($terceros as $dt) {
    $tercero[] = [
        'id' =>  $dt['id_tercero'],
        'nombre' => $dt['apellido1'] . ' ' . $dt['apellido2'] . ' ' . $dt['nombre1'] . ' ' . $dt['nombre2'] . ' ' . $dt['razon_social'],
    ];
}
$data = [];

if ($buscar == '%%') {
    foreach ($tercero as $t) {
        $data[] = [
            'id' => $t['id'],
            'label' => $t['nombre']
        ];
    }
} else {
    foreach ($tercero as $t) {
        $key = stristr($t['nombre'], $buscar);
        if ($key !== false) {
            $data[] = [
                'id' => $t['id'],
                'label' => $t['nombre']
            ];
        }
    }
}

if (!empty($data)) {
    echo json_encode($data);
}
