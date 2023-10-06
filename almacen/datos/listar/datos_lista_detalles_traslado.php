<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
$id_trasl_alma = isset($_POST['id_trasl_alma']) ? $_POST['id_trasl_alma'] : exit('Acción no permitida');
$vigencia = $_SESSION['vigencia'];
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_detalles_traslado`.`id_det_traslado`
                , `seg_detalles_traslado`.`id_entrada`
                , `seg_detalles_traslado`.`id_producto`
                , `seg_bien_servicio`.`bien_servicio`
                , `seg_detalles_traslado`.`id_traslado`
                , `seg_traslados_almacen`.`acta_remision`
                , `seg_detalles_traslado`.`cantidad`
                , `seg_detalles_traslado`.`observacion`
                , `seg_traslados_almacen`.`estado`
                , `seg_detalle_entrada_almacen`.`lote`
            FROM
                `seg_detalles_traslado`
                INNER JOIN `seg_bien_servicio` 
                    ON (`seg_detalles_traslado`.`id_producto` = `seg_bien_servicio`.`id_b_s`)
                INNER JOIN `seg_traslados_almacen` 
                    ON (`seg_detalles_traslado`.`id_traslado` = `seg_traslados_almacen`.`id_trasl_alm`)
                INNER JOIN `seg_detalle_entrada_almacen` 
                    ON (`seg_detalles_traslado`.`id_entrada` = `seg_detalle_entrada_almacen`.`id_entrada`)
            WHERE `seg_detalles_traslado`.`id_traslado` = '$id_trasl_alma'";
    $rs = $cmd->query($sql);
    $detalles_trasl = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$data = [];
if (!empty($detalles_trasl)) {
    foreach ($detalles_trasl as $d) {
        $id_det = $d['id_det_traslado'];
        $editar = $borrar = null;
        if ($d['estado'] < 3) {
            if ((intval($permisos['editar'])) == 1) {
                $editar = '<a value="' . $id_det . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Actualizar o modificar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            }
            if ((intval($permisos['borrar'])) == 1) {
                $borrar = '<a value="' . $id_det . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
            }
        }
        $data[] = [
            "id_prod" => $d['id_producto'],
            "prod" => $d['bien_servicio'],
            "cantidad" => $d['cantidad'],
            "lote" => $d['lote'],
            "observacion" => $d['observacion'],
            "accion" => '<div class="text-center">' . $editar . $borrar . '</div>',
        ];
    }
}
$datos = ['data' => $data];
echo json_encode($datos);
