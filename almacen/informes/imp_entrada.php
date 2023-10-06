<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
function calcularDV($nit)
{
    if (!is_numeric($nit)) {
        return false;
    }

    $arr = array(
        1 => 3, 4 => 17, 7 => 29, 10 => 43, 13 => 59, 2 => 7, 5 => 19,
        8 => 37, 11 => 47, 14 => 67, 3 => 13, 6 => 23, 9 => 41, 12 => 53, 15 => 71
    );
    $x = 0;
    $y = 0;
    $z = strlen($nit);
    $dv = '';

    for ($i = 0; $i < $z; $i++) {
        $y = substr($nit, $i, 1);
        $x += ($y * $arr[$z - $i]);
    }

    $y = $x % 11;

    if ($y > 1) {
        $dv = 11 - $y;
        return $dv;
    } else {
        $dv = $y;
        return $dv;
    }
}
include '../../conexion.php';
$id_entrada = $_POST['id'];
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
                CONCAT_WS(' ', `nombre1`, `nombre2`, `apellido1`, `apellido2`) AS `nombre`
            FROM
                `seg_usuarios`
            WHERE (`id_usuario` = $_SESSION[id_user])";
    $res = $cmd->query($sql);
    $usuario = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `seg_usuarios`.`documento`
                , CONCAT_WS(' ', `seg_usuarios`.`nombre1`
                , `seg_usuarios`.`nombre2`
                , `seg_usuarios`.`apellido1`
                , `seg_usuarios`.`apellido2`) AS `responsable`
                , `seg_bodega_almacen`.`nombre`
                , `seg_responsable_bodega`.`id_bodega`
            FROM
                `seg_responsable_bodega`
                INNER JOIN `seg_bodega_almacen` 
                    ON (`seg_responsable_bodega`.`id_bodega` = `seg_bodega_almacen`.`id_bodega`)
                INNER JOIN `seg_usuarios` 
                    ON (`seg_responsable_bodega`.`id_usuario` = `seg_usuarios`.`id_usuario`)
            WHERE `seg_responsable_bodega`.`id_bodega` = 1";
    $res = $cmd->query($sql);
    $responsable = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `seg_entrada_almacen`.`id_entrada`
                ,`seg_entrada_almacen`.`consecutivo`
                , `seg_entrada_almacen`.`id_tercero_api`
                , `seg_tipo_entrada`.`descripcion`
                , `seg_entrada_almacen`.`no_factura`
                , `seg_entrada_almacen`.`acta_remision`
                , `seg_entrada_almacen`.`fec_entrada`
                , `seg_entrada_almacen`.`observacion`
                , `seg_entrada_almacen`.`estado`
                , `seg_entrada_almacen`.`id_user_reg`
                , `seg_detalle_entrada_almacen`.`id_prod`
                , `seg_sedes_empresa`.`nombre` AS `sede`
                , `seg_bodega_almacen`.`nombre` AS `bodega`
                , `seg_tipo_bien_servicio`.`id_tipo_b_s`
                , `seg_tipo_bien_servicio`.`tipo_bn_sv`
                , `seg_bien_servicio`.`bien_servicio`
                , `seg_detalle_entrada_almacen`.`cant_ingresa`
                , `seg_detalle_entrada_almacen`.`iva`
                , `seg_detalle_entrada_almacen`.`valu_ingresa`
                , `seg_detalle_entrada_almacen`.`iva`
                , `seg_detalle_entrada_almacen`.`lote`
                , `seg_marcas`.`descripcion` as `marca`
                , `seg_detalle_entrada_almacen`.`invima`
                , `seg_detalle_entrada_almacen`.`fecha_vence`
                , `seg_tipo_entrada`.`descripcion` as `tipo_entrada`
            FROM
                `seg_detalle_entrada_almacen`
                INNER JOIN `seg_entrada_almacen` 
                    ON (`seg_detalle_entrada_almacen`.`id_entra` = `seg_entrada_almacen`.`id_entrada`)
                INNER JOIN `seg_tipo_entrada` 
                    ON (`seg_entrada_almacen`.`id_tipo_entrada` = `seg_tipo_entrada`.`id_entrada`)
                INNER JOIN `seg_bien_servicio` 
                    ON (`seg_detalle_entrada_almacen`.`id_prod` = `seg_bien_servicio`.`id_b_s`)
                INNER JOIN `seg_sedes_empresa` 
                    ON (`seg_detalle_entrada_almacen`.`id_sede` = `seg_sedes_empresa`.`id_sede`)
                INNER JOIN `seg_bodega_almacen` 
                    ON (`seg_detalle_entrada_almacen`.`id_bodega` = `seg_bodega_almacen`.`id_bodega`)
                INNER JOIN `seg_tipo_bien_servicio` 
                    ON (`seg_bien_servicio`.`id_tipo_bn_sv` = `seg_tipo_bien_servicio`.`id_tipo_b_s`)
                LEFT JOIN `seg_marcas` 
                    ON (`seg_detalle_entrada_almacen`.`id_marca` = `seg_marcas`.`id_marca`)
            WHERE (`seg_entrada_almacen`.`id_entrada` = $id_entrada)
            ORDER BY `seg_tipo_bien_servicio`.`tipo_bn_sv`,`seg_bien_servicio`.`bien_servicio`,`seg_detalle_entrada_almacen`.`lote`, `seg_detalle_entrada_almacen`.`marca`, `seg_detalle_entrada_almacen`.`invima` ASC";
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
            WHERE (`id_usuario` = $user)";
    $res = $cmd->query($sql);
    $usuario = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$id_tercero = $datos[0]['id_tercero_api'];
