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
                `seg_salida_dpdvo`.`id_pedido`
                , `seg_salidas_almacen`.`fec_reg`
            FROM
                `seg_salidas_almacen`
                INNER JOIN `seg_salida_dpdvo` 
                    ON (`seg_salidas_almacen`.`id_devolucion` = `seg_salida_dpdvo`.`id_devolucion`)
            WHERE (`seg_salida_dpdvo`.`id_pedido` = $id_pedido)
            ORDER BY `seg_salidas_almacen`.`fec_reg` ASC LIMIT 1";
    $res = $cmd->query($sql);
    $fec_inicia = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fec_in = !empty($fec_inicia) ? date('Y-m-d', strtotime($fec_inicia['fec_reg'])) :  $date->format('Y-m-d');
$fecha1 = isset($_POST['fecha1']) ? $_POST['fecha1'] : $fec_in;
$fecha2 = isset($_POST['fecha2']) ? $_POST['fecha2'] : $date->format('Y-m-d');
$condicicon = " AND `seg_salidas_almacen`.`fec_reg` BETWEEN '" . $fecha1 . "' AND '" . $fecha2 . " 23:59:59'";
try {
        $sql = "SELECT
                    `seg_salida_dpdvo`.`id_tipo_salida`
                    , `seg_tipo_salidas`.`descripcion`
                    , `seg_salida_dpdvo`.`id_pedido`
                    , `seg_salidas_almacen`.`id_entrada`
                    , `seg_salidas_almacen`.`cantidad`
                    , `seg_detalle_entrada_almacen`.`valu_ingresa`
                    , `seg_detalle_entrada_almacen`.`lote`
                    , `seg_detalle_entrada_almacen`.`marca`
                    , `seg_detalle_entrada_almacen`.`invima`
                    , `seg_detalle_entrada_almacen`.`fecha_vence`
                    , `seg_bien_servicio`.`bien_servicio`
                    , `seg_tipo_bien_servicio`.`tipo_bn_sv`
                    , `seg_tipo_bien_servicio`.`id_tipo_b_s`
                    , `seg_detalle_entrada_almacen`.`id_prod`
                    , `seg_detalle_entrada_almacen`.`iva`
                    , `seg_salida_dpdvo`.`id_devolucion` AS `id_consumo`
                    , `seg_pedidos_almacen`.`id_bodega`
                FROM
                    `seg_salidas_almacen`
                    INNER JOIN `seg_salida_dpdvo` 
                        ON (`seg_salidas_almacen`.`id_devolucion` = `seg_salida_dpdvo`.`id_devolucion`)
                    INNER JOIN `seg_tipo_salidas` 
                        ON (`seg_salida_dpdvo`.`id_tipo_salida` = `seg_tipo_salidas`.`id_salida`)
                    INNER JOIN `seg_detalle_entrada_almacen` 
                        ON (`seg_salidas_almacen`.`id_entrada` = `seg_detalle_entrada_almacen`.`id_entrada`)
                    INNER JOIN `seg_bien_servicio` 
                        ON (`seg_detalle_entrada_almacen`.`id_prod` = `seg_bien_servicio`.`id_b_s`)
                    INNER JOIN `seg_tipo_bien_servicio` 
                        ON (`seg_bien_servicio`.`id_tipo_bn_sv` = `seg_tipo_bien_servicio`.`id_tipo_b_s`)
                    INNER JOIN `seg_pedidos_almacen` 
                        ON (`seg_salida_dpdvo`.`id_pedido` = `seg_pedidos_almacen`.`id_pedido`)
                WHERE (`seg_salida_dpdvo`.`id_pedido` = $id_pedido" . $condicicon . ")
                ORDER BY `seg_tipo_bien_servicio`.`tipo_bn_sv` ASC
                    , `seg_bien_servicio`.`bien_servicio` ASC
                    , `seg_detalle_entrada_almacen`.`lote` ASC
                    , `seg_detalle_entrada_almacen`.`marca` ASC
                    , `seg_detalle_entrada_almacen`.`invima` ASC
                    ,`seg_salidas_almacen`.`cantidad` ASC";
    $res = $cmd->query($sql);
    $datos = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if (!empty($datos)) {
    $bodega = $datos[0]['id_bodega'];
} else {
    $bodega = 1;
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
            WHERE `seg_responsable_bodega`.`id_bodega` = $bodega";
    $res = $cmd->query($sql);
    $responsable = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$datas = [];
$consec = 0;
if (!empty($datos)) {
    foreach ($datos as $fila) {
        $tps = $fila['tipo_bn_sv'];
        $bs = $fila['bien_servicio'];
        $lt = $fila['lote'];
        if ($lt == '') {
            $lt = 'EACII' . $consec;
            $consec++;
        }
        if (isset($datas[$tps][$bs][$lt]['cantd']) && $datas[$tps][$bs][$lt]['cantd'] > 0) {
            $sumar = $datas[$tps][$bs][$lt]['cantd'] + $fila['cantidad'];
        } else {
            $sumar = $fila['cantidad'];
        }
        $datas[$tps][$bs][$lt]['cantd'] = $sumar;
        $datas[$tps][$bs][$lt]['datos']['costo'] =  $fila['valu_ingresa'] * (1 + $fila['iva'] / 100);
        $datas[$tps][$bs][$lt]['datos']['vence'] =  $fila['fecha_vence'];
        $datas[$tps][$bs][$lt]['datos']['id_bn'] =  $fila['id_prod'];
        $datas[$tps][$bs][$lt]['datos']['id_tb'] =  $fila['id_tipo_b_s'];
        $datas[$tps][$bs][$lt]['datos']['invima'] = $fila['invima'];
        $datas[$tps][$bs][$lt]['datos']['marca'] =  $fila['marca'];
        $datas[$tps][$bs][$lt]['datos']['iva'] =  $fila['iva'];
    }
}
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
?>
<div class="text-right py-3">
    <a type="button" id="btnExcelEntrada" class="btn btn-outline-success btn-sm" value="01" title="Exprotar a Excel">
        <span class="fas fa-file-excel fa-lg" aria-hidden="true"></span>
    </a>
    <a type="button" class="btn btn-primary btn-sm" onclick="printJS('areaImprimir', 'html')">Imprimir</a>
    <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cerrar</a>
</div>
<div class="form-row">
    <div class="form-group col-md-5">
        <label for="fecha1" class="small">Fecha Inicial</label>
        <input type="date" class="form-control form-control-sm" id="fecha1" value="<?php echo $fecha1 ?>">
    </div>
    <div class="form-group col-md-5">
        <label for="fecha2" class="small">Fecha Final</label>
        <input type="date" class="form-control form-control-sm" id="fecha2" value="<?php echo $fecha2 ?>">
    </div>
    <div class="form-group col-md-2">
        <label for="consumoXfechas" class="small">&nbsp;</label>
        <button type="button" class="btn btn-light btn-sm btn-block" id="consumoXfechas">Filtrar</button>
    </div>
</div>
<div class="content bg-light" id="areaImprimir">
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
    <table style="width:100% !important; border-collapse: collapse;">
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
                            <td colspan="6">
                                <b>CONSUMO No: <?php echo str_pad(isset($datos[0]['id_consumo']) ? $datos[0]['id_consumo'] : 0, 5, "0", STR_PAD_LEFT) ?>
                            </td>
                            <td colspan="3" style="font-size:9px;text-align: right;">
                                <table style="width:100% !important;">
                                    <tr>
                                        <td style="text-align: right;">Fecha Imp.</td>
                                        <td><?php echo $date->format('Y/m/d') ?></td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: right;">Fec. Consumo:</td>
                                        <td><?php echo date('Y/m/d', strtotime($fecha1)) . ' A ' . date('Y-m-d', strtotime($fecha2)) ?></td>
                                </table>
                            </td>
                        </tr>
                        <tr style="font-size: 85%; text-align:left">
                            <td colspan="10">CONSUME: <?php echo isset($datos[0]['id_consumo']) ? $responsable['nombre'] : '' ?></td>
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
        <?php if (!empty($datos)) { ?>
            <tbody style="font-size: 60%;">
                <?php
                $total = 0;
                $row_tipo = '';
                $lote = 'EAC';
                $cambia_color = 0;
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
                                if ($numLotes > 1) {
                                    $cambia_color++;
                                }
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
                                        <td style="text-align: right;">' . pesos($lote['datos']['costo']) . '</td>
                                        <td style="text-align: right;">' . pesos($lote['cantd'] * $lote['datos']['costo']) . '</td>
                                        </tr>';
                                        $cant_prom++;
                                        $suma_val = $suma_val + $lote['datos']['costo'];
                                        $cambia_color++;
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
                                        <td style="text-align: right;">' . pesos($lote['datos']['costo']) . '</td>
                                        <td style="text-align: right;">' . pesos($sumaLote * $lote['datos']['costo']) . '</td>
                                        </tr>';
                                        $cant_prom = 1;
                                        $suma_val =  $lote['datos']['costo'];
                                        $cambia_color++;
                                    }
                                }
                                if ($bandera) {
                                    $row_bien .= $row_lote;
                                } else {
                                    $cambia_color++;
                                    if ($cambia_color % 2 == 0) {
                                        $stilo = 'background-color: #F2F3F4;';
                                    } else {
                                        $stilo = 'background-color: #F8F9FA;';
                                    }
                                    $prom_valunid = $suma_val / $cant_prom;
                                    $row_bien .= '<tr class="resaltar">
                                        <td>' . $id_bien . '</td>
                                        <td style="text-align:right;">' . $keybn . '</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td>' . $sumaLote . '</td>
                                        <td></td>
                                        <td style="text-align: right;">' . pesos($prom_valunid) . '</td>
                                        <td style="text-align: right;">' . pesos($sumaLote * $prom_valunid) . '</td>
                                        </tr>' . $row_lote;
                                    $cambia_color++;
                                }
                                $valorXbien = $sumaLote * ($suma_val / $cant_prom);
                                $totalBien += $valorXbien;
                                $total += $valorXbien;
                            }
                        }
                        $row_tipo .= '<tr style="font-size: 11px;" class="resaltar">
                        <th>' . $id_tipo . '</th>
                        <th style="text-align: left;" colspan="8">' . $keytb . '</th>
                        <th style="text-align: right;">' . pesos($totalBien) . '</th>
                        </tr>' . $row_bien;
                    }
                }
                $totalExistencia = '<tr style="font-size: 12px;" class="resaltar">
                                    <th style="text-align: left;" colspan="8">ELEMENTOS DE CONSUMO O CARGO DIFERIDO</th>
                                    <th style="text-align: right;" colspan="2">' . pesos($total) . '</th>
                                </tr>' . $row_tipo;
                echo $totalExistencia;
                ?>
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
                                    Recibe:
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
                                    C.C.:
                                </td>
                                <td colspan="3">

                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </tbody>
        <?php } else { ?>
            <tr>
                <td colspan="10">
                    <div class="alert alert-warning" role="alert">
                        <h4 class="alert-heading">No hay datos</h4>
                        <p>No se encontraron datos para mostrar.</p>
                    </div>
                </td>
            </tr>
        <?php } ?>
    </table>
    <script src="https://printjs-4de6.kxcdn.com/print.min.js"></script>
    <script src="https://printjs-4de6.kxcdn.com/print.min.css"></script>
</div>