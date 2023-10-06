<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
$id_dev = isset($_POST['id_devDetalles']) ? $_POST['id_devDetalles'] : exit('Acción no permitida');
$vigencia = $_SESSION['vigencia'];
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_salidas_almacen`.`id_salida`
                , `seg_salidas_almacen`.`id_devolucion`
                , `seg_salidas_almacen`.`id_producto`
                , `seg_bien_servicio`.`bien_servicio`
                , `seg_tipo_salidas`.`descripcion`
                , `seg_salida_dpdvo`.`id_tercero_api`
                , `seg_salida_dpdvo`.`acta_remision`
                , `seg_salida_dpdvo`.`fec_acta_remision`
                , `seg_salidas_almacen`.`observacion`
                , `seg_salidas_almacen`.`cantidad`
                , `seg_salida_dpdvo`.`fec_acta_remision`
                , `seg_salidas_almacen`.`vigencia`
                , `seg_detalle_entrada_almacen`.`lote`
                , `seg_detalle_entrada_almacen`.`fecha_vence` AS `fec_vence`
                , `seg_salida_dpdvo`.`estado`
            FROM
                `seg_salidas_almacen`
                INNER JOIN `seg_bien_servicio` 
                    ON (`seg_salidas_almacen`.`id_producto` = `seg_bien_servicio`.`id_b_s`)
                INNER JOIN `seg_salida_dpdvo` 
                    ON (`seg_salidas_almacen`.`id_devolucion` = `seg_salida_dpdvo`.`id_devolucion`)
                INNER JOIN `seg_tipo_salidas` 
                    ON (`seg_salida_dpdvo`.`id_tipo_salida` = `seg_tipo_salidas`.`id_salida`)
                INNER JOIN `seg_detalle_entrada_almacen` 
                    ON  (`seg_salidas_almacen`.`id_entrada` = `seg_detalle_entrada_almacen`.`id_entrada`)
            WHERE `seg_salidas_almacen`.`id_devolucion` = $id_dev AND `seg_salida_dpdvo`.`vigencia` = '$vigencia'";
    $rs = $cmd->query($sql);
    $detalles_dev = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_salida_dpdvo`.`id_devolucion` 
                , `seg_detalle_entrada_almacen`.`id_prod`  AS `id_producto`
                , `seg_bien_servicio`.`bien_servicio` 
                , `seg_detalle_entrada_almacen`.`cant_ingresa` AS `cantidad`
                , '' AS `lote`
                , '' AS `observacion`
                , 2 AS `estado`
            FROM
                `seg_entrada_almacen`
                INNER JOIN `seg_salida_dpdvo` 
                    ON (`seg_entrada_almacen`.`id_devolucion` = `seg_salida_dpdvo`.`id_devolucion`)
                INNER JOIN `seg_detalle_entrada_almacen` 
                    ON (`seg_detalle_entrada_almacen`.`id_entra` = `seg_entrada_almacen`.`id_entrada`)
                INNER JOIN `seg_bien_servicio` 
                    ON (`seg_detalle_entrada_almacen`.`id_prod` = `seg_bien_servicio`.`id_b_s`)
            WHERE (`seg_salida_dpdvo`.`id_devolucion` = $id_dev AND  `seg_salida_dpdvo`.`estado` <3)";
    $rs = $cmd->query($sql);
    $fianza = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$data = [];
