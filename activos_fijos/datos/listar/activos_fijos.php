<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$busca = isset($_POST['term']) ? $_POST['term'] : exit('Acción no permitida');
$condicion = '';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `seg_bien_servicio`.`id_b_s`
                , `seg_bien_servicio`.`bien_servicio`
            FROM
                `seg_tipo_bien_servicio`
                INNER JOIN `seg_tipo_contrata` 
                    ON (`seg_tipo_bien_servicio`.`id_tipo_cotrato` = `seg_tipo_contrata`.`id_tipo`)
                INNER JOIN `seg_bien_servicio` 
                    ON (`seg_bien_servicio`.`id_tipo_bn_sv` = `seg_tipo_bien_servicio`.`id_tipo_b_s`)
            WHERE `seg_tipo_contrata`.`id_tipo` = '7' AND `seg_bien_servicio`.`bien_servicio` LIKE '%$busca%'";
    $rs = $cmd->query($sql);
    $lotes = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
foreach ($lotes as $ls) {
    $data[] = [
        'id' => $ls['id_b_s'],
        'label' => $ls['bien_servicio'],
    ];
}
if (empty($data)) {
    $data[] = [
        'id' => '0',
        'label' => 'No hay coincidencias...',
    ];
}
echo json_encode($data);
