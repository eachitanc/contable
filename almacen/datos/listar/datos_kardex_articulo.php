<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
$id_art = isset($_POST['id_articulo']) ? $_POST['id_articulo'] : exit('Acción no permitida');
$bodega = isset($_POST['bodega']) ? $_POST['bodega'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT * FROM 
                (SELECT
                    `seg_entrada_almacen`.`consecutivo`
                    , `seg_detalle_entrada_almacen`.`id_entrada`
                    , `seg_detalle_entrada_almacen`.`id_prod`
                    , `seg_detalle_entrada_almacen`.`id_bodega`
                    , `seg_bodega_almacen`.`nombre` AS `bodega`
                    , `seg_detalle_entrada_almacen`.`id_sede`
                    , `seg_sedes_empresa`.`nombre` AS `sede`
                    , IFNULL(`seg_entrada_almacen`.`id_tercero_api`,0) AS tercero
                    , CONCAT(`seg_entrada_almacen`.`no_factura`, ' - ',`seg_entrada_almacen`.`acta_remision`) AS `factura`
                    , `seg_detalle_entrada_almacen`.`cant_ingresa` AS `cantidad`
                    , `seg_detalle_entrada_almacen`.`valu_ingresa` +  `seg_detalle_entrada_almacen`.`valu_ingresa` * (`seg_detalle_entrada_almacen`.`iva` / 100) AS `valu_ingresa`
                    , `seg_detalle_entrada_almacen`.`lote`
                    , `seg_detalle_entrada_almacen`.`marca`
                    , `seg_detalle_entrada_almacen`.`invima`
                    , `seg_detalle_entrada_almacen`.`fecha_vence`
                    , `seg_detalle_entrada_almacen`.`fec_reg`
                    , `seg_tipo_entrada`.`descripcion` AS `tipo_entrada`
                    , '1' AS `tipo` 
                FROM
                    `seg_detalle_entrada_almacen`
                    INNER JOIN `seg_sedes_empresa` 
                        ON (`seg_detalle_entrada_almacen`.`id_sede` = `seg_sedes_empresa`.`id_sede`)
                    INNER JOIN `seg_bodega_almacen` 
                        ON (`seg_detalle_entrada_almacen`.`id_bodega` = `seg_bodega_almacen`.`id_bodega`)
                    INNER JOIN `seg_entrada_almacen` 
                        ON (`seg_detalle_entrada_almacen`.`id_entra` = `seg_entrada_almacen`.`id_entrada`)
                    INNER JOIN `seg_tipo_entrada` 
                        ON (`seg_entrada_almacen`.`id_tipo_entrada` = `seg_tipo_entrada`.`id_entrada`)
                WHERE (`seg_detalle_entrada_almacen`.`id_prod` = $id_art AND `seg_detalle_entrada_almacen`.`id_bodega` = $bodega)
                UNION ALL 
                SELECT
                    `seg_traslados_almacen`.`id_trasl_alm`
                    , `seg_detalles_traslado`.`id_entrada`
                    , `seg_detalles_traslado`.`id_producto`
                    , `seg_traslados_almacen`.`id_bodega_entra`
                    , `seg_bodega_almacen`.`nombre` 
                    , `seg_traslados_almacen`.`id_sede_entra`
                    , `seg_sedes_empresa`.`nombre`
                    , '0' AS `tercero`
                    , `seg_traslados_almacen`.`acta_remision`
                    , `seg_detalles_traslado`.`cantidad`
                    , `seg_detalle_entrada_almacen`.`valu_ingresa` +  `seg_detalle_entrada_almacen`.`valu_ingresa` * (`seg_detalle_entrada_almacen`.`iva` / 100) AS `valu_ingresa`
                    , `seg_detalle_entrada_almacen`.`lote`
                    , `seg_detalle_entrada_almacen`.`marca`
                    , `seg_detalle_entrada_almacen`.`invima`
                    , `seg_detalle_entrada_almacen`.`fecha_vence`
                    , `seg_detalles_traslado`.`fec_reg`
                    , 'TRASLADO' AS `tipo_entrada` 
                    , '2' AS `tipo` 
                FROM
                    `seg_detalles_traslado`
                    INNER JOIN `seg_traslados_almacen` 
                        ON (`seg_detalles_traslado`.`id_traslado` = `seg_traslados_almacen`.`id_trasl_alm`)
                    INNER JOIN `seg_sedes_empresa` 
                        ON (`seg_traslados_almacen`.`id_sede_sale` = `seg_sedes_empresa`.`id_sede`)
                    INNER JOIN `seg_bodega_almacen` 
                        ON (`seg_traslados_almacen`.`id_bodega_sale` = `seg_bodega_almacen`.`id_bodega`)
                    INNER JOIN `seg_detalle_entrada_almacen` 
                        ON (`seg_detalles_traslado`.`id_entrada` = `seg_detalle_entrada_almacen`.`id_entrada`)
                WHERE `seg_detalles_traslado`.`id_producto` = $id_art AND `seg_traslados_almacen`.`id_bodega_entra` = $bodega
                UNION ALL
                SELECT
                    `seg_traslados_almacen`.`id_trasl_alm`
                    ,`seg_detalles_traslado`.`id_entrada`
                    , `seg_detalles_traslado`.`id_producto`
                    , `seg_traslados_almacen`.`id_bodega_sale`
                    , `seg_bodega_almacen`.`nombre` 
                    , `seg_traslados_almacen`.`id_sede_sale`
                    , `seg_sedes_empresa`.`nombre`
                    , '0' AS `tercero`
                    , `seg_traslados_almacen`.`acta_remision`
                    , `seg_detalles_traslado`.`cantidad`
                    , `seg_detalle_entrada_almacen`.`valu_ingresa` +  `seg_detalle_entrada_almacen`.`valu_ingresa` * (`seg_detalle_entrada_almacen`.`iva` / 100) AS `valu_ingresa`
                    , `seg_detalle_entrada_almacen`.`lote`
                    , `seg_detalle_entrada_almacen`.`marca`
                    , `seg_detalle_entrada_almacen`.`invima`
                    , `seg_detalle_entrada_almacen`.`fecha_vence`
                    , `seg_detalles_traslado`.`fec_reg`
                    , 'TRASLADO' AS `tipo_entrada` 
                    , '3' AS `tipo` 
                FROM
                    `seg_detalles_traslado`
                    INNER JOIN `seg_traslados_almacen` 
                        ON (`seg_detalles_traslado`.`id_traslado` = `seg_traslados_almacen`.`id_trasl_alm`)
                    INNER JOIN `seg_sedes_empresa` 
                        ON (`seg_traslados_almacen`.`id_sede_entra` = `seg_sedes_empresa`.`id_sede`)
                    INNER JOIN `seg_bodega_almacen` 
                        ON (`seg_traslados_almacen`.`id_bodega_entra` = `seg_bodega_almacen`.`id_bodega`)
                    INNER JOIN `seg_detalle_entrada_almacen` 
                        ON (`seg_detalles_traslado`.`id_entrada` = `seg_detalle_entrada_almacen`.`id_entrada`)
                WHERE `seg_detalles_traslado`.`id_producto` = $id_art AND `seg_traslados_almacen`.`id_bodega_sale` = $bodega
                UNION ALL
                SELECT
                    `seg_salida_dpdvo`.`consecutivo`
                    , `seg_salidas_almacen`.`id_entrada`
                    , `seg_salidas_almacen`.`id_producto`
                    , `seg_pedidos_almacen`.`id_bodega`
                    , `seg_bodega_almacen`.`nombre`
                    , `seg_sedes_empresa`.`id_sede`
                    , `seg_sedes_empresa`.`nombre`
                    , IFNULL(`seg_salida_dpdvo`.`id_tercero_api`,0) AS `tercero`
                    , `seg_salida_dpdvo`.`acta_remision`
                    , `seg_salidas_almacen`.`cantidad`
                    , `seg_detalle_entrada_almacen`.`valu_ingresa` +  `seg_detalle_entrada_almacen`.`valu_ingresa` * (`seg_detalle_entrada_almacen`.`iva` / 100) AS `valu_ingresa`
                    , `seg_detalle_entrada_almacen`.`lote`
                    , `seg_detalle_entrada_almacen`.`marca`
                    , `seg_detalle_entrada_almacen`.`invima`
                    , `seg_detalle_entrada_almacen`.`fecha_vence`
                    , `seg_salidas_almacen`.`fec_reg`
                    , `seg_tipo_salidas`.`descripcion`
                    , '4' AS `tipo` 
                FROM
                    `seg_salidas_almacen`
                    INNER JOIN `seg_salida_dpdvo` 
                        ON (`seg_salidas_almacen`.`id_devolucion` = `seg_salida_dpdvo`.`id_devolucion`)
                    INNER JOIN `seg_tipo_salidas` 
                        ON (`seg_salida_dpdvo`.`id_tipo_salida` = `seg_tipo_salidas`.`id_salida`)
                    LEFT JOIN `seg_pedidos_almacen` 
                        ON (`seg_salida_dpdvo`.`id_pedido` = `seg_pedidos_almacen`.`id_pedido`)
                    INNER JOIN `seg_detalle_entrada_almacen` 
                        ON (`seg_salidas_almacen`.`id_entrada` = `seg_detalle_entrada_almacen`.`id_entrada`)
                    INNER JOIN `seg_bodega_almacen` 
                        ON (`seg_pedidos_almacen`.`id_bodega` = `seg_bodega_almacen`.`id_bodega`)
                    INNER JOIN `seg_sedes_empresa` 
                        ON (`seg_bodega_almacen`.`id_sede` = `seg_sedes_empresa`.`id_sede`)
                WHERE `seg_salidas_almacen`.`id_producto` = $id_art AND `seg_pedidos_almacen`.`id_bodega` = $bodega) AS `t1`
            ORDER BY `t1`.`fec_reg` ASC ";
    $rs = $cmd->query($sql);
    $movimientos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$id_t = [];
foreach ($movimientos as $l) {
    $id_t[] = $l['tercero'];
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
if ($terceros == '0') {
    $terceros = [];
}
$data = [];
if (!empty($movimientos)) {
    $exitencia = 0;
    foreach ($movimientos as $mvto) {
        $key = array_search($mvto['tercero'], array_column($terceros, 'id_tercero'));
        if ($key !== false) {
            $nombre = $terceros[$key]['nombre1'] . ' ' . $terceros[$key]['nombre2'] . ' ' . $terceros[$key]['apellido1'] . ' ' . $terceros[$key]['apellido2'] . ' ' . $terceros[$key]['razon_social'];
        } else {
            $nombre = '';
        }
        if ($mvto['tipo'] == 1 || $mvto['tipo'] == 2) {
            $exitencia += $mvto['cantidad'];
            $move = $mvto['tipo_entrada'];
            $movs = '';
        } else {
            $exitencia -= $mvto['cantidad'];
            $movs = $mvto['tipo_entrada'];
            $move = '';
        }
        $data[] = [
            'fec_reg' => $mvto['fec_reg'],
            'sede' => $mvto['sede'],
            'bodega' => $mvto['bodega'],
            'factura' => $mvto['factura'],
            'tercero' => $nombre,
            'entrada' => $move,
            'salida' => $movs,
            'cantidad' => $mvto['cantidad'],
            'val_uni' => '<div class="text-right">' . pesos($mvto['valu_ingresa']) . ' </div>',
            'lote' => $mvto['lote'],
            'fec_vence' => $mvto['fecha_vence'],
            'existencia' => $exitencia,
        ];
    }
}
$datos = ['data' => $data];
echo json_encode($datos);
