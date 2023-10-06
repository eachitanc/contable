<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
$id_compra = isset($_POST['id']) ? $_POST['id'] : exit('Acción no pemitida');

include '../../../conexion.php';
function pesos($valor)
{
    return '$ ' . number_format($valor, 0, ',', '.');
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_adquisiciones`.`id_adquisicion`
                , `seg_adquisiciones`.`id_tipo_bn_sv`
                , `seg_adquisiciones`.`id_modalidad`
                , `seg_modalidad_contrata`.`modalidad`
                , `seg_adquisiciones`.`objeto`
                , `seg_adquisiciones`.`id_supervision`
                , `seg_terceros`.`id_tercero_api`
                , `seg_area_c`.`id_area`
                , `seg_area_c`.`area`
            FROM
                `seg_adquisiciones`
            INNER JOIN `seg_modalidad_contrata` 
                ON (`seg_adquisiciones`.`id_modalidad` = `seg_modalidad_contrata`.`id_modalidad`)
            INNER JOIN `seg_terceros`
                ON (`seg_adquisiciones`.`id_tercero` = `seg_terceros`.`id_tercero`)
            INNER JOIN `seg_area_c` 
                ON (`seg_adquisiciones`.`id_area` = `seg_area_c`.`id_area`)
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
                `seg_estudios_previos`.`forma_pago`
            FROM
                `seg_estudios_previos`
            INNER JOIN `seg_forma_pago_compras` 
                ON (`seg_estudios_previos`.`id_forma_pago` = `seg_forma_pago_compras`.`id_form_pago`)
            WHERE `id_compra` = '$id_compra'";
    $rs = $cmd->query($sql);
    $estudio_prev = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$iduser = $_SESSION['id_user'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT CONCAT_WS (' ', `nombre1` , `nombre2` , `apellido1` , `apellido2`) AS `nombre` FROM `seg_usuarios` WHERE (`id_usuario`  = '$iduser')";
    $rs = $cmd->query($sql);
    $usuario = $rs->fetch();
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
                , `seg_contrato_compra`.`val_contrato`
                , `seg_forma_pago_compras`.`descripcion`
                , `seg_contrato_compra`.`id_supervisor`
                , `id_secop`
                ,`num_contrato`
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
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_adquisiciones`.`id_adquisicion`
                , `seg_pto_documento`.`id_manu`
                , `seg_pto_documento`.`objeto`
                , `seg_pto_documento`.`fecha`
                , `seg_pto_mvto`.`valor`
            FROM
                `seg_pto_documento`
                INNER JOIN `seg_adquisiciones` 
                    ON (`seg_pto_documento`.`id_pto_doc` = `seg_adquisiciones`.`id_cdp`)
                INNER JOIN `seg_pto_mvto`
                ON (`seg_pto_mvto`.`id_pto_doc` = `seg_pto_documento`.`id_pto_doc`)
            WHERE `seg_adquisiciones`.`id_adquisicion` = '$id_compra' LIMIT 1";
    $rs = $cmd->query($sql);
    $cdp = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_pto_documento`.`id_manu`
                , `seg_pto_documento`.`fecha`
                , `seg_pto_documento`.`objeto`
                , `seg_pto_mvto`.`valor`
            FROM
                `seg_adquisiciones`
                INNER JOIN `seg_pto_documento` 
                    ON (`seg_adquisiciones`.`id_cdp` = `seg_pto_documento`.`id_auto`)
                INNER JOIN `seg_pto_mvto`
                ON (`seg_pto_mvto`.`id_pto_doc` = `seg_pto_documento`.`id_pto_doc`)
            WHERE `seg_adquisiciones`.`id_adquisicion` = '$id_compra' AND `seg_pto_documento`.`tipo_doc` ='CRP' LIMIT 1";
    $rs = $cmd->query($sql);
    $crp = $rs->fetch();
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
$url = $api . 'terceros/datos/res/datos/id/' . $compra['id_tercero_api'];
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
$tercer = json_decode($result, true);

$url = $api . 'terceros/datos/res/listar/supervision/' . $compra['id_supervision'];
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
$supervision = json_decode($result, true);

$contra = $contrato['id_contrato_compra'];

require_once '../../../vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

$letras = new NumberFormatter("es", NumberFormatter::SPELLOUT);
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha_h = $date->format('Y-m-d');
$genero = $tercer[0]['genero'] == 'F' ? 'a' : 'o';
$solicitante = $compra['area'];
$objeto = $compra['objeto'];
$vigencia = $_SESSION['vigencia'];
$supervisor = $supervisor_res[0]['nombre1'] . ' ' . $supervisor_res[0]['nombre2'] . ' ' . $supervisor_res[0]['apellido1'] . ' ' . $supervisor_res[0]['apellido2'];
$tercero = $tercer[0]['nombre1'] . ' ' . $tercer[0]['nombre2'] . ' ' . $tercer[0]['apellido1'] . ' ' . $tercer[0]['apellido2'];
$cedula_ter = $tercer[0]['cc_nit'];
$meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$fecha = explode('-', $fecha_h);
$hoy_min = mb_strtolower($fecha[2] . ' de ' . $meses[intval($fecha[1])] . ' de ' . $fecha[0]);
$fec_contrato = explode('-', $contrato['fec_ini']);
$fecha_contrato = mb_strtolower($fec_contrato[2] . ' de ' . $meses[intval($fec_contrato[1])] . ' de ' . $fec_contrato[0]);
$no_contrato = mb_strtoupper(str_pad($contrato['num_contrato'], 3, "0", STR_PAD_LEFT) . ' de ' . $fecha_contrato);
$contratomin = mb_strtolower($no_contrato);
$hoy_may = mb_strtoupper($hoy_min);
$forma_pago = explode('||', $estudio_prev['forma_pago']);
$pago = [];
foreach ($forma_pago as $fp) {
    $pago[] = ['pago' => $fp];
}
$fec_designa = explode('-', $supervision['fec_designacion']);
$fecha = mb_strtolower($fec_designa[2] . ' de ' . $meses[intval($fec_designa[1])] . ' de ' . $fec_designa[0]);
$fechaM = mb_strtoupper($fecha);
$fec_inicia = mb_strtoupper($letras->format($fec_contrato[2]) . ' (' . $fec_contrato[2] . ') de ' . $meses[intval($fec_contrato[1])] . ' de ' . $fec_contrato[0]);
$fec_contrato_f = explode('-', $contrato['fec_fin']);
$fec_fin = mb_strtoupper($letras->format($fec_contrato_f[2]) . ' (' . $fec_contrato_f[2] . ') de ' . $meses[intval($fec_contrato_f[1])] . ' de ' . $fec_contrato_f[0]);
$fcdp = explode('-', strtotime('Y-m-d', $cdp['fecha']));
$fcrp = explode('-', strtotime('Y-m-d', $crp['fecha']));
$fec_cdp = mb_strtoupper($fcdp[2] . ' de ' . $meses[intval($fcdp[1])] . ' de ' . $fcdp[0]);
$fec_crp = mb_strtoupper($fcrp[2] . ' de ' . $meses[intval($fcrp[1])] . ' de ' . $fcrp[0]);
$n_cdp = !empty($cdp['id_manu']) ? $cdp['id_manu'] : 'XXX' . '-' . $fec_cdp;
$n_rpres = !empty($crp['id_manu']) ? $crp['id_manu'] : 'XXX' . '-' . $fec_crp;
$valor = $contrato['val_contrato'];
$val_num = pesos($valor);
$val_letras = str_replace('-', '', mb_strtoupper($letras->format($valor, 2)));
$start = new DateTime($contrato['fec_ini']);
$end = new DateTime($contrato['fec_fin']);
$plazo = $start->diff($end);
$p_mes = $plazo->format('%m');
$p_dia = $plazo->format('%d');
if ($p_dia >= 28) {
    $p_mes++;
    $p_dia = 0;
}
if ($p_mes < 1) {
    $p_mes = '';
} else if ($p_mes == 1) {
    $p_mes = 'UN (01) MES';
} else {
    $p_mes = mb_strtoupper($letras->format($p_mes)) . ' (' . str_pad($p_mes, 2, '0', STR_PAD_LEFT) . ') MESES';
}
$y = ' Y ';
if ($p_dia < 1) {
    $y = '';
    $p_dia = '';
} else if ($p_dia == 1) {
    $p_dia = 'UN (01) DÍA';
} else {
    $p_dia = mb_strtoupper($letras->format($p_dia)) . ' (' . str_pad($p_dia, 2, '0', STR_PAD_LEFT) . ') DÍAS';
}
$proyecto = $usuario['nombre'];
$plazo = $p_mes == '' ? $p_dia : $p_mes . $y . $p_dia;
if ($compra['id_area'] == '5') {
    $docx = 'plantilla_acta_inicio_salud.docx';
} else {
    $docx = 'plantilla_acta_inicio.docx';
}

$plantilla = new TemplateProcessor($docx);

$plantilla->setValue('solicitante', $solicitante);
$plantilla->setValue('objeto', $objeto);
$plantilla->setValue('supervisor', $supervisor);
$plantilla->setValue('tercero', $tercero);
$plantilla->setValue('plazo', $plazo);
$plantilla->setValue('fecha', $fecha);
$plantilla->setValue('fec_inicia', $fec_inicia);
$plantilla->setValue('fec_fin', $fec_fin);
$plantilla->setValue('val_num', $val_num);
$plantilla->setValue('val_letras', $val_letras);
$plantilla->setValue('cedula', number_format($cedula_ter, 0, '', '.'));
$plantilla->cloneRowAndSetValues('pago', $pago);
$plantilla->setValue('no_contrato', $no_contrato);
$plantilla->setValue('contratomin', $contratomin);
$plantilla->setValue('hoy_may', $hoy_may);
$plantilla->setValue('n_cdp', $n_cdp);
$plantilla->setValue('n_rpres', $n_rpres);
$plantilla->setValue('genero', $genero);
$plantilla->setValue('hoy_min', $hoy_min);
$plantilla->setValue('proyecto', $proyecto);


$plantilla->saveAs('acta_inicio.docx');
header("Content-Disposition: attachment; Filename=acta_inicio.docx");
echo file_get_contents('acta_inicio.docx');
unlink('acta_inicio.docx');