$url = $api . 'terceros/datos/res/datos/id/' . $id_tercero;
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res_api = curl_exec($ch);
curl_close($ch);
$dat_ter = json_decode($res_api, true);
if ($dat_ter[0] != '0') {
    $tercero = $dat_ter[0]['apellido1'] . ' ' . $dat_ter[0]['apellido2'] . ' ' . $dat_ter[0]['nombre2'] . ' ' . $dat_ter[0]['nombre1'] . ' ' . $dat_ter[0]['razon_social'];
    $ccnit = $dat_ter[0]['cc_nit'] . '-' . calcularDV($dat_ter[0]['cc_nit']);
} else {
    $tercero = '';
    $ccnit = '';
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
    $costo = $fila['valu_ingresa'] + $fila['valu_ingresa'] * $fila['iva'] / 100;
    $datas[$tps][$bs][$lt]['cantd'] = $fila['cant_ingresa'];
    $datas[$tps][$bs][$lt]['datos']['costo'] =  $costo;
    $datas[$tps][$bs][$lt]['datos']['valin'] =  $fila['valu_ingresa'];
    $datas[$tps][$bs][$lt]['datos']['vence'] =  $fila['fecha_vence'];
    $datas[$tps][$bs][$lt]['datos']['id_bn'] =  $fila['id_prod'];
    $datas[$tps][$bs][$lt]['datos']['id_tb'] =  $fila['id_tipo_b_s'];
    $datas[$tps][$bs][$lt]['datos']['invima'] = $fila['invima'];
    $datas[$tps][$bs][$lt]['datos']['marca'] =  $fila['marca'];
    $datas[$tps][$bs][$lt]['datos']['iva'] =  $fila['iva'];
}
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$subtotal = 0;
$iva = 0;
foreach ($datos as $dt) {
    $iva += $dt['cant_ingresa'] * ($dt['valu_ingresa'] * $dt['iva'] / 100);
    $subtotal += $dt['valu_ingresa'] * $dt['cant_ingresa'];
}
$grantotal = $subtotal + $iva;
?>
<div class="text-right py-3">
    <a type="button" id="btnExcelEntrada" class="btn btn-outline-success btn-sm" value="01" title="Exprotar a Excel">
        <span class="fas fa-file-excel fa-lg" aria-hidden="true"></span>
    </a>
    <a type="button" class="btn btn-primary btn-sm" id="btnImprimir">Imprimir</a>
    <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cerrar</a>
