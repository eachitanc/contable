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
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
// consulto el nombre de la empresa de la tabla seg_empresas
$id_user = $_SESSION['id_user'];
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
            WHERE (`id_usuario` = $id_user)";
    $res = $cmd->query($sql);
    $usuario = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `id_sede`, `nombre`
            FROM
                `seg_sedes_empresa`";
    $res = $cmd->query($sql);
    $sedes = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$rol = $_SESSION['rol'];
try {
    $sql = "SELECT 
                `id_usuario`, `seg_bodega_almacen`.`id_bodega`, `seg_bodega_almacen`.`nombre`
            FROM 
                `seg_responsable_bodega`
            INNER JOIN `seg_bodega_almacen` 
                ON (`seg_responsable_bodega`.`id_bodega` = `seg_bodega_almacen`.`id_bodega`)
            WHERE`id_resp` IN (SELECT MAX(`id_resp`) FROM `seg_responsable_bodega` GROUP BY (`id_bodega`))
            ORDER BY `seg_bodega_almacen`.`nombre` ASC";
    $res = $cmd->query($sql);
    $allbodegas = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$id_bodega = isset($_POST['bodega']) ? $_POST['bodega'] : 0;
if ($id_bodega == -1) {
    if ($rol == 1 || $rol == 3) {
        $valida = '';
    } else {
        $valida = 'WHERE `id_usuario` = ' . $id_user;
    }
} else {
    $valida = "WHERE `id_bodega` = $id_bodega";
}
try {
    $sql = "SELECT
                `id_bodega`
                , `id_usuario`
            FROM
                `seg_responsable_bodega` $valida";
    $res = $cmd->query($sql);
    $list_bgs = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$allbg = [];
if (!empty($list_bgs)) {
    foreach ($list_bgs as $ab) {
        $allbg[] = $ab['id_bodega'];
    }
}
if ($rol == 1 || $rol == 3) {
    $bodegaxresp = '';
} else {
    try {
        $sql = "SELECT `id_usuario`,`id_bodega` FROM `seg_responsable_bodega` 
                WHERE `id_resp` IN (SELECT MAX(`id_resp`) FROM `seg_responsable_bodega` WHERE `id_usuario` = $id_user GROUP BY (`id_bodega`))";
        $res = $cmd->query($sql);
        $bgxresp = $res->fetchAll();
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    $ids = [];
    if (!empty($bgxresp)) {
        foreach ($bgxresp as $br) {
            $ids[] = $br['id_bodega'];
        }
    }
    $bodegaxresp = ' AND (`seg_bodega_almacen`.`id_bodega` IN (' . implode(',', $ids) . '))';
}
if (isset($_POST['sede'])) {
    try {
        $sql = "SELECT
                    `id_bodega`, `nombre`, `id_sede`
                FROM
                    `seg_bodega_almacen`
                WHERE (`id_sede` = $_POST[sede])" . $bodegaxresp;
        $res = $cmd->query($sql);
        $bodegas = $res->fetchAll();
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
}
$fecha = isset($_POST['fecha']) ? $_POST['fecha'] : '';
$fecha = $fecha == '' ? '2999-12-31 23:59:59' : $fecha . ' 23:59:59';
$datas = [];
$resp = '';
$datos = [];
if (isset($_POST['bodega'])) {
    $cond_ceros = 'WHERE `t1`.`existe` > 0';
    try {
        if ($id_bodega == -1) {
            $id_bg = $allbg;
        } else {
            $id_bg[] = $id_bodega;
        }
        $proveedor = '';
        $suma = '`entradas`.`cant_entra`';
        $on = '';
        $tabla = '`entradas`';
        foreach ($id_bg as $key => $ib) {
            if ($id_bodega == 1) {
                $proveedor = "(SELECT
                                `id_entrada`
                                , `cant_ingresa`
                            FROM
                                `seg_detalle_entrada_almacen`
                            WHERE (`id_bodega` = " . $ib . " AND `seg_detalle_entrada_almacen`.`fec_reg` <= '" . $fecha . "')) AS `proveedor` 
                            LEFT JOIN";
                $suma = 'IFNULL(`proveedor`.`cant_ingresa`,0) + IFNULL(`entradas`.`cant_entra`,0)';
                $on = 'ON (`proveedor`.`id_entrada` = `entradas`.`id_entrada`)';
                $tabla = '`proveedor`';
            }
            $sql = "SELECT 
                    `t1`.`id_entrada`
                    , CASE WHEN `t1`.`existe`  < 0 THEN 0 ELSE `t1`.`existe` END AS `queda`
                    , `seg_marcas`.`descripcion` as `marca`
                    , `seg_detalle_entrada_almacen`.`id_prod`
                    , `seg_detalle_entrada_almacen`.`iva`
                    , `seg_detalle_entrada_almacen`.`lote`
                    , `seg_detalle_entrada_almacen`.`id_lote`
                    , `seg_detalle_entrada_almacen`.`invima`
                    , `seg_detalle_entrada_almacen`.`valu_ingresa`
                    , `seg_detalle_entrada_almacen`.`fecha_vence`
                    , `seg_bien_servicio`.`id_tipo_bn_sv`
                    , `seg_bien_servicio`.`bien_servicio` 
                    , `seg_tipo_bien_servicio`.`id_tipo_cotrato`
                    , `seg_tipo_bien_servicio`.`tipo_bn_sv`
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
                    WHERE (`seg_detalles_traslado`.`estado` > 0 AND `seg_traslados_almacen`.`id_bodega_entra` = $ib AND `seg_traslados_almacen`.`fec_reg` <= '$fecha')
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
                    WHERE (`seg_detalles_traslado`.`estado` > 0 AND `seg_traslados_almacen`.`id_bodega_sale` = $ib AND `seg_traslados_almacen`.`fec_reg` <= '$fecha')
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
                    WHERE (`seg_salidas_almacen`.`estado` > 0 AND `seg_pedidos_almacen`.`id_bodega` = $ib AND `seg_salidas_almacen`.`fec_reg` <= '$fecha')
                    GROUP BY `seg_salidas_almacen`.`id_entrada`) AS `consumo` 
                        ON ($tabla.`id_entrada` = `consumo`.`id_entrada`)) AS `t1`
                INNER JOIN `seg_detalle_entrada_almacen`
                    ON (`seg_detalle_entrada_almacen`.`id_entrada` = `t1`.`id_entrada`)
                LEFT JOIN `seg_marcas` 
                        ON (`seg_detalle_entrada_almacen`.`id_marca` = `seg_marcas`.`id_marca`)
                INNER JOIN `seg_bien_servicio`
                    ON (`seg_detalle_entrada_almacen`.`id_prod` = `seg_bien_servicio`.`id_b_s`)
                INNER JOIN `seg_tipo_bien_servicio`
                    ON (`seg_bien_servicio`.`id_tipo_bn_sv` = `seg_tipo_bien_servicio`.`id_tipo_b_s`)
                $cond_ceros
                ORDER BY `seg_tipo_bien_servicio`.`tipo_bn_sv`,`seg_bien_servicio`.`bien_servicio`,`seg_detalle_entrada_almacen`.`lote` DESC";
            $res = $cmd->query($sql);
            $datos[$ib] = $res->fetchAll(PDO::FETCH_ASSOC);
        }
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
    $productos = [];
    $productos[0] = 0;
    $consec = 0;
    $subtotal = 0;
    $iva = 0;
    foreach ($datos as $key => $fila) {
        if (!empty($fila)) {
            foreach ($fila as $fl) {
                $tps = $fl['tipo_bn_sv'];
                $bs = $fl['bien_servicio'];
                $cta_contable = '';
                foreach ($cuentas as $cta) {
                    if ($cta['id_bodega'] == $key && $cta['id_tipo_bn_sv'] == $fl['id_tipo_bn_sv']) {
                        $cta_contable = $cta['cuenta'];
                        break;
                    }
                }
                $lt = $fl['lote'] == '' ? 'EACII' . $consec : $fl['lote'];
                $queda = $fl['queda'];
                $sumalote = isset($datas[$key][$tps][$bs][$lt]['cantd']) ? $datas[$key][$tps][$bs][$lt]['cantd'] : 0;
                $datas[$key][$tps][$bs][$lt]['cantd'] = $queda + $sumalote;
                $costo = $fl['valu_ingresa'] + $fl['valu_ingresa'] * $fl['iva'] / 100;
                $datas[$key][$tps][$bs][$lt]['datos']['costo'] =  $costo;
                $datas[$key][$tps][$bs][$lt]['datos']['vence'] =  $fl['fecha_vence'];
                $datas[$key][$tps][$bs][$lt]['datos']['id_bn'] =  $fl['id_prod'];
                $datas[$key][$tps][$bs][$lt]['datos']['id_tb'] =  $fl['id_tipo_bn_sv'];
                $datas[$key][$tps][$bs][$lt]['datos']['invima'] =  $fl['invima'];
                $datas[$key][$tps][$bs][$lt]['datos']['marca'] =  $fl['marca'];
                $datas[$key][$tps][$bs][$lt]['datos']['cuenta'] =  $cta_contable;
                $idProd = $fl['id_prod'];
                $productos[$idProd] = $idProd;
            }
        }
    }
    $productos = implode(',', $productos);
    try {
        $sql = "SELECT
                    `id_prod`
                    , AVG(`valu_ingresa` * (1+ `iva`/100)) AS `val_prom`
                FROM
                    `seg_detalle_entrada_almacen`
                WHERE  `id_prod` IN ($productos) AND `fec_reg` <= '$fecha'
                GROUP BY `id_prod`";
        $res = $cmd->query($sql);
        $promedios = $res->fetchAll();
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    if ($id_bodega > 0 && $id_bodega != -1) {
        try {
            $sql = "SELECT `id_usuario`,`id_bodega` FROM `seg_responsable_bodega` 
                WHERE `id_resp`
                    IN (SELECT MAX(`id_resp`) FROM `seg_responsable_bodega` WHERE `id_bodega` = $id_bodega GROUP BY (`id_bodega`))";
            $res = $cmd->query($sql);
            $idresponsable = $res->fetch();
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
        }
        $id_resp = !empty($idresponsable) ? $idresponsable['id_usuario'] : 0;
        try {
            $sql = "SELECT 
                        `id_usuario`,CONCAT_WS(' ',`nombre1`,`nombre2`,`apellido1`,`apellido2`) AS `nombre` 
                    FROM `seg_usuarios` WHERE `id_usuario` = $id_resp;";
            $res = $cmd->query($sql);
            $responsable = $res->fetch();
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
        }
        $resp = !empty($responsable) ? $responsable['nombre'] : '';
    }
}
$farmacia = [];
$inicial = [];
if ($id_bodega == 40 && $_POST['optionCeros'] != 1) {
    try {
        $sql = "SELECT
                    `lote`, SUM(`cantidad`) AS `cantidad`
                FROM
                    `seg_ids_farmacia`
                INNER JOIN `vista_salidas_farmacia` 
                    ON (`seg_ids_farmacia`.`id_med` = `vista_salidas_farmacia`.`id_med`)
                WHERE (`vista_salidas_farmacia`.`fec_cierre` <= '$fecha')
                GROUP BY `lote`";
        $res = $cmd->query($sql);
        $farmacia = $res->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    try {
        $sql = "SELECT 
                    `t1`.`lote`, SUM(`t1`.`cantidad`) AS `cantidad` 
                FROM 
                    (SELECT
                        `far_orden_ingreso`.`id_ingreso`
                        , `far_medicamentos`.`id_med`
                        , `far_medicamento_lote`.`id_lote`
                        , `far_medicamento_lote`.`lote`
                        , `far_medicamento_lote`.`fec_vencimiento`
                        , `far_orden_ingreso`.`fec_cierre`    
                        , `far_orden_ingreso_detalle`.`cantidad`
                        , `far_orden_ingreso_detalle`.`valor`       
                    FROM   
                        $bd_base_f.`far_orden_ingreso_detalle`
                        INNER JOIN $bd_base_f.`far_orden_ingreso` 
                            ON (`far_orden_ingreso_detalle`.`id_ingreso` = `far_orden_ingreso`.`id_ingreso`)
                        INNER JOIN $bd_base_f.`far_medicamento_lote` 
                            ON (`far_orden_ingreso_detalle`.`id_lote` = `far_medicamento_lote`.`id_lote`)
                        INNER JOIN $bd_base_f.`far_medicamentos` 
                            ON (`far_medicamento_lote`.`id_med` = `far_medicamentos`.`id_med`)
                    WHERE `far_orden_ingreso`.`id_ingreso` IN (1,2,3,4,6)
                        AND `far_orden_ingreso`.`fec_cierre` <= '2023-12-31 23:59:59') AS `t1`
                GROUP BY `t1`.`lote`";
        $res = $cmd->query($sql);
        $inicial = $res->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
}
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
?>
<form id="formInvFisico">
    <div class="form-row">
        <div class="form-group col-md-4">
            <label for="sedeif" class="small">SEDE</label>
            <select class="form-control form-control-sm" id="sedeif" name="sede">
                <option value="0">--Selecionar--</option>
                <?php
                foreach ($sedes as $fila) {
                    if ($fila['nombre'] != 'CONVENIOS') {
                        $id_sede = isset($_POST['sede']) ? $_POST['sede'] : 0;
                        $slc = $fila['id_sede'] == $id_sede ? 'selected' : '';
                        echo '<option value="' . $fila['id_sede'] . '" ' . $slc . '>' . $fila['nombre'] . '</option>';
                    }
                }
                ?>
            </select>
        </div>
        <div class="form-group col-md-4">
            <label for="bodega" class="small">BODEGA</label>
            <select class="form-control form-control-sm" id="bodega" name="bodega">
                <option value="0">--Selecionar--</option>
                <option value="-1" <?php echo $id_bodega == -1 ? 'selected' : '' ?>>TODO</option>
                <?php
                $bodegaslc = '';
                if (isset($bodegas)) {
                    foreach ($bodegas as $bg) {
                        if ($bg['id_bodega'] == $id_bodega) {
                            $slc = 'selected';
                            $bodegaslc = $bg['nombre'];
                        } else {
                            $slc = '';
                        }
                        echo '<option value="' . $bg['id_bodega'] . '" ' . $slc . '>' . $bg['nombre'] . '</option>';
                    }
                }
                ?>
            </select>
        </div>
        <div class="form-group col-md-1">
            <label class="small">&nbsp;</label>
            <div>
                <a type="button" id="btnGenInvFisico" class="btn btn-outline-warning btn-sm" title="Filtrar">
                    <span class="fas fa-filter fa-lg" aria-hidden="true"></span>
                </a>
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
        <div class="form-group col-md-3">
            <input type="date" class="form-control form-control-sm" id="fecha" name="fecha" value="<?php echo $fecha == '2999-12-31 23:59:59' ? '' : substr($fecha, 0, 10); ?>">
        </div>
    </div>
</form>
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
                    <td colspan="8">
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
                                    <center>CAPTURA INVENTARIO FISICO EN<b> <?php echo mb_strtoupper($bodegaslc) ?></b></center>
                                </td>
                                <td colspan="4" style="text-align: right; font-size:70%">Imp. <?php echo $date->format('d/m/Y H:i') ?></td>
                            </tr>
                            <tr>
                                <td colspan="8" style="font-size: 90%;text-align:left">
                                    RESPONSABLE: <?php echo mb_strtoupper($resp) ?>
                                </td>
                                <td style="font-size: 90%; text-align:right">
                                    <span><b>CORTE:</b> <?php echo $fecha == '2999-12-31 23:59:59' ? date('Y/m/d') : substr($fecha, 0, 10); ?></span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr style="background-color: #CED3D3;">
                    <th>ID</th>
                    <th>Cuenta</th>
                    <th>Producto</th>
                    <th>Vence</th>
                    <th>Lote</th>
                    <th>Cant.</th>
                    <th style="width: 90px;">Físico</th>
                    <th style="width: 90px;">Diferencia</th>
                </tr>
            </thead>
            <tbody style="font-size: 75%;">
                <?php
                if (!empty($datas)) {
                    $total = 0;
                    $row_tipo = '';
                    $lote = 'EAC';
                    $valorXbien = 0;
                    $row_bg = '';
                    foreach ($datas as $key => $filtro) {
                        if (!empty($filtro)) {
                            $bc = array_search($key, array_column($allbodegas, 'id_bodega'));
                            $row_tipo = '';
                            $totalBodega = 0;
                            foreach ($filtro as $keytb => $tipob) {
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
                                                $id_bien = $lote['datos']['id_bn'];
                                                $id_tipo = $lote['datos']['id_tb'];
                                                $cta_ctb = $lote['datos']['cuenta'];
                                                $keylt = strncmp($keylt, 'EACII', strlen('EACII')) === 0 ? '' : $keylt;
                                                $keyconsumo = array_search($keylt, array_column($farmacia, 'lote'));
                                                $con_far = $keyconsumo !== false ? $farmacia[$keyconsumo]['cantidad'] : 0;
                                                $keyinicial = array_search($keylt, array_column($inicial, 'lote'));
                                                $inv_ini =  $keyinicial !== false ? $inicial[$keyinicial]['cantidad'] : 0;
                                                $disponible =  $lote['cantd'] - $con_far + $inv_ini;
                                                if ($numLotes > 1) {
                                                    $sumaLote += $disponible;
                                                    $row_lote .= '<tr class="resaltar" style="height: 30px;">
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td>' . $lote['datos']['vence'] . '</td>
                                                    <td>' . $keylt . '</td>
                                                    <td style="text-align: center;">' . $disponible . '</td>
                                                    <td style="text-align: center;"> _____  _____ </td>
                                                    <td style="text-align: center;"> _____  _____ </td>
                                                    </tr>';
                                                    if (($disponible) > 0) {
                                                        $cant_prom++;
                                                        $suma_val = $suma_val + $lote['datos']['costo'];
                                                    }
                                                } else {
                                                    $bandera = true;
                                                    $sumaLote = $disponible;
                                                    $row_lote = '<tr  class="resaltar" style="height: 30px;">
                                                    <td>' . $id_bien . '</td>
                                                    <td colspan="2" style="text-align: left;">' . $keybn . '</td>
                                                    <td>' . $lote['datos']['vence'] . '</td>
                                                    <td>' . $keylt . '</td>
                                                    <th>' . $disponible . '</th>
                                                    <td style="text-align: center;"> _____  _____ </td>
                                                    <td style="text-align: center;"> _____  _____ </td>
                                                    </tr>';
                                                    $cant_prom = 1;
                                                    $suma_val =  $lote['datos']['costo'];
                                                }
                                            }
                                            $keypm = array_search($id_bien, array_column($promedios, 'id_prod'));
                                            $val_prom = $keypm !== false ? $promedios[$keypm]['val_prom'] : 0;
                                            if ($bandera) {
                                                $row_bien .=  $row_lote;
                                            } else {
                                                if ($cant_prom == 0) {
                                                    $cant_prom = 1;
                                                }
                                                $prom_valunid =  $val_prom == 0 ? $suma_val / $cant_prom : $val_prom;
                                                $row_bien .= '<tr class="resaltar" style="height: 30px;">
                                                <td>' . $id_bien . '</td>
                                                <td style="text-align: left;">' . $keybn . '</td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <th>' . $sumaLote . '</th>
                                                <td style="text-align: center;"> _____  _____ </td>
                                                <td style="text-align: center;"> _____  _____ </td>
                                                </tr>' . $row_lote;
                                            }
                                            $prom_valunid =  $val_prom == 0 ? $suma_val / $cant_prom : $val_prom;
                                            $valorXbien = $sumaLote * $prom_valunid;
                                            $totalBien += $valorXbien;
                                            $total += $valorXbien;
                                        }
                                    }
                                    $row_tipo .= '<tr class="resaltar">
                                    <th>' . $id_tipo . '</th>
                                    <th>' . $cta_ctb . '</th>
                                    <th style="text-align: left;" colspan="5">' . $keytb . '</th>
                                    <th style="text-align: right;"></th>
                                    </tr>' . $row_bien;
                                    $totalBodega += $totalBien;
                                }
                            }
                            $row_bodega = '<tr  class="resaltar"><th colspan="7" style="text-align:center;">' . $allbodegas[$bc]['nombre'] . '</th></tr>';
                            $row_bg .= $row_bodega . $row_tipo;
                        }
                    }
                    $totalExistencia = '<tr class="resaltar">
                                    <th style="text-align: left;" colspan="7">ELEMENTOS DE CONSUMO O CARGO DIFERIDO</th>
                                </tr>' . $row_bg;
                    echo $totalExistencia . '<tr>
                    <td style="text-align: left;" colspan="7">Nota: El presente conteo de inventarios se inicio en la fecha y Hora: __________________, termino en la Fecha y Hora:__________</td>
                </tr>
                <tr style="height: 40px;"><td colspan="7"></td></tr>
                <tr>
                    <td style="text-align: left;" colspan="3">_______________________________</td>
                    <td style="text-align: left;" colspan="4">_______________________________</td>
                </tr>
                <tr>
                    <td style="text-align: left;" colspan="3">Responsables Toma de Inventario:</td>
                    <td style="text-align: left;" colspan="4">Recibe Responsable:</td>
                </tr>
                <tr>
                    <td style="text-align: left;" colspan="3">C.C:</td>
                    <td style="text-align: left;" colspan="4">C.C:</td>
                </tr>
                <tr>
                    <td style="text-align: left;" colspan="3">Cargo:</td>
                    <td style="text-align: left;" colspan="4">Cargo:</th>
                </tr>';
                } else {
                    echo '<tr  class="resaltar"><td colspan="7" style="text-align:center">No hay datos para mostrar</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>