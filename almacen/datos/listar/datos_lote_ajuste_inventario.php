<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$busca = isset($_POST['term']) ? $_POST['term'] : exit('Acción no permitida');
$id_prod = $_POST['id_prod'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

    $sql = "SELECT
                `seg_bien_servicio`.`id_b_s`
                , `seg_detalle_entrada_almacen`.`lote` AS `label` 
            FROM
                `seg_detalle_entrada_almacen`
                INNER JOIN `seg_bien_servicio` 
                    ON (`seg_detalle_entrada_almacen`.`id_prod` = `seg_bien_servicio`.`id_b_s`)
            WHERE `seg_detalle_entrada_almacen`.`lote` LIKE '%$busca%' AND `seg_bien_servicio`.`id_b_s`= '$id_prod' ORDER BY `bien_servicio` ASC";
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
