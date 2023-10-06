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

$fecha_cierre = fechaCierre($_SESSION['vigencia'], 4, $cmd);

// Div de acciones de la lista
$tipo_doc = $_POST['id_pto_doc'];
$id_pto_presupuestos = $_POST['id_pto_ppto'];
try {
    $sql = "SELECT
    `seg_pto_documento`.`id_pto_doc` as id_pto_doc
    , `seg_pto_documento`.`id_pto_presupuestos` as id_pto_presupuestos
    , `seg_pto_documento`.`tipo_doc` as tipo_doc
    , `seg_pto_documento`.`id_manu` as id_manu
    , `seg_pto_documento`.`fecha` as fecha
    , `seg_pto_documento`.`estado` as estado
    , `seg_pto_actos_admin`.`acto` as acto
FROM
    `seg_pto_documento`
    INNER JOIN `seg_pto_actos_admin` 
        ON (`seg_pto_documento`.`tipo_mod` = `seg_pto_actos_admin`.`id_pto_actos`)
        WHERE `seg_pto_documento`.`tipo_doc` ='$tipo_doc';";
    $rs = $cmd->query($sql);
    $listappto = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$num = 0;
if (!empty($listappto)) {

    foreach ($listappto as $lp) {
        $dato = null;
        $id_pto = $lp['id_pto_doc'];
        $fecha = date('Y-m-d', strtotime($lp['fecha']));
        $tipo_doc = $lp['tipo_doc'];
        // Consultar los valores registrados en base de datos por cada doucmento validando sumas iguales
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $sql = "SELECT sum(valor) as valorsum FROM seg_pto_mvto WHERE id_pto_doc =  $id_pto AND estado =1 GROUP BY id_pto_doc";
            $rs = $cmd->query($sql);
            $valores = $rs->fetch();
            $valor1 = $valores['valorsum'];
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
        }
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $sql = "SELECT sum(valor) as valorsum FROM seg_pto_mvto WHERE id_pto_doc =  $id_pto AND estado =0 GROUP BY id_pto_doc";
            $rs = $cmd->query($sql);
            $valores2 = $rs->fetch();
            $valor2 = $valores2['valorsum'];
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
        }
        $diferencia = $valor1 - $valor2;
        // si $fecha es menor a $fecha_cierre no se puede editar ni eliminar
        if ($fecha <= $fecha_cierre) {
            $anular = null;
        } else {
            $anular = '<a value="' . $id_pto . '" class="dropdown-item sombra " href="#" onclick="anulacionCrp(' . $id_pto . ');">Anulación</a>';
        }
        // Para el caso de los documentos aplazados
        if ($lp['tipo_doc'] == 'APL' || $lp['tipo_doc'] == 'DES') {
            $diferencia = 0;
        }
        if ($diferencia == 0) {
            $valor2 = number_format($valor2, 2, '.', ',');
            $estado = '<div class="text-right">' . $valor2 . '</div>';
        } else {
            $estado = '<div class="text-center"><span class="label text-danger">Incorrecto</span></div>';
        }

        if ($rol['id_rol'] == 4 || $rol['id_rol'] == 1) {
            if ($lp['estado'] == 0) {
                $cerrar = '<a value="' . $id_pto . '" class="dropdown-item sombra carga" onclick="abrirDocumentoMod(' . $id_pto . ')" href="#">Abrir documento</a>';
            } else {
                $cerrar = '<a value="' . $id_pto . '" class="dropdown-item sombra carga" onclick="cerrarDocumentoMod(' . $id_pto . ')" href="#">Cerrar documento</a>';
            }
            if ($fecha < $fecha_cierre) {
                $cerrar = null;
            }
        } else {
            $cerrar = null;
        }
        if ($lp['estado'] != 0) {
            $cerrar = '<a value="' . $id_pto . '" class="dropdown-item sombra carga" onclick="cerrarDocumentoMod(' . $id_pto . ')" href="#">Cerrar documento</a>';
        }
        if ($tipo_doc == 'APL') {
            $desaplazar = '<a value="' . $id_pto . '" class="dropdown-item sombra carga" onclick="redirecionarListaMod(' . $id_pto . ')" href="#">Desaplazar</a>';;
        } else {
            $desaplazar = null;
        }
        if ((intval($permisos['editar'])) === 1) {
            $detalles = '<a value="' . $id_pto . '" onclick="cargarListaDetalleMod(' . $id_pto . ')" class="btn btn-outline-primary btn-sm btn-circle shadow-gb" title="Detalles"><span class="fas fa-pencil-alt fa-lg"></span></a>';
            $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
            ...
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            ' . $cerrar . '
            ' . $desaplazar . '
            ' . $anular . '
            </div>';
            if ($fecha < $fecha_cierre) {
                $detalles = null;
            }
        } else {
            $detalles = null;
        }
        if ((intval($permisos['editar'])) === 1) {
            $imprimir = '<a value="' . $id_pto . '" onclick="imprimirFormatoMod(' . $id_pto . ')" class="btn btn-outline-success btn-sm btn-circle shadow-gb detalles" title="Detalles"><span class="fas fa-print fa-lg"></span></a>';
        }

        if ((intval($permisos['borrar'])) === 1) {
            $borrar = '<a id ="eliminar_' . $id_pto . '" value="' . $id_pto . '" onclick="eliminarModPresupuestal(' . $id_pto . ')" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
            if ($fecha < $fecha_cierre) {
                $borrar = null;
            }
        } else {
            $borrar = null;
        }
        // verifico estado del documento
        if ($lp['estado'] == '0') {
            $borrar = null;
        }
        if ($lp['estado'] == 5) {
            $borrar = null;
            $detalles = null;
            $acciones = null;
            $imprimir = null;
            $dato = 'Anulado';
        }
        $num = $num + 1;
        $data[] = [
            'num' => $num,
            'fecha' => $fecha,
            'documento' => $lp['acto'],
            'numero' => $lp['id_manu'],
            'valor' => $estado,
            'botones' => '<div class="text-center" style="position:relative">' . $borrar . $detalles . $imprimir . $acciones . $dato . '</div>',

        ];
    }
} else {
    $data = [];
}

$datos = ['data' => $data];


echo json_encode($datos);
