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
                `seg_adquisiciones`.`id_adquisicion`
                , `seg_adquisiciones`.`id_modalidad`
                , `seg_modalidad_contrata`.`modalidad`
                , `seg_adquisiciones`.`objeto`
                , `seg_adquisiciones`.`id_supervision`
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
$id_ter_sup = $contrato['id_supervisor'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `no_doc` FROM `seg_terceros` WHERE `id_tercero_api` = '$id_ter_sup'";
    $rs = $cmd->query($sql);
    $terceros_sup = $rs->fetch();
    //API URL
    $url = $api . 'terceros/datos/res/lista/' . $terceros_sup['no_doc'];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    $supervisor_res = json_decode($result, true);

    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$url = $api . 'terceros/datos/res/listar/supervision/' . $compra['id_supervision'];
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
$supervision = json_decode($result, true);

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
require_once '../../../vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

$objeto = $compra['objeto'];
$vigencia = $_SESSION['vigencia'];
$memorando = $supervision['memorando'];
$supervisor = $supervisor_res[0]['apellido1'] . ' ' . $supervisor_res[0]['apellido2'] . ' ' . $supervisor_res[0]['nombre1'] . ' ' . $supervisor_res[0]['nombre2'];
$meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$fec_designa = explode('-', $supervision['fec_designacion']);
$fecha = mb_strtolower($fec_designa[2] . ' de ' . $meses[$fec_designa[1]] . ' de ' . $fec_designa[0]);
$fechaM = mb_strtoupper($fecha);


$plantilla = new TemplateProcessor('plantilla_designa_supervisor.docx');

$plantilla->setValue('objeto', $objeto);
$plantilla->setValue('vigencia', $vigencia);
$plantilla->setValue('memorando', $memorando);
$plantilla->setValue('supervisor', $supervisor);
$plantilla->setValue('fecha', $fecha);
$plantilla->setValue('fechaM', $fechaM);


$plantilla->saveAs('designacion_supervisor.docx');
header("Content-Disposition: attachment; Filename=designacion_supervisor.docx");
echo file_get_contents('designacion_supervisor.docx');
unlink('designacion_supervisor.docx');
