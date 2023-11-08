<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}

include '../conexion.php';
include '../permisos.php';
$vigencia = $_SESSION['vigencia'];
$articulo = isset($_POST['articulo']) ? $_POST['articulo'] : 0;
$id_marca = isset($_POST['id_marca']) ? $_POST['id_marca'] : 0;
$describe = isset($_POST['describe']) ? $_POST['describe'] : '';
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '0';
$cond_marca = $id_marca == 0 ? '' : 'AND `id_marca` = ' . $id_marca;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_salida`, `descripcion` FROM  `seg_tipo_salidas`";
    $rs = $cmd->query($sql);
    $tsalidas = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$rol = $_SESSION['rol'];
$user = $_SESSION['id_user'];
if ($rol == 1 || $rol == 3) {
    $condicion = ' ORDER BY `seg_sedes_empresa`.`nombre`,`seg_bodega_almacen`.`nombre` ASC';
} else {
    $condicion = ' AND`seg_responsable_bodega`.`id_usuario` = ' . $user . ' ORDER BY `seg_sedes_empresa`.`nombre`,`seg_bodega_almacen`.`nombre` ASC';
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_sedes_empresa`.`id_sede`
                , `seg_sedes_empresa`.`nombre` AS `nombre_sede`
                , `seg_responsable_bodega`.`id_bodega`
                , `seg_bodega_almacen`.`nombre` AS `nombre_bodega`
                , `seg_responsable_bodega`.`id_resp`
                , `seg_responsable_bodega`.`id_usuario`
            FROM
                `seg_responsable_bodega`
                INNER JOIN `seg_bodega_almacen` 
                    ON (`seg_responsable_bodega`.`id_bodega` = `seg_bodega_almacen`.`id_bodega`)
                INNER JOIN `seg_sedes_empresa` 
                    ON (`seg_bodega_almacen`.`id_sede` = `seg_sedes_empresa`.`id_sede`)
            WHERE `seg_responsable_bodega`.`id_resp` IN (SELECT MAX(`id_resp`) FROM `seg_responsable_bodega`  GROUP BY `id_bodega`) " . $condicion;
    $rs = $cmd->query($sql);
    $bodegas = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$sedes = [];
if (!empty($bodegas)) {
    foreach ($bodegas as $bdg) {
        if (!isset($sedes[$bdg['id_sede']])) {
            $sedes[$bdg['id_sede']] = $bdg['nombre_sede'];
        }
    }
}
$_POST['sede'] = isset($_POST['sede']) ? $_POST['sede'] : '0';
$_POST['bodega'] = isset($_POST['bodega']) ? $_POST['bodega'] : '0';
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fec_in = $date->format('Y-m-d');
$fecha1 = isset($_POST['fecha1']) ? $_POST['fecha1'] : $fec_in;
$fecha2 = isset($_POST['fecha2']) ? $_POST['fecha2'] : $date->format('Y-m-d');
$bodega = $_POST['bodega'];
$nullos = '';
$farmacia = '';
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
                    INNER JOIN `seg_salida_dpdvo` 
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
                WHERE `seg_salidas_almacen`.`id_producto` = $articulo AND `seg_pedidos_almacen`.`id_bodega` IS NULL";
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
                    WHERE `seg_ids_farmacia`.`id_prod` = $articulo
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
                    WHERE (`seg_ids_farmacia`.`id_prod` = $articulo AND `vista_salidas_farmacia`.`fec_cierre` <= '$fecha2 23:59:59')";
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
                    , IFNULL(`seg_entrada_almacen`.`id_tercero_api`,0) AS `tercero`
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
                WHERE (`seg_detalle_entrada_almacen`.`id_prod` = $articulo AND `seg_detalle_entrada_almacen`.`id_bodega` = $bodega)
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
                WHERE `seg_detalles_traslado`.`id_producto` = $articulo AND `seg_traslados_almacen`.`id_bodega_entra` = $bodega
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
                WHERE `seg_detalles_traslado`.`id_producto` = $articulo AND `seg_traslados_almacen`.`id_bodega_sale` = $bodega
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
                WHERE `seg_salidas_almacen`.`id_producto` = $articulo AND `seg_pedidos_almacen`.`id_bodega` = $bodega
                $nullos) AS `t1`
            WHERE `t1`.`fec_reg` <='$fecha2 23:59:59' $cond_marca
            ORDER BY `t1`.`fec_reg` ASC";
    $rs = $cmd->query($sql);
    $movimientos = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $sql = "SELECT
                `id_prod`
                , AVG(`valu_ingresa` * (1+ `iva`/100)) AS `val_prom`
            FROM
                `seg_detalle_entrada_almacen`
            WHERE  `id_prod` = $articulo AND `fec_reg` <= '$fecha2'
            GROUP BY `id_prod` LIMIT 1";
    $res = $cmd->query($sql);
    $promedio = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$id_t = [];
