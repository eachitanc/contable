<?php
session_start();
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
    $terceros = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$idst = '0';
foreach ($terceros as $t) {
    $idst .= ',' . $t['id_tercero_api'];
}
//API URL
$url = $api . 'terceros/datos/res/datos/id/' . $idst;
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res_api = curl_exec($ch);
curl_close($ch);
$dat_ter = json_decode($res_api, true);
$tercero = [];
foreach ($dat_ter as $dt) {
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
