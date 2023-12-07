<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
$datas = isset($_POST['id_pd']) ? explode('|', $_POST['id_pd']) : exit('Acción no permitida');
$id_pd = $datas[0];
$tipo = $datas[1];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `id_entrada`,`id_tercero_api`,`acta_remision`, `observacion`, `id_devolucion`
            FROM `seg_entrada_almacen`
            WHERE `id_entrada` = '$id_pd'";
    $rs = $cmd->query($sql);
    $data_pd = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$remision = $data_pd['acta_remision'];
$detalle = $data_pd['observacion'];
$id_salida = $data_pd['id_devolucion'] == '' ? 0 : $data_pd['id_devolucion'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_detalle_entrada_almacen`.`id_entrada`
                , `seg_detalle_entrada_almacen`.`id_prod`
                , `seg_bien_servicio`.`bien_servicio`
                , `seg_detalle_entrada_almacen`.`id_tercero_api`
                , `seg_detalle_entrada_almacen`.`id_tipo_entrada`
                , `seg_detalle_entrada_almacen`.`id_entra`
                , `seg_detalle_entrada_almacen`.`cant_ingresa`
                , `seg_detalle_entrada_almacen`.`valu_ingresa`
                , `seg_detalle_entrada_almacen`.`val_prom`
                , `seg_detalle_entrada_almacen`.`lote`
                , `seg_detalle_entrada_almacen`.`fecha_vence`
                , `seg_detalle_entrada_almacen`.`iva`
                , `seg_entrada_almacen`.`id_devolucion`
            FROM
            `seg_entrada_almacen`
                LEFT JOIN `seg_detalle_entrada_almacen` 
                    ON (`seg_detalle_entrada_almacen`.`id_entra` = `seg_entrada_almacen`.`id_entrada`)
                LEFT JOIN `seg_bien_servicio` 
                    ON (`seg_detalle_entrada_almacen`.`id_prod` = `seg_bien_servicio`.`id_b_s`)
            WHERE  `seg_entrada_almacen`.`id_entrada` = '$id_pd'";
    $rs = $cmd->query($sql);
    $entradas = $rs->fetchAll();
    $sql = "SELECT `estado`  FROM `seg_entrada_almacen`WHERE `id_entrada` = '$id_pd'";
    $rs = $cmd->query($sql);
    $status = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `t1`.`id_devolucion` 
                , `t1`.`id_producto`
                , `seg_bien_servicio`.`bien_servicio`
                , `t1`.`total`
                , IFNULL(`t2`.`recibido`,0) AS `recibido`
            FROM (SELECT
                        `seg_salida_dpdvo`.`id_devolucion`
                        , `seg_salidas_almacen`.`id_producto`
                        , `seg_salidas_almacen`.`cantidad` AS `total`
                    FROM
                        `seg_salidas_almacen`
                        INNER JOIN `seg_salida_dpdvo` 
                            ON (`seg_salidas_almacen`.`id_devolucion` = `seg_salida_dpdvo`.`id_devolucion`)
                    WHERE (`seg_salida_dpdvo`.`id_devolucion` = $id_salida)) AS `t1`
            INNER JOIN `seg_bien_servicio`
                ON (`t1`.`id_producto` = `seg_bien_servicio`.`id_b_s`)
            LEFT JOIN
            (SELECT
                `seg_detalle_entrada_almacen`.`id_prod`
                , SUM(`seg_detalle_entrada_almacen`.`cant_ingresa`) AS `recibido`  
            FROM
                `seg_detalle_entrada_almacen`
                INNER JOIN `seg_entrada_almacen` 
                    ON (`seg_detalle_entrada_almacen`.`id_entra` = `seg_entrada_almacen`.`id_entrada`)
            WHERE (`seg_entrada_almacen`.`id_devolucion` = $id_salida)
            GROUP BY `seg_detalle_entrada_almacen`.`id_prod`) AS `t2`
            ON (`t1`.`id_producto` = `t2`.`id_prod`)";
    $rs = $cmd->query($sql);
    $salida = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$total = 0;
$subtotal = 0;
$iva = 0;
$data = [];
if (!($tipo == 2 || $tipo == 7)) {
    if (!empty($entradas)) {
        foreach ($entradas as $e) {
            $id_entrada = $e['id_entrada'];
            $editar = $borrar = null;
            if ($permisos['editar'] == 1) {
                $editar = '<a value="' . $id_entrada . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Actualizar o modificar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            }
            if ($permisos['borrar'] == 1) {
                $borrar = '<a value="' . $id_entrada . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
            }
            if ($status['estado'] > 2) {
                $editar = null;
                $borrar = null;
            }

            $subtotal = $subtotal + $e['cant_ingresa'] * $e['valu_ingresa'];
            $iva = $iva + ($e['cant_ingresa'] * $e['valu_ingresa']) * ($e['iva'] / 100);
            $total = $subtotal + $iva;
            $data[] = [
                'id_pd' => $e['id_prod'],
                'bien_servi' => $e['bien_servicio'],
                'detalle' => $detalle,
                'cant_ingresa' => $e['cant_ingresa'],
                'valu_ingresa' => '<div class="text-right">' . pesos($e['valu_ingresa']) . '</div>',
                'iva' => '<div class="text-right">' . $e['iva'] . '%</div>',
                'subtotalsiniva' => '<div class="text-right">' . pesos($e['cant_ingresa'] * $e['valu_ingresa']) . '</div>',
                'subtotalconiva' => '<div class="text-right">' . pesos($e['cant_ingresa'] * ($e['valu_ingresa'] + $e['valu_ingresa'] * ($e['iva'] / 100))) . '</div>',
                'lote' => $e['lote'],
                'fecha_vence' => $e['fecha_vence'],
                'botones' => '<div class="text-center">' . $editar . $borrar . '</div>',
            ];
        }
        $data[] = [
            'id_pd' => 0,
            'bien_servi' => '',
            'acta_remision' => '',
            'cant_ingresa' => '',
            'valu_ingresa' => '',
            'iva' => '',
            'subtotalsiniva' => '',
            'subtotalconiva' => '',
            'lote' => '',
            'fecha_vence' => '<div class="text-left"><b>SUBTOTAL FACTURA</b></div>',
            'botones' => '<div class="text-right">' . pesos($subtotal) . '</div>',
        ];
        $data[] = [
            'id_pd' => 00,
            'bien_servi' => '',
            'acta_remision' => '',
            'cant_ingresa' => '',
            'valu_ingresa' => '',
            'iva' => '',
            'subtotalsiniva' => '',
            'subtotalconiva' => '',
            'lote' => '',
            'fecha_vence' => '<div class="text-left"><b>IVA FACTURA</b></div>',
            'botones' => '<div class="text-right" style = "border-bottom: 3px double black;">' . pesos($iva) . '</div>',
        ];
        $data[] = [
            'id_pd' => 000,
            'bien_servi' => '',
            'acta_remision' => '',
            'cant_ingresa' => '',
            'valu_ingresa' => '',
            'iva' => '',
            'subtotalsiniva' => '',
            'subtotalconiva' => '',
            'lote' => '',
            'fecha_vence' => '<div class="text-left"><b>TOTAL FACTURA</b></div>',
            'botones' => '<div class="text-right ">' . pesos($total) . '</div>',
        ];
    }
} else {
    foreach ($salida as $s) {
        $id_producto = $s['id_producto'];
        $key = array_search($id_producto, array_column($entradas, 'id_prod'));
        $id_entrada = $key !== false ? $entradas[$key]['id_entrada'] : 0;
        $editar = $borrar = null;
        if ($permisos['borrar'] == 1) {
            $borrar = '<a value="' . $id_entrada . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }

        $subtotal =  $key !== false ? $subtotal + $entradas[$key]['cant_ingresa'] * $entradas[$key]['valu_ingresa'] : 0;
        $iva =  $key !== false ? $iva + ($entradas[$key]['cant_ingresa'] * $entradas[$key]['valu_ingresa']) * ($entradas[$key]['iva'] / 100) : 0;
        $total = $subtotal + $iva;
        $val_ingresa = $key !== false ? $entradas[$key]['valu_ingresa'] : 0;
        $cant_ingresa = $key !== false ? $entradas[$key]['cant_ingresa'] : 0;
        $cantidad =  $cant_ingresa . ' =>' . $s['recibido'] . ' DE ' . $s['total'];
        $iva = $key !== false ? $entradas[$key]['iva'] : 0;
        $lote = $key !== false ? $entradas[$key]['lote'] : '';
        $vence = $key !== false ? $entradas[$key]['fecha_vence'] : '';
        $max = $s['total'] - $s['recibido'];
        if ($permisos['editar'] == 1) {
            $editar = '<a value="' . $id_entrada . '|' . $id_producto . '|' . $max . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Actualizar o modificar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if ($status['estado'] > 2) {
            $editar = null;
            $borrar = null;
        }
        $data[] = [
            'id_pd' => $id_producto,
            'bien_servi' => $s['bien_servicio'],
            'detalle' => $detalle,
            'cant_ingresa' => $cantidad,
            'valu_ingresa' => '<div class="text-right">' . pesos($val_ingresa) . '</div>',
            'iva' => '<div class="text-right">' . $iva . '%</div>',
            'subtotalsiniva' => '<div class="text-right">' . pesos($cant_ingresa * $val_ingresa) . '</div>',
            'subtotalconiva' => '<div class="text-right">' . pesos($cant_ingresa * ($val_ingresa + $val_ingresa * ($iva / 100))) . '</div>',
            'lote' => $lote,
            'fecha_vence' => $vence,
            'botones' => '<div class="text-center">' . $editar . $borrar . '</div>',
        ];
    }
    $data[] = [
        'id_pd' => 0,
        'bien_servi' => '',
        'acta_remision' => '',
        'cant_ingresa' => '',
        'valu_ingresa' => '',
        'iva' => '',
        'subtotalsiniva' => '',
        'subtotalconiva' => '',
        'lote' => '',
        'fecha_vence' => '<div class="text-left"><b>SUBTOTAL FACTURA</b></div>',
        'botones' => '<div class="text-right">' . pesos($subtotal) . '</div>',
    ];
    $data[] = [
        'id_pd' => 00,
        'bien_servi' => '',
        'acta_remision' => '',
        'cant_ingresa' => '',
        'valu_ingresa' => '',
        'iva' => '',
        'subtotalsiniva' => '',
        'subtotalconiva' => '',
        'lote' => '',
        'fecha_vence' => '<div class="text-left"><b>IVA FACTURA</b></div>',
        'botones' => '<div class="text-right" style = "border-bottom: 3px double black;">' . pesos($iva) . '</div>',
    ];
    $data[] = [
        'id_pd' => 000,
        'bien_servi' => '',
        'acta_remision' => '',
        'cant_ingresa' => '',
        'valu_ingresa' => '',
        'iva' => '',
        'subtotalsiniva' => '',
        'subtotalconiva' => '',
        'lote' => '',
        'fecha_vence' => '<div class="text-left"><b>TOTAL FACTURA</b></div>',
        'botones' => '<div class="text-right ">' . pesos($total) . '</div>',
    ];
}
$datos = ['data' => $data];

echo json_encode($datos);
