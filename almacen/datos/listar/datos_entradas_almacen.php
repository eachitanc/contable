<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
$vigencia = $_SESSION['vigencia'];

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_adquisiciones`.`id_adquisicion`
                , `seg_adquisiciones`.`objeto`
                , `seg_adquisiciones`.`estado`
                , `seg_adquisiciones`.`fecha_adquisicion`
                , `seg_terceros`.`id_tercero_api`
                , `seg_tipo_contrata`.`id_tipo`
            FROM
                `seg_adquisiciones`
                INNER JOIN `seg_tipo_bien_servicio` 
                    ON (`seg_adquisiciones`.`id_tipo_bn_sv` = `seg_tipo_bien_servicio`.`id_tipo_b_s`)
                INNER JOIN `seg_tipo_contrata` 
                    ON (`seg_tipo_bien_servicio`.`id_tipo_cotrato` = `seg_tipo_contrata`.`id_tipo`)
                INNER JOIN `seg_terceros` 
                    ON (`seg_adquisiciones`.`id_tercero` = `seg_terceros`.`id_tercero`)
            WHERE `vigencia` = '$vigencia' AND `seg_tipo_contrata`.`id_tipo` <> '7'";
    $rs = $cmd->query($sql);
    $ladquis = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$data = [];
if (!empty($ladquis)) {
    foreach ($ladquis as $la) {
        $id_adq = $la['id_tercero_api'] . '|' . $_SESSION['nit_emp'] . '|' . $la['id_adquisicion'];
        $detalles = null;
        if ((intval($permisos['editar']))) {
            $detalles = '<a value="' . $id_adq . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb detalles" title="Detalles"><span class="fas fa-eye fa-lg"></span></a>';
            if ($la['estado'] > 10) {
                $detalles = '<a class="btn btn-outline-secondary btn-sm btn-circle shadow-gb completo" title="Detalles"><span class="fas fa-eye fa-lg"></span></a>';
            }
        }
        if ($la['estado'] == 10 || $la['estado'] == 9) {
            $data[] = [
                'id_adq' => $la['id_adquisicion'],
                'objeto' => $la['objeto'],
                'fecha' => $la['fecha_adquisicion'],
                'botones' => '<div class="text-center">' . $detalles . '</div>',
            ];
        }
    }
}

$datos = ['data' => $data];

echo json_encode($datos);
