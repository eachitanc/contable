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
$id_inicia = isset($_POST['inicia']) ? $_POST['inicia'] : 0;
$id_inicia = $id_inicia == '' ? 0 : $id_inicia;
$id_final = isset($_POST['fin']) ? $_POST['fin'] : 0;
$id_final = $id_final == '' ? 0 : $id_final;
$fec_inicia = isset($_POST['fec_inicia']) ? $_POST['fec_inicia'] : '2022-01-01';
$fec_inicia = $fec_inicia == '' ? '2022-01-01' : $fec_inicia;
$fec_final = isset($_POST['fec_final']) ? $_POST['fec_final'] : date('Y-m-d');
$fec_final = $fec_final == '' ? date('Y-m-d') : $fec_final;
$tipoT = isset($_POST['tipo']) ? $_POST['tipo'] : 1;
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
try {
    $sql = "SELECT
                `seg_bodega_almacen`.`id_bodega`
                , `seg_ctas_gasto`.`id_tipo_bn_sv`
                , `seg_ctas_gasto`.`cuenta`
            FROM
                `seg_ctas_gasto`
                INNER JOIN `seg_bodega_almacen` 
                    ON (`seg_ctas_gasto`.`id_bodega` = `seg_bodega_almacen`.`id_bodega`)";
    $rs = $cmd->query($sql);
    $cuentas = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if ($tipoT == 1) {
    $where = "WHERE (`seg_traslados_almacen`.`id_trasl_alm` BETWEEN $id_inicia AND $id_final)";
} else {
    $where  = "WHERE (`seg_traslados_almacen`.`fec_traslado` BETWEEN '$fec_inicia' AND '$fec_final')";
}
try {
    $sql = "SELECT `nombre`, `nit`, `dig_ver` FROM `seg_empresas`";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `seg_traslados_almacen`.`id_trasl_alm`
                , `seg_traslados_almacen`.`id_pedido`
                , `seg_traslados_almacen`.`fec_traslado`
                , `seg_bodega_almacen`.`nombre` AS `bodega_sale`
                , `seg_bodega_almacen_1`.`nombre` AS `bodega_entra`
                , `seg_pedidos_almacen`.`fec_cierre`
                , `seg_pedidos_almacen`.`fec_reg`
            FROM
                `seg_traslados_almacen`
                INNER JOIN `seg_bodega_almacen` 
                    ON (`seg_traslados_almacen`.`id_bodega_sale` = `seg_bodega_almacen`.`id_bodega`)
                INNER JOIN `seg_bodega_almacen` AS `seg_bodega_almacen_1`
                    ON (`seg_traslados_almacen`.`id_bodega_entra` = `seg_bodega_almacen_1`.`id_bodega`)
                INNER JOIN `seg_pedidos_almacen`
            ON (`seg_traslados_almacen`.`id_pedido` = `seg_pedidos_almacen`.`id_pedido`)
            $where";
    $res = $cmd->query($sql);
    $encabezado = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `seg_detalles_traslado`.`cantidad`
                ,`seg_traslados_almacen`.`id_trasl_alm`
                ,`seg_traslados_almacen`.`id_bodega_sale`
                ,`seg_traslados_almacen`.`id_bodega_entra`
                , `seg_detalles_traslado`.`observacion`
                , `seg_detalles_traslado`.`id_producto`
                , `seg_bien_servicio`.`bien_servicio`
                , `seg_tipo_bien_servicio`.`id_tipo_b_s`
                , `seg_bien_servicio`.`id_tipo_bn_sv`
                , `seg_tipo_bien_servicio`.`tipo_bn_sv`
                , `seg_traslados_almacen`.`id_pedido`
                , `seg_traslados_almacen`.`id_user_reg`
                , `seg_traslados_almacen`.`fec_reg`
                , `seg_traslados_almacen`.`fec_traslado`
                , `seg_traslados_almacen`.`fec_reg`
                , `seg_detalles_traslado`.`id_entrada`
                , `seg_detalles_traslado`.`id_traslado`
                , `seg_detalle_entrada_almacen`.`id_entra`
                , `seg_detalle_entrada_almacen`.`valu_ingresa`
                , `seg_detalle_entrada_almacen`.`iva`
                , `seg_detalle_entrada_almacen`.`lote`
                , `seg_marcas`.`descripcion` AS `marca`
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
            $where
            ORDER BY `seg_traslados_almacen`.`id_trasl_alm`,`seg_tipo_bien_servicio`.`tipo_bn_sv`,`seg_bien_servicio`.`bien_servicio` ASC";
    $res = $cmd->query($sql);
    $datos = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$ids_pedido = array_unique(array_column($datos, 'id_pedido'));
$user = isset($datos[0]['id_user_reg']) ? $datos[0]['id_user_reg'] : 0;
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
$ids_pedido = !empty($ids_pedido) ? implode(',', $ids_pedido) : 0;
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
            WHERE (`seg_pedidos_almacen`.`id_pedido`  IN ($ids_pedido))";
    $res = $cmd->query($sql);
    $pedidos = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$datas = [];
$consec = 0;
foreach ($datos as $fila) {
    $traslado = $fila['id_trasl_alm'];
    $tps = $fila['tipo_bn_sv'];
    $bs = $fila['bien_servicio'];
    $lt = $fila['lote'];
    if ($lt == '') {
        $lt = 'EACII' . $consec;
        $consec++;
    }
    $sumar = isset($datas[$traslado][$tps][$bs][$lt]['cantd']) ? $datas[$traslado][$tps][$bs][$lt]['cantd'] : 0;
    $costo = $fila['valu_ingresa'] + $fila['valu_ingresa'] * $fila['iva'] / 100;
    $datas[$traslado][$tps][$bs][$lt]['cantd'] = $fila['cantidad'] + $sumar;
    $datas[$traslado][$tps][$bs][$lt]['datos']['costo'] =  $costo;
    $datas[$traslado][$tps][$bs][$lt]['datos']['valin'] =  $fila['valu_ingresa'];
    $datas[$traslado][$tps][$bs][$lt]['datos']['vence'] =  $fila['fecha_vence'];
    $datas[$traslado][$tps][$bs][$lt]['datos']['id_bn'] =  $fila['id_producto'];
    $datas[$traslado][$tps][$bs][$lt]['datos']['id_tb'] =  $fila['id_tipo_b_s'];
    $datas[$traslado][$tps][$bs][$lt]['datos']['invima'] = $fila['invima'];
    $datas[$traslado][$tps][$bs][$lt]['datos']['marca'] =  $fila['marca'];
    $datas[$traslado][$tps][$bs][$lt]['datos']['iva'] =  $fila['iva'];
}
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
?>
<div class="form-row">
    <div class="form-group col-md-1">
        <label class="small">&nbsp;</label>
        <div class="form-control form-control-sm">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="categoria" id="radMultiple" value="1" <?php echo $tipoT == 1 ? 'checked' : '' ?> title="Multiple">
            </div>
        </div>
    </div>
    <div class="form-group col-md-4">
        <label for="idInicia" class="small">Inicia</label>
        <input type="number" class="form-control form-control-sm" id="idInicia" value="<?php echo $id_inicia; ?>" placeholder="ID traslado inicial">
    </div>
    <div class="form-group col-md-4">
        <label for="idFinal" class="small">final</label>
        <input type="number" class="form-control form-control-sm" id="idFinal" value="<?php echo $id_final; ?>" placeholder="ID traslado Final">
    </div>
    <div class="form-group col-md-1 text-left">
        <label class="small">&nbsp;</label>
        <div>
            <button class="btn btn-outline-info btn-sm" id="btnListTraslMult"><span class="fas fa-search fa-lg" aria-hidden="true"></span></button>
        </div>
    </div>
    <div class="form-group col-md-2 text-right">
        <label class="small">&nbsp;</label>
        <div>
            <a type="" id="btnReporteGral" class="btn btn-outline-success btn-sm" value="01" title="Exprotar a Excel">
                <span class="fas fa-file-excel fa-lg" aria-hidden="true"></span>
            </a>
            <a type="button" class="btn btn-primary btn-sm" title="Imprimir" onclick="imprSelecTes('areaImprimir',<?php echo 0; ?>);"><span class="fas fa-print fa-lg" aria-hidden="true"></span></a>
            <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" title="Cerrar"><span class="fas fa-times fa-lg" aria-hidden="true"></span></a>
        </div>
    </div>
</div>
<div class="form-row">
    <div class="form-group col-md-1">
        <div class="form-control form-control-sm">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="categoria" id="radControl" value="2" <?php echo $tipoT == 2 ? 'checked' : '' ?> title="Control">
            </div>
        </div>
    </div>
    <div class="form-group col-md-4">
        <input type="date" class="form-control form-control-sm" id="fecInicia" value="<?php echo $fec_inicia; ?>">
    </div>
    <div class="form-group col-md-4">
        <input type="date" class="form-control form-control-sm" id="fecFinal" value="<?php echo $fec_final; ?>">
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
        <div class="table-responsive">
            <table class="page_break_avoid" style="width:100% !important;">
                <thead style="background-color: white !important;font-size:80%">
                    <tr style="padding: bottom 3px; color:black">
                        <td colspan="10">
                            <table style="width:100% !important;">
                                <tr>
                                    <td rowspan="3" class='text-center' style="width:18%"><label class="small"><img src="<?php echo $_SESSION['urlin'] ?>/images/logos/logo.png" width="100"></label></td>
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
                                    <td colspan="7">
                                        <?php if ($tipoT == 1) { ?>
                                            <b>TRASLADO MULTIPLES DEL No <?php echo str_pad($id_inicia, 5, "0", STR_PAD_LEFT) . ' AL No ' . str_pad($id_final, 5, "0", STR_PAD_LEFT) ?>
                                            <?php } else { ?>
                                                <b> CONTROL DE TRASLADO ENTRE EL <?php echo $fec_inicia . ' Y EL ' . $fec_final ?>
                                                <?php } ?>
                                    </td>
                                    <td colspan="2" style="scale: 0.7; text-align: right;">
                                        <table style="width:100% !important;">
                                            <tr>
                                                <td>Fecha Imp.</td>
                                                <td><?php echo $date->format('Y/m/d') ?></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr style="background-color: #F2F3F4; text-align:center">
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
                    <tr style="background-color: #F2F3F4; text-align:center">
                        <th>Pedido</th>
                        <th>Entregado</th>
                    </tr>
                </thead>
                <tbody style="font-size: 60%;">
                    <?php
                    if (!empty($datas)) {
                        $row_traslado = '';
                        $totalExistencia = '';
                        $granTotal = 0;
                        foreach ($datas as $ts => $trasl) {
                            $total = 0;
                            $row_tipo = '';
                            $lote = 'EAC';
                            $valorXbien = 0;
                            $keyts = array_search($ts, array_column($encabezado, 'id_trasl_alm'));
                            if ($keyts !== false) {
                                $row_traslado = '<tr style="background-color: #F2F3F4; color: black">
                                            <td colspan="3">
                                                Genera: ' . $encabezado[$keyts]['bodega_sale'] . '
                                            </td>
                                            <td colspan="7">
                                                Solicita: ' . $encabezado[$keyts]['bodega_entra'] . '
                                            </td>
                                        </tr>
                                        <tr style="background-color: #F2F3F4;  color:#black">
                                            <td colspan="3">
                                                Traslado No.: ' . $ts . '
                                            </td>
                                            <td colspan="7">
                                                Pedido No.: ' . str_pad($encabezado[$keyts]["id_pedido"], 5, "0", STR_PAD_LEFT) . ' Fecha: ' . date('Y/m/d', strtotime($encabezado[$keyts]['fec_cierre'] == '' ? $encabezado[$keyts]['fec_reg'] : $encabezado[$keyts]['fec_cierre'])) . '
                                            </td>
                                        </tr>';
                            } else {
                                $row_traslado = '<tr style="background-color: #F2F3F4;  color:#black">
                                            <td colspan="10">
                                                Genera: ' . $ts . '
                                            </td>
                                        </tr>';
                            }
                            foreach ($trasl as $keytb => $tipob) {
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
                                                $ketsol = array_search($id_bien, array_column($pedidos, 'id_producto'));
                                                $solicitado = $ketsol !== false ? $pedidos[$ketsol]['cantidad'] : 0;
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
                                    if ($tipoT == 2) {
                                        $row_bien = NULL;
                                    }
                                    $row_tipo .= '<tr style="font-size: 11px; background-color" class="resaltar">
                        <th>' . $id_tipo . '</th>
                        <th style="text-align: left;" colspan="8">' . $keytb . '</th>
                        <th style="text-align: right;">' . pesos($totalBien) . '</th>
                        </tr>' . $row_bien;
                                }
                            }
                            $granTotal += $total;
                            $totalExistencia .= $row_traslado . '<tr style="font-size: 12px; background-color" class="resaltar">
                                        <th colspan=9">SUBTOTAL</th>
                                        <th>' . pesos($total) . '</th>
                                        </tr>' . $row_tipo;
                        }

                        echo '<tr style="font-size: 12px; text-align:center" class="resaltar">
                            <th colspan=9">TOTAL</th>
                            <th>' . pesos($granTotal) . '</th>
                        </tr>' . $totalExistencia;
                    } else {
                        echo '<tr style="font-size: 12px; text-align:center" class="resaltar">
                            <th colspan=10">No hay datos para mostrar</th>
                        </tr>';
                    }
                    ?>
                    <tr colspan="10">
                        <td style="height: 30px;"></td>
                    </tr>
                    <tr>
                        <td colspan="5">
                            Solicitó: _______________________________________________________
                        </td>
                        <td colspan="5">
                            Recibe: _______________________________________________________
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5">
                            Elaboró: <?php echo mb_strtoupper(isset($usuario['nombre']) ? $usuario['nombre'] : ''); ?>
                        </td>
                        <td colspan="5">
                            C.C:
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>