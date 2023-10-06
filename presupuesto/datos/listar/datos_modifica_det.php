<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
// Div de acciones de la lista
$tipo_doc = $_POST['id_pto_mod'];
$tipo_mod = $_POST['tipo_mod'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
    `seg_pto_mvto`.`id_pto_doc` as `id_pto_doc`
    , `seg_pto_mvto`.`rubro` as rubro
    , `seg_pto_mvto`.`mov` as mov
    , `seg_pto_cargue`.`nom_rubro` as nom_rubro
    , `seg_pto_mvto`.`valor` as valor
    , `seg_pto_cargue`.`id_pto_presupuestos` as id_pto
    , `seg_pto_mvto`.`id_pto_mvto`
    FROM
    `seg_pto_mvto`
    INNER JOIN `seg_pto_cargue` 
        ON (`seg_pto_mvto`.`rubro` = `seg_pto_cargue`.`cod_pptal`)
    WHERE (`seg_pto_mvto`.`id_pto_doc` =$tipo_doc);";
    // Si documento es igual a TRA modificamos la consulta
    $rs = $cmd->query($sql);
    $listappto = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consulto el id de presupuesto de ingresos
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_pto_presupuestos FROM seg_pto_presupuestos WHERE id_pto_tipo=1 AND vigencia =$_SESSION[vigencia]";
    $rs = $cmd->query($sql);
    $ptoingreso = $rs->fetch();
    $id_ingreso = $ptoingreso['id_pto_presupuestos'];
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

if (!empty($listappto)) {

    foreach ($listappto as $lp) {
        $id_pto_mvto = $lp['id_pto_mvto'];
        $id_pto = $lp['id_pto_doc'];
        if ($lp['id_pto'] == $id_ingreso) {
            $valor1 = number_format($lp['valor'], 2, '.', ',');
            $valor2 = 0;
        } else {
            $valor1 = 0;
            $valor2 = number_format($lp['valor'], 2, '.', ',');
            if ($tipo_mod == 'TRA') {
                if ($lp['mov'] == 0) {
                    $valor1 = number_format($lp['valor'], 2, '.', ',');
                    $valor2 = 0;
                } else {
                    $valor2 = number_format($lp['valor'], 2, '.', ',');
                    $valor1 = 0;
                }
            }
        }
        if ((intval($permisos['editar'])) === 1) {
            $editar = '<a value="' . $id_pto . '" onclick="editarListaDetalleMod(' . $id_pto . ')" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar detalle"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            $detalles = '<a value="' . $id_pto . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb detalles" title="Detalles"><span class="fas fa-eye fa-lg"></span></a>';
            $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
            ...
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <a value="' . $id_pto . '" class="dropdown-item sombra carga" href="#">Cargar2 presupuesto</a>
            <a value="' . $id_pto . '" class="dropdown-item sombra modifica" href="#">Modificaciones</a>
            <a value="' . $id_pto . '" class="dropdown-item sombra ejecuta" href="#">Ejecución</a>
            </div>';
        } else {
            $editar = null;
            $detalles = null;
        }
        if ((intval($permisos['borrar'])) === 1) {
            $borrar = '<a value="' .  $id_pto_mvto . '"  onclick="eliminarRubroDetalleMod(' .  $id_pto_mvto . ')"class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        } else {
            $borrar = null;
        }
        $data[] = [
            'rubro' => $lp['rubro'] . ' - ' . $lp['nom_rubro'],
            'valor' => '<div class="text-right">' . $valor1 . '</div>',
            'valor2' => '<div class="text-right">' . $valor2 . '</div>',
            'botones' => '<div class="text-center" style="position:relative">' . $editar . $borrar . $detalles . $acciones . '</div>',

        ];
    }
} else {
    $data = [];
}

$datos = ['data' => $data];


echo json_encode($datos);
