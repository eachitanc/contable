<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';

$id_pedido = isset($_POST['id_pdo']) ? $_POST['id_pdo'] : exit('Acción no permitida');
$rol = $_SESSION['rol'];
$id_user = $_SESSION['id_user'];
$detalles = [];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_bodega`
                , `id_usuario`
            FROM
                `seg_responsable_bodega`
            WHERE (`id_usuario` = $id_user AND `estado` = 1)";
    $rs = $cmd->query($sql);
    $bodegas = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_detalle_pedido`.`id_detalle`
                , `seg_detalle_pedido`.`id_producto`
                , `seg_bien_servicio`.`bien_servicio`
                , `seg_detalle_pedido`.`cantidad`
                , `seg_detalle_pedido`.`estado`
                , `seg_detalle_pedido`.`entrega`
                , `seg_detalle_pedido`.`consumo`
            FROM
                `seg_detalle_pedido`
                INNER JOIN `seg_bien_servicio` 
                    ON (`seg_detalle_pedido`.`id_producto` = `seg_bien_servicio`.`id_b_s`)
            WHERE `seg_detalle_pedido`.`id_pedido` = $id_pedido";
    $rs = $cmd->query($sql);
    $detalles = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `estado`, `id_pedido`, `id_entrada`, `id_user_reg`, `bod_entrega` FROM `seg_pedidos_almacen` WHERE `id_pedido` = $id_pedido";
    $rs = $cmd->query($sql);
    $pedido = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$bodega = $pedido['bod_entrega'];
$key  = array_search($bodega, array_column($bodegas, 'id_bodega'));
if ($bodega == '1') {
    $proveedor = '(SELECT
                        `id_entrada`
                        , `cant_ingresa`
                    FROM
                        `seg_detalle_entrada_almacen`
                    WHERE (`id_bodega` = ' . $bodega . ')) AS `proveedor`
                    LEFT JOIN';
    $suma = 'IFNULL(`proveedor`.`cant_ingresa`,0) + IFNULL(`entradas`.`cant_entra`,0)';
    $on = 'ON (`proveedor`.`id_entrada` = `entradas`.`id_entrada`)';
    $tabla = '`proveedor`';
} else {
    $proveedor = '';
    $suma = '`entradas`.`cant_entra`';
    $on = '';
    $tabla = '`entradas`';
}
if ($pedido['id_entrada'] > 0) {
    $condicion = 'AND `seg_detalle_entrada_almacen`.`id_entra` = ' . $pedido['id_entrada'];
} else {
    $condicion = '';
}
if ($pedido['estado'] == 3 && ($key !== false || $rol == 1 || $rol == 3)) {
    $ids = [];
    foreach ($detalles as $dt) {
        $ids[] = $dt['id_producto'];
    }
    $ids = implode(',', $ids);
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "SELECT 
                    `t1`.`id_entrada`
                    , CASE WHEN `t1`.`existe`  < 0 THEN 0 ELSE `t1`.`existe` END AS `existencia`
                    , `seg_marcas`.`descripcion`
                    , `seg_detalle_entrada_almacen`.`id_prod`
                    , `seg_detalle_entrada_almacen`.`lote`
                    , `seg_detalle_entrada_almacen`.`id_lote`
                    , `seg_detalle_entrada_almacen`.`invima`
                    , `seg_detalle_entrada_almacen`.`fecha_vence`
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
                    WHERE (`seg_detalles_traslado`.`estado` > 0 AND `seg_traslados_almacen`.`id_bodega_entra` = $bodega)
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
                    WHERE (`seg_detalles_traslado`.`estado` > 0 AND `seg_traslados_almacen`.`id_bodega_sale` = $bodega)
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
                    WHERE (`seg_salidas_almacen`.`estado` > 0 AND `seg_pedidos_almacen`.`id_bodega` = $bodega)
                    GROUP BY `seg_salidas_almacen`.`id_entrada`) AS `consumo` 
                        ON ($tabla.`id_entrada` = `consumo`.`id_entrada`)) AS `t1`
                INNER JOIN `seg_detalle_entrada_almacen`
                    ON (`seg_detalle_entrada_almacen`.`id_entrada` = `t1`.`id_entrada`)
                LEFT JOIN `seg_marcas` 
                        ON (`seg_detalle_entrada_almacen`.`id_marca` = `seg_marcas`.`id_marca`)
                WHERE `seg_detalle_entrada_almacen`.`id_prod` IN ($ids) $condicion
                ORDER BY `seg_detalle_entrada_almacen`.`fecha_vence` ASC";
        $rs = $cmd->query($sql);
        $existencias = $rs->fetchAll(PDO::FETCH_ASSOC);
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}
if ($pedido['estado'] == 4) {
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
                    WHERE (`seg_traslados_almacen`.`id_pedido` = $id_pedido)) AS `t1`
                LEFT JOIN 
                    (SELECT
                        `seg_salidas_almacen`.`id_entrada`
                        , SUM(`seg_salidas_almacen`.`cantidad`) AS `consumido`
                    FROM
                        `seg_salidas_almacen`
                        INNER JOIN `seg_salida_dpdvo` 
                            ON (`seg_salidas_almacen`.`id_devolucion` = `seg_salida_dpdvo`.`id_devolucion`)
                    WHERE (`seg_salida_dpdvo`.`id_pedido` = $id_pedido)
                    GROUP BY `seg_salidas_almacen`.`id_entrada`) AS `t2`
                ON (`t1`.`id_entrada` = `t2`.`id_entrada`)";
        $rs = $cmd->query($sql);
        $lista = $rs->fetchAll(PDO::FETCH_ASSOC);
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}
$data = [];
if (!empty($detalles)) {
    foreach ($detalles as $det) {
        $id_detalle = $det['id_detalle'];
        $id_producto = $det['id_producto'];
        $editar = $borrar = $entregar = null;
        if ((intval($permisos['editar'])) == 1) {
            $editar = '<a value="' . $id_detalle . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Actualizar o modificar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if ((intval($permisos['borrar'])) == 1) {
            $borrar = '<a value="' . $id_detalle . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }
        if ($pedido['estado'] > 2) {
            $editar = $borrar = null;
        }
        $input = null;
        if ($pedido['estado'] == 0) {
            $editar = $borrar = $entregar = null;
        }

        if ($pedido['estado'] <= 2 || $pedido['estado'] > 4) {
            $data[] = [
                "id" => $id_detalle,
                "bien" => mb_strtoupper($det['bien_servicio']),
                "lote" => '',
                "vence" => '',
                "cantidad" => '<div class="text-center centro-vertical">' . $det['cantidad'] . '</div>',
                "botones" => '<div class="text-center centro-vertical">' . $editar . $borrar . $entregar . '</div>',
            ];
        }
        if ($pedido['estado'] == 4) {
            $filtro = [];
            $filtro = array_filter($lista, function ($lista) use ($id_producto) {
                return $lista["id_producto"] == $id_producto;
            });
            if (!empty($filtro)) {
                foreach ($filtro as $f) {

                    if ($id_user == $pedido['id_user_reg']) {
                        if ($f['consumido'] < $f['cantidad']) {
                            $max = $f['cantidad'] - $f['consumido'];

                            //<div class="input-group-prepend">
                            //<button value="' . $id_detalle . '|' . $id_producto . '"class="btn btn-outline-info editConsumo" type="button" title="Consumir"><span class="fas fa-arrow-alt-circle-down fa-lg"></span></button>
                            //</div>
                            $input = '<input type="number" name="cantxprod[' . $id_detalle . '|' . $id_producto . '|' . $f['id_entrada']  . ']" class="form-control" value="0" min="0" max="' . $max . '">';
                        } else {
                            $input = '';
                        }
                    }
                    $entregar = '<div class="input-group input-group-sm" style="scale:0.8">
                                    ' . $input . '
                                    <div class="input-group-append" title="CONSUMIDO" style="cursor:pointer;">
                                        <span class="input-group-text"> ' . $f['consumido'] . ' DE ' . $f['cantidad'] . '</span>
                                    </div>
                                </div>';
                    $data[] = [
                        "id" => $id_detalle,
                        "bien" => mb_strtoupper($f['bien_servicio']),
                        "lote" => $f['lote'],
                        "vence" => $f['fecha_vence'],
                        "cantidad" => '<div class="text-center centro-vertical">' . $f['cantidad'] . '</div>',
                        "botones" => '<div class="text-center centro-vertical">' . $entregar . '</div>',
                    ];
                }
            }
        }
        if ($pedido['estado'] == 3 && ($key !== false || $rol == 1 || $rol == 3)) {
            //$entregar = '<a value="' . $id_detalle . '" class="btn btn-outline-info btn-sm btn-circle shadow-gb entregar" title="Entregar"><span class="fas fa-arrow-alt-circle-up fa-lg"></span></a>';
            $filtro = [];
            $filtro = array_filter($existencias, function ($existencias) use ($id_producto) {
                return $existencias["id_prod"] == $id_producto;
            });
            if (empty($filtro)) {
                $filtro[] = [
                    'existencia' => 0,
                    'lote' => '',
                    'invima' => '',
                    'fecha_vence' => '',
                    'id_entrada' => '-1',
                ];
            }
            $valida = true;
            $cuenta = 0;
            $cantidad = $det['cantidad'];
            foreach ($filtro as $f) {
                if ($f['existencia'] > 0) {
                    $lote = $f['lote'];
                    $vence = $f['fecha_vence'];
                    $diponible = $f['existencia'];
                    $cantidadaux = $cantidad;
                    if ($cantidad > 0) {
                        $cantidadaux = $cantidadaux - $diponible;
                        if ($cantidadaux > 0) {
                            $pasa =  $diponible;
                            $cantidad = $cantidad - $diponible;
                        } else if ($cantidadaux <= 0) {
                            $pasa = $cantidad;
                            $cantidad = 0;
                        }
                    } else {
                        $pasa = 0;
                    }
                    $ent = '';
                    $entregar = '
                <div class="input-group input-group-sm" style="scale:0.9">
                    <div class="input-group-prepend">
                        <button class="btn btn-outline-secondary editcantidad" type="button" title="Abrir"><span class="fas fa-box"></span></button>
                    </div>
                    <input prod="' . $id_producto . '" type="number" name="cantxprod[' . $id_detalle . '|' . $f['id_entrada'] . '|' . $id_producto . ']" min="0" max="' . $f['existencia'] . '" class="form-control" style="width:1rem;" readonly value="' . $pasa . '">
                    <div class="input-group-append">
                        <span class="input-group-text">' . str_pad($f['existencia'], 3, "0", STR_PAD_LEFT) . '</span>
                    </div>
                </div>';
                    if ($cuenta == 0) {
                        $cant_pedido = $det['cantidad'];
                        $entregar .= '<input type="hidden" id="' . $id_producto . '" value="' . $cant_pedido . '">';
                    } else {
                        $cant_pedido = '';
                    }
                    $data[] = [
                        "id" => $id_detalle,
                        "bien" => mb_strtoupper($det['bien_servicio']),
                        "lote" => $lote,
                        "vence" => $vence,
                        "cantidad" => '<div class="text-center centro-vertical">' . $cant_pedido . '</div>',
                        "botones" => '<div class="text-center centro-vertical">' . $editar . $borrar . $entregar . '</div>',
                    ];
                    $cuenta++;
                }
            }
        }
    }
}
$datos = ['data' => $data];
echo json_encode($datos);
