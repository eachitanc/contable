<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
// Div de acciones de la lista
$id_ctb_doc = $_POST['id_doc'];
$vigencia = $_SESSION['vigencia'];
$dato = null;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_ctb_doc`.`id_ctb_doc`
                , `seg_ctb_doc`.`id_manu`
                , `seg_ctb_doc`.`fecha`
                , `seg_ctb_doc`.`detalle`
                , `seg_ctb_doc`.`id_tercero`
                , `seg_ctb_doc`.`estado`
                , `seg_nominas`.`id_nomina`
                , `seg_nominas`.`tipo`
                , `seg_ctb_doc`.`tipo_doc`
                , `seg_ctb_doc`.`vigencia`
            FROM
                `seg_ctb_doc`
                LEFT JOIN `seg_nominas` 
                    ON (`seg_ctb_doc`.`id_nomina` = `seg_nominas`.`id_nomina`)
            WHERE (`seg_ctb_doc`.`fecha` > '2022-12-31'
                AND `seg_ctb_doc`.`tipo_doc` = '$id_ctb_doc'
                AND `seg_ctb_doc`.`vigencia` = '$vigencia')";
    $rs = $cmd->query($sql);
    $listappto = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// consultar la fecha de cierre del periodo del módulo de presupuesto 
try {
    $sql = "SELECT fecha_cierre FROM seg_fin_periodos WHERE id_modulo=6";
    $rs = $cmd->query($sql);
    $fecha_cierre = $rs->fetch();
    $fecha_cierre = $fecha_cierre['fecha_cierre'];
    $fecha_cierre = date('Y-m-d', strtotime($fecha_cierre));
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if (!empty($listappto)) {

    $ids = [];
    foreach ($listappto as $lp) {
        if ($lp['id_tercero'] !== null) {
            $ids[] = $lp['id_tercero'];
        }
    }
    $payload = json_encode($ids);
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
        $id_ctb = $lp['id_ctb_doc'];
        $estado = $lp['estado'];
        $enviar = NULL;
        $dato = null;
        // Buscar el nombre del tercero
        $key = array_search($lp['id_tercero'], array_column($terceros, 'id_tercero'));
        if ($key !== false) {
            $tercero = $terceros[$key]['nombre1'] . ' ' . $terceros[$key]['nombre2'] . ' ' . $terceros[$key]['apellido1'] . ' ' . $terceros[$key]['apellido2'] . ' ' . $terceros[$key]['razon_social'];
            $ccnit = $terceros[$key]['cc_nit'];
        } else {
            $tercero = '';
        }
        if ($lp['tipo'] == 'N') {
            $enviar = '<button id ="enviar_' . $id_ctb . '" value="' . $lp['id_nomina'] . '" onclick="EnviarNomina(this)" class="btn btn-outline-primary btn-sm btn-circle shadow-gb"  title="Procesar nómina (Soporte Electrónico)"><span class="fas fa-paper-plane fa-lg"></span></button>';
        }
        // fin api terceros
        // consultar la suma de debito y credito en la tabla seg_ctb_libaux para el documento
        $sql = "SELECT sum(debito) as debito, sum(credito) as credito FROM seg_ctb_libaux WHERE id_ctb_doc=$id_ctb GROUP BY id_ctb_doc";
        $rs3 = $cmd->query($sql);
        $suma = $rs3->fetch();
        $dif = $suma['debito'] - $suma['credito'];
        if ($dif != 0) {
            $valor_total = 'Error';
        } else {
            $valor_total = number_format($suma['credito'], 2, ',', '.');
        }
        $fecha = date('Y-m-d', strtotime($lp['fecha']));


        // Sumar el valor del crp de la tabla id_pto_mtvo asociado al CDP
        // si $fecha es menor a $fecha_cierre no se puede editar ni eliminar
        if ($fecha <= $fecha_cierre) {
            $anular = null;
            $cerrar = null;
        } else {
            $anular = '<a value="' . $id_ctb . '" class="dropdown-item sombra " href="#" onclick="anularDocumentoTes(' . $id_ctb . ');">Anulación</a>';
        }
        if ((intval($permisos['editar'])) === 1) {
            $editar = '<a id ="editar_' . $id_ctb . '" value="' . $id_ctb . '" onclick="cargarListaDetallePagoEdit(' . $id_ctb . ')" class="btn btn-outline-primary btn-sm btn-circle shadow-gb"  title="Editar_' . $id_ctb . '"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            $detalles = '<a value="' . $id_ctb . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb detalles" title="Detalles"><span class="fas fa-eye fa-lg"></span></a>';
            $imprimir = '<a value="' . $id_ctb . '" onclick="imprimirFormatoTes(' . $lp['id_ctb_doc'] . ')" class="btn btn-outline-success btn-sm btn-circle shadow-gb " title="Detalles"><span class="fas fa-print fa-lg"></span></a>';
            // Acciones teniendo en cuenta el tipo de rol
            //si es lider de proceso puede abrir o cerrar documentos

            if ($rol['id_rol'] == 4 || $rol['id_rol'] == 1) {
                if ($estado == 0) {
                    $cerrar = '<a value="' . $id_ctb . '" class="dropdown-item sombra carga" onclick="cerrarDocumentoCtb(' . $id_ctb . ')" href="#">Cerrar documento</a>';
                } else {
                    $cerrar = '<a value="' . $id_ctb . '" class="dropdown-item sombra carga" onclick="abrirDocumentoTes(' . $id_ctb . ')" href="#">Abrir documento</a>';
                }
                $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
                ...
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
               ' . $cerrar . '
                ' . $anular . '
                <a value="' . $id_ctb . '" class="dropdown-item sombra" href="#">Duplicar</a>
                <a value="' . $id_ctb . '" class="dropdown-item sombra" href="#">Parametrizar</a>
                </div>';
            } else {
                $cerrar = null;
            }
            if ($estado == 0) {
                $cerrar = '<a value="' . $id_ctb . '" class="dropdown-item sombra carga" onclick="cerrarDocumentoCtb(' . $id_ctb . ')" href="#">Cerrar documento</a>';
            }
        } else {
            $editar = null;
            $detalles = null;
            $acciones = null;
        }

        if ((intval($permisos['borrar'])) === 1) {
            $borrar = '<a value="' . $id_ctb . '" onclick="eliminarRegistroTec(' . $id_ctb . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb "  title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        } else {
            $borrar = null;
        }

        if ($estado == 1) {
            $editar = null;
            $borrar = null;
        }

        if ($estado == 5) {
            $editar = null;
            $borrar = null;
            $imprimir = null;
            $acciones = null;
            $enviar = null;
            $dato = '<span class="badge badge-pill badge-danger">Anulado</span>';
        }
        $data[] = [

            'numero' =>  $lp['id_manu'],
            'fecha' => $fecha,
            'ccnit' => $ccnit,
            'tercero' => $tercero,
            'valor' =>  '<div class="text-right">' . $valor_total . '</div>',
            'botones' => '<div class="text-center" style="position:relative">' . $editar . $borrar . $imprimir . $acciones . $enviar . $dato . '</div>',
        ];
    }
} else {
    $data = [];
}
$cmd = null;
$datos = ['data' => $data];


echo json_encode($datos);
