<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
$vigencia = $_SESSION['vigencia'];
$id_tiposal = isset($_POST['id_tipo']) ? $_POST['id_tipo'] : exit('Acción no permitida');
$user = $_SESSION['id_user'];
$rol = $_SESSION['rol'];
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_devolucion`
                , `id_tercero_api`
                , `id_tipo_salida`
                , `acta_remision`
                , `fec_acta_remision`
                , `observacion`
                , `estado`
                , `id_pedido`
                , `consecutivo`
            FROM
                `seg_salida_dpdvo`
            WHERE `vigencia` = '$vigencia' AND `id_tipo_salida` = $id_tiposal";
    $rs = $cmd->query($sql);
    $devoluciones = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT * FROM 
                (SELECT
                    `seg_salida_dpdvo`.`id_devolucion`
                    , `seg_salida_dpdvo`.`id_pedido`
                    , `seg_bodega_almacen`.`nombre`
                    , `seg_pedidos_almacen`.`id_bodega`
                FROM
                    `seg_salida_dpdvo`
                    INNER JOIN `seg_pedidos_almacen` 
                        ON (`seg_salida_dpdvo`.`id_pedido` = `seg_pedidos_almacen`.`id_pedido`)
                    INNER JOIN `seg_bodega_almacen` 
                        ON (`seg_pedidos_almacen`.`id_bodega` = `seg_bodega_almacen`.`id_bodega`)) AS `t3`
            INNER JOIN 
                (SELECT 
                    `t1`.`id_resp`
                    , `t1`.`id_bodega`
                    , `seg_responsable_bodega`.`id_usuario`
                    , CONCAT_WS(' ', `seg_usuarios`.`nombre1`
                    , `seg_usuarios`.`nombre2`
                    , `seg_usuarios`.`apellido1`
                    , `seg_usuarios`.`apellido2`) AS `responsable`
                FROM 
                    (SELECT
                        MAX(`id_resp`) AS `id_resp`
                        , `id_bodega`
                    FROM
                        `seg_responsable_bodega`
                    GROUP BY `id_bodega`) AS `t1`
                    INNER JOIN `seg_responsable_bodega`
                        ON(`t1`.`id_resp` = `seg_responsable_bodega`.`id_resp`)
                    INNER JOIN `seg_usuarios`
                        ON (`seg_responsable_bodega`.`id_usuario` = `seg_usuarios`.`id_usuario`)) AS `t2`
            ON (`t3`.`id_bodega`=`t2`.`id_bodega`)";
    $rs = $cmd->query($sql);
    $responsables = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$id_t = [];
foreach ($devoluciones as $l) {
    if ($l['id_tercero_api'] != '') {
        $id_t[] = $l['id_tercero_api'];
    }
}
if (!empty($id_t)) {
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
    $dat_ter = json_decode($result, true);
} else {
    $dat_ter = [];
}
$idusuario = 0;
$data = [];
if (!empty($devoluciones)) {
    foreach ($devoluciones as $d) {
        $id_dev = $d['id_devolucion'];
        $key = array_search($d['id_tercero_api'], array_column($dat_ter, 'id_tercero'));

        if (false !== $key) {
            $tercer = $dat_ter[$key]['apellido1'] . ' ' . $dat_ter[$key]['apellido2'] . ' ' . $dat_ter[$key]['nombre2'] . ' ' . $dat_ter[$key]['nombre1'] . ' ' . $dat_ter[$key]['razon_social'];
        } else {
            $key = array_search($id_dev, array_column($responsables, 'id_devolucion'));
            if (false !== $key) {
                $tercer = mb_strtoupper($responsables[$key]['responsable']);
                $d['acta_remision'] = $responsables[$key]['nombre'];
                $idusuario = $responsables[$key]['id_usuario'];
            } else {
                $tercer = '';
            }
        }
        $detalles = $editar = $borrar = $imprimir = null;
        if ($d['estado'] <= 2) {
            if ((intval($permisos['editar'])) == 1) {
                $editar = '<a value="' . $id_dev . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Actualizar o modificar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            }
            if ((intval($permisos['borrar'])) == 1) {
                $borrar = '<a value="' . $id_dev . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
            }
        }
        if ($d['estado'] >= 2) {
            $imprimir = '<a value="' . $d['id_pedido'] . '" class="btn btn-outline-success btn-sm btn-circle shadow-gb btnImprimirConsumo" title="Imprimir"><span class="fas fa-print fa-lg"></span></a>';
        }
        switch ($d['estado']) {
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
            $detalles = '<a value="' . $id_dev . '" class="btn btn-outline-' . $coloricon . ' btn-sm btn-circle shadow-gb detalles" title="Detalles"><span class="fas fa-eye fa-lg"></span></a>';
        }
        if ($idusuario == $user || $rol == 1 || $rol == 3) {
            $data[] = [
                "id_devolucion" => $id_dev,
                "consecutivo" => $d['consecutivo'],
                "tercero" => $tercer,
                "acta" => $d['acta_remision'],
                "fec_acta" => $d['fec_acta_remision'],
                "observacion" => $d['observacion'],
                "estado" => $estado,
                "accion" => '<div class="text-center">' . $editar . $borrar . $detalles . $imprimir . '</div>',
            ];
        }
    }
}
$datos = ['data' => $data];
echo json_encode($datos);
