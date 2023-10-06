<?php
include '../../../conexion.php';

$tabla = '<table border=1><tr>';
$tabla .= '<th>id_pedido</th>';
$tabla .= '<th>id_entrada</th>';
$tabla .= '<th>id_producto</th>';
$tabla .= '<th>bien_servicio</th>';
$tabla .= '<th>fecha_vence</th>';
$tabla .= '<th>lote</th>';
$tabla .= '<th>cantidad</th>';
$tabla .= '<th>consumido</th>';
$tabla .= '</tr>';
for ($i = 1; $i <= 298; $i++) {
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "SELECT 
                `t1`.`id_pedido`
                , `t1`.`id_entrada`
                , `t1`.`id_producto`
                , `t1`.`bien_servicio`
                , `t1`.`fecha_vence`
                , `t1`.`lote`
                , `t1`.`cantidad`
                , IFNULL(`t2`.`consumido`,0) AS `consumido` 
            FROM 
            (SELECT
                `seg_traslados_almacen`.`id_pedido`
                , `seg_detalles_traslado`.`id_entrada`
                , `seg_detalles_traslado`.`id_producto`
                , `seg_bien_servicio`.`bien_servicio`
                , `seg_detalle_entrada_almacen`.`fecha_vence`
                , `seg_detalle_entrada_almacen`.`lote`
                , `seg_detalles_traslado`.`cantidad`
            FROM
                `seg_detalles_traslado`
                INNER JOIN `seg_traslados_almacen` 
                    ON (`seg_detalles_traslado`.`id_traslado` = `seg_traslados_almacen`.`id_trasl_alm`)
                INNER JOIN `seg_bien_servicio` 
                    ON (`seg_detalles_traslado`.`id_producto` = `seg_bien_servicio`.`id_b_s`)
                INNER JOIN `seg_detalle_entrada_almacen` 
                    ON (`seg_detalles_traslado`.`id_entrada` = `seg_detalle_entrada_almacen`.`id_entrada`)
            WHERE (`seg_traslados_almacen`.`id_pedido` = $i)) AS `t1`
            LEFT JOIN 
            (SELECT
                `seg_salidas_almacen`.`id_entrada`
                , SUM(`seg_salidas_almacen`.`cantidad`) AS `consumido`
            FROM
                `seg_salidas_almacen`
                INNER JOIN `seg_salida_dpdvo` 
                    ON (`seg_salidas_almacen`.`id_devolucion` = `seg_salida_dpdvo`.`id_devolucion`)
            WHERE (`seg_salida_dpdvo`.`id_pedido` = $i)
            GROUP BY `seg_salidas_almacen`.`id_entrada`) AS `t2`
            ON (`t1`.`id_entrada` = `t2`.`id_entrada`) ORDER BY `t1`.`id_producto`";
        $rs = $cmd->query($sql);
        $bodegas = $rs->fetchAll(PDO::FETCH_ASSOC);
        foreach ($bodegas as $b) {
            $tabla .= '<tr>';
            $tabla .= '<td>' . $b['id_pedido'] . '</td>';
            $tabla .= '<td>' . $b['id_entrada'] . '</td>';
            $tabla .= '<td>' . $b['id_producto'] . '</td>';
            $tabla .= '<td>' . $b['bien_servicio'] . '</td>';
            $tabla .= '<td>' . $b['fecha_vence'] . '</td>';
            $tabla .= '<td>' . $b['lote'] . '</td>';
            $tabla .= '<td>' . $b['cantidad'] . '</td>';
            $tabla .= '<td>' . $b['consumido'] . '</td>';
            $tabla .= '</tr>';
        }
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}

$tabla .= '</table>';
echo $tabla;