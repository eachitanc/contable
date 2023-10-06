<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
// Llega el id del presupuesto que se esta listando
$id_pto_presupuestos = $_POST['id_ejec'];
// Recuperar los parámetros start y length enviados por DataTables
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$search_value = $_POST['search'] ?? '';
// Verifico si serach_value tiene datos para buscar
if (!empty($search_value)) {
    $buscar = "AND (seg_pto_documento.id_manu LIKE '%$search_value%' OR seg_pto_documento.objeto LIKE '%$search_value%' OR seg_pto_documento.fecha LIKE '%$search_value%' OR comprom.comprometido LIKE '$search_value' )";
} else {
    $buscar = '';
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_pto_documento`.`id_pto_doc`
                , `tipo_doc`
                , `id_manu`
                , `fecha`
                , `id_tercero`
                , `id_auto`
                , `objeto`
                , `num_contrato`
                , `id_pto_presupuestos`
                , `estado`
                ,IFNULL(comprom.comprometido,0) AS comprometido
            FROM `seg_pto_documento`
            LEFT JOIN (
                SELECT
                    SUM(`seg_pto_mvto`.`valor`) AS comprometido
                    , `seg_pto_mvto`.`id_pto_doc`
                FROM
                    `seg_pto_mvto`
                    INNER JOIN `seg_pto_documento` ON (`seg_pto_mvto`.`id_pto_doc` = `seg_pto_documento`.`id_pto_doc`)
                WHERE (`seg_pto_documento`.`estado` =0  AND `seg_pto_mvto`.`tipo_mov` ='CRP')
                GROUP BY `seg_pto_mvto`.`id_pto_doc`
            ) AS comprom ON(seg_pto_documento.id_pto_doc=comprom.id_pto_doc)

            WHERE `tipo_doc` ='CRP' AND `id_pto_presupuestos` =$id_pto_presupuestos $buscar
            ORDER BY id_manu DESC 
            LIMIT $start, $length
    ;";
    $rs = $cmd->query($sql);
    $listappto = $rs->fetchAll();
    $id_ter = 75;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// obtener el numero total de registros de la anterior consulta
try {
    $sql = "SELECT COUNT(*) AS total FROM seg_pto_documento WHERE id_pto_presupuestos=$id_pto_presupuestos AND tipo_doc='CRP'";
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
    $id_t = [];
    foreach ($listappto as $rp) {
        $id_t[] = $rp['id_tercero'];
    }
    $payload = json_encode($id_t);
    //API URL
    $url = $api . 'terceros/datos/res/lista/terceros';
    $ch = curl_init($url);
    //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    $terceros = json_decode($result, true);


    foreach ($listappto as $lp) {
        $id_pto = $lp['id_pto_doc'];
        $dato = null;
        // Sumar el valor del crp de la tabla id_pto_mtvo
        $sql = "SELECT SUM(valor) AS valor FROM seg_pto_mvto WHERE id_pto_doc=$id_pto AND tipo_mov='CRP'";
        $rs2 = $cmd->query($sql);
        $suma = $rs2->fetch();
        $valor_cdp = $suma['valor'];

        $valor_cdp = number_format($valor_cdp, 2, ',', '.');
        $key = array_search($lp['id_tercero'], array_column($terceros, 'id_tercero'));

        $tercero = $terceros[$key]['apellido1'] . ' ' . $terceros[$key]['apellido2'] . ' ' . $terceros[$key]['nombre1'] . ' ' . $terceros[$key]['nombre2'] . ' ' . $terceros[$key]['razon_social'];
        $ccnit = $terceros[$key]['cc_nit'];
        // fin api terceros
        if ($lp['id_tercero'] == 0) {
            $tercero = 'NOMINA DE EMPLEADOS';
        }
        $fecha = date('Y-m-d', strtotime($lp['fecha']));
        // si $fecha es menor a $fecha_cierre no se puede editar ni eliminar
        if ($fecha <= $fecha_cierre) {
            $anular = null;
        } else {
            $anular = '<a value="' . $id_pto . '" class="dropdown-item sombra " href="#" onclick="anulacionCrp(' . $id_pto . ');">Anulación</a>';
        }

        // Numero de cdp asociado al registros
        $sql = "SELECT id_manu FROM seg_pto_documento WHERE id_pto_doc =$lp[id_auto]";
        $rs = $cmd->query($sql);
        $listmanu = $rs->fetch();
        if ((intval($permisos['editar'])) === 1) {
            $editar = '<a value="' . $id_pto . '" onclick="CargarListadoCrpp(' . $id_pto . ')" class="btn btn-outline-primary btn-sm btn-circle shadow-gb" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            $detalles = '<a value="' . $id_pto . '" onclick="imprimirFormatoCrp(' . $id_pto . ')" class="btn btn-outline-success btn-sm btn-circle shadow-gb" title="Detalles"><span class="fas fa-print fa-lg" ></span></a>';
            $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
            ...
            </button>
            
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            ' . $anular . '
            <a value="' . $id_pto . '" class="dropdown-item sombra " href="#">Ver historial</a>
            </div>';
        } else {
            $editar = null;
            $detalles = null;
        }
        if ((intval($permisos['borrar'])) === 1) {
            $borrar = '<a value="' . $id_pto . '" onclick="eliminarCrpp(' . $id_pto . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb" title="Registrar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        } else {
            $borrar = null;
        }
        // Consulta para validar que el crpp no tiene documentos asociados para poder eliminarlo
        $sql = "SELECT
        `seg_pto_documento`.`tipo_doc`
        FROM
        `seg_pto_documento`
        INNER JOIN `seg_ctb_factura` 
            ON (`seg_pto_documento`.`id_pto_doc` = `seg_ctb_factura`.`id_pto_crp`)
         WHERE (`seg_pto_documento`.`tipo_doc` ='CRP' AND `seg_pto_documento`.`id_pto_doc` = $id_pto);";
        $rs = $cmd->query($sql);
        // Verifico si la consulta vulve registros
        $listfact = $rs->fetch();
        if (!empty($listfact)) {
            $borrar = null;
            $editar = null;
            $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
            ...
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <a value="' . $id_pto . '" class="dropdown-item sombra " href="#">Ver historial</a>
            </div>';
        }
        // si estado es 5 quiere decir que el crp esta anulado
        if ($lp['estado'] == 5) {
            $borrar = null;
            $editar = null;
            $detalles = null;
            $acciones = null;
            $dato = 'Anulado';
        }

        $data[] = [
            'numero' => $lp['id_manu'],
            'cdp' => $listmanu['id_manu'],
            'fecha' => $fecha,
            'contrato' => $lp['num_contrato'],
            'ccnit' => $ccnit,
            'tercero' => $tercero,
            'valor' =>  '<div class="text-right">' . $valor_cdp . '</div>',
            'botones' => '<div class="text-center" style="position:relative">' . $editar . $borrar . $detalles . $acciones . $dato . '</div>',

        ];
    }
} else {
    $data = [];
}
$cmd = null;
$cmd = null;
$datos = [
    'data' => $data,
    'recordsFiltered' => $totalRecords,
];


echo json_encode($datos);
