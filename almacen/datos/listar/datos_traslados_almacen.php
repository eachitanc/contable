<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
$t_traslado = isset($_POST['t_traslado']) ? $_POST['t_traslado'] : exit('Acción no permitida');
$vigencia = $_SESSION['vigencia'];
$user = $_SESSION['id_user'];
$rol = $_SESSION['rol'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_traslados_almacen`.`id_trasl_alm`
                , `seg_traslados_almacen`.`id_tipo_trasl`
                , `seg_tipo_traslado_almacen`.`descripcion`
                , `seg_traslados_almacen`.`observacion`
                , `seg_traslados_almacen`.`acta_remision`
                , `seg_traslados_almacen`.`id_sede_sale`
                , `seg_traslados_almacen`.`id_bodega_sale`
                , `seg_traslados_almacen`.`id_sede_entra`
                , `seg_traslados_almacen`.`id_bodega_entra`
                , `seg_traslados_almacen`.`fec_traslado`
                , `seg_traslados_almacen`.`vigencia`
                , `seg_traslados_almacen`.`estado`
                , `seg_traslados_almacen`.`id_pedido`
            FROM
                `seg_traslados_almacen`
                INNER JOIN `seg_tipo_traslado_almacen` 
                    ON (`seg_traslados_almacen`.`id_tipo_trasl` = `seg_tipo_traslado_almacen`.`id_traslado`)
            WHERE `seg_traslados_almacen`.`vigencia` = '$vigencia' AND `seg_traslados_almacen`.`id_tipo_trasl` = '$t_traslado'";
    $rs = $cmd->query($sql);
    $traslados = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_bodega`, `nombre` FROM `seg_bodega_almacen`";
    $rs = $cmd->query($sql);
    $bodegas = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_sede`, `nombre` FROM `seg_sedes_empresa`";
    $rs = $cmd->query($sql);
    $sedes = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `t1`.`id_resp`
                , `t1`.`id_bodega`
                , `seg_responsable_bodega`.`id_usuario`
            FROM 
                (SELECT
                    MAX(`id_resp`) AS `id_resp`
                    , `id_bodega`
                FROM
                    `seg_responsable_bodega`
                GROUP BY `id_bodega`) AS `t1`
                INNER JOIN `seg_responsable_bodega`
                    ON(`t1`.`id_resp` = `seg_responsable_bodega`.`id_resp`)
            WHERE `seg_responsable_bodega`.`id_usuario` = $user";
    $rs = $cmd->query($sql);
    $responsables = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$data = [];
foreach ($traslados as $t) {
    $id_tr = $t['id_trasl_alm'];
    $key = array_search($t['id_sede_sale'], array_column($sedes, 'id_sede'));
    $sede_sale = false !== $key ? $sedes[$key]['nombre'] : '';
    $key = array_search($t['id_bodega_sale'], array_column($bodegas, 'id_bodega'));
    $bodega_sale = false !== $key ? $bodegas[$key]['nombre'] : '';
    $id_bg_sale = $t['id_bodega_sale'];
    $key = array_search($t['id_sede_entra'], array_column($sedes, 'id_sede'));
    $sede_entra = false !== $key ? $sedes[$key]['nombre'] : '';
    $key = array_search($t['id_bodega_entra'], array_column($bodegas, 'id_bodega'));
    $bodega_entra = false !== $key ? $bodegas[$key]['nombre'] : '';
    $id_bg_entra = $t['id_bodega_entra'];
    $detalles = $editar = $borrar = $imprimir = null;
    if ($t['estado'] == 1) {
        if ((intval($permisos['editar'])) == 1) {
            $editar = '<a value="' . $id_tr . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Actualizar o modificar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if ((intval($permisos['borrar'])) == 1) {
            $borrar = '<a value="' . $id_tr . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }
    }
    if ($t['estado'] >= 2) {
        $imprimir = '<a value="' . $t['id_pedido'] . '" class="btn btn-outline-success btn-sm btn-circle shadow-gb btnImprimirTraslado" title="Imprimir"><span class="fas fa-print fa-lg"></span></a>';
    }
    switch ($t['estado']) {
        case 1:
            $estado = 'INICIALLIZADO';
            $coloricon = 'warning';
            break;
        case 2:
            $estado = 'ABIERTO';
            $coloricon = 'secondary';
            break;
        case 3:
            $estado = 'CERRADO';
            $coloricon = 'info';
            break;
    }
    if ((intval($permisos['listar'])) == 1) {
        $detalles = '<a value="' . $id_tr . '" class="btn btn-outline-' . $coloricon . ' btn-sm btn-circle shadow-gb detalles" title="Detalles"><span class="fas fa-eye fa-lg"></span></a>';
    }
    $keyrps = array_search($id_bg_sale, array_column($responsables, 'id_bodega'));
    $keyrpe = array_search($id_bg_entra, array_column($responsables, 'id_bodega'));
    if ($rol == 1 || $rol == 3 || $keyrps !== false || $keyrpe !== false) {
        $data[] = [
            'id_traslado' => $id_tr,
            'tipo' => $t['descripcion'],
            'sede_sale' => $sede_sale,
            'bodega_sale' => $bodega_sale,
            'sede_entra' => $sede_entra,
            'bodega_entra' => $bodega_entra,
            'acta' => $t['acta_remision'],
            'observacion' => $t['observacion'],
            'fecha' => $t['fec_traslado'],
            'acciones' => '<div class="text-center">' . $editar . $borrar . $detalles . $imprimir . '</div>',
        ];
    }
}
$datos = [
    'data' => $data,
];
echo json_encode($datos);
