<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
function pesos($valor)
{
    return '$ ' . number_format($valor, 2, ',', '.');
}
$id = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida .-');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_dcto`, `id_empleado`, `fecha`, `concepto`, `valor`
            FROM
                `otros_descuentos`
            WHERE (`id_empleado` = $id)";
    $rs = $cmd->query($sql);
    $descuentos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
include '../../../../permisos.php';
$data = [];
if (!empty($descuentos)) {
    foreach ($descuentos as $l) {
        $id_dcto = $l['id_dcto'];
        if ((intval($permisos['editar'])) == 1) {
            $editar = '<button value="' . $id_dcto . '" class="btn btn-outline-primary btn-sm btn-circle editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></button>';
        } else {
            $editar = null;
        }
        if ((intval($permisos['borrar'])) == 1) {
            $borrar = '<button value="' . $id_dcto . '" class="btn btn-outline-danger btn-sm btn-circle borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></button>';
        } else {
            $borrar = null;
        }
        $data[] = [
            'id_dcto' => $id_dcto,
            'fecha' => $l['fecha'],
            'concepto' => $l['concepto'],
            'valor' => '<div class="text-right">' . pesos($l['valor']) . '</div>',
            'botones' => '<div class="text-center">' . $editar . $borrar . '</div>'
        ];
    }
}

$datos = ['data' => $data];

echo json_encode($datos);
