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
$id_art = isset($_POST['articulo']) ? $_POST['articulo'] : exit('Acción no permitida');
$bodega = isset($_POST['bodega']) ? $_POST['bodega'] : exit('Acción no permitida');
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
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fec_in = $date->format('Y-m-d');
$fecha1 = isset($_POST['fecha1']) ? $_POST['fecha1'] : $fec_in;
$fecha2 = isset($_POST['fecha2']) ? $_POST['fecha2'] : $date->format('Y-m-d');
$id_marca = isset($_POST['id_mrc']) ? $_POST['id_mrc'] : 0;
$cond_marca = $id_marca == 0 ? '' : 'AND `id_marca` = ' . $id_marca;
$farmacia = $nullos = '';
if ($bodega == 1) {
    $nullos = "UNION ALL
                SELECT
                    `seg_salida_dpdvo`.`consecutivo`
                    , `seg_salidas_almacen`.`id_entrada`
                    , `seg_salidas_almacen`.`id_producto`
                    , `seg_pedidos_almacen`.`id_bodega`
                    , `seg_bodega_almacen`.`nombre`
                    , `seg_sedes_empresa`.`id_sede`
                    , `seg_sedes_empresa`.`nombre`
                    , IFNULL(`seg_salida_dpdvo`.`id_tercero_api`,0) AS `tercero`
                    , `seg_salida_dpdvo`.`acta_remision`
                    , `seg_salidas_almacen`.`cantidad`
                    , `seg_detalle_entrada_almacen`.`valu_ingresa` +  `seg_detalle_entrada_almacen`.`valu_ingresa` * (`seg_detalle_entrada_almacen`.`iva` / 100) AS `valu_ingresa`
                    , `seg_detalle_entrada_almacen`.`lote`
                    , `seg_detalle_entrada_almacen`.`id_marca`
                    , `seg_detalle_entrada_almacen`.`invima`
                    , `seg_detalle_entrada_almacen`.`fecha_vence`
                    , `seg_salidas_almacen`.`fec_reg`
                    , `seg_tipo_salidas`.`descripcion`
                    , '5' AS `tipo` 
                FROM
                    `seg_salidas_almacen`
                    LEFT JOIN `seg_salida_dpdvo` 
                        ON (`seg_salidas_almacen`.`id_devolucion` = `seg_salida_dpdvo`.`id_devolucion`)
                        LEFT JOIN `seg_tipo_salidas` 
                        ON (`seg_salida_dpdvo`.`id_tipo_salida` = `seg_tipo_salidas`.`id_salida`)
                    LEFT JOIN `seg_pedidos_almacen` 
                        ON (`seg_salida_dpdvo`.`id_pedido` = `seg_pedidos_almacen`.`id_pedido`)
                    LEFT JOIN `seg_detalle_entrada_almacen` 
                        ON (`seg_salidas_almacen`.`id_entrada` = `seg_detalle_entrada_almacen`.`id_entrada`)
                    LEFT JOIN `seg_bodega_almacen` 
                        ON (`seg_pedidos_almacen`.`id_bodega` = `seg_bodega_almacen`.`id_bodega`)
                    LEFT JOIN `seg_sedes_empresa` 
                        ON (`seg_bodega_almacen`.`id_sede` = `seg_sedes_empresa`.`id_sede`)
                WHERE `seg_salidas_almacen`.`id_producto` = $id_art AND `seg_pedidos_almacen`.`id_bodega` IS NULL";
}
if ($bodega == 40) {
    $farmacia = "UNION ALL
                    SELECT 
                        '' AS `id_trasl_alm`
                        , '' AS `id_entrada`
                        , `seg_ids_farmacia`.`id_prod`
                        , '40' AS `id_bodega_sale`
                        , 'FARMACIA' AS  `nombre`
                        , '' AS `id_sede`
                        , '' AS `sede`
                        , '0' AS `tercero`
                        , '' AS `acta_remision`
                        , `t1`.`cantidad`
                        , `t1`.`valor`
                        , `t1`.`lote`
                        , 0  AS `marca`
                        , '' AS `invima`
                        , `t1`.`fec_vencimiento`
                        , `t1`.`fec_cierre`
                        , 'INVENTARIO INICIAL' AS `tipo_entrada`
                        , '1' AS `tipo` 
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
                            AND `far_orden_ingreso`.`fec_cierre` <= '$fecha2 23:59:59') AS `t1`
                    LEFT JOIN `seg_ids_farmacia`
                        ON (`seg_ids_farmacia`.`id_med` = `t1`.`id_med`)
                    WHERE `seg_ids_farmacia`.`id_prod` = $id_art
                    UNION ALL 
                    SELECT
                        `vista_salidas_farmacia`.`id_egreso`AS `id_trasl_alm`
                        , '' AS `id_entrada` 
                        , `seg_ids_farmacia`.`id_prod`
                        , '40' AS `id_bodega_sale`
                        , 'FARMACIA' AS  `nombre`
                        , '' AS `id_sede`
                        , '' AS `sede`
                        , '0' AS `tercero`
                        , '' AS `acta_remision`
                        , `vista_salidas_farmacia`.`cantidad`  AS `consumo`
                        , `vista_salidas_farmacia`.`val_promedio`  AS `valor`
                        , `vista_salidas_farmacia`.`lote`
                        , '0' AS `id_marca`
                        , `vista_salidas_farmacia`.`reg_invima`
                        , `vista_salidas_farmacia`.`fec_vencimiento`
                        , `vista_salidas_farmacia`.`fec_cierre`
                        , `vista_salidas_farmacia`.`nom_tipo_egreso`
                        , '4' AS `tipo`
                        
                    FROM
                        `vista_salidas_farmacia`
                        INNER JOIN `seg_ids_farmacia` 
                            ON (`vista_salidas_farmacia`.`id_med` = `seg_ids_farmacia`.`id_med`)
                    WHERE (`seg_ids_farmacia`.`id_prod` = $id_art AND `vista_salidas_farmacia`.`fec_cierre` <= '$fecha2 23:59:59')";
}
try {
    $sql = "SELECT * FROM 
                (SELECT
                    `seg_entrada_almacen`.`consecutivo`
                    , `seg_detalle_entrada_almacen`.`id_entrada`
                    , `seg_detalle_entrada_almacen`.`id_prod`
                    , `seg_detalle_entrada_almacen`.`id_bodega`
                    , `seg_bodega_almacen`.`nombre` AS `bodega`
                    , `seg_detalle_entrada_almacen`.`id_sede`
                    , `seg_sedes_empresa`.`nombre` AS `sede`
                    , IFNULL(`seg_entrada_almacen`.`id_tercero_api`,0) AS tercero
                    , CONCAT(`seg_entrada_almacen`.`no_factura`, ' - ',`seg_entrada_almacen`.`acta_remision`) AS `factura`
                    , `seg_detalle_entrada_almacen`.`cant_ingresa` AS `cantidad`
                    , `seg_detalle_entrada_almacen`.`valu_ingresa` +  `seg_detalle_entrada_almacen`.`valu_ingresa` * (`seg_detalle_entrada_almacen`.`iva` / 100) AS `valu_ingresa`
                    , `seg_detalle_entrada_almacen`.`lote`
                    , `seg_detalle_entrada_almacen`.`id_marca`
                    , `seg_detalle_entrada_almacen`.`invima`
                    , `seg_detalle_entrada_almacen`.`fecha_vence`
                    , `seg_detalle_entrada_almacen`.`fec_reg`
                    , `seg_tipo_entrada`.`descripcion` AS `tipo_entrada`
                    , '1' AS `tipo` 
                FROM
                    `seg_detalle_entrada_almacen`
                    INNER JOIN `seg_sedes_empresa` 
                        ON (`seg_detalle_entrada_almacen`.`id_sede` = `seg_sedes_empresa`.`id_sede`)
                    INNER JOIN `seg_bodega_almacen` 
                        ON (`seg_detalle_entrada_almacen`.`id_bodega` = `seg_bodega_almacen`.`id_bodega`)
                    INNER JOIN `seg_entrada_almacen` 
                        ON (`seg_detalle_entrada_almacen`.`id_entra` = `seg_entrada_almacen`.`id_entrada`)
                    INNER JOIN `seg_tipo_entrada` 
                        ON (`seg_entrada_almacen`.`id_tipo_entrada` = `seg_tipo_entrada`.`id_entrada`)
                WHERE (`seg_detalle_entrada_almacen`.`id_prod` = $id_art AND `seg_detalle_entrada_almacen`.`id_bodega` = $bodega)
                UNION ALL 
                SELECT
                    `seg_traslados_almacen`.`id_trasl_alm`
                    , `seg_detalles_traslado`.`id_entrada`
                    , `seg_detalles_traslado`.`id_producto`
                    , `seg_traslados_almacen`.`id_bodega_entra`
                    , `seg_bodega_almacen`.`nombre` 
                    , `seg_traslados_almacen`.`id_sede_entra`
                    , `seg_sedes_empresa`.`nombre`
                    , '0' AS `tercero`
                    , `seg_traslados_almacen`.`acta_remision`
                    , `seg_detalles_traslado`.`cantidad`
                    , `seg_detalle_entrada_almacen`.`valu_ingresa` +  `seg_detalle_entrada_almacen`.`valu_ingresa` * (`seg_detalle_entrada_almacen`.`iva` / 100) AS `valu_ingresa`
                    , `seg_detalle_entrada_almacen`.`lote`
                    , `seg_detalle_entrada_almacen`.`id_marca`
                    , `seg_detalle_entrada_almacen`.`invima`
                    , `seg_detalle_entrada_almacen`.`fecha_vence`
                    , `seg_detalles_traslado`.`fec_reg`
                    , 'TRASLADO' AS `tipo_entrada` 
                    , '2' AS `tipo` 
                FROM
                    `seg_detalles_traslado`
                    INNER JOIN `seg_traslados_almacen` 
                        ON (`seg_detalles_traslado`.`id_traslado` = `seg_traslados_almacen`.`id_trasl_alm`)
                    INNER JOIN `seg_sedes_empresa` 
                        ON (`seg_traslados_almacen`.`id_sede_sale` = `seg_sedes_empresa`.`id_sede`)
                    INNER JOIN `seg_bodega_almacen` 
                        ON (`seg_traslados_almacen`.`id_bodega_sale` = `seg_bodega_almacen`.`id_bodega`)
                    INNER JOIN `seg_detalle_entrada_almacen` 
                        ON (`seg_detalles_traslado`.`id_entrada` = `seg_detalle_entrada_almacen`.`id_entrada`)
                WHERE `seg_detalles_traslado`.`id_producto` = $id_art AND `seg_traslados_almacen`.`id_bodega_entra` = $bodega
                UNION ALL
                SELECT
                    `seg_traslados_almacen`.`id_trasl_alm`
                    ,`seg_detalles_traslado`.`id_entrada`
                    , `seg_detalles_traslado`.`id_producto`
                    , `seg_traslados_almacen`.`id_bodega_sale`
                    , `seg_bodega_almacen`.`nombre` 
                    , `seg_traslados_almacen`.`id_sede_sale`
                    , `seg_sedes_empresa`.`nombre`
                    , '0' AS `tercero`
                    , `seg_traslados_almacen`.`acta_remision`
                    , `seg_detalles_traslado`.`cantidad`
                    , `seg_detalle_entrada_almacen`.`valu_ingresa` +  `seg_detalle_entrada_almacen`.`valu_ingresa` * (`seg_detalle_entrada_almacen`.`iva` / 100) AS `valu_ingresa`
                    , `seg_detalle_entrada_almacen`.`lote`
                    , `seg_detalle_entrada_almacen`.`id_marca`
                    , `seg_detalle_entrada_almacen`.`invima`
                    , `seg_detalle_entrada_almacen`.`fecha_vence`
                    , `seg_detalles_traslado`.`fec_reg`
                    , 'TRASLADO' AS `tipo_entrada` 
                    , '3' AS `tipo` 
                FROM
                    `seg_detalles_traslado`
                    INNER JOIN `seg_traslados_almacen` 
                        ON (`seg_detalles_traslado`.`id_traslado` = `seg_traslados_almacen`.`id_trasl_alm`)
                    INNER JOIN `seg_sedes_empresa` 
                        ON (`seg_traslados_almacen`.`id_sede_entra` = `seg_sedes_empresa`.`id_sede`)
                    INNER JOIN `seg_bodega_almacen` 
                        ON (`seg_traslados_almacen`.`id_bodega_entra` = `seg_bodega_almacen`.`id_bodega`)
                    INNER JOIN `seg_detalle_entrada_almacen` 
                        ON (`seg_detalles_traslado`.`id_entrada` = `seg_detalle_entrada_almacen`.`id_entrada`)
                WHERE `seg_detalles_traslado`.`id_producto` = $id_art AND `seg_traslados_almacen`.`id_bodega_sale` = $bodega
                $farmacia
                UNION ALL
                SELECT
                    `seg_salida_dpdvo`.`consecutivo`
                    , `seg_salidas_almacen`.`id_entrada`
                    , `seg_salidas_almacen`.`id_producto`
                    , `seg_pedidos_almacen`.`id_bodega`
                    , `seg_bodega_almacen`.`nombre`
                    , `seg_sedes_empresa`.`id_sede`
                    , `seg_sedes_empresa`.`nombre`
                    , IFNULL(`seg_salida_dpdvo`.`id_tercero_api`,0) AS `tercero`
                    , `seg_salida_dpdvo`.`acta_remision`
                    , `seg_salidas_almacen`.`cantidad`
                    , `seg_detalle_entrada_almacen`.`valu_ingresa` +  `seg_detalle_entrada_almacen`.`valu_ingresa` * (`seg_detalle_entrada_almacen`.`iva` / 100) AS `valu_ingresa`
                    , `seg_detalle_entrada_almacen`.`lote`
                    , `seg_detalle_entrada_almacen`.`id_marca`
                    , `seg_detalle_entrada_almacen`.`invima`
                    , `seg_detalle_entrada_almacen`.`fecha_vence`
                    , `seg_salidas_almacen`.`fec_reg`
                    , `seg_tipo_salidas`.`descripcion`
                    , '4' AS `tipo` 
                FROM
                    `seg_salidas_almacen`
                    INNER JOIN `seg_salida_dpdvo` 
                        ON (`seg_salidas_almacen`.`id_devolucion` = `seg_salida_dpdvo`.`id_devolucion`)
                    INNER JOIN `seg_tipo_salidas` 
                        ON (`seg_salida_dpdvo`.`id_tipo_salida` = `seg_tipo_salidas`.`id_salida`)
                    LEFT JOIN `seg_pedidos_almacen` 
                        ON (`seg_salida_dpdvo`.`id_pedido` = `seg_pedidos_almacen`.`id_pedido`)
                    INNER JOIN `seg_detalle_entrada_almacen` 
                        ON (`seg_salidas_almacen`.`id_entrada` = `seg_detalle_entrada_almacen`.`id_entrada`)
                    INNER JOIN `seg_bodega_almacen` 
                        ON (`seg_pedidos_almacen`.`id_bodega` = `seg_bodega_almacen`.`id_bodega`)
                    INNER JOIN `seg_sedes_empresa` 
                        ON (`seg_bodega_almacen`.`id_sede` = `seg_sedes_empresa`.`id_sede`)
                WHERE `seg_salidas_almacen`.`id_producto` = $id_art AND `seg_pedidos_almacen`.`id_bodega` = $bodega $nullos) AS `t1`
            WHERE `t1`.`fec_reg` <='$fecha2 23:59:59' $cond_marca
            ORDER BY `t1`.`fec_reg` ASC ";
    $rs = $cmd->query($sql);
    $movimientos = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$id_t = [];
foreach ($movimientos as $l) {
    $id_t[] = $l['tercero'];
}
$payload = json_encode($id_t);
//API URL
$url = $api . 'terceros/datos/res/lista/terceros';
$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
$terceros = json_decode($result, true);
if ($terceros == '0') {
    $terceros = [];
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
try {
    $sql = "SELECT
                `id_b_s`, `bien_servicio`
            FROM
                `seg_bien_servicio`
            WHERE `id_b_s` = $id_art";
    $res = $cmd->query($sql);
    $articulos = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_detalle_entrada_almacen`.`id_prod`
                , `seg_marcas`.`id_marca`
                , `seg_marcas`.`descripcion`
            FROM
                `seg_detalle_entrada_almacen`
                INNER JOIN `seg_marcas` 
                    ON (`seg_detalle_entrada_almacen`.`id_marca` = `seg_marcas`.`id_marca`)
            WHERE `seg_detalle_entrada_almacen`.`id_prod` = $id_art
            GROUP BY `seg_marcas`.`id_marca` ORDER BY `seg_marcas`.`descripcion` ASC";
    $rs = $cmd->query($sql);
    $marcas = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
?>

<div class="form-row">
    <div class="form-group col-md-3">
        <label for="fecha1" class="small">Fecha Inicial</label>
        <input type="date" class="form-control form-control-sm" id="fecha11" value="<?php echo $fecha1 ?>">
    </div>
    <div class="form-group col-md-3">
        <label for="fecha2" class="small">Fecha Final</label>
        <input type="date" class="form-control form-control-sm" id="fecha22" value="<?php echo $fecha2 ?>">
    </div>
    <div class="form-group col-md-3">
        <label for="slcMarcaXprod" class="small">Marca</label>
        <select name="slcMarcaXprod" id="slcMarcaProd" class="form-control form-control-sm">
            <option value="0">--Seleccionar--</option>
            <?php
            $id_mrc = isset($_POST['id_mrc']) ? $_POST['id_mrc'] : 0;
            foreach ($marcas as $marca) {
                $selected = $marca['id_marca'] == $id_mrc ? 'selected' : '';
            ?>
                <option value="<?php echo $marca['id_marca'] ?>" <?php echo $selected ?>><?php echo $marca['descripcion'] ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="form-group col-md-1 text-left">
        <label class="small">&nbsp;</label>
        <div>
            <button class="btn btn-outline-info btn-sm" id="kardeXfecha"><span class="fas fa-search fa-lg" aria-hidden="true"></span></button>
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
                                <b>CONTROL DE EXISTENCIAS EN: <?php echo mb_strtoupper($responsable['nombre']) ?>
                            </td>
                            <td colspan="3" style="font-size:9px;text-align: right;">
                                <table style="width:100% !important;">
                                    <tr>
                                        <td style="text-align: right;">Fecha Imp.</td>
                                        <td><?php echo $date->format('Y/m/d') ?></td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: right;">Periodo:</td>
                                        <td><?php echo date('Y/m/d', strtotime($fecha1)) . ' A ' . date('Y-m-d', strtotime($fecha2)) ?></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="10" style="text-align:left">
                                <b><?php echo mb_strtoupper($id_art . ' - ' . $articulos['bien_servicio']) ?></b>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr style="background-color: #CED3D3; text-align:center">
                <th>Fecha</th>
                <th colspan="3">Documento Referencia</th>
                <th>Vr. Unit.</th>
                <th>Vr.Total</th>
                <th>Entra</th>
                <th>Sale</th>
                <th>Lote</th>
                <th>Vencimiento</th>
            </tr>
        </thead>
        <?php if (!empty($movimientos)) { ?>
            <tbody style="font-size: 60%;">
                <?php
                $total_entra = 0;
                $total_sale = 0;
                $promedio = 0;
                $cntdd = 0;
                $saldo = 0;
                $fecha1 = date('Y-m-d 00:00:00', strtotime($fecha1));
                $first = true;
                foreach ($movimientos as $mv) {
                    $t = $mv['tercero'];
                    $tipo = $mv['tipo'];
                    if ($mv['fec_reg'] < $fecha1) {
                        if ($tipo == 1 || $tipo == 2) {
                            $saldo += $mv['cantidad'];
                        } else {
                            $saldo -= $mv['cantidad'];
                        }
                    } else {
                        $key = array_search($t, array_column($terceros, 'id_tercero'));
                        if ($key !== false) {
                            $nombre = $terceros[$key]['nombre1'] . ' ' . $terceros[$key]['nombre2'] . ' ' . $terceros[$key]['apellido1'] . ' ' . $terceros[$key]['apellido2'] . ' ' . $terceros[$key]['razon_social'];
                        } else {
                            $nombre = '';
                        }
                        if ($tipo == 1 || $tipo == 2) {
                            $entra = $mv['cantidad'];
                            $total_entra += $mv['cantidad'];
                            $sale = '';
                        } else {
                            $entra = '';
                            $sale = $mv['cantidad'];
                            $total_sale += $mv['cantidad'];
                        }
                        if ($tipo == 3 || $tipo == 2) {
                            $bdg = $mv['bodega'];
                        } else {
                            $bdg = $nombre;
                        }
                        if ($first) {
                            echo '<tr style="text-align:left" class="resaltar">
                                <td></td>
                                <td colspan="3"><b>SALDO ANTERIOR</b></td>
                                <td></td>
                                <td></td>
                                <td>' . $saldo . '</td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>';
                            $first = false;
                        }
                        $vence = $mv['fecha_vence'] != '' ? date('Y/m/d', strtotime($mv['fecha_vence'])) : '';
                        echo "<tr style='text-align:left' class='resaltar'>";
                        echo "<td>" . date('Y/m/d', strtotime($mv['fec_reg'])) . "</td>";
                        echo "<td colspan='3'> Mv No. " . str_pad($mv['consecutivo'], 5, "0", STR_PAD_LEFT) . ' - ' . $mv['tipo_entrada'] . ' ' . $bdg . "</td>";
                        echo "<td style='text-align:right'>" . number_format($mv['valu_ingresa'], 0, ',', '.') . "</td>";
                        echo "<td style='text-align:right'>" . number_format($mv['valu_ingresa'] * $mv['cantidad'], 0, ',', '.') . "</td>";
                        echo "<td style='text-align: center'>" . $entra . "</td>";
                        echo "<td style='text-align: center'>" . $sale . "</td>";
                        echo "<td>" . $mv['lote'] . "</td>";
                        echo "<td>" . $vence . "</td>";
                        echo "</tr>";
                        $promedio += $mv['valu_ingresa'];
                        $cntdd++;
                    }
                }
                $cntdd = $cntdd == 0 ? 1 : $cntdd;
                ?>
                <tr>
                    <td colspan="10" style="border-top: 1px double black;" class="resaltar"></td>
                </tr>
                <tr style='text-align:left' class="resaltar">
                    <td></td>
                    <td colspan="3" class="text-right">Promedio</td>
                    <td><?php echo   number_format($promedio / $cntdd, 0, ',', '.') ?></td>
                    <td></td>
                    <td style='text-align: center'><?php echo $total_entra ?></td>
                    <td style='text-align: center'><?php echo $total_sale ?></td>
                    <td>Disponible:</td>
                    <td><?php echo $total_entra - $total_sale + $saldo ?></td>
                </tr>
            </tbody>
        <?php } else { ?>
            <tr class="resaltar">
                <td colspan="10">
                    <div class="alert alert-warning" role="alert">
                        <h4 class="alert-heading">No hay datos</h4>
                        <p>No se encontraron datos para mostrar.</p>
                    </div>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>