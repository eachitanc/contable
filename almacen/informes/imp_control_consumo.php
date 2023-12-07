<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
include '../../conexion.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
// consulto el nombre de la empresa de la tabla seg_empresas
$fecini = isset($_POST['inicia']) ?  $_POST['inicia'] : '1900-01-01';
$fecfin =  isset($_POST['fin']) ? $_POST['fin'] . ' 23:59:59' : '1900-01-01';
$check = isset($_POST['check']) ? $_POST['check'] : 0;
try {
    $sql = "SELECT `nombre`, `nit`, `dig_ver` FROM `seg_empresas`";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                CONCAT_WS(' ', `nombre1`, `nombre2`, `apellido1`, `apellido2`) AS `nombre`
            FROM
                `seg_usuarios`
            WHERE (`id_usuario` = $_SESSION[id_user])";
    $res = $cmd->query($sql);
    $usuario = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$datas = [];
try {
    $sql = "SELECT * FROM 
                (SELECT
                    `seg_bodega_almacen`.`nombre` AS `bodega`
                    , `seg_salida_dpdvo`.`id_devolucion`
                    , `seg_salida_dpdvo`.`consecutivo`
                    , `seg_sedes_empresa`.`nombre` AS `sede`
                    , `seg_pedidos_almacen`.`id_bodega`
                    , `seg_tipo_contrata`.`id_tipo`
                    , `seg_tipo_contrata`.`tipo_contrato`
                    , `seg_bien_servicio`.`id_tipo_bn_sv`
                    , `seg_tipo_bien_servicio`.`tipo_bn_sv`
                    , `seg_bien_servicio`.`id_b_s`
                    , `seg_bien_servicio`.`bien_servicio`
                    , `seg_detalle_entrada_almacen`.`valu_ingresa`
                    , `seg_detalle_entrada_almacen`.`iva`
                    , `seg_salidas_almacen`.`cantidad`
                    , `seg_tipo_salidas`.`id_salida`
                    , `seg_tipo_salidas`.`descripcion` AS `tipo_salida`
                    , `seg_pedidos_almacen`.`estado`
                    , DATE_FORMAT(`seg_salida_dpdvo`.`fec_reg`, '%Y/%m/%d') AS `fec_reg`
                FROM
                    `seg_salidas_almacen`
                    INNER JOIN `seg_salida_dpdvo` 
                        ON (`seg_salidas_almacen`.`id_devolucion` = `seg_salida_dpdvo`.`id_devolucion`)
                    INNER JOIN `seg_detalle_entrada_almacen` 
                        ON (`seg_salidas_almacen`.`id_entrada` = `seg_detalle_entrada_almacen`.`id_entrada`)
                    INNER JOIN `seg_pedidos_almacen` 
                        ON (`seg_salida_dpdvo`.`id_pedido` = `seg_pedidos_almacen`.`id_pedido`)
                    INNER JOIN `seg_tipo_salidas` 
                        ON (`seg_salida_dpdvo`.`id_tipo_salida` = `seg_tipo_salidas`.`id_salida`)
                    INNER JOIN `seg_bodega_almacen` 
                        ON (`seg_pedidos_almacen`.`id_bodega` = `seg_bodega_almacen`.`id_bodega`)
                    INNER JOIN `seg_sedes_empresa` 
                        ON (`seg_bodega_almacen`.`id_sede` = `seg_sedes_empresa`.`id_sede`)
                    INNER JOIN `seg_bien_servicio` 
                        ON (`seg_detalle_entrada_almacen`.`id_prod` = `seg_bien_servicio`.`id_b_s`)
                    INNER JOIN `seg_tipo_bien_servicio` 
                        ON (`seg_bien_servicio`.`id_tipo_bn_sv` = `seg_tipo_bien_servicio`.`id_tipo_b_s`)
                    INNER JOIN `seg_tipo_contrata` 
                        ON (`seg_tipo_bien_servicio`.`id_tipo_cotrato` = `seg_tipo_contrata`.`id_tipo`)
                WHERE (`seg_tipo_salidas`.`id_salida` = 7
                    AND `seg_salida_dpdvo`.`fec_reg` BETWEEN '$fecini' AND '$fecfin'))AS `tb`
                ORDER BY `tb`.`fec_reg`, `tb`.`sede`, `tb`.`bodega`,`tb`.`tipo_bn_sv` ASC";
    $res = $cmd->query($sql);
    $datos = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
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
$consec = 0;
$subtotal = 0;
$iva = 0;
$datas = [];
if ($check ==  0) {
    foreach ($datos as $fila) {
        $consumo = $fila['id_devolucion'];
        $bdg = $fila['bodega'];
        $id_bdg = $fila['id_bodega'];
        $tipo = $fila['tipo_bn_sv'];
        $id_tipo = $fila['id_tipo_bn_sv'];
        $cta_contable = '-';
        foreach ($cuentas as $cta) {
            if ($cta['id_bodega'] == $id_bdg && $cta['id_tipo_bn_sv'] == $id_tipo) {
                $cta_contable = $cta['cuenta'];
                break;
            }
        }
        $datas[$consumo]['fecha'] = $fila['fec_reg'];
        $datas[$consumo]['sede'] = $fila['sede'];
        $valor = isset($datas[$consumo]['bodega'][$bdg][$tipo]['valor']) ? $datas[$consumo]['bodega'][$bdg][$tipo]['valor'] : 0;
        $datas[$consumo]['bodega'][$bdg][$tipo]['valor'] = $valor + ($fila['valu_ingresa'] * $fila['cantidad'] * (1 + ($fila['iva'] / 100)));
        $datas[$consumo]['bodega'][$bdg][$tipo]['id_tipo'] = $fila['id_tipo_bn_sv'];
        $datas[$consumo]['bodega'][$bdg][$tipo]['cuenta'] = $cta_contable;
    }
} else if ($check == 1) {
    foreach ($datos as $fila) {
        $consumo = 0;
        $bdg = $fila['bodega'];
        $id_bdg = $fila['id_bodega'];
        $tipo = $fila['tipo_bn_sv'];
        $id_tipo = $fila['id_tipo_bn_sv'];
        $cta_contable = '-';
        foreach ($cuentas as $cta) {
            if ($cta['id_bodega'] == $id_bdg && $cta['id_tipo_bn_sv'] == $id_tipo) {
                $cta_contable = $cta['cuenta'];
                break;
            }
        }
        $datas[$consumo]['fecha'] = '';
        $datas[$consumo]['sede'] = $fila['sede'];
        $valor = isset($datas[$consumo]['bodega'][$bdg][$tipo]['valor']) ? $datas[$consumo]['bodega'][$bdg][$tipo]['valor'] : 0;
        $datas[$consumo]['bodega'][$bdg][$tipo]['valor'] = $valor + ($fila['valu_ingresa'] * $fila['cantidad'] * (1 + ($fila['iva'] / 100)));
        $datas[$consumo]['bodega'][$bdg][$tipo]['id_tipo'] = $fila['id_tipo_bn_sv'];
        $datas[$consumo]['bodega'][$bdg][$tipo]['cuenta'] = $cta_contable;
    }
} else if ($check == 2) {
    foreach ($datos as $fila) {
        $consumo = 0;
        $bdg = $fila['bodega'];
        $tipo = 0;
        $datas[$consumo]['fecha'] = '';
        $datas[$consumo]['sede'] = $fila['sede'];
        $valor = isset($datas[$consumo]['bodega'][$bdg][$tipo]['valor']) ? $datas[$consumo]['bodega'][$bdg][$tipo]['valor'] : 0;
        $datas[$consumo]['bodega'][$bdg][$tipo]['valor'] = $valor + ($fila['valu_ingresa'] * $fila['cantidad'] * (1 + ($fila['iva'] / 100)));
        $datas[$consumo]['bodega'][$bdg][$tipo]['id_tipo'] = $fila['id_tipo_bn_sv'];
    }
}
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
?>

<div class="form-row">
    <div class="form-group col-md-4">
        <label for="fecInicia" class="small">Inicia</label>
        <input type="date" class="form-control form-control-sm" id="fecInicia" value="<?php echo $fecini != '1900-01-01' ? $fecini : ''; ?>">
    </div>
    <div class="form-group col-md-4">
        <label for="fecFin" class="small">Termina</label>
        <input type="date" class="form-control form-control-sm" id="fecFin" value="<?php echo isset($_POST['fin']) ? $_POST['fin'] : ''; ?>">
    </div>
    <div class="form-group col-md-1 text-left">
        <label class="small">&nbsp;</label>
        <div>
            <button class="btn btn-outline-info btn-sm" id="btnFiltraConsumo"><span class="fas fa-search fa-lg" aria-hidden="true"></span></button>
        </div>
    </div>
    <div class="form-group col-md-3 text-right">
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
    <div class="form-group col-md-12 text-left">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="consolidado" id="consumo" value="0" <?php echo $check == 0 ? 'checked' : '' ?>>
            <label class="form-check-label small" for="consumo">Consumo</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="consolidado" id="contp" value="1" <?php echo $check == 1 ? 'checked' : '' ?>>
            <label class="form-check-label small" for="contp">Consolidado Tipo</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="consolidado" id="conbg" value="2" <?php echo $check == 2 ? 'checked' : '' ?>>
            <label class="form-check-label small" for="conbg">Consolidado Bodega</label>
        </div>
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
    <div class="px-2 text-lef pagina">
        <table class="page_break_avoid" style="width:100% !important; border-collapse: collapse;">
            <thead style="background-color: white !important;font-size:80%">
                <tr style="padding: bottom 3px; color:black">
                    <td colspan="10">
                        <table id="lista" class="bg-light" style="width:100% !important;">
                            <tr>
                                <td rowspan="2" class='text-center' style="width:18%"><label class="small"><img src="<?php echo $_SESSION['urlin'] ?>/images/logos/logo.png" width="100"></label></td>
                                <td colspan="8" style="text-align:center">
                                    <strong><?php echo $empresa['nombre']; ?> </strong>
                                    <div>NIT <?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4">
                                    <?php $fi = isset($_POST['inicia']) ? date('d/m/Y', strtotime($fecini)) : ' - ';
                                    $ff = isset($_POST['fin']) ? date('d/m/Y', strtotime($fecfin)) : ' - ';
                                    ?>
                                    <center>CONTROL DE CONSUMOS, ENTRE EL <?php echo $fi . ' Y EL ' . $ff ?></center>
                                </td>
                                <td colspan="4" style="text-align: right; font-size:70%">Imp. <?php echo $date->format('d/m/Y H:i') ?></td>
                            </tr>
                            <tr>
                                <?php
                                if ($check == 0) {
                                    $tipo = 'CONSUMO POR PEDIDO';
                                } elseif ($check == 1) {
                                    $tipo = 'CONSOLIDADO CONSUMO POR TIPOS DE PRODUCTO Y BODEGAS';
                                } else {
                                    $tipo = 'CONSOLIDADO CONSUMO POR BODEGAS';
                                }
                                ?>
                                <td colspan="9" style="text-align: left;"><?php echo 'TIPO: ' . $tipo ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr style="background-color: #CED3D3; width:100%">
                    <th>ID</th>
                    <th>Cuenta</th>
                    <th><?php echo  $check == 0 ? 'Fecha' : ''; ?></th>
                    <th colspan="5">Almacén</th>
                    <th>Vr. Total</th>
                    <th><?php echo  $check == 0 ? 'Estado' : ''; ?></th>
                </tr>
            </thead>
            <tbody style="font-size: 60%;">
                <?php
                if (!empty($datas)) {
                    $tabla = '';
                    $totalf = 0;
                    foreach ($datas as $key => $dc) {
                        $sede = $dc['sede'];
                        $fecha = $check == 0 ? date('d/m/Y', strtotime($dc['fecha'])) : '';
                        $key = $check == 0 ? $key : '';
                        $bodega = $dc['bodega'];
                        $print = '';
                        $total = 0;
                        foreach ($bodega as $bdg => $tipo) {
                            $row_tipo = '';
                            $subtotal = 0;
                            $rowbg = '';
                            foreach ($tipo as $tp => $dt) {
                                $row_tipo .=  '<tr class="resaltar">';
                                $row_tipo .= '<td colspan="1" style="text-align:left">' . $dt['id_tipo'] . '</td>';
                                $row_tipo .= '<td colspan="1" style="text-align:left">' . $dt['cuenta'] . '</td>';
                                $row_tipo .= '<td colspan="6" style="text-align:left">' . $tp . '</td>';
                                $row_tipo .= '<td style="text-align:right">' . number_format($dt['valor'], 2, ',', '.') . '</td>';
                                $row_tipo .= '<td></td>';
                                $row_tipo .= '</tr>';
                                $subtotal += $dt['valor'];
                            }
                            $estado = $check == 0 ? 'REALIZADO' : '';
                            $row_tipo = $check == 2 ? '' : $row_tipo;
                            $rowbg .= '<tr class="resaltar">';
                            $rowbg .= '<th colspan="2">' . $key . '</th>';
                            $rowbg .= '<th>' . $fecha . '</th>';
                            $rowbg .= '<th colspan="5" style="text-align:left">' . $bdg . '</th>';
                            $rowbg .= '<th style="text-align:right">' . number_format($subtotal, 2, ',', '.') . '</th>';
                            $rowbg .= '<th>' . $estado . '</th>';
                            $rowbg .= '</tr>';
                            $print .= $rowbg . $row_tipo;
                            $total += $subtotal;
                        }
                        $tabla .= $print;
                        $totalf += $total;
                    }
                    $t = $check == 0 ? 'TOTAL' : number_format($totalf, 2, ',', '.');
                    $t2 = $check == 0 ? number_format($totalf, 2, ',', '.') : '';
                    echo '<tr style="font-size: 10px;" class="resaltar">';
                    echo '<th style="text-align:left">' . $datos[0]['id_tipo'] . '</th>';
                    echo '<th></th>';
                    echo '<th colspan="6" style="text-align:left">' . $datos[0]['tipo_contrato'] . '</th>';
                    echo '<th style="text-align:right">' . $t . '</th>';
                    echo '<th style="text-align:right">' . $t2 . '</th>';
                    echo $tabla;
                } else {
                    echo '<tr class="resaltar"><td colspan="9" style="text-align:center">No hay datos para mostrar</td></tr>';
                }
                ?>
                <tr>
                    <td colspan="9" style="padding:15px">
                    </td>
                </tr>
                <tr>
                    <td colspan="9">
                        <table style="font-size: 10px;">
                            <tr>
                                <td>
                                    Elaboró:
                                </td>
                                <td>
                                    _____________________________________________
                                </td>
                            </tr>
                            <tr>
                                <td>
                                </td>
                                <td>
                                    <?php echo mb_strtoupper($usuario['nombre']); ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                </td>
                                <td>
                                    ALMACEN GENERAL
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</div>