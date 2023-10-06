<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';

$usuario = $_SESSION['id_user'];
$tipo = $_POST['tipo_bien'];
$vigencia = $_SESSION['vigencia'];
$rol = $_SESSION['rol'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_bodega`
                , `id_usuario`
            FROM
                `seg_responsable_bodega`
            WHERE (`id_usuario` = $usuario)";
    $rs = $cmd->query($sql);
    $bodegasUser = $rs->fetchAll();
    $bodegas = !empty($bodegasUser) ? implode(',', array_unique(array_column($bodegasUser, 'id_bodega'))) : 0;
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if ($usuario == 1) {
    $user = '';
} else {
    $user = " AND (`seg_pedidos_almacen`.`id_bodega` IN ($bodegas) OR `seg_pedidos_almacen`.`bod_entrega` IN ($bodegas))";
}
if ($rol == 3) {
    $user = " AND `seg_pedidos_almacen`.`estado` >= 0 OR `id_user_reg` = '$usuario'";
}
$pedidos = [];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_pedidos_almacen`.`id_pedido`
                , `seg_pedidos_almacen`.`id_bodega`
                , `seg_pedidos_almacen`.`bod_entrega`
                , `seg_bodega_almacen`.`nombre` AS `area`
                , `seg_bodega_almacen_1`.`nombre` AS `area_entrega`
                , `seg_pedidos_almacen`.`estado`
                , CONCAT_WS(' ',`seg_usuarios`.`nombre1`, `seg_usuarios`.`nombre2`, `seg_usuarios`.`apellido1`, `seg_usuarios`.`apellido2`) AS `responsable`
                , `seg_pedidos_almacen`.`fec_cierre`
                , `seg_pedidos_almacen`.`fec_reg`
                , `seg_pedidos_almacen`.`id_user_reg`
            FROM
                `seg_pedidos_almacen`
                INNER JOIN `seg_bodega_almacen` 
                    ON (`seg_pedidos_almacen`.`id_bodega` = `seg_bodega_almacen`.`id_bodega`)
                INNER JOIN `seg_bodega_almacen` AS `seg_bodega_almacen_1`
                    ON (`seg_pedidos_almacen`.`bod_entrega` = `seg_bodega_almacen_1`.`id_bodega`)
                INNER JOIN `seg_usuarios` 
                    ON (`seg_pedidos_almacen`.`id_user_reg` = `seg_usuarios`.`id_usuario`)
            WHERE `vigencia` = $vigencia" . $user;
    $rs = $cmd->query($sql);
    $pedidos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$data = [];
if (!empty($pedidos)) {
    foreach ($pedidos as $pdo) {
        $id_pedido = $pdo['id_pedido'];
        $editar = $borrar = $detalles = $estado = $anular = $imprimir =  null;
        if ((intval($permisos['editar'])) == 1) {
            $editar = '<a value="' . $id_pedido . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Actualizar o modificar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if ((intval($permisos['borrar'])) == 1) {
            $borrar = '<a value="' . $id_pedido . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }
        if ($pdo['estado'] != 1 || $pdo['id_user_reg'] != $usuario) {
            $editar = $borrar = null;
        }
        if ($pdo['estado'] == 0) {
            $estado = '<span class="badge badge-pill badge-secondary">ANULADO</span>';
        } else if ($pdo['estado'] == 1) {
            $estado = '<span class="badge badge-pill badge-info">INICIAL</span>';
        } else if ($pdo['estado'] == 2) {
            $estado = '<span class="badge badge-pill badge-primary">SIN ENVIAR</span>';
        } else if ($pdo['estado'] == 3) {
            $estado = '<span class="badge badge-pill badge-warning">PENDIENTE</span>';
        } else if ($pdo['estado'] == 4) {
            $estado = '<span class="badge badge-pill badge-success">ENTREGADO</span>';
        }
        if ($pdo['estado'] > 0 && $pdo['estado'] <= 3 && ($pdo['id_user_reg'] == $usuario || $rol == 3 || $rol == 1)) {
            $anular = '<a value="' . $id_pedido . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb anular" title="Anular"><span class="fas fa-ban fa-lg"></span></a>';
        }
        if ($pdo['estado'] > 1) {
            $imprimir = '<a value="' . $id_pedido . '" class="btn btn-outline-success btn-sm btn-circle shadow-gb imprimir" title="Imprimir"><span class="fas fa-print fa-lg"></span></a>';
        }
        $detalles = '<a value="' . $id_pedido . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb detalles" title="Ver detalles"><span class="fas fa-eye fa-lg"></span></a>';
        $fec_pide = $pdo['fec_cierre'] == '' ? $pdo['fec_reg'] : $pdo['fec_cierre'];
        $data[] = [
            "id" => $id_pedido,
            "entrega" => $pdo['area_entrega'],
            "solicita" => $pdo['area'],
            "responsable" => mb_strtoupper($pdo['responsable']),
            "fecha" => '<div class="text-center centro-vertical">' . date('Y-m-d', strtotime($fec_pide)) . '</div>',
            "estado" => '<div class="text-center centro-vertical">' . $estado . '</div>',
            "botones" => '<div class="text-center centro-vertical">' . $editar . $borrar . $detalles . $imprimir . $anular . '</div>',
        ];
    }
}
$datos = ['data' => $data];
echo json_encode($datos);
