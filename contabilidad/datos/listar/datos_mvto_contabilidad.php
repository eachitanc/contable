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
                , `seg_ctb_factura`.`tipo_doc` as `tipo`
                , `seg_ctb_doc`.`tipo_doc`
            FROM
                `seg_ctb_factura`
                RIGHT JOIN `seg_ctb_doc` 
                    ON (`seg_ctb_factura`.`id_ctb_doc` = `seg_ctb_doc`.`id_ctb_doc`)
            WHERE `seg_ctb_doc`.`tipo_doc`='$id_ctb_doc' AND `seg_ctb_doc`.`vigencia` = $vigencia";
    $rs = $cmd->query($sql);
    $listappto = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// consultar la fecha de cierre del periodo del módulo de presupuesto 
try {
    $sql = "SELECT fecha_cierre FROM seg_fin_periodos WHERE id_modulo=3";
    $rs = $cmd->query($sql);
    $fecha_cierre = $rs->fetch();
    $fecha_cierre = $fecha_cierre['fecha_cierre'];
    $fecha_cierre = date('Y-m-d', strtotime($fecha_cierre));
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// consulto la diferencia de la suma debito credito de la tabla seg_ctb_libaux
try {
    $sql = "SELECT
    `id_ctb_doc`
    ,SUM(`debito`) AS debito
    , SUM(`credito`) AS credito
    , SUM(debito - credito) AS diferencia
    
    FROM
     `seg_ctb_libaux`
    GROUP BY `id_ctb_doc`;";
    $rs = $cmd->query($sql);
    $diferencias = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$inicia = $_SESSION['vigencia'] . '-01-01';
$termina = $_SESSION['vigencia'] . '-12-31';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_soporte`, `id_factura_no`, `shash`, `referencia`, `fecha`
            FROM
                `seg_soporte_fno`
            WHERE (`fecha` BETWEEN '$inicia' AND '$termina')";
    $rs = $cmd->query($sql);
    $equivalente = $rs->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
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
if (!empty($listappto)) {
    foreach ($listappto as $lp) {
        $valor_debito = 0;
        $id_ctb = $lp['id_ctb_doc'];
        $estado = $lp['estado'];
        $dato = null;
        // Buscar el nombre del tercero
        if ($lp['id_tercero'] !== null) {
            $id_ter = $lp['id_tercero'];
        } else {
            $id_ter = 0;
        }
        $key = array_search($id_ter, array_column($terceros, 'id_tercero'));
        if ($key !== false) {
            $tercero = $terceros[$key]['nombre1'] . ' ' . $terceros[$key]['nombre2'] . ' ' . $terceros[$key]['apellido1'] . ' ' . $terceros[$key]['apellido2'] . ' ' . $terceros[$key]['razon_social'];
        } else {
            $tercero = '';
        }
        // consultar la suma de debito y credito en la tabla seg_ctb_libaux para el documento
        /*
        $sql = "SELECT sum(debito) as debito, sum(credito) as credito FROM seg_ctb_libaux WHERE id_ctb_doc=$id_ctb GROUP BY id_ctb_doc";
        $rs3 = $cmd->query($sql);
        $suma = $rs3->fetch();
        $dif = $suma['debito'] - $suma['credito'];
        */
        // consultar la diferencia en array diferencias
        $key = array_search($id_ctb, array_column($diferencias, 'id_ctb_doc'));
        $dif = $diferencias[$key]['diferencia'];
        if ($key  !== false) {
            $valor_debito = $diferencias[$key]['debito'];
        } else {
            $valor_debito = 0;
        }
        if ($dif != 0) {
            $valor_total = 'Error';
        } else {
            $valor_total = number_format($valor_debito, 2, ',', '.');
        }
        // Consulto el numero de registro presupuestal asociado al documento
        /*
       $sql = "SELECT
        `seg_pto_documento`.`id_manu`
        , `seg_pto_mvto`.`id_ctb_doc`
        FROM
        `seg_pto_mvto`
        INNER JOIN `seg_pto_documento` 
            ON (`seg_pto_mvto`.`id_pto_doc` = `seg_pto_documento`.`id_pto_doc`)
        WHERE (`seg_pto_mvto`.`id_ctb_doc` =$id_ctb )
        GROUP BY `seg_pto_mvto`.`id_ctb_doc`;";
        $rs4 = $cmd->query($sql);
        $docment = $rs4->fetch();
        */
        $id_manu_rp = 0; // $docment['id_manu'];

        $fecha = date('Y-m-d', strtotime($lp['fecha']));
        // Sumar el valor del crp de la tabla id_pto_mtvo asociado al CDP
        if ($estado == 0) {
            $cerrar = '<a value="' . $id_ctb . '" class="dropdown-item sombra carga" onclick="cerrarDocumentoCtb(' . $id_ctb . ')" href="#">Cerrar documento</a>';
        } else {
            $cerrar = '<a value="' . $id_ctb . '" class="dropdown-item sombra carga" onclick="abrirDocumentoCtb(' . $id_ctb . ')" href="#">Abrir documento</a>';
        }
        // si $fecha es menor a $fecha_cierre no se puede editar ni eliminar
        if ($fecha <= $fecha_cierre) {
            $anular = null;
            $cerrar = null;
        } else {
            $anular = '<a value="' . $id_ctb . '" class="dropdown-item sombra " href="#" onclick="anularDocumentoCont(' . $id_ctb . ');">Anulación</a>';
        }
        if ((intval($permisos['editar'])) === 1) {
            $editar = '<a id ="editar_' . $id_ctb . '" value="' . $id_ctb . '" onclick="cargarListaDetalle(' . $id_ctb . ')" class="btn btn-outline-primary btn-sm btn-circle shadow-gb"  title="Editar_' . $id_ctb . '"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            $detalles = '<a value="' . $id_ctb . '" class="btn btn-outline-warning btn-sm btn-circle shadow-gb detalles" title="Detalles"><span class="fas fa-eye fa-lg"></span></a>';
            $imprimir = '<a value="' . $id_ctb . '" onclick="imprimirFormatoDoc(' . $lp['id_ctb_doc'] . ')" class="btn btn-outline-success btn-sm btn-circle shadow-gb " title="Detalles"><span class="fas fa-print fa-lg"></span></a>';
            $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
            ...
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
           ' . $cerrar . '
           ' . $anular . '
            <a value="' . $id_ctb . '" class="dropdown-item sombra" href="#">Duplicar</a>
            <a value="' . $id_ctb . '" class="dropdown-item sombra" href="#">Parametrizar</a>
            </div>';
            // Acciones teniendo en cuenta el tipo de rol
            //si es lider de proceso puede abrir o cerrar documentos
            // $acciones = null;
            if ($rol['id_rol'] == 3 || $rol['id_rol'] == 1) {
                if ($estado == 0) {
                    $cerrar = '<a value="' . $id_ctb . '" class="dropdown-item sombra carga" onclick="cerrarDocumentoCtb(' . $id_ctb . ')" href="#">Cerrar documento</a>';
                } else {
                    $cerrar = '<a value="' . $id_ctb . '" class="dropdown-item sombra carga" onclick="abrirDocumentoCtb(' . $id_ctb . ')" href="#">Abrir documento</a>';
                }
            } else {
                $cerrar = null;
            }
        } else {
            $editar = null;
            $detalles = null;
            $acciones = null;
        }
        if ((intval($permisos['borrar'])) === 1) {
            $borrar = '<a value="' . $id_ctb . '" onclick="eliminarRegistroDoc(' . $id_ctb . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb "  title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        } else {
            $borrar = null;
        }

        if ($estado == 1) {
            $editar = null;
            $borrar = null;
        }
        $enviar = null;
        if ($fecha < '2023-02-16') {
            $enviar = null;
        } else {
            if ($lp['tipo'] == 3) {
                $key = array_search($id_ctb, array_column($equivalente, 'id_factura_no'));
                if ($key !== false) {
                    $enviar = '<a onclick="VerSoporteElectronico(' . $equivalente[$key]['id_soporte'] . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb" title="VER DOCUMENTO"><span class="far fa-file-pdf fa-lg"></span></a>';
                } else {
                    $enviar = '<a id="enviaSoporte" onclick="EnviaDocumentoSoporte(' . $id_ctb . ')" class="btn btn-outline-info btn-sm btn-circle shadow-gb" title="REPORTAR FACTURA"><span class="fas fa-paper-plane fa-lg"></span></a>';
                }
            }
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

            'numero' => $lp['id_manu'],
            'rp' =>  $id_manu_rp,
            'fecha' => $fecha,
            'tercero' => $tercero,
            'valor' =>  '<div class="text-right">' . $valor_total . '</div>',
            'botones' => '<div class="text-center" style="position:relative">' . $editar . $borrar . $imprimir  . $enviar . $acciones .  $dato . '</div>',
        ];
    }
} else {
    $data = ['entro' => $sql];
}
$cmd = null;
$datos = ['data' => $data];


echo json_encode($datos);
