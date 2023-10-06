<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$busca = isset($_POST['term']) ? $_POST['term'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                        `id_b_s`,`bien_servicio` AS `label` 
                    FROM `seg_bien_servicio` 
                    WHERE `bien_servicio` LIKE '%$busca%' OR `id_b_s` LIKE '%$busca%' ORDER BY `bien_servicio` ASC";
    $rs = $cmd->query($sql);
    $bs_se = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
foreach ($bs_se as $bs) {
    $data[] = [
        'id' => $bs['id_b_s'],
        'label' => $bs['label'],
    ];
}
if (empty($data)) {
    $data[] = [
        'id' => '0',
        'label' => 'No se encontraron coincidencias...',
    ];
}
echo json_encode($data);
