<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
$lote = isset($_POST['desc_lote']) ? $_POST['desc_lote'] : '0';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_entrada_almacen`.`id_entrada`
                , `seg_entrada_almacen`.`id_tercero_api`
                , `seg_detalle_entrada_almacen`.`id_prod`
                , `seg_bien_servicio`.`id_b_s`
                , `seg_bien_servicio`.`bien_servicio`
                , `seg_sedes_empresa`.`nombre` AS `sede`
                , `seg_bodega_almacen`.`nombre` AS `bodega`
                , `seg_tipo_entrada`.`descripcion`
                , `seg_entrada_almacen`.`acta_remision` AS `remision`
                , `seg_entrada_almacen`.`no_factura` AS `factura`
                , `seg_detalle_entrada_almacen`.`cant_ingresa`
                , `seg_detalle_entrada_almacen`.`valu_ingresa`
                , `seg_detalle_entrada_almacen`.`val_prom`
                , `seg_detalle_entrada_almacen`.`lote`
                , `seg_detalle_entrada_almacen`.`fecha_vence`
                , `seg_detalle_entrada_almacen`.`existencia`
                , `seg_detalle_entrada_almacen`.`fec_reg`
                
            FROM
            `seg_detalle_entrada_almacen`
                INNER JOIN `seg_entrada_almacen` 
                    ON (`seg_detalle_entrada_almacen`.`id_entra` = `seg_entrada_almacen`.`id_entrada`)
                INNER JOIN `seg_bien_servicio` 
                    ON (`seg_detalle_entrada_almacen`.`id_prod` = `seg_bien_servicio`.`id_b_s`)
                INNER JOIN `seg_bodega_almacen` 
                    ON (`seg_detalle_entrada_almacen`.`id_bodega` = `seg_bodega_almacen`.`id_bodega`)
                INNER JOIN `seg_sedes_empresa` 
                    ON (`seg_detalle_entrada_almacen`.`id_sede` = `seg_sedes_empresa`.`id_sede`) AND (`seg_bodega_almacen`.`id_sede` = `seg_sedes_empresa`.`id_sede`)
                INNER JOIN `seg_tipo_entrada` 
                    ON (`seg_entrada_almacen`.`id_tipo_entrada` = `seg_tipo_entrada`.`id_entrada`)
            WHERE `seg_detalle_entrada_almacen`.`lote` = '$lote' ORDER BY  `seg_detalle_entrada_almacen`.`fec_reg` ASC";
    $rs = $cmd->query($sql);
    $entradas_art = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_salidas_almacen`.`id_producto`
                , `seg_sedes_empresa`.`nombre`  AS `sede`
                , `seg_bodega_almacen`.`nombre` AS `bodega`
                , `seg_salida_dpdvo`.`id_tercero_api`
                , `seg_tipo_salidas`.`descripcion`
                , `seg_salida_dpdvo`.`acta_remision`
                , `seg_salidas_almacen`.`cantidad`
                , `seg_detalle_entrada_almacen`.`valu_ingresa`
                , `seg_detalle_entrada_almacen`.`val_prom`
                , `seg_detalle_entrada_almacen`.`lote`
                , `seg_detalle_entrada_almacen`.`fecha_vence`
                , `seg_salidas_almacen`.`observacion`
                , `seg_salidas_almacen`.`existencia`
                , `seg_salidas_almacen`.`fec_reg`
            FROM
                `seg_salidas_almacen`
                INNER JOIN `seg_salida_dpdvo` 
                    ON (`seg_salidas_almacen`.`id_devolucion` = `seg_salida_dpdvo`.`id_devolucion`)
                INNER JOIN `seg_detalle_entrada_almacen` 
                    ON (`seg_salidas_almacen`.`id_entrada` = `seg_detalle_entrada_almacen`.`id_entrada`)
                INNER JOIN `seg_bien_servicio` 
                    ON (`seg_salidas_almacen`.`id_producto` = `seg_bien_servicio`.`id_b_s`) AND (`seg_detalle_entrada_almacen`.`id_prod` = `seg_bien_servicio`.`id_b_s`)
                INNER JOIN `seg_sedes_empresa` 
                    ON (`seg_detalle_entrada_almacen`.`id_sede` = `seg_sedes_empresa`.`id_sede`)
                INNER JOIN `seg_bodega_almacen` 
                    ON (`seg_bodega_almacen`.`id_sede` = `seg_sedes_empresa`.`id_sede`) AND (`seg_detalle_entrada_almacen`.`id_bodega` = `seg_bodega_almacen`.`id_bodega`)
                INNER JOIN `seg_tipo_salidas` 
                    ON (`seg_salida_dpdvo`.`id_tipo_salida` = `seg_tipo_salidas`.`id_salida`)
            WHERE `seg_detalle_entrada_almacen`.`lote` = '$lote' ORDER BY  `seg_salidas_almacen`.`fec_reg` ASC";
    $rs = $cmd->query($sql);
    $salidas_art = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_sede`, `nombre` FROM `seg_sedes_empresa`";
    $rs = $cmd->query($sql);
    $sedes = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_bodega`,`id_sede`, `nombre` FROM `seg_bodega_almacen`";
    $rs = $cmd->query($sql);
    $bodegas = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_tercero_api` FROM `seg_terceros`";
    $rs = $cmd->query($sql);
    $terceros = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$idst = '0';
foreach ($terceros as $t) {
    $idst .= ',' . $t['id_tercero_api'];
}
//API URL
$url = $api . 'terceros/datos/res/datos/id/' . $idst;
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res_api = curl_exec($ch);
curl_close($ch);
$dat_ter = json_decode($res_api, true);
$datos = [];
$data = [];
$tot_entra = $tot_sale = 0;
if (!empty($entradas_art)) {
    foreach ($entradas_art as $ea) {
        $key = array_search($ea['id_tercero_api'], array_column($dat_ter, 'id_tercero'));
        if (false !== $key) {
            $nom_tercer = $dat_ter[$key]['apellido1'] . ' ' . $dat_ter[$key]['apellido2'] . ' ' . $dat_ter[$key]['nombre1'] . ' ' . $dat_ter[$key]['nombre2'] . ' ' . $dat_ter[$key]['razon_social'];
        } else {
            $nom_tercer = '';
        }
        if (end($entradas_art) == $ea && empty($salidas_art)) {
            $tot = $tot_entra + $ea['cant_ingresa'];
            $celda = $ea['cant_ingresa'] . '<input type="hidden" id="tot_existe_lote" value="' . $tot . '">';
        } else {
            $celda = $ea['cant_ingresa'];
        }
        $data[] = [
            'fec_reg' => $ea['fec_reg'],
            'sede' => $ea['sede'],
            'bodega' => $ea['bodega'],
            'remision' => $ea['remision'],
            'tercero' => $nom_tercer,
            'entrada' => $ea['descripcion'],
            'salida' => isset($ea['salida']) ? $ea['salida'] : '',
            'cantidad' => $celda,
            'lote' => $ea['lote'],
        ];
        $tot_entra += $ea['cant_ingresa'];
    }
}
if (!empty($salidas_art)) {
    foreach ($salidas_art as $sa) {
        $key = array_search($sa['id_tercero_api'], array_column($dat_ter, 'id_tercero'));
        if (false !== $key) {
            $nom_tercer = $dat_ter[$key]['apellido1'] . ' ' . $dat_ter[$key]['apellido2'] . ' ' . $dat_ter[$key]['nombre1'] . ' ' . $dat_ter[$key]['nombre2'] . ' ' . $dat_ter[$key]['razon_social'];
        } else {
            $nom_tercer = '';
        }
        if (end($salidas_art) == $sa) {
            $tot = $tot_entra - $tot_sale - $sa['cantidad'];
            $celda = $sa['cantidad'] . '<input type="hidden" id="tot_existe_lote" value="' . $tot . '">';
        } else {
            $celda = $sa['cantidad'];
        }
        $data[] = [
            'fec_reg' => $sa['fec_reg'],
            'sede' => $sa['sede'],
            'bodega' => $sa['bodega'],
            'remision' => $sa['acta_remision'],
            'tercero' => $nom_tercer,
            'entrada' => isset($sa['entrada']) ? $sa['entrada'] : '',
            'salida' => $sa['descripcion'],
            'cantidad' => $celda,
            'lote' => $sa['lote'],
        ];
        $tot_sale += $sa['cantidad'];
    }
}
if (!empty($entradas_art) || !empty($salidas_art)) {
    $total = $tot_entra - $tot_sale;
    $data[] = [
        'fec_reg' => '0000-00-00 00:00:00',
        'sede' => '',
        'bodega' => '',
        'remision' => '',
        'tercero' => '',
        'entrada' => '<div class="text-center"><strong>EXISTENCIA: </strong></div>',
        'salida' => '',
        'cantidad' => '<div class="text-center"><b>' . $total . '<b></div>',
        'lote' => '',
    ];
}
$datos = [
    'data' => $data,
];
echo json_encode($datos);
