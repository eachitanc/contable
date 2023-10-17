<?php

use Sabberworm\CSS\Value\Value;

session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
include '../../conexion.php';
$id_pedido = $_POST['id'];
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
// consulto el nombre de la empresa de la tabla seg_empresas
try {
    $sql = "SELECT `nombre`, `nit`, `dig_ver` FROM `seg_empresas`";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `seg_detalles_traslado`.`cantidad`
                , `seg_detalles_traslado`.`observacion`
                , `seg_detalles_traslado`.`id_producto`
                , `seg_bien_servicio`.`bien_servicio`
                , `seg_tipo_bien_servicio`.`id_tipo_b_s`
                , `seg_bien_servicio`.`id_tipo_bn_sv`
                , `seg_tipo_bien_servicio`.`tipo_bn_sv`
                , `seg_traslados_almacen`.`id_pedido`
                , `seg_traslados_almacen`.`id_user_reg`
                , `seg_traslados_almacen`.`fec_reg`
                , `seg_detalles_traslado`.`id_entrada`
                , `seg_detalles_traslado`.`id_traslado`
                , `seg_detalle_entrada_almacen`.`id_entra`
                , `seg_detalle_entrada_almacen`.`valu_ingresa`
                , `seg_detalle_entrada_almacen`.`iva`
                , `seg_detalle_entrada_almacen`.`lote`
                , `seg_marcas`.`descripcion` as `marca`
                , `seg_detalle_entrada_almacen`.`invima`
                , `seg_detalle_entrada_almacen`.`fecha_vence`
            FROM
                `seg_detalles_traslado`
                INNER JOIN `seg_traslados_almacen` 
                    ON (`seg_detalles_traslado`.`id_traslado` = `seg_traslados_almacen`.`id_trasl_alm`)
                INNER JOIN `seg_bien_servicio` 
                    ON (`seg_detalles_traslado`.`id_producto` = `seg_bien_servicio`.`id_b_s`)
                INNER JOIN `seg_tipo_bien_servicio` 
                    ON (`seg_bien_servicio`.`id_tipo_bn_sv` = `seg_tipo_bien_servicio`.`id_tipo_b_s`)
                INNER JOIN `seg_detalle_entrada_almacen` 
                    ON (`seg_detalles_traslado`.`id_entrada` = `seg_detalle_entrada_almacen`.`id_entrada`)
                LEFT JOIN `seg_marcas` 
                    ON (`seg_detalle_entrada_almacen`.`id_marca` = `seg_marcas`.`id_marca`)
            WHERE (`seg_traslados_almacen`.`id_pedido` = $id_pedido)
            ORDER BY `seg_tipo_bien_servicio`.`tipo_bn_sv`,`seg_bien_servicio`.`bien_servicio` ASC";
    $res = $cmd->query($sql);
    $datos = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$user = $datos[0]['id_user_reg'];
try {
    $sql = "SELECT
                CONCAT_WS(' ', `nombre1`, `nombre2`, `apellido1`, `apellido2`) AS `nombre`
            FROM
                `seg_usuarios`
            WHERE `id_usuario` = $user";
    $res = $cmd->query($sql);
    $usuario = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `seg_pedidos_almacen`.`id_pedido`
                , `seg_pedidos_almacen`.`id_bodega`
                , `seg_detalle_pedido`.`id_producto`
                , `seg_detalle_pedido`.`cantidad`
                , `seg_pedidos_almacen`.`fec_reg`
                , `seg_pedidos_almacen`.`fec_cierre`
                , `seg_bodega_almacen`.`nombre` as `bodega`
                , `seg_pedidos_almacen`.`id_user_reg`
                , CONCAT_WS(' ', `seg_usuarios`.`nombre1`, `seg_usuarios`.`nombre2`, `seg_usuarios`.`apellido1`, `seg_usuarios`.`apellido2`) AS `nombre`
            FROM
                `seg_detalle_pedido`
                INNER JOIN `seg_pedidos_almacen` 
                    ON (`seg_detalle_pedido`.`id_pedido` = `seg_pedidos_almacen`.`id_pedido`)
                INNER JOIN `seg_bodega_almacen` 
                    ON (`seg_pedidos_almacen`.`id_bodega` = `seg_bodega_almacen`.`id_bodega`)
                INNER JOIN `seg_usuarios` 
                    ON (`seg_detalle_pedido`.`id_user_reg` = `seg_usuarios`.`id_usuario`)
            WHERE (`seg_pedidos_almacen`.`id_pedido`  = $id_pedido)";
    $res = $cmd->query($sql);
    $pedido = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$datas = [];
$consec = 0;
foreach ($datos as $fila) {
    $tps = $fila['tipo_bn_sv'];
    $bs = $fila['bien_servicio'];
    $lt = $fila['lote'];
    if ($lt == '') {
        $lt = 'EACII' . $consec;
        $consec++;
    }
    $sumar = isset($datas[$tps][$bs][$lt]['cantd']) ? $datas[$tps][$bs][$lt]['cantd'] : 0;
    $costo = $fila['valu_ingresa'] + $fila['valu_ingresa'] * $fila['iva'] / 100;
    $datas[$tps][$bs][$lt]['cantd'] = $fila['cantidad'] + $sumar;
    $datas[$tps][$bs][$lt]['datos']['costo'] =  $costo;
    $datas[$tps][$bs][$lt]['datos']['valin'] =  $fila['valu_ingresa'];
    $datas[$tps][$bs][$lt]['datos']['vence'] =  $fila['fecha_vence'];
    $datas[$tps][$bs][$lt]['datos']['id_bn'] =  $fila['id_producto'];
    $datas[$tps][$bs][$lt]['datos']['id_tb'] =  $fila['id_tipo_b_s'];
    $datas[$tps][$bs][$lt]['datos']['invima'] = $fila['invima'];
    $datas[$tps][$bs][$lt]['datos']['marca'] =  $fila['marca'];
    $datas[$tps][$bs][$lt]['datos']['iva'] =  $fila['iva'];
}
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
?>
<div class="text-right py-3">
    <div>
        <a type="" id="btnReporteGral" class="btn btn-outline-success btn-sm" value="01" title="Exprotar a Excel">
            <span class="fas fa-file-excel fa-lg" aria-hidden="true"></span>
        </a>
        <a type="button" class="btn btn-primary btn-sm" title="Imprimir" onclick="imprSelecTes('areaImprimir',<?php echo 0; ?>);"><span class="fas fa-print fa-lg" aria-hidden="true"></span></a>
        <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" title="Cerrar"><span class="fas fa-times fa-lg" aria-hidden="true"></span></a>
    </div>
</div>
<div class="contenedor bg-light" id="areaImprimir">
    <style>
        @media print {
            body {
                font-family: Arial, sans-serif;
            }
        }

        .resaltar:nth-child(even) {
            background-color: #F8F9F9;
        }

        .resaltar:nth-child(odd) {
            background-color: #ffffff;
        }
    </style>
    <div class="p-4 text-left">
        <table class="page_break_avoid" style="width:100% !important;">
            <thead style="background-color: white !important;font-size:80%">
                <tr style="padding: bottom 3px; color:black">
                    <td colspan="10">
                        <table style="width:100% !important;">
                            <tr>
                                <td rowspan="4" class='text-center' style="width:18%"><label class="small"><img src="<?php echo $_SESSION['urlin'] ?>/images/logos/logo.png" width="100"></label></td>
                                <td colspan="9" style="text-align:center">
                                    <strong><?php echo $empresa['nombre']; ?> </strong>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="9" style="text-align:center">
                                    NIT <?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6" rowspan="2">
                                    <b>TRASLADO No: <?php echo str_pad($datos[0]['id_traslado'], 5, "0", STR_PAD_LEFT) ?>
                                </td>
                                <td colspan="3" style="text-align: right;">Fecha Doc: <?php echo date('Y/m/d', strtotime($datos[0]['fec_reg'])) ?></td>
                            </tr>
                            <tr>
                                <td colspan="3" style="text-align: right;">Fecha Imp: <?php echo date('Y/m/d', strtotime($datos[0]['fec_reg'])) ?></td>
                            </tr>
                            <tr style="font-size: 85%;">
                                <td colspan="3">
                                    GENERA: ALMACÉN GENERAL
                                </td>
                                <td colspan="7">
                                    SOLICITA: <?php echo $pedido[0]['bodega'] ?>
                                </td>
                            </tr>
                            <tr style="font-size: 85%;">
                                <td colspan="10">
                                    PEDIDO No.: <?php echo str_pad($id_pedido, 5, "0", STR_PAD_LEFT) ?> Fecha: <?php echo date('Y/m/d', strtotime($pedido[0]['fec_cierre'] == '' ? $pedido[0]['fec_reg'] : $pedido[0]['fec_cierre'])) ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr style="background-color: #CED3D3; text-align:center">
                    <th rowspan="2">ID</th>
                    <th rowspan="2">Producto</th>
                    <th rowspan="2">Invima</th>
                    <th rowspan="2">Vence</th>
                    <th rowspan="2">Lote</th>
                    <th rowspan="2">Marca</th>
                    <th colspan="2">Cantidad</th>
                    <th rowspan="2">Val.Und.</th>
                    <th rowspan="2">Total</th>
                </tr>
                <tr style="background-color: #CED3D3; text-align:center">
                    <th>Pedido</th>
                    <th>Entregado</th>
                </tr>
            </thead>
            <tbody style="font-size: 60%;">
                <?php
                $total = 0;
                $row_tipo = '';
                $lote = 'EAC';
                $valorXbien = 0;
                foreach ($datas as $keytb => $tipob) {
                    $row_bien = '';
                    if (!empty($tipob)) {
                        $totalBien = 0;
                        foreach ($tipob as $keybn => $bien) {
                            $numLotes = count($bien);
                            if (!empty($bien)) {
                                $row_lote = '';
                                $quedaXbien = 0;
                                $sumaLote = 0;
                                $cant_prom = 0;
                                $suma_val = 0;
                                $bandera = false;
                                foreach ($bien as $keylt => $lote) {
                                    $keylt = strncmp($keylt, 'EACII', strlen('EACII')) === 0 ? '' : $keylt;
                                    $id_bien = $lote['datos']['id_bn'];
                                    $id_tipo = $lote['datos']['id_tb'];
                                    $ketsol = array_search($id_bien, array_column($pedido, 'id_producto'));
                                    $solicitado = $ketsol !== false ? $pedido[$ketsol]['cantidad'] : 0;
                                    if ($numLotes > 1) {
                                        $sumaLote += $lote['cantd'];
                                        $row_lote .= '<tr class="resaltar">
                                                        <td></td>
                                                        <td></td>
                                                        <td>' . $lote['datos']['invima'] . '</td>
                                                        <td>' . $lote['datos']['vence'] . '</td>
                                                        <td>' . $keylt . '</td>
                                                        <td>' . $lote['datos']['marca'] . '</td>
                                                        <td style="text-align:center;"></td>
                                                        <td style="text-align:center;">' . $lote['cantd'] . '</td>
                                                        <td style="text-align:right;">' . pesos($lote['datos']['valin']) . '</td>
                                                        <td style="text-align:right;">' . pesos($lote['cantd'] * $lote['datos']['costo']) . '</td>
                                                    </tr>';
                                        $cant_prom++;
                                        $suma_val = $suma_val + $lote['datos']['costo'];
                                    } else {
                                        $bandera = true;
                                        $sumaLote = $lote['cantd'];
                                        $row_lote = '<tr class="resaltar">
                                        <td>' . $id_bien . '</td>
                                        <td style="text-align:left;">' . $keybn . '</td>
                                        <td>' . $lote['datos']['invima'] . '</td>
                                        <td>' . $lote['datos']['vence'] . '</td>
                                        <td>' . $keylt . '</td>
                                        <td>' . $lote['datos']['marca'] . '</td>
                                        <td style="text-align:center;">' . $solicitado . '</td>
                                        <td style="text-align:center;">' . $lote['cantd'] . '</td>
                                        <td style="text-align:right;">' . pesos($lote['datos']['valin']) . '</td>
                                        <td style="text-align:right;">' . pesos($sumaLote * $lote['datos']['costo']) . '</td>
                                        </tr>';
                                        $cant_prom = 1;
                                        $suma_val =  $lote['datos']['costo'];
                                    }
                                }
                                if ($bandera) {
                                    $row_bien .= $row_lote;
                                } else {
                                    $prom_valunid = $suma_val / $cant_prom;
                                    $row_bien .= '<tr  class="resaltar">
                                        <td>' . $id_bien . '</td>
                                        <td style="text-align:left;">' . $keybn . '</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td style="text-align:center;">' . $solicitado . '</td>
                                        <td style="text-align:center;">' . $sumaLote . '</td>
                                        <td style="text-align:right;">' . pesos($prom_valunid) . '</td>
                                        <td style="text-align:right;">' . pesos($sumaLote * $prom_valunid) . '</td>
                                        </tr>' . $row_lote;
                                }
                                $valorXbien = $sumaLote * ($suma_val / $cant_prom);
                                $totalBien += $valorXbien;
                                $total += $valorXbien;
                            }
                        }
                        $row_tipo .= '<tr style="font-size: 11px; background-color" class="resaltar">
                        <th>' . $id_tipo . '</th>
                        <th style="text-align: left;" colspan="8">' . $keytb . '</th>
                        <th style="text-align: right;">' . pesos($totalBien) . '</th>
                        </tr>' . $row_bien;
                    }
                }
                $totalExistencia = '<tr style="font-size: 12px; background-color" class="resaltar">
                                        <th colspan=9">TOTAL</th>
                                        <th>' . pesos($total) . '</th>
                                        </tr>' . $row_tipo;
                echo $totalExistencia;
                ?>
                <tr colspan="10">
                    <td style="height: 30px;"></td>
                </tr>
                <tr>
                    <td colspan="5">
                        Solicitó: <?php echo mb_strtoupper($pedido[0]['nombre']); ?>
                    </td>
                    <td colspan="5">
                        Recibe: _______________________________________________________
                    </td>
                </tr>
                <tr>
                    <td colspan="5">
                        Elaboró: <?php echo mb_strtoupper($usuario['nombre']); ?>
                    </td>
                    <td colspan="5">
                        C.C:
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</div>