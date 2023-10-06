<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id = isset($_POST['id_3']) ? $_POST['id_3'] : exit('Acción no permitida');
$bodega = 1;
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
            LEFT JOIN `seg_marcas` 
                    ON (`seg_detalle_entrada_almacen`.`id_marca` = `seg_marcas`.`id_marca`)
            WHERE `seg_detalle_entrada_almacen`.`id_prod` IN ($id) AND `t1`.`existe` > 0
            ORDER BY `seg_detalle_entrada_almacen`.`fecha_vence` ASC";
    $rs = $cmd->query($sql);
    $existencias = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$tabla = '';
$tabla .= '<div class="overflow"><table class="table table-bordered table-striped table-hover table-sm" style="font-size:13px">';
$tabla .= '<thead>';
$tabla .= '<tr>';
$tabla .= '<th>Código</th>';
$tabla .= '<th>Lote</th>';
$tabla .= '<th>INVIMA</th>';
$tabla .= '<th>Vence</th>';
$tabla .= '<th>Existe</th>';
$tabla .= '<th>Transf.</th>';
$tabla .= '</tr>';
$tabla .= '</thead>';
$tabla .= '<tbody>';
if (!empty($existencias)) {
    foreach ($existencias as $existencia) {
        $input = '<input type="number" class="form-control form-control-sm altura xdisponiblex" name="canTrasforma[' . $existencia['id_entrada'] . ']" value="0" min="0" max="' . $existencia['existencia'] . '">';
        $tabla .= '<tr>';
        $tabla .= '<td>' . $existencia['id_entrada'] . '</td>';
        $tabla .= '<td>' . $existencia['lote'] . '</td>';
        $tabla .= '<td>' . $existencia['invima'] . '</td>';
        $tabla .= '<td>' . $existencia['fecha_vence'] . '</td>';
        $tabla .= '<td>' . $existencia['existencia'] . '</td>';
        $tabla .= '<td>' . $input . '</td>';
        $tabla .= '</tr>';
    }
} else {
    $tabla .= '<tr>';
    $tabla .= '<td colspan="6" class="text-center">No existen cantidades disponibles</td>';
    $tabla .= '</tr>';
}
$tabla .= '</tbody>';
$tabla .= '</table></div>';
echo $tabla;