if (empty($fianza)) {

    if (!empty($detalles_dev)) {
        foreach ($detalles_dev as $d) {
            $id_salida = isset($d['id_salida']) ? $d['id_salida'] : 0;
            $editar = $borrar = null;
            $data[] = [
                "id_prod" => $d['id_producto'],
                "prod" => $d['bien_servicio'],
                "cantidad" => $d['cantidad'],
                "lote" => $d['lote'],
                "fec_vence" => $d['fec_vence'],
                "accion" => '<div class="text-center">' . $editar . $borrar . '</div>',
            ];
        }
    }
} else {
    $bodega  = 1;
    $ids = [];
    foreach ($fianza as $dt) {
        $ids[] = $dt['id_producto'];
    }
    $proveedor = '(SELECT
                        `id_entrada`
                        , `cant_ingresa`
                    FROM
                        `seg_detalle_entrada_almacen`
                    WHERE (`id_bodega` = ' . $bodega . ')) AS `proveedor`
                    LEFT JOIN';
    $suma = 'IFNULL(`proveedor`.`cant_ingresa`,0) + IFNULL(`entradas`.`cant_entra`,0)';
    $on = 'ON (`proveedor`.`id_entrada` = `entradas`.`id_entrada`)';
    $tabla = '`proveedor`';
    $ids = implode(',', $ids);
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "SELECT 
                    `t1`.`id_entrada`
                    , CASE WHEN `t1`.`existe`  < 0 THEN 0 ELSE `t1`.`existe` END AS `existencia`
                    , `seg_marcas`.`descripcion`
                    , `seg_detalle_entrada_almacen`.`id_prod`
                    , `seg_detalle_entrada_almacen`.`lote`
                    , `seg_detalle_entrada_almacen`.`id_lote`
                    , `seg_detalle_entrada_almacen`.`invima`
                    , `seg_detalle_entrada_almacen`.`fecha_vence`
                FROM 
                (SELECT
                    $tabla.`id_entrada`
                    , ($suma - IFNULL(`salidas`. `cant_sale`,0) - IFNULL(`consumo`.`cant_consume`,0)) AS `existe` 
                FROM
                    $proveedor
                    (SELECT
                        `seg_detalles_traslado`.`id_entrada`
                        , SUM(`seg_detalles_traslado`.`cantidad`) AS `cant_entra`
                    FROM
                        `seg_traslados_almacen`
                        LEFT JOIN `seg_detalles_traslado` 
                            ON (`seg_detalles_traslado`.`id_traslado` = `seg_traslados_almacen`.`id_trasl_alm`)
                    WHERE (`seg_detalles_traslado`.`estado` > 0 AND `seg_traslados_almacen`.`id_bodega_entra` = $bodega)
                    GROUP BY `seg_detalles_traslado`.`id_entrada`) AS `entradas`
                        $on
                LEFT JOIN 
                    (SELECT
                        `seg_detalles_traslado`.`id_entrada`
                        , SUM(`seg_detalles_traslado`.`cantidad`) AS `cant_sale`
                    FROM
                        `seg_traslados_almacen`
                        LEFT JOIN `seg_detalles_traslado` 
                            ON (`seg_detalles_traslado`.`id_traslado` = `seg_traslados_almacen`.`id_trasl_alm`)
                    WHERE (`seg_detalles_traslado`.`estado` > 0 AND `seg_traslados_almacen`.`id_bodega_sale` = $bodega)
                    GROUP BY `seg_detalles_traslado`.`id_entrada`) AS `salidas`
                        ON ($tabla.`id_entrada` = `salidas`.`id_entrada`)
                LEFT JOIN 
                    (SELECT
                        `seg_salidas_almacen`.`id_entrada`
                        , SUM(`seg_salidas_almacen`.`cantidad`) AS `cant_consume`
                    FROM
                        `seg_salidas_almacen`
                        INNER JOIN `seg_salida_dpdvo` 
                            ON (`seg_salidas_almacen`.`id_devolucion` = `seg_salida_dpdvo`.`id_devolucion`)
                        INNER JOIN `seg_tipo_salidas` 
                            ON (`seg_salida_dpdvo`.`id_tipo_salida` = `seg_tipo_salidas`.`id_salida`)
                        INNER JOIN `seg_pedidos_almacen` 
                            ON (`seg_salida_dpdvo`.`id_pedido` = `seg_pedidos_almacen`.`id_pedido`)
                    WHERE (`seg_salidas_almacen`.`estado` > 0 AND `seg_pedidos_almacen`.`id_bodega` = $bodega)
                    GROUP BY `seg_salidas_almacen`.`id_entrada`) AS `consumo` 
                        ON ($tabla.`id_entrada` = `consumo`.`id_entrada`)) AS `t1`
                INNER JOIN `seg_detalle_entrada_almacen`
                    ON (`seg_detalle_entrada_almacen`.`id_entrada` = `t1`.`id_entrada`)
                INNER JOIN `seg_marcas` 
                        ON (`seg_detalle_entrada_almacen`.`id_marca` = `seg_marcas`.`id_marca`)
                WHERE `seg_detalle_entrada_almacen`.`id_prod` IN ($ids)
                ORDER BY `seg_detalle_entrada_almacen`.`fecha_vence` ASC";
        $rs = $cmd->query($sql);
        $existencias = $rs->fetchAll(PDO::FETCH_ASSOC);
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    foreach ($fianza as $det) {
        $id_producto = $det['id_producto'];
        $entregar = null;
        $filtro = [];
        $filtro = array_filter($existencias, function ($existencias) use ($id_producto) {
            return $existencias["id_prod"] == $id_producto;
        });
        if (empty($filtro)) {
            $filtro[] = [
                'existencia' => 0,
                'lote' => '',
                'invima' => '',
                'fecha_vence' => '',
                'id_entrada' => '-1',
            ];
        }
        $valida = true;
        $cuenta = 0;
        $cantidad = $det['cantidad'];
        foreach ($filtro as $f) {
            $lote = $f['lote'];
            $vence = $f['fecha_vence'];
            $diponible = $f['existencia'];
            $cantidadaux = $cantidad;
            if ($cantidad > 0) {
                $cantidadaux = $cantidadaux - $diponible;
                if ($cantidadaux > 0) {
                    $pasa =  $diponible;
                    $cantidad = $cantidad - $diponible;
                } else if ($cantidadaux <= 0) {
                    $pasa = $cantidad;
                    $cantidad = 0;
                }
            } else {
                $pasa = 0;
            }
            $ent = '';
            $entregar = '
                <div class="input-group input-group-sm" style="scale:0.9">
                    <div class="input-group-prepend">
                        <button class="btn btn-outline-secondary editcantidad" type="button" title="Abrir"><span class="fas fa-box"></span></button>
                    </div>
                    <input prod="' . $id_producto . '" type="number" name="cantxprod[' . $f['id_entrada'] . '|' . $id_producto . ']" min="0" max="' . $f['existencia'] . '" class="form-control" style="width:1rem;" readonly value="' . $pasa . '">
                    <div class="input-group-append">
                        <span class="input-group-text">' . str_pad($f['existencia'], 3, "0", STR_PAD_LEFT) . '</span>
                    </div>
                </div>';
            if ($cuenta == 0) {
                $cant_pedido = $det['cantidad'];
                $entregar .= '<input type="hidden" id="' . $id_producto . '" value="' . $cant_pedido . '">';
            } else {
                $cant_pedido = '';
            }
            $data[] = [
                "id_prod" => $id_producto,
                "prod" => mb_strtoupper($det['bien_servicio']),
                "cantidad" => $cant_pedido,
                "lote" => $lote,
                "fec_vence" => '<div class="text-center centro-vertical">' . $vence . '</div>',
                "accion" => '<div class="text-center centro-vertical">' . $entregar . '</div>',
            ];
            $cuenta++;
        }
    }
}
$datos = ['data' => $data];
echo json_encode($datos);