$id_mc = [];
foreach ($movimientos as $l) {
    $id_t[] = $l['tercero'];
    $id_mc[] = $l['id_marca'];
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
if ($articulo > 0) {
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
                WHERE `seg_detalle_entrada_almacen`.`id_prod` = $articulo
                GROUP BY `seg_marcas`.`id_marca` ORDER BY `seg_marcas`.`descripcion` ASC";
        $rs = $cmd->query($sql);
        $marcas = $rs->fetchAll();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
} else {
    $marcas = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../head.php' ?>

<body class="sb-nav-fixed <?php if ($_SESSION['navarlat'] == '1') {
                                echo 'sb-sidenav-toggled';
                            } ?>">
    <?php include '../navsuperior.php' ?>
    <div id="layoutSidenav">
        <?php include '../navlateral.php' ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid p-2">
                    <div class="card mb-4">
                        <div class="card-header" id="divTituloPag">
                            <div class="row">
                                <div class="col-md-11">
                                    <i class="fas fa-list-ul fa-lg" style="color:#1D80F7"></i>
                                    KARDEX POR ARTÍCULO.
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div class="form-row">
                                <div class="form-group col-md-2 text-center">
                                    <label for="slcSede" class="small">SEDES</label>
                                    <select id="slcSede" class="form-control form-control-sm">
                                        <option value="0">--Seleccionar--</option>
                                        <?php
                                        foreach ($sedes as $key => $value) {
                                            $slc = $_POST['sede'] == $key ? 'selected' : '';
                                            echo '<option value="' . $key . '" ' . $slc . '>' . $value . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-2 text-center">
                                    <label for="slcBodega" class="small">BODEGA</label>
                                    <select id="slcBodega" class="form-control form-control-sm">
                                        <option value="0">--Seleccionar--</option>
                                        <?php
                                        foreach ($bodegas as $bg) {
                                            if ($_POST['sede'] == $bg['id_sede']) {
                                                $slc = $_POST['bodega'] == $bg['id_bodega'] ? 'selected' : '';
                                                echo '<option value="' . $bg['id_bodega'] . '" ' . $slc . '>' . $bg['nombre_bodega'] . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-2 text-center">
                                    <label for="slctipoBusqueda" class="small">tipo de busqueda</label>
                                    <select id="slctipoBusqueda" class="form-control form-control-sm">
                                        <option value="0" <?php echo $tipo == 0 ? 'selected' : '' ?>>--Seleccionar--</option>
                                        <option value="1" <?php echo $tipo == 1 ? 'selected' : '' ?>>Artículo</option>
                                        <!--<option value="2" <?php echo $tipo == 2 ? 'selected' : '' ?>>Lote</option>-->
                                    </select>
                                </div>
                                <div class="form-group col-md-3 text-center">
                                    <label for="buscarArticulo" class="small">Buscar</label>
                                    <input id="buscarArticulo" class="form-control form-control-sm" value="<?php echo $describe ?>">
                                    <input id="id_articulo" type="hidden" value="<?php echo $articulo ?>">
                                    <input id="id_tipo_B" type="hidden" value="<?php echo $tipo ?>">
                                    <input id="lote_k" type="hidden" value="<?php echo $describe ?>">
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="slcMarcaXprod" class="small">Marca</label>
                                    <select name="slcMarcaXprod" id="slcMarcaXprod" class="form-control form-control-sm">
                                        <option value="0">--Seleccionar--</option>
                                        <?php foreach ($marcas as $marca) {
                                            $selected = $marca['id_marca'] == $id_marca ? 'selected' : '';
                                        ?>
                                            <option value="<?php echo $marca['id_marca'] ?>" <?php echo $selected ?>><?php echo $marca['descripcion'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-1">
                                    <label for="btnGeneraKardex" class="small">&nbsp;</label>
                                    <div>
                                        <button id="btnGeneraKardex" class="btn btn-outline-primary btn-sm" title="Generar Kardex"><i class="fas fa-search"></i></button>
                                        <button id="btnImprimeKardex" class="btn btn-outline-success btn-sm" title="Imprimir Kardex"><i class="fas fa-print"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-2">
                                    <label for="fecha1" class="small">Fecha Inicial</label>
                                    <input type="date" class="form-control form-control-sm" id="fecha1" value="<?php echo $fecha1 ?>">
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="fecha2" class="small">Fecha Final</label>
                                    <input type="date" class="form-control form-control-sm" id="fecha2" value="<?php echo $fecha2 ?>">
                                </div>
                            </div>
                            <?php if ($articulo != 0) { ?>
                                <table id="tableKardexArticulo" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%; font-size:80%">
                                    <thead>
                                        <tr class="text-center centro-vertical">
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
                                        <tbody>
                                            <?php
                                            $total_entra = 0;
                                            $total_sale = 0;
                                            $saldo = 0;
                                            $cntdd = 0;
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
                                                        echo '<tr style="text-align:left">
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
                                                    echo "<tr style='text-align:left'>";
                                                    echo "<td>" . date('Y/m/d', strtotime($mv['fec_reg'])) . "</td>";
                                                    echo "<td colspan='3'> Mv No. " . str_pad($mv['consecutivo'], 5, "0", STR_PAD_LEFT) . ' - ' . $mv['tipo_entrada'] . ' ' . $bdg . "</td>";
                                                    echo "<td style='text-align:right'>" . number_format($mv['valu_ingresa'], 0, ',', '.') . "</td>";
                                                    echo "<td style='text-align:right'>" . number_format($mv['valu_ingresa'] * $mv['cantidad'], 0, ',', '.') . "</td>";
                                                    echo "<td class='text-center'>" . $entra . "</td>";
                                                    echo "<td class='text-center'>" . $sale . "</td>";
                                                    echo "<td>" . $mv['lote'] . "</td>";
                                                    echo "<td>" . $vence . "</td>";
                                                    echo "</tr>";
                                                }
                                            }
                                            $cntdd = $cntdd == 0 ? 1 : $cntdd;
                                            ?>
                                            <tr style='text-align:left'>
                                                <td></td>
                                                <td colspan="3" class="text-right">Promedio</td>
                                                <td><?php echo number_format($promedio['val_prom'], 0, ',', '.') ?></td>
                                                <td></td>
                                                <td><?php echo $total_entra ?></td>
                                                <td><?php echo $total_sale ?></td>
                                                <td>Disponible:</td>
                                                <td><?php echo $total_entra - $total_sale + $saldo ?></td>
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
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../footer.php' ?>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="divModalError" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header" id="divModalHeader">
                        <h5 class="modal-title" id="exampleModalLongTitle">
                            <i class="fas fa-exclamation-circle fa-lg" style="color:red"></i>
                            ¡Error!
                        </h5>
                    </div>
                    <div class="modal-body text-center" id="divMsgError">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success btn-sm" data-dismiss="modal">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
        <!-- Modal -->
        <div class="modal fade" id="divModalConfDel" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header" id="divModalHeaderConfir">
                        <h5 class="modal-title" id="exampleModalLongTitle">
                            <i class="fas fa-exclamation-triangle fa-lg" style="color: #E67E22;"></i>
                            ¡Confirmar!
                        </h5>
                    </div>
                    <div class="modal-body" id="divMsgConfdel">

                    </div>
                    <div class="modal-footer" id="divBtnsModalDel">
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="divModalDone" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header" id="divDone">
                        <h5 class="modal-title" id="exampleModalLongTitle">
                            <i class="fas fa-check-circle fa-lg" style="color:#2FDA49"></i>
                            ¡Correcto!
                        </h5>
                    </div>
                    <div class="modal-body text-center" id="divMsgDone">

                    </div>
                    <div class="modal-footer">
                        <a type="button" class="btn btn-primary btn-sm" data-dismiss="modal"> Aceptar</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
        <!-- Modal -->
        <div class="modal fade" id="divModalForms" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div id="divTamModalForms" class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body text-center" id="divForms">

                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
        <!-- Modal -->
        <div class="modal fade" id="divModalReg" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div id="divTamModalReg" class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body text-center" id="divFormsReg">

                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
    </div>
    <?php include '../scripts.php' ?>
    <script src="https://printjs-4de6.kxcdn.com/print.min.js"></script>
    <link rel="stylesheet" href="https://printjs-4de6.kxcdn.com/print.min.css">
</body>

</html>