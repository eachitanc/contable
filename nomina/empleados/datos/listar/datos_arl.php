<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';
$id = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT id_novarl, seg_arl.id_arl, nombre_arl, CONCAT(nit_arl, '-', dig_ver) AS nitarl, id_riesgo, CONCAT(clase, ' - ', riesgo) AS riesgo, fec_afiliacion, fec_retiro
            FROM
                seg_novedades_arl
            INNER JOIN seg_arl 
                ON (seg_novedades_arl.id_arl = seg_arl.id_arl)
            INNER JOIN seg_riesgos_laboral 
                ON (seg_novedades_arl.id_riesgo = seg_riesgos_laboral.id_rlab)
            WHERE id_empleado = '$id'
            ORDER BY fec_afiliacion ASC";
    $rs = $cmd->query($sql);
    $arl = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$hoy = $date->format('Y-m-d');
if (!empty($arl)) {
    foreach ($arl as $a) {
        if ((intval($permisos['editar'])) === 1) {
            $editar = '<button value="' . $a['id_novarl'] . '" class="btn btn-outline-primary btn-sm btn-circle editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></button>';
        } else {
            $editar = null;
        }
        if ((intval($permisos['borrar'])) === 1) {
            $borrar = '<button value="' . $a['id_novarl'] . '" class="btn btn-outline-danger btn-sm btn-circle borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></button>';
        } else {
            $borrar = null;
        }
        if ($a['fec_retiro'] != ''  && $a['fec_retiro'] <= $hoy) {
            $editar = $borrar = null;
        }
        $data[] = [
            'id_novarl' => $a['id_novarl'],
            'nombre_arl' => $a['nombre_arl'],
            'nitarl' => $a['nitarl'],
            'riesgo' => $a['riesgo'],
            'fec_afiliacion' => $a['fec_afiliacion'],
            'fec_retiro' => $a['fec_retiro'],
            'botones' => '<div class="center-block">' . $editar . $borrar . '</div>'
        ];
    }
} else {
    $data = [
        'id_novarl' => '',
        'nombre_arl' => '',
        'nitarl' => '',
        'riesgo' => '',
        'fec_afiliacion' => '',
        'fec_retiro' => '',
        'botones' => '',
    ];
}

$datos = ['data' => $data];

echo json_encode($datos);
