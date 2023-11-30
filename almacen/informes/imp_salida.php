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
$id_salida = $_POST['id'];
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
                `seg_salida_dpdvo`.`id_devolucion`
                , `seg_salida_dpdvo`.`consecutivo`
                , `seg_salida_dpdvo`.`id_tercero_api`
                , `seg_tipo_salidas`.`descripcion` AS `tipo_salida`
                , `seg_salida_dpdvo`.`id_tipo_salida`
                , `seg_salida_dpdvo`.`acta_remision`
                , `seg_salida_dpdvo`.`fec_acta_remision`
                , `seg_salida_dpdvo`.`observacion`
                , `seg_salida_dpdvo`.`estado`
                , `seg_salida_dpdvo`.`id_user_reg`
                , `seg_salidas_almacen`.`id_producto` AS `id_prod`
                , `seg_tipo_bien_servicio`.`id_tipo_b_s`
                , `seg_tipo_bien_servicio`.`tipo_bn_sv`
                , `seg_bien_servicio`.`bien_servicio`
                , `seg_salidas_almacen`.`cantidad`
                , `seg_detalle_entrada_almacen`.`iva`
                , `seg_detalle_entrada_almacen`.`valu_ingresa`
                , `seg_detalle_entrada_almacen`.`lote`
                , `seg_detalle_entrada_almacen`.`marca`
                , `seg_detalle_entrada_almacen`.`invima`
                , `seg_detalle_entrada_almacen`.`fecha_vence`
            FROM
                `seg_salidas_almacen`
                INNER JOIN `seg_detalle_entrada_almacen` 
                    ON (`seg_salidas_almacen`.`id_entrada` = `seg_detalle_entrada_almacen`.`id_entrada`)
                INNER JOIN `seg_salida_dpdvo` 
                    ON (`seg_salidas_almacen`.`id_devolucion` = `seg_salida_dpdvo`.`id_devolucion`)
                INNER JOIN `seg_bien_servicio` 
                    ON (`seg_salidas_almacen`.`id_producto` = `seg_bien_servicio`.`id_b_s`)
                INNER JOIN `seg_tipo_salidas` 
                    ON (`seg_salida_dpdvo`.`id_tipo_salida` = `seg_tipo_salidas`.`id_salida`)
                INNER JOIN `seg_tipo_bien_servicio` 
                    ON (`seg_bien_servicio`.`id_tipo_bn_sv` = `seg_tipo_bien_servicio`.`id_tipo_b_s`)
            WHERE (`seg_salida_dpdvo`.`id_devolucion` = $id_salida)
            ORDER BY `seg_tipo_bien_servicio`.`tipo_bn_sv`,`seg_bien_servicio`.`bien_servicio`,`seg_detalle_entrada_almacen`.`lote`, `seg_detalle_entrada_almacen`.`marca`, `seg_detalle_entrada_almacen`.`invima` ASC";
    $res = $cmd->query($sql);
    $datos = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$tipo_salida = isset($datos[0]['id_tipo_salida']) ? $datos[0]['id_tipo_salida'] : 0;
