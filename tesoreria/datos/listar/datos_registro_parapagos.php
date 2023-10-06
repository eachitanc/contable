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

try {
    $sql = "SELECT
    `seg_ctb_doc`.`vigencia`
    , `seg_pto_mvto`.`estado`
    , `seg_pto_mvto`.`id_pto_doc`
    , `seg_pto_mvto`.`id_auto_dep`
    , `seg_ctb_doc`.`id_tercero`
    , `seg_ctb_doc`.`id_manu`
    , `seg_ctb_doc`.`fecha`
    , `seg_ctb_doc`.`id_ctb_doc`
    FROM
    `seg_pto_mvto`
    INNER JOIN `seg_ctb_doc` 
        ON (`seg_pto_mvto`.`id_ctb_doc` = `seg_ctb_doc`.`id_ctb_doc`)
    WHERE (`seg_ctb_doc`.`vigencia` =$vigencia
    AND `seg_pto_mvto`.`estado` =0
    AND `seg_pto_mvto`.`tipo_mov` ='COP')
    GROUP BY `seg_pto_mvto`.`id_ctb_doc`;";
    $rs = $cmd->query($sql);
    $listado = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$id_t = [];
foreach ($listado as $rp) {
    if ($rp['id_tercero'] !== null) {
        $id_t[] = $rp['id_tercero'];
    }
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
foreach ($listado as $ce) {
    $id_doc = $ce['id_ctb_doc'];
    $id_ter = $ce['id_tercero'];
    $fecha = date('Y-m-d', strtotime($ce['fecha']));
    // consulto el id_manu de la tabla seg_ctb_doc cuando id_ctb_doc el $id_doc
    try {
        $sql = "SELECT id_manu FROM seg_pto_documento WHERE id_pto_doc =$ce[id_pto_doc]";
        $rs = $cmd->query($sql);
        $datamanu = $rs->fetch();
        $id_manu = $datamanu['id_manu'];
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    // Consulta terceros en la api
    $key = array_search($ce['id_tercero'], array_column($terceros, 'id_tercero'));
    $tercero = $terceros[$key]['apellido1'] . ' ' .  $terceros[$key]['apellido2'] . ' ' . $terceros[$key]['nombre2'] . ' ' .  $terceros[$key]['nombre1'] . ' ' .  $terceros[$key]['razon_social'];
    $ccnit = $terceros[$key]['cc_nit'];
    // fin api terceros
    // Obtener el saldo del registro por obligar valor del registro - el valor obligado efectivamente
    try {
        $sql = "SELECT sum(valor) as valorcop FROM seg_pto_mvto WHERE id_ctb_doc =$ce[id_ctb_doc] AND tipo_mov='COP'";
        $rs = $cmd->query($sql);
        $sumacrp = $rs->fetch();
        $valor_obl = $sumacrp['valorcop'];
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    try {
        $sql = "SELECT sum(valor) as valorpag FROM seg_pto_mvto WHERE id_ctb_cop =$ce[id_ctb_doc] AND tipo_mov='PAG'";
        $rs = $cmd->query($sql);
        $sumacop = $rs->fetch();
        $valor_pag = $sumacop['valorpag'];
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    $saldo_rp = $valor_obl - $valor_pag;

    // Obtengo el numero del contrato
    try {
        $sql = "SELECT
        `seg_contrato_compra`.`id_compra`
        , `seg_pto_documento`.`id_auto`
    FROM
        `seg_contrato_compra`
        INNER JOIN `seg_adquisiciones` 
            ON (`seg_contrato_compra`.`id_compra` = `seg_adquisiciones`.`id_adquisicion`)
        INNER JOIN `seg_pto_documento` 
            ON (`seg_adquisiciones`.`id_cdp` = `seg_pto_documento`.`id_auto`)
    WHERE (`seg_pto_documento`.`id_auto` =$ce[id_auto_dep]);";
        $rs = $cmd->query($sql);
        $num_contrato = $rs->fetch();
        $numeroc = $num_contrato['id_compra'];
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }

    if ((intval($permisos['editar'])) === 1) {
        $editar = '<a value="' . $id_doc . '" onclick="cargarListaDetallePago(' . $id_doc . ')" class="btn btn-outline-success btn-sm btn-circle shadow-gb editar" title="Causar"><span class="fas fa-plus-square fa-lg"></span></a>';
        $acciones = '<button  class="btn btn-outline-pry btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
        ...
        </button>
        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
        <a value="' . $id_doc . '" class="dropdown-item sombra carga" href="#">Historial</a>
        </div>';
    } else {
        $editar = null;
        $detalles = null;
    }
    if ($saldo_rp > 0) {

        $data[] = [

            'numero' =>  $lp['id_manu'],
            'fecha' => $fecha,
            'tercero' => $tercero,
            'valor' =>  '<div class="text-right">' . $valor_total . '</div>',
            'botones' => '<div class="text-center" style="position:relative">' . $editar . $borrar . $imprimir . $acciones .  '</div>',
        ];
    }
}
$cmd = null;
$datos = ['data' => $data];


echo json_encode($datos);
