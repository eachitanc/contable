<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$busca = isset($_POST['term']) ? $_POST['term'] : exit('Acción no permitida');
$sede = $_POST['sede'];
$bodega = $_POST['bodega'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_prod`,`id_entrada`,`lote`,`cant_ingresa` FROM `seg_detalle_entrada_almacen` WHERE `lote` LIKE '%$busca%' AND `id_sede` = '$sede' AND `id_bodega` = '$bodega' ORDER BY `lote` ASC";
    $rs = $cmd->query($sql);
    $lotes = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_detalles_traslado`.`id_det_traslado`
                , `seg_detalles_traslado`.`id_entrada`
                , `seg_traslados_almacen`.`id_sede_entra`
                , `seg_traslados_almacen`.`id_bodega_entra`
                , `seg_detalle_entrada_almacen`.`lote`
                , `seg_detalles_traslado`.`id_producto`
                , `seg_detalles_traslado`.`cantidad`
            FROM
                `seg_detalles_traslado`
                INNER JOIN `seg_traslados_almacen` 
                    ON (`seg_detalles_traslado`.`id_traslado` = `seg_traslados_almacen`.`id_trasl_alm`)
                INNER JOIN `seg_detalle_entrada_almacen` 
                    ON (`seg_detalles_traslado`.`id_entrada` = `seg_detalle_entrada_almacen`.`id_entrada`)
            WHERE `seg_traslados_almacen`.`id_sede_entra` = '$sede' AND `seg_traslados_almacen`.`id_bodega_entra` = '$bodega' AND `lote` LIKE '%$busca%' ORDER BY `lote` ASC";
    $rs = $cmd->query($sql);
    $lotes_trasl = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (!empty($lotes)) {
    foreach ($lotes as $ls) {
        $data[] = [
            'id' => $ls['id_prod'],
            'label' => $ls['lote'],
            'max' => $ls['cant_ingresa'],
            'id_entrada' => $ls['id_entrada'],
        ];
    }
}
if (!empty($lotes_trasl)) {
    foreach ($lotes_trasl as $ls) {
        $key = array_search($ls['lote'], array_column($data, 'label'));
        if (!(false !== $key)) {
            $data[] = [
                'id' => $ls['id_producto'],
                'label' => $ls['lote'],
                'max' => $ls['cantidad'],
                'id_entrada' => $ls['id_entrada'],
            ];
        }
    }
}
if (empty($data)) {
    $data[] = [
        'id' => '0',
        'label' => 'No hay coincidencias...',
    ];
}
echo json_encode($data);
