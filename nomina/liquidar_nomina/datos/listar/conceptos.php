<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `con_vigencias`.`anio`
                , `seg_valxvigencia`.`id_concepto`
                , `seg_conceptosxvigencia`.`concepto`
                , `seg_valxvigencia`.`valor`
                , `seg_valxvigencia`.`id_valxvig`
            FROM
                `seg_valxvigencia`
                INNER JOIN `seg_conceptosxvigencia` 
                    ON (`seg_valxvigencia`.`id_concepto` = `seg_conceptosxvigencia`.`id_concp`)
                INNER JOIN `con_vigencias` 
                    ON (`seg_valxvigencia`.`id_vigencia` = `con_vigencias`.`id_vigencia`)
            WHERE (`con_vigencias`.`anio` = '$vigencia')";
    $rs = $cmd->query($sql);
    $conceptos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$datos = [];
foreach ($conceptos as $cp) {
    $id = $cp['id_valxvig'];
    $actualizar = '<a value="' . $id . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb actualizar" title="Actualizar valor concepto"><span class="fas fa-pencil-alt fa-lg"></span></a>';
    $eliminar = '<a value="' . $id . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb eliminar" title="Eliminar concepto"><span class="fas fa-trash-alt fa-lg"></span></a>';
    $datos[] = array(
        'id' => $cp['id_concepto'],
        'concepto' => mb_strtoupper($cp['concepto']),
        'valor' => '<div class="text-right">' . pesos($cp['valor']) . '</div>',
        'botones' => '<div class="text-center">' . $actualizar . $eliminar . '</div>'
    );
}
$data = [
    'data' => $datos
];
echo json_encode($data);
