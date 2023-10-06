<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_entrada = isset($_POST['id_entrada']) ? $_POST['id_entrada'] : exit('Acción no permitida');
$id_ter_dev = $_POST['idTerDv'];

function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_detalle_entrada_almacen`.`id_entrada`
                , `seg_detalle_entrada_almacen`.`id_prod`
                , `seg_bien_servicio`.`bien_servicio`
                , `seg_detalle_entrada_almacen`.`id_tercero_api`
                , `seg_detalle_entrada_almacen`.`id_tipo_entrada`
                , `seg_tipo_entrada`.`descripcion`
                , `seg_detalle_entrada_almacen`.`remision`
                , `seg_detalle_entrada_almacen`.`factura`
                , `seg_detalle_entrada_almacen`.`cant_ingresa`
                , `seg_detalle_entrada_almacen`.`valu_ingresa`
                , `seg_detalle_entrada_almacen`.`val_prom`
                , `seg_detalle_entrada_almacen`.`lote`
                , `seg_detalle_entrada_almacen`.`fecha_vence`
            FROM
                `seg_detalle_entrada_almacen`
                INNER JOIN `seg_bien_servicio` 
                    ON (`seg_detalle_entrada_almacen`.`id_prod` = `seg_bien_servicio`.`id_b_s`)
                INNER JOIN `seg_tipo_entrada` 
                    ON (`seg_detalle_entrada_almacen`.`id_tipo_entrada` = `seg_tipo_entrada`.`id_entrada`)
            WHERE `seg_detalle_entrada_almacen`.`id_entrada` = '$id_entrada'";
    $rs = $cmd->query($sql);
    $lote = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$id_ter = $lote['id_tercero_api'];
//API URL
$url = $api . 'terceros/datos/res/datos/id/' . $id_ter;
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res_api = curl_exec($ch);
curl_close($ch);
$dat_ter = json_decode($res_api, true);
$datercero = $dat_ter[0];
$data = '';
$data = '
<tr>
    <td>' . $lote['id_entrada'] . '<input id="idEntrada_' . $id_entrada . '" type="hidden">' . '</td>
    <td>' . $lote['bien_servicio'] . '<input name="idProd_' . $id_entrada . '" type="hidden" value="' . $lote['id_prod'] . '">' . '</td>
    <td>' . $lote['descripcion'] . '</td>
    <td>' . $datercero['apellido1'] . ' ' . $datercero['apellido2'] . ' ' . $datercero['nombre2'] . ' ' . $datercero['nombre1'] . ' ' . $datercero['razon_social'] . '</td>
    <td>' . $lote['remision'] . '</td>
    <td>' . $lote['cant_ingresa'] . '</td>
    <td class="text-right">' . pesos($lote['valu_ingresa']) . '</td>
    <td>' . $lote['lote'] . '</td>
    <td>' . $lote['fecha_vence'] . '</td>
    <td class="text-center"><input type="number" class="form-control form-control-sm altura" name="cantDev[' . $id_entrada . ']" min="1" max=' . $lote['cant_ingresa'] . '></td>
</tr>';
if ($id_ter_dev == 0) {
    $data .= '<input id="idTerceroDev" type="hidden" name="idTerceroDev" value="' . $datercero['id_tercero'] . '">';
    echo $data;
} else if ($id_ter_dev == $datercero['id_tercero']) {
    echo $data;
} else {
    echo 0;
}
