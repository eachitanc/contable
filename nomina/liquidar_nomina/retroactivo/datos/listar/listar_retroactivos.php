<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../../index.php");</script>';
    exit();
}
include '../../../../../conexion.php';
include '../../../../../permisos.php';
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
            `seg_retroactivos`.`id_retroactivo`
            , `seg_retroactivos`.`fec_inicio`
            , `seg_retroactivos`.`fec_final`
            , `seg_retroactivos`.`meses`
            , `seg_retroactivos`.`id_incremento`
            , `seg_incremento_salario`.`porcentaje`
            , `seg_retroactivos`.`observaciones`
            , `seg_retroactivos`.`estado`
            , `seg_retroactivos`.`vigencia`
        FROM
            `seg_retroactivos`
            INNER JOIN `seg_incremento_salario` 
                ON (`seg_retroactivos`.`id_incremento` = `seg_incremento_salario`.`id_inc`)
        WHERE (`seg_retroactivos`.`vigencia` = '$vigencia')";
    $rs = $cmd->query($sql);
    $retroactivos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$datos = [];
foreach ($retroactivos as $ra) {
    $id = $ra['id_retroactivo'];
    $editar = $borrar = $incrementa = null;
    if ($ra['estado'] == '1') {
        if ((intval($permisos['editar'])) == 1) {
            $editar = '<a value="' . $id . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Actualizar o modificar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            $incrementa = '<a value="' . $id . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb incrementar" title="Efectuar incremento"><span class="fas fa-sort-amount-up fa-lg"></span></a>';
        }
        if ((intval($permisos['borrar'])) == 1) {
            $borrar = '<a value="' . $id . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }
    } else {
        $incrementa = '<a value="' . $id . '" class="btn btn-outline-secondary btn-sm btn-circle shadow-gb incrementar" title="Efectuar incremento"><span class="fas fa-sort-amount-up fa-lg"></span></a>';
    }
    $datos[] = array(
        'id' => $id,
        'inicia' => $ra['fec_inicio'],
        'termina' => $ra['fec_final'],
        'meses' => $ra['meses'],
        'incremento' => $ra['porcentaje'].' %',
        'observa' => $ra['observaciones'],
        'botones' => '<div class="text-center">' . $editar . $borrar . $incrementa . '</div>'
    );
}
$data = [
    'data' => $datos
];
echo json_encode($data);
