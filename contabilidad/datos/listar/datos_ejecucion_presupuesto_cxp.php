<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
// Llega el id del presupuesto que se esta listando
$id_pto_presupuestos = 2;//$_POST['id_ejec'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_pto_documento`.`id_pto_doc`
                , `seg_pto_documento`.`id_pto_presupuestos`
                , `seg_pto_documento`.`id_manu`
                , `seg_pto_documento`.`fecha`
                , `seg_pto_documento`.`id_auto`
                , `seg_pto_documento`.`id_tercero`
                , `z_terceros`.`nombre`
                , `seg_pto_documento`.`tipo_doc`
            FROM
                `seg_pto_documento`
                INNER JOIN `z_terceros` 
                    ON (`seg_pto_documento`.`id_tercero` = `z_terceros`.`num_id`)
            WHERE (`seg_pto_documento`.`id_pto_presupuestos` ='$id_pto_presupuestos'
                AND `seg_pto_documento`.`tipo_doc` ='CRP');
            ";
    $rs = $cmd->query($sql);
    $listappto = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

if (!empty($listappto)) {

    foreach ($listappto as $lp) {
        $id_pto = $lp['id_pto_doc'];
        // Sumar el valor del crp de la tabla id_pto_mtvo
        $sql = "SELECT SUM(valor) AS valor FROM seg_pto_mvto WHERE id_pto_doc=$id_pto";
        $rs2 = $cmd->query($sql);
        $suma = $rs2->fetch();
        $valor_cdp = $suma['valor'];
        $valor_cdp = number_format($valor_cdp, 2, ',', '.');
        $tercero = $lp['id_tercero'] . " - " . $lp['nombre'];
        $fecha = date('Y-m-d', strtotime($lp['fecha']));
        // Numero de cdp asociado al registros
        $sql = "SELECT id_manu FROM seg_pto_documento WHERE id_pto_doc =$lp[id_auto]";
        $rs = $cmd->query($sql);
        $listmanu = $rs->fetch();
        if ((intval($permisos['editar'])) === 1) {
            $editar = '<a value="' . $id_pto . '" onclick="CargarListadoCrpp(' . $id_pto . ')" class="btn btn-outline-primary btn-sm btn-circle shadow-gb" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            $detalles = '<a value="' . $id_pto . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb detalles" title="Detalles"><span class="fas fa-eye fa-lg"></span></a>';
            $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
            ...
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <a value="' . $id_pto . '" class="dropdown-item sombra carga" href="#">Ver historial</a>
            <a value="' . $id_pto . '" class="dropdown-item sombra" href="#">imprimir</a>
            </div>';
            $contabilizar = '<a value="' . $id_pto . '" onclick="cargarFormCxp(' . $id_pto . ')" class="text-blue " role="button" title="Detalles"><span>Contabilizar</span></a>';
        } else {
            $editar = null;
            $detalles = null;
        }
        if ((intval($permisos['borrar'])) === 1) {
            $borrar = '<a value="' . $id_pto . '" class="btn btn-outline-danger btn-sm btn-circle shadow-gb registrar" title="Registrar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        } else {
            $borrar = null;
        }
        $data[] = [

            'numero' => $lp['id_manu'],
            'cdp' => $listmanu['id_manu'],
            'fecha' => $fecha,
            'tercero' => $tercero,
            'valor' =>  '<div class="text-right">' . $valor_cdp . '</div>',
            'causacion' => '<div class="text-center">' . $contabilizar . '</div>',
            'botones' => '<div class="text-center" style="position:relative">' . $detalles . $acciones . '</div>',

        ];
    }
} else {
    $data = [];
}
$cmd = null;
$datos = ['data' => $data];


echo json_encode($datos);