</div>
<div class="content bg-light" id="areaImprimir">
    <style>
        @media print {
            @page {
                size: auto;
            }

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
    <div class="page">
        <table style="width:100% !important; border-collapse: collapse;" class="dynamic-table">
            <thead style="background-color: white !important;font-size:80%">
                <tr style="padding: bottom 3px; color:black">
                    <td colspan="10">
                        <table style="width:100% !important;">
                            <tr>
                                <td rowspan="3" class='text-center' style="width:18%"><label class="small"><img src="<?php echo $_SESSION['urlin'] ?>/images/logos/logo.png" width="100"></label></td>
                                <td colspan="9" style="text-align:center">
                                    <header><strong><?php echo $empresa['nombre']; ?> </strong></header>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="9" style="text-align:center">
                                    NIT <?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3">
                                    <b>ENTRADA No: <?php echo str_pad($datos[0]['consecutivo'], 5, "0", STR_PAD_LEFT) ?>
                                </td>
                                <td colspan="3">
                                    <b>TIPO: <?php echo $datos[0]['tipo_entrada'] ?>
                                </td>
                                <td colspan="3" style="font-size:9px;text-align: right;">
                                    <table style="width:100% !important;">
                                        <tr>
                                            <td style="text-align: right;">Fecha entrada.</td>
                                            <td><?php echo date('Y/m/d', strtotime($datos[0]['fec_entrada'])) ?></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: right;">Fecha Imp.</td>
                                            <td><?php echo $date->format('Y/m/d') ?></td>
                                    </table>
                                </td>
                            </tr>
                            <tr style="font-size: 85%;">
                                <td colspan="4">
                                    TERCERO: <?php echo $tercero ?>
                                </td>
                                <td colspan="3">
                                    NIT: <?php echo $ccnit ?>
                                </td>
                                <td colspan="3">
                                    RECIBE: <?php echo mb_strtoupper($datos[0]['sede'] . ' - ' .   $datos[0]['bodega']) ?>
                                </td>
                                <td colspan="1">
                                    <span id="numero-pagina"></span>
                                </td>
                            </tr>
                            <tr style="font-size: 85%;">
                                <td colspan="8" style="text-align:right">
                                    <span>ESTADO: <b><?php echo $datos[0]['estado'] <= 2 ? 'BORRADOR' : 'DEFINITIVO' ?></b></span>
                                </td>
                                <td colspan="2" style="text-align:right">
                                    <div class="page-number"></div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr style="background-color: #CED3D3; text-align:center">
                    <th>ID</th>
                    <th>Producto</th>
                    <th>Invima</th>
                    <th>Vence</th>
                    <th>Lote</th>
                    <th>Marca</th>
                    <th>IVA</th>
                    <th>Cantidad</th>
                    <th>Val.Und.</th>
                    <th>Total</th>
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
                                    if ($numLotes > 1) {
                                        $sumaLote += $lote['cantd'];
                                        $row_lote .= '<tr class="resaltar">
                                                        <td></td>
                                                        <td></td>
                                                        <td>' . $lote['datos']['invima'] . '</td>
                                                        <td>' . $lote['datos']['vence'] . '</td>
                                                        <td>' . $keylt . '</td>
                                                        <td>' . $lote['datos']['marca'] . '</td>
                                                        <td style="text-align:right;">' . $lote['datos']['iva'] . '%</td>
                                                        <td style="text-align:right;">' . $lote['cantd'] . '</td>
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
                                        <td style="text-align:right;">' . $lote['datos']['iva'] . '%</td>
                                        <td style="text-align:right;">' . $lote['cantd'] . '</td>
                                        <td style="text-align: right;">' . pesos($lote['datos']['valin']) . '</td>
                                        <td style="text-align: right;">' . pesos($sumaLote * $lote['datos']['costo']) . '</td>
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
                                        <td></td>
                                        <td style="text-align:right;">' . $sumaLote . '</td>
                                        <td style="text-align:right;">' . pesos($prom_valunid) . '</td>
                                        <td style="text-align: ight;">' . pesos($sumaLote * $prom_valunid) . '</td>
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
                $totalExistencia = '<tr style="font-size: 12px;" class="resaltar">
                                    <th style="text-align: left;" colspan="8">ELEMENTOS DE CONSUMO O CARGO DIFERIDO</th>
                                    <th style="text-align: right;" colspan="2">' . pesos(round($grantotal)) . '</th>
                                </tr>
                                <tr style="font-size: 12px;">
                                    <th style="text-align: left;" colspan="8">SUBTOTAL</th>
                                    <th style="text-align: right;" colspan="2">' . pesos($subtotal) . '</th>
                                </tr>
                                <tr style="font-size: 12px;" class="resaltar">
                                    <th style="text-align: left;" colspan="8">IVA</th>
                                    <th style="text-align: right;" colspan="2">' . pesos($iva) . '</th>
                                </tr>' . $row_tipo;
                echo $totalExistencia;
                ?>
                <tr>
                    <td colspan="10" style="text-align: left;">
                        <span>OBSERVACIÓN: <b><?php echo $datos[0]['observacion'] ?></b></span>
                    </td>
                <tr>
                    <td colspan="10" style="height: 30px;"></td>
                </tr>
                <tr>
                    <td colspan="10">
                        <table style="width: 100%;">
                            <tr>
                                <td colspan="2">
                                    Elaboró:
                                </td>
                                <td colspan="3">
                                    _____________________________________________
                                </td>
                                <td colspan="2">
                                    Recibido por:
                                </td>
                                <td colspan="3">
                                    _____________________________________________
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    Nombre:
                                </td>
                                <td colspan="3">
                                    <?php echo mb_strtoupper($usuario['nombre']); ?>
                                </td>
                                <td colspan="2">
                                    Nombre:
                                </td>
                                <td colspan="3">
                                    <?php echo mb_strtoupper($responsable['responsable']); ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="10">
                        <div class="footer">
                        </div>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>