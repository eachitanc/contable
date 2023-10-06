<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
$id_compra = isset($_POST['id']) ? $_POST['id'] : exit('Acción no pemitida');

include '../../../conexion.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_empresas`.`nit`
                , `seg_empresas`.`dig_ver`
                , `seg_empresas`.`nombre`
                , `seg_municipios`.`nom_municipio`
            FROM
            `seg_empresas`
            INNER JOIN `seg_municipios` 
                ON (`seg_empresas`.`id_ciudad` = `seg_municipios`.`id_municipio`) LIMIT 1";
    $rs = $cmd->query($sql);
    $compania = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_adquisiciones`.`id_adquisicion`
                , `seg_adquisiciones`.`id_modalidad`
                , `seg_modalidad_contrata`.`modalidad`
                , `seg_adquisiciones`.`objeto`
            FROM
                `seg_adquisiciones`
            INNER JOIN `seg_modalidad_contrata` 
                ON (`seg_adquisiciones`.`id_modalidad` = `seg_modalidad_contrata`.`id_modalidad`)
            WHERE `id_adquisicion` = '$id_compra' LIMIT 1";
    $rs = $cmd->query($sql);
    $compra = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_contrato_compra`.`id_contrato_compra`
                , `seg_contrato_compra`.`id_compra`
                , `seg_contrato_compra`.`fec_ini`
                , `seg_contrato_compra`.`fec_fin`
                , `seg_forma_pago_compras`.`descripcion`
                , `seg_contrato_compra`.`id_supervisor`
            FROM
                `seg_contrato_compra`
            INNER JOIN `seg_forma_pago_compras` 
                ON (`seg_contrato_compra`.`id_forma_pago` = `seg_forma_pago_compras`.`id_form_pago`)
            WHERE `id_compra` = '$id_compra'";
    $rs = $cmd->query($sql);
    $contrato = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$contra = $contrato['id_contrato_compra'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_garantias_contrato_compra`.`id_contrato_compra`
                ,`seg_garantias_contrato_compra`.`id_poliza`
                , `seg_polizas`.`descripcion`
                , `seg_polizas`.`porcentaje`
            FROM
                `seg_garantias_contrato_compra`
            INNER JOIN `seg_polizas` 
                ON (`seg_garantias_contrato_compra`.`id_poliza` = `seg_polizas`.`id_poliza`)
            WHERE `seg_garantias_contrato_compra`.`id_contrato_compra` = '$contra'";
    $rs = $cmd->query($sql);
    $garantias = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$polizas = '';
$num = 1;
foreach ($garantias as $g) {
    $polizas .=  $num . '. ' . ucfirst(strtolower($g['descripcion']) . ' por el ' . $g['porcentaje'] . '%. ');
    $num++;
}
$id_ter = $contrato['id_supervisor'];
//API URL
$url = $api . 'terceros/datos/res/datos/id/' . $id_ter;
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res_api = curl_exec($ch);
curl_close($ch);
$dat_ter = json_decode($res_api, true);
$supervisa = $dat_ter[0]['apellido1'] . ' ' . $dat_ter[0]['apellido2'] . ' ' . $dat_ter[0]['nombre2'] . ' ' . $dat_ter[0]['nombre1'] . ' ' . $dat_ter[0]['razon_social'];
require_once '../../../vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

$empresa = $compania['nombre'];
$municipio = $compania['nom_municipio'];
$objeto = $compra['objeto'];
$modalidad_contratacion = $compra['modalidad'];
$meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$fec_ini = explode('-', $contrato['fec_ini']);
$fec_fin = explode('-', $contrato['fec_fin']);
$anio_ini = $fec_ini[0];
$mes_ini = intval($fec_ini[1]);
$dia_ini = $fec_ini[2];
$anio_fin = $fec_fin[0];
$mes_fin = intval($fec_fin[1]);
$dia_fin = $fec_fin[2];
$val_letras = new NumberFormatter("es", NumberFormatter::SPELLOUT);
$dia_ini_l = $val_letras->format($dia_ini, 2);
$dia_fin_l = $val_letras->format($dia_fin, 2);
$mes_ini_l = $meses[$mes_ini];
$mes_fin_l = $meses[$mes_fin];
$forma_pago = $contrato['descripcion'];
$supervisor = $supervisa;
$fec_inicio = $dia_ini_l . ' (' . $dia_ini . ') de ' . $mes_ini_l . ' de ' . $anio_ini;
$fec_final = $dia_fin_l . ' (' . $dia_fin . ') de ' . $mes_fin_l . ' de ' . $anio_fin;
//echo $fec_ini_contrato . ' hasta ' . $fec_fin_contrato; 
$plantilla = new TemplateProcessor('plantilla_pres_servicios.docx');

$plantilla->setValue('id_contrato', $contra);
$plantilla->setValue('fec_inicio', $fec_inicio);
$plantilla->setValue('fec_final', $fec_final);


$plantilla->saveAs('plantilla_contrato_servicios.docx');
header("Content-Disposition: attachment; Filename=contrato_servicios.docx");
echo file_get_contents('plantilla_contrato_servicios.docx');
unlink('plantilla_contrato_servicios.docx');
