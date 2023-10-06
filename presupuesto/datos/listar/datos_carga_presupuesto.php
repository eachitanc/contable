<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
// Div de acciones de la lista
$id_pto_presupuestos = $_POST['id_cpto'];

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
    id_pto_cargue
    ,cod_pptal
    ,nom_rubro
    ,tipo_dato
FROM
    `seg_pto_cargue`
where
id_pto_presupuestos =$id_pto_presupuestos AND vigencia= $_SESSION[vigencia]";
    $rs = $cmd->query($sql);
    $listappto = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if (!empty($listappto)) {

    foreach ($listappto as $lp) {
        $id_pto = $lp['id_pto_cargue'];
        if ($lp['tipo_dato'] == 0) $tipo_dato = 'M';
        else $tipo_dato = 'D';
        //Consulto el valor cargado a presupuestos por cada rubro y presupuesto seleccionado
        $sql = "SELECT sum(ppto_aprob) as valor  FROM seg_pto_cargue where cod_pptal like '$lp[cod_pptal]%' and id_pto_presupuestos ='$id_pto_presupuestos' and tipo_dato=1";
        $rs = $cmd->query($sql);
        $valor = $rs->fetch();
        $valor_ppto = number_format($valor['valor'], 2, '.', ',');
        if ((intval($permisos['editar'])) === 1) {
            $editar = '<a value="' . $id_pto . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            $detalles = '<a value="' . $id_pto . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb detalles" title="Detalles"><span class="fas fa-eye fa-lg"></span></a>';
            $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
            ...
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            
            </div>';
        } else {
            $editar = null;
            $detalles = null;
        }
        if ((intval($permisos['borrar'])) === 1) {
            $borrar = '<a value="' . $id_pto . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        } else {
            $borrar = null;
        }
        $data[] = [

            'rubro' => $lp['cod_pptal'],
            'nombre' => $lp['nom_rubro'],
            'tipo_dato' => $tipo_dato,
            'valor' => '<div class="text-right">' . $valor_ppto . '</div>',
            'botones' => '<div class="text-center" style="position:relative">' . $editar . $borrar . $detalles . $acciones . '</div>',

        ];
    }
} else {
    $data = [];
}
$cmd = null;
$datos = ['data' => $data];


echo json_encode($datos);
