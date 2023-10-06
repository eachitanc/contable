<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';
$vigencia = $_SESSION['vigencia'];
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_vacaciones`.`id_vac`
                , `seg_empleado`.`no_documento`
                , CONCAT_WS(' ',`seg_empleado`.`apellido1`, `seg_empleado`.`apellido2`, `seg_empleado`.`nombre1`, `seg_empleado`.`nombre2`) AS `nombre`
                , `seg_liq_vac`.`fec_inicio`
                , `seg_liq_vac`.`fec_fin`
                , `seg_liq_vac`.`dias_liqs`
                , `seg_liq_vac`.`val_liq`
                , `seg_liq_vac`.`val_prima_vac`
                , `seg_liq_vac`.`val_bsp`
                , `seg_liq_vac`.`val_bon_recrea`
                , `seg_vacaciones`.`corte`
                , `seg_vacaciones`.`anticipo`
                , `seg_vacaciones`.`dias_habiles`
            FROM
                `seg_liq_vac`
                INNER JOIN `seg_vacaciones` 
                    ON (`seg_liq_vac`.`id_vac` = `seg_vacaciones`.`id_vac`)
                INNER JOIN `seg_empleado` 
                    ON (`seg_vacaciones`.`id_empleado` = `seg_empleado`.`id_empleado`)
            WHERE `seg_liq_vac`.`anio_vac` = '$vigencia'";
    $rs = $cmd->query($sql);
    $vac_liquidadas = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (!empty($vac_liquidadas)) {
    foreach ($vac_liquidadas as $vl) {
        $anticipo = $vl['anticipo'] == 1 ? '<span class="badge badge-success">SI</span>' : '<span class="badge badge-secondary">NO</span>';
        $data[] = [
            'id' => $vl['id_vac'],
            'no_doc' => $vl['no_documento'],
            'nombre' => mb_strtoupper($vl['nombre']),
            'fec_inicia' => $vl['fec_inicio'],
            'fec_fin' => $vl['fec_fin'],
            'dias_liq' => $vl['dias_liqs'],
            'val_vac' => '<div class="text-right">' . pesos($vl['val_liq']) . '</div>',
            'val_pri_vac' => '<div class="text-right">' . pesos($vl['val_prima_vac']) . '</div>',
            'val_bsp' => '<div class="text-right">' . pesos($vl['val_bsp']) . '</div>',
            'val_brecrea' => '<div class="text-right">' . pesos($vl['val_bon_recrea']) . '</div>',
            'corte' => $vl['corte'],
            'anticipo' => '<div class="text-center">' . $anticipo . '</div>',
            'dias_hab' => $vl['dias_habiles'],
            'total' => '<div class="text-right">' . pesos($vl['val_liq'] + $vl['val_prima_vac'] + $vl['val_bsp'] + $vl['val_bon_recrea']) . '</div>',
        ];
    }
} else {
    $data = [];
}

$datos = ['data' => $data];
echo json_encode($datos);
