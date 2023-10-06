<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
include '../../../financiero/consultas.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

// Consulta funcion fechaCierre del modulo 4
$fecha_cierre = fechaCierre($_SESSION['vigencia'], 4, $cmd);
// Div de acciones de la lista
$id_pto_presupuestos = $_POST['id_ejec'];
// Recuperar los parámetros start y length enviados por DataTables
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$search_value = $_POST['search'] ?? '';
// Verifico si serach_value tiene datos para buscar
if (!empty($search_value)) {
    $buscar = "AND (seg_pto_documento.id_manu LIKE '%$search_value%' OR seg_pto_documento.objeto LIKE '%$search_value%' OR seg_pto_documento.fecha LIKE '%$search_value%' OR afec.dispon LIKE '$search_value' )";
} else {
    $buscar = '';
}
try {
    //$sql = "SELECT id_pto_doc,id_manu,fecha,objeto FROM seg_pto_documento WHERE id_pto_presupuestos=$id_pto_presupuestos AND tipo_doc='CDP' ORDER BY id_manu DESC LIMIT $start, $length";
    $sql = "SELECT 
                seg_pto_documento.id_pto_doc
                ,seg_pto_documento.id_manu
                ,seg_pto_documento.fecha
                ,seg_pto_documento.objeto
                ,seg_pto_documento.estado
                ,afec.dispon AS disponibilidad
                ,IFNULL(comp.comprometido,0) AS comprometido
                ,IFNULL(anula.anulado,0) AS anulado
            FROM seg_pto_documento 
            LEFT JOIN (
                SELECT
                    SUM(`seg_pto_mvto`.`valor`) AS dispon
                    , `seg_pto_mvto`.`id_pto_doc`
                FROM
                    `seg_pto_mvto`
                    INNER JOIN `seg_pto_documento` ON (`seg_pto_mvto`.`id_pto_doc` = `seg_pto_documento`.`id_pto_doc`)
                WHERE (`seg_pto_documento`.`estado` =0  AND `seg_pto_mvto`.`tipo_mov` ='CDP')
                GROUP BY `seg_pto_mvto`.`id_pto_doc`
            ) AS afec ON(seg_pto_documento.`id_pto_doc`=afec.id_pto_doc)
            LEFT JOIN (
                SELECT
                    SUM(`seg_pto_mvto`.`valor`) AS comprometido
                    ,`seg_pto_mvto`.`id_auto_dep`
                FROM
                    `seg_pto_documento` 
                    INNER JOIN `seg_pto_mvto` ON (`seg_pto_documento`.`id_pto_doc` = `seg_pto_mvto`.`id_auto_dep`)
                WHERE `seg_pto_mvto`.`estado` =0 AND (`seg_pto_mvto`.`tipo_mov` ='CRP' OR `seg_pto_mvto`.`tipo_mov` ='LRP')
                GROUP BY `seg_pto_mvto`.`id_auto_dep`
            ) AS comp ON(seg_pto_documento.`id_pto_doc`=comp.id_auto_dep)
            LEFT JOIN (
                SELECT
                    SUM(`seg_pto_mvto`.`valor`) AS anulado
                    ,`seg_pto_mvto`.`id_auto_dep`
                FROM
                    `seg_pto_documento`
                    INNER JOIN `seg_pto_mvto` 
                    ON (`seg_pto_documento`.`id_pto_doc` = `seg_pto_mvto`.`id_auto_dep`)
                WHERE (`seg_pto_documento`.`estado` =0
                    AND `seg_pto_mvto`.`tipo_mov` ='LCD')
                GROUP BY `seg_pto_mvto`.`id_auto_dep`
            ) AS anula ON(seg_pto_documento.`id_pto_doc`=anula.id_auto_dep)
            WHERE id_pto_presupuestos=$id_pto_presupuestos AND tipo_doc='CDP' $buscar 
            ORDER BY id_manu DESC 
            LIMIT $start, $length";
    $sql2 = $sql;
    $rs = $cmd->query($sql);
    $listappto = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// obtener el numero total de registros de la anterior consulta
try {
    $sql = "SELECT COUNT(*) AS total FROM seg_pto_documento WHERE id_pto_presupuestos=$id_pto_presupuestos AND tipo_doc='CDP'";
    $rs = $cmd->query($sql);
    $total = $rs->fetch();
    $totalRecords = $total['total'];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// consultar la fecha de cierre del periodo del módulo de presupuesto 
try {
    $sql = "SELECT fecha_cierre FROM seg_fin_periodos WHERE id_modulo=4";
    $rs = $cmd->query($sql);
    $fecha_cierre = $rs->fetch();
    $fecha_cierre = $fecha_cierre['fecha_cierre'];
    $fecha_cierre = date('Y-m-d', strtotime($fecha_cierre));
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if (!empty($listappto)) {

    foreach ($listappto as $lp) {
        $dato = null;
        $borrar = null;
        $id_pto = $lp['id_pto_doc'];
        // Sumar el valor del cdp de la tabla id_pto_mtvo
        $valor_cdp = number_format($lp['disponibilidad'], 2, ',', '.');
        $cxregistrar = ($lp['disponibilidad'] + $lp['anulado']) - $lp['comprometido'];
        $xregistrar = number_format($cxregistrar, 2, ',', '.');
        $fecha = date('Y-m-d', strtotime($lp['fecha']));
        // si $fecha es menor a $fecha_cierre no se puede editar ni eliminar
        if ($fecha <= $fecha_cierre) {
            $anular = null;
        } else {
            $anular = '<a value="' . $id_pto . '" class="dropdown-item sombra " href="#" onclick="anulacionCrp(' . $id_pto . ');">Anulación</a>';
        }
        if ((intval($permisos['editar'])) === 1) {
            $registrar = '<a value="' . $id_pto . '" onclick="CargarFormularioCrpp(' . $id_pto . ')" class="text-blue " role="button" title="Detalles"><span>Registrar</span></a>';
            if ($cxregistrar  == 0) {
                $registrar = '--';
                $anular = null;
            }
            if ($fecha < $fecha_cierre) {
                $editar = null;
            }
            $editar = '<a value="' . $id_pto . '" class="btn btn-outline-primary btn-sm btn-circle shadow-gb editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            $detalles = '<a value="' . $id_pto . '" onclick="generarFormatoCdp(' . $id_pto . ')" class="btn btn-outline-warning btn-sm btn-circle shadow-gb detalles" title="Detalles"><span class="fas fa-eye fa-lg"></span></a>';
            $imprimir = '<a value="' . $id_pto . '" onclick="imprimirFormatoCdp(' . $id_pto . ')" class="btn btn-outline-success btn-sm btn-circle shadow-gb detalles" title="Detalles"><span class="fas fa-print fa-lg"></span></a>';
            $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
            ...
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <a value="' . $id_pto . '" onclick="verLiquidarCdp(' . $id_pto . ')" class="dropdown-item sombra" href="#">Ver historial</a>
            ' . $anular . '
            </div>';
        } else {
            $editar = null;
            $detalles = null;
        }
        if ((intval($permisos['borrar'])) === 1) {
            $borrar = '<a value="' . $id_pto . '"    onclick="eliminarCdp(' . $id_pto . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb " title="Registrar"><span class="fas fa-trash-alt fa-lg"></span></a>';
            if ($fecha < $fecha_cierre) {
                $borrar = null;
            }
            if ($lp['disponibilidad'] ==  $cxregistrar) {
            } else {
                $borrar = null;
                $editar = null;
            }
        }
        if ($lp['estado'] == 5) {
            $borrar = null;
            $editar = null;
            $detalles = null;
            $acciones = null;
            $imprimir = null;
            $dato = 'Anulado';
            // Consultar el velor del cdp anulado
            $sql = "SELECT SUM(valor) as valor FROM seg_pto_mvto WHERE id_pto_doc=$id_pto";
            $rs = $cmd->query($sql);
            $valorcdp = $rs->fetch();
            $valor_cdp = number_format($valorcdp['valor'], 2, ',', '.');
        }
        $data[] = [
            'numero' => $lp['id_manu'],
            'fecha' => $fecha,
            'objeto' => $lp['objeto'],
            'valor' =>  '<div class="text-right">' . $valor_cdp . '</div>',
            'xregistrar' =>  '<div class="text-right">' . $xregistrar  . '</div>',
            'accion' => '<div class="text-center">' . $registrar . '</div>',
            'botones' => '<div class="text-center" style="position:relative">' . $editar . $borrar . $imprimir . $acciones . $dato . '</div>',
        ];
    }
} else {
    $data = [];
}
$cmd = null;
$datos = [
    'data' => $data,
    'recordsFiltered' => $totalRecords,
];


echo json_encode($datos);