$user = isset($datos[0]['id_user_reg']) ? $datos[0]['id_user_reg'] : 0;
try {
    $sql = "SELECT
                `seg_entrada_almacen`.`consecutivo`
                , `seg_entrada_almacen`.`estado`
                , `seg_entrada_almacen`.`id_devolucion`
                , `seg_tipo_entrada`.`descripcion`
            FROM
                `seg_entrada_almacen`
                INNER JOIN `seg_tipo_entrada` 
                    ON (`seg_entrada_almacen`.`id_tipo_entrada` = `seg_tipo_entrada`.`id_entrada`)
            WHERE (`seg_entrada_almacen`.`id_devolucion` = $id_salida)";
    $res = $cmd->query($sql);
    $relacion = $res->fetch();
    if (!empty($datos)) {
        $rel = !empty($relacion) ? ' -> ' . mb_strtoupper($relacion['descripcion']) . ' ' . str_pad($relacion['consecutivo'], 5, "0", STR_PAD_LEFT) : '';
    } else {
        $rel = '';
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
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
$id_tercero = isset($datos[0]['id_tercero_api']) ? $datos[0]['id_tercero_api'] : 0;
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
    $datas[$tps][$bs][$lt]['cantd'] = $fila['cantidad'];
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
    $iva += $dt['cantidad'] * ($dt['valu_ingresa'] * $dt['iva'] / 100);
    $subtotal += $dt['valu_ingresa'] * $dt['cantidad'];
}
$grantotal = $subtotal + $iva;
?>
<div class="py-3 text-right">
    <a type="" id="btnExcelEntrada" class="btn btn-outline-success btn-sm" value="01" title="Exprotar a Excel">
        <span class="fas fa-file-excel fa-lg" aria-hidden="true"></span>
    </a>
    <a type="button" class="btn btn-primary btn-sm" title="Imprimir" onclick="imprSelecTes('areaImprimir',<?php echo 0; ?>);"><span class="fas fa-print fa-lg" aria-hidden="true"></span></a>
    <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" title="Cerrar"><span class="fas fa-times fa-lg" aria-hidden="true"></span></a>
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
                                <td rowspan="2" class='text-center' style="width:18%"><label class="small"><img src="<?php echo $_SESSION['urlin'] ?>/images/logos/logo.png" width="100"></label></td>
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
                                    <b>SALIDA No: <?php echo isset($datos[0]['consecutivo']) ? str_pad($datos[0]['consecutivo'], 5, "0", STR_PAD_LEFT) : '' ?>
                                </td>
                                <td colspan="3">
                                    <b>TIPO: <?php echo isset($datos[0]['tipo_salida']) ? $datos[0]['tipo_salida'] : '';
                                                echo ' ' . $rel ?>
                                </td>
                                <td colspan="4" style="text-align: right;">Fecha entrada: <?php echo isset($datos[0]['fec_acta_remision']) ? date('Y/m/d', strtotime($datos[0]['fec_acta_remision'])) : '' ?></td>
                            </tr>
                            <tr>
                                <td colspan="10" style="text-align: right;">Fecha Imp: <?php echo $date->format('Y/m/d H:m:s') ?></td>
                            </tr>
                            <tr style="font-size: 85%;">
                                <td colspan="4">
                                    TERCERO: <?php echo $tercero ?>
                                </td>
                                <td colspan="3">
                                    NIT: <?php echo $ccnit ?>
                                </td>
                                <td colspan="3" style="text-align:right">

                                </td>
                            </tr>
                            <tr style="font-size: 85%;">
                                <td colspan="10" style="text-align:right">
                                    <span>ESTADO: <b><?php echo isset($datos[0]['estado']) ? $datos[0]['estado'] <= 2 ? 'BORRADOR' : 'DEFINITIVO' : '' ?></b></span>
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
                if (!empty($datas)) {
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
                } else {
                    echo '<tr style="font-size: 12px;">
                    <th style="text-align: center;" colspan="12">NO HAY REGISTROS DISPONIBLES</th>
                </tr>';
                }
                ?>
                <tr>
                    <td colspan="10" style="text-align: left; padding:5px;">
                        <span>OBSERVACIÓN: <b><?php echo isset($datos[0]['observacion']) ? $datos[0]['observacion'] : '' ?></b></span>
                    </td>
                <tr>
                    <td colspan="10" style="height: 30px;"></td>
                </tr>
                <tr>
                    <td colspan="10">
                        <table style="width: 100%; text-align:left">
                            <tr>
                                <td colspan="2">
                                    Elaboró:
                                </td>
                                <td colspan="3">
                                    _________________________________________
                                </td>
                                <td colspan="2">
                                </td>
                                <td colspan="3">
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
                                </td>
                                <td colspan="3">
                                </td>
                            </tr>
                            <?php if ($tipo_salida == 9) { ?>
                                <tr>
                                    <td colspan="10" style="padding-top: 30px;">
                                        SUJETO A APROBACIÓN: _________________________________________<br>
                                        Jefe administrativa y financiera.
                                    </td>
                                </tr>
                            <?php } ?>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>