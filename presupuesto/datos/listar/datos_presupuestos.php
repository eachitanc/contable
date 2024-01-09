<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
// Div de acciones de la lista

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_pto_presupuestos`
                ,`seg_pto_presupuestos`.`nombre`
                , `seg_pto_tipo`.`nombre` as tipo
                , `seg_pto_presupuestos`.`vigencia`
            FROM
                `seg_pto_presupuestos`
                INNER JOIN `seg_pto_tipo` 
                    ON (`seg_pto_presupuestos`.`id_pto_tipo` = `seg_pto_tipo`.`id_pto_tipo`)
            WHERE `seg_pto_presupuestos`.`vigencia`= $_SESSION[vigencia] ";
    $rs = $cmd->query($sql);
    $listappto = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if (!empty($listappto)) {

    foreach ($listappto as $lp) {
        $id_pto = $lp['id_pto_presupuestos'];
        if ((intval($permisos['editar'])) === 1) {
            $editar = '<a value="' . $id_pto . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            $detalles = '<a value="' . $id_pto . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb detalles" title="Detalles"><span class="fas fa-eye fa-lg"></span></a>';
            $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
            ...
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <a value="' . $id_pto . '" class="dropdown-item sombra carga" href="#">Cargar presupuesto</a>
            <a value="' . $id_pto . '" class="dropdown-item sombra modifica" href="#">Modificaciones</a>
            <a value="' . $id_pto . '" class="dropdown-item sombra ejecuta" href="#">Ejecución</a>
            <a value="' . $id_pto . '" class="dropdown-item sombra homologa" href="#">Homologación</a>
            </div>';
        } else {
            $editar = null;
            $detalles = null;
            $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
            ...
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <a value="' . $id_pto . '" class="dropdown-item sombra ejecuta" href="#">Ejecución</a>
            </div>';
        }
        if ((intval($permisos['borrar'])) === 1) {
            $borrar = '<a value="' . $id_pto . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        } else {
            $borrar = null;
        }

        $data[] = [
            'id_pto' => $lp['id_pto_presupuestos'],
            'nombre' => $lp['nombre'],
            'tipo' => $lp['tipo'],
            'vigencia' => $lp['vigencia'],
            'botones' => '<div class="text-center" style="position:relative">' . $editar . $borrar . $detalles . $acciones . '</div>',

        ];
    }
} else {
    $data = [];
}

$datos = ['data' => $data];


echo json_encode($datos);
