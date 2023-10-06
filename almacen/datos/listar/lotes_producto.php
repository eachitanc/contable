<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_producto = isset($_POST['id_producto']) ? $_POST['id_producto'] : exit('Acceso denegado');
$id_bodega = $_POST['id_bodega'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `id_entrada`, `id_prod`, `valu_ingresa`, `iva`, `lote`, `marca`, `invima`, `fecha_vence`, `queda`  
            FROM 
                (SELECT
                    `seg_detalle_entrada_almacen`.`id_entrada`
                    , `seg_detalle_entrada_almacen`.`id_prod`
                    , `seg_detalle_entrada_almacen`.`valu_ingresa`
                    , `seg_detalle_entrada_almacen`.`iva`
                    , `seg_detalle_entrada_almacen`.`lote`
                    , `seg_detalle_entrada_almacen`.`marca`
                    , `seg_detalle_entrada_almacen`.`invima`
                    , `seg_detalle_entrada_almacen`.`fecha_vence`
                    , `seg_detalle_entrada_almacen`.`cant_ingresa` - IFNULL(`t1`.`cantidad`,0) AS `queda`
                FROM
                    `seg_detalle_entrada_almacen`
                    INNER JOIN `seg_entrada_almacen` 
                        ON (`seg_detalle_entrada_almacen`.`id_entra` = `seg_entrada_almacen`.`id_entrada`)
                    INNER JOIN `seg_bien_servicio` 
                        ON (`seg_detalle_entrada_almacen`.`id_prod` = `seg_bien_servicio`.`id_b_s`)
                    INNER JOIN `seg_tipo_bien_servicio` 
                        ON (`seg_bien_servicio`.`id_tipo_bn_sv` = `seg_tipo_bien_servicio`.`id_tipo_b_s`)
                    INNER JOIN `seg_tipo_contrata` 
                        ON (`seg_tipo_bien_servicio`.`id_tipo_cotrato` = `seg_tipo_contrata`.`id_tipo`)
                    LEFT JOIN
                            (SELECT
                                `seg_detalles_traslado`.`id_entrada`
                                , SUM(`seg_detalles_traslado`.`cantidad`) AS `cantidad`
                            FROM
                                `seg_detalles_traslado`
                                INNER JOIN `seg_traslados_almacen` 
                                    ON (`seg_detalles_traslado`.`id_traslado` = `seg_traslados_almacen`.`id_trasl_alm`)
                            WHERE (`seg_traslados_almacen`.`id_bodega_entra` <> $id_bodega)
                            GROUP BY `seg_detalles_traslado`.`id_entrada`) AS `t1`
                        ON (`t1`.`id_entrada` = `seg_detalle_entrada_almacen`.`id_entrada`)
                WHERE `seg_tipo_bien_servicio`.`id_tipo_cotrato`  = 17 AND `seg_detalle_entrada_almacen`.`cant_ingresa` - IFNULL(`t1`.`cantidad`,0) >= 0
                ORDER BY `seg_tipo_bien_servicio`.`tipo_bn_sv`,`seg_bien_servicio`.`bien_servicio`,`seg_detalle_entrada_almacen`.`lote` DESC) AS `tb`
            WHERE `tb`. `id_prod` = $id_producto";
    $rs = $cmd->query($sql);
    $lotes = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$data = [];
if (count($lotes) > 0) {
    $total = 0;
    foreach ($lotes as $l) {
        $data[] = [
            'id' => $l['id_entrada'],
            'lote' => $l['lote'],
            'marca' => $l['marca'],
            'invima' => $l['invima'],
            'fecha' => $l['fecha_vence'],
            'cantidad' => '<div class="insertInput">' . $l['queda'] . '</div><input type="hidden" class="form-control form-control-sm altura ajustar" name="ajuste[' . $l['id_entrada'] . ']" value="' . $l['queda'] . '"><input type="hidden" name="queda[' . $l['id_entrada'] . ']" value="' . $l['queda'] . '">',
        ];
        $total += $l['queda'];
    }
    $data[] = [
        'id' => '',
        'lote' => '',
        'marca' => '',
        'invima' => '',
        'fecha' => '<b>TOTAL</b>',
        'cantidad' => $total . '<input type="hidden" name="total" id="total" value="' . $total . '">',
    ];
}
$datos = ['data' => $data];
echo json_encode($datos);
