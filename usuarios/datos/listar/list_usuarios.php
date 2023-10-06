<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: <?php echo $_SESSION["urlin"] ?>/index.php');
    exit;
}
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}

include '../../../conexion.php';
include '../../../permisos.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_usuarios`.`id_usuario`
                , `seg_usuarios`.`tip_documento`
                , `seg_usuarios`.`documento`
                , `seg_usuarios`.`nombre1`
                , `seg_usuarios`.`nombre2`
                , `seg_usuarios`.`apellido1`
                , `seg_usuarios`.`apellido2`
                , `seg_usuarios`.`login`
                , `seg_usuarios`.`correo`
                , `seg_usuarios`.`estado`
                , `seg_rol`.`nombre`
            FROM
                `seg_usuarios`
                INNER JOIN `seg_rol` 
                    ON (`seg_usuarios`.`id_rol` = `seg_rol`.`id_rol`)";
    $rs = $cmd->query($sql);
    $obj = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$data = [];
if (!empty($obj)) {
    foreach ($obj as $o) {
        $id_user = $o['id_usuario'];
        $editar = $borrar = null;
        if ((intval($permisos['editar'])) == 1) {
            $editar = '<a value="' . $id_user . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Actualizar o modificar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        }
        if ((intval($permisos['borrar'])) == 1) {
            $borrar = '<a value="' . $id_user . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        }
        if ($o['estado'] == '1') {
            $estado = '<a value="' . $id_user . '|1' . '" class="btn btn-sm btn-circle estado" title="Activo"><span class="fas fa-toggle-on fa-2x" style="color:#37E146;"></span></a>';
        } else {
            $estado = '<a value="' . $id_user . '|0' . '" class="btn btn-sm btn-circle estado" title="Inactivo"> <span class="fas fa-toggle-off fa-2x" style="color:gray;"></span></a>';
        }
        $data[] = [
            'num_doc' => $o['documento'],
            'nombres' => mb_strtoupper($o['nombre1'] . ' ' . $o['nombre2']),
            'apellidos' => mb_strtoupper($o['apellido1'] . ' ' . $o['apellido2']),
            'correo' => $o['correo'],
            'user' => $o['login'],
            'rol' => $o['nombre'],
            'estado' => '<div class="text-center">' . $estado . '</div>',
            'botones' => '<div class="text-center">' . $editar . $borrar . '</div>',
        ];
    }
}

$datos = [
    'data' => $data,
];
echo json_encode($datos);
