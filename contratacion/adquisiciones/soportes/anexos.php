<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
$id_compra = isset($_POST['id']) ? $_POST['id'] : exit('Acción no pemitida');
function pesos($valor)
{
    return '$ ' . number_format($valor, 0, ',', '.');
}
$vigencia = $_SESSION['vigencia'];
include '../../../conexion.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_adquisiciones`.`id_adquisicion`
                , `seg_adquisiciones`.`id_tipo_bn_sv`
                , `seg_adquisiciones`.`id_modalidad`
                , `seg_modalidad_contrata`.`modalidad`
                , `seg_adquisiciones`.`objeto`
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
                `seg_pto_mvto`.`id_pto_mvto` AS `id_cdp`
                , `seg_pto_documento`.`fecha` AS `fecha_cdp`
            FROM
                `seg_adquisiciones`
            INNER JOIN `seg_pto_mvto` 
                ON (`seg_adquisiciones`.`id_cdp` = `seg_pto_mvto`.`id_pto_mvto`)
            INNER JOIN `seg_pto_documento` 
                ON (`seg_pto_mvto`.`id_pto_doc` = `seg_pto_documento`.`id_pto_doc`);)
            WHERE `seg_adquisiciones`.`id_adquisicion` = '$id_compra'";
    $rs = $cmd->query($sql);
    $data_cdp = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$tipo_bn = $compra['id_tipo_bn_sv'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_escala_honorarios`.`id_pto_cargue`, `seg_pto_cargue`.`cod_pptal`, `seg_pto_cargue`.`nom_rubro`
            FROM
                `seg_escala_honorarios`
                INNER JOIN`seg_pto_cargue`
                ON (`seg_escala_honorarios`.`id_pto_cargue` = `seg_pto_cargue`.`cod_pptal`)
            WHERE `seg_escala_honorarios`.`id_tipo_b_s` = '$tipo_bn' AND `seg_escala_honorarios`.`vigencia` = '$vigencia'";
    $rs = $cmd->query($sql);
    $cod_cargue = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_estudios_previos`.`id_est_prev`
                , `seg_estudios_previos`.`id_compra`
                , `seg_estudios_previos`.`fec_ini_ejec`
                , `seg_estudios_previos`.`fec_fin_ejec`
                , `seg_estudios_previos`.`val_contrata`
                , `seg_estudios_previos`.`necesidad`
                , `seg_estudios_previos`.`act_especificas`
                , `seg_estudios_previos`.`prod_entrega`
                , `seg_estudios_previos`.`obligaciones`
                , `seg_estudios_previos`.`forma_pago`
                , `seg_estudios_previos`.`requisitos`
                , `seg_estudios_previos`.`describe_valor`
                , `seg_estudios_previos`.`num_ds`
                , `seg_forma_pago_compras`.`descripcion`
                , `seg_estudios_previos`.`id_supervisor`
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
$id_ter_sup = $estudio_prev['id_supervisor'];
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

$actividades = explode('||', $estudio_prev['act_especificas']);
$productos = explode('||', $estudio_prev['prod_entrega']);
$obligaciones = explode('||', $estudio_prev['obligaciones']);
$forma_pago = explode('||', $estudio_prev['forma_pago']);
$requisitos = explode('||', $estudio_prev['requisitos']);
$valores = explode('||', $estudio_prev['describe_valor']);
$actividad = [];
$actividad1 = [];
$necesidad = [];
$producto = [];
$obligacion = [];
$pago = [];
$pago_s = [];
$req_min = [];
$req_min1 = [];
$describ_val = [];
foreach ($actividades as $ac) {
    $actividad[] = ['actividad' => $ac];
}
foreach ($actividades as $ac) {
    $actividad1[] = ['actividad1' => $ac];
}
foreach ($productos as $pr) {
    $producto[] = ['producto' => $pr];
}
foreach ($obligaciones as $ob) {
    $obligacion[] = ['obligacion' => $ob];
}
foreach ($forma_pago as $fp) {
    $pago[] = ['pago' => $fp];
}
foreach ($forma_pago as $fp) {
    $pago_s[] = ['pago_s' => $fp];
}
foreach ($requisitos as $rm) {
    $req_min[] = ['req_min' => $rm];
}
foreach ($requisitos as $rm) {
    $req_min1[] = ['req_min1' => $rm];
}
foreach ($valores as $vl) {
    $describ_val[] = ['describ_val' => $vl];
}
$meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$fecI = explode('-', $estudio_prev['fec_ini_ejec']);
$fecF = explode('-', $estudio_prev['fec_fin_ejec']);
$fecha = mb_strtoupper($fecI[2] . ' de ' . $meses[intval($fecI[1])] . ' de ' . $fecI[0]);
$valor = $estudio_prev['val_contrata'];
$val_num = pesos($valor);
$objeto = mb_strtoupper($compra['objeto']);
$letras = new NumberFormatter("es", NumberFormatter::SPELLOUT);
$val_letras = str_replace('-', '', mb_strtoupper($letras->format($valor, 2)));
$start = new DateTime($estudio_prev['fec_ini_ejec']);
$end = new DateTime($estudio_prev['fec_fin_ejec']);
$plazo = $start->diff($end);
$p_mes = $plazo->format('%m');
$p_dia = $plazo->format('%d');
if ($p_dia >= 29) {
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
    $p_dia = 'UN DÍA';
} else {
    $p_dia = mb_strtoupper($letras->format($p_dia)) . ' (' . str_pad($p_dia, 2, '0', STR_PAD_LEFT) . ') DÍAS';
}
$plazo = $p_mes == '' ? $p_dia : $p_mes . $y . $p_dia;
if (intval($fecI[2]) == 1) {
    $expedicion = 'el primer (01) día del mes de ' . $meses[intval($fecI[1])] . ' de ' . $fecI[0];
} else {
    $expedicion = 'a los ' . $fecI[2] . ' días del mes de ' . $meses[intval($fecI[1])] . ' de ' . $fecI[0];
}
$rubro = !empty($cod_cargue) ? $cod_cargue['nom_rubro'] : 'XXX';
$cod_presupuesto = !empty($cod_cargue) ? $cod_cargue['id_pto_cargue'] : 'XXX';
$cpd = !empty($data_cdp) ? $data_cdp['id_cdp'] : 'XXX';
$fec_cdp = !empty($data_cdp) ? $data_cdp['fecha_cdp'] : 'XXX';
$tercero = $tercer[0]['nombre1'] . ' ' . $tercer[0]['nombre2'] . ' ' . $tercer[0]['apellido1'] . ' ' . $tercer[0]['apellido2'];
$cedula = $tercer[0]['cc_nit'];
$supervisor = $supervisor_res[0]['apellido1'] . ' ' . $supervisor_res[0]['apellido2'] . ' ' . $supervisor_res[0]['nombre1'] . ' ' . $supervisor_res[0]['nombre2'];
$supervisor = $id_ter_sup == '' ? 'XXXXX' : $supervisor;
$solicitante = $compra['area']; //area solicitante
$dir_tercero = $tercer[0]['direccion'] ? 'XXXXX' : $tercer[0]['direccion'];
$tel_tercero = $tercer[0]['telefono'] ? 'XXXXX' : $tercer[0]['telefono'];
$id_ciudad = $tercer[0]['municipio'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_departamento`.`nombre_dpto`, `seg_municipios`.`nom_municipio`
            FROM
                `seg_municipios`
                INNER JOIN `seg_departamento` 
                    ON (`seg_municipios`.`id_departamento` = `seg_departamento`.`id_dpto`)
            WHERE `seg_municipios`.`id_municipio` = '$id_ciudad'";
    $rs = $cmd->query($sql);
    $reside = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$mun_tercero = ucfirst(strtolower($reside['nom_municipio']));
$dpto_tercero = ucfirst(strtolower($reside['nombre_dpto']));
$numds = str_pad($estudio_prev['num_ds'], 3, "0", STR_PAD_LEFT) . '-' . $_SESSION['vigencia'];
require_once '../../../vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

if ($compra['id_area'] == '5') {
    $docx = "plantilla_anexos_salud.docx";
} else {
    $docx = "plantilla_anexos.docx";
}

$plantilla = new TemplateProcessor($docx);
if ($compra['id_area'] == '5') {
    $plantilla->cloneRowAndSetValues('req_min', $req_min);
    $plantilla->cloneRowAndSetValues('req_min1', $req_min1);
    $plantilla->cloneRowAndSetValues('describ_val', $describ_val);
}
$plantilla->setValue('fecha', $fecha);
$plantilla->setValue('val_num', $val_num);
$plantilla->setValue('objeto', $objeto);
$plantilla->setValue('expedicion', $expedicion);
$plantilla->setValue('val_letras', $val_letras);
$plantilla->cloneRowAndSetValues('actividad', $actividad);
$markerToCheck = 'actividad1';
$placeholders = $plantilla->getVariables();
$marker_exists = in_array($markerToCheck, $placeholders);
if ($marker_exists) {
    $plantilla->cloneRowAndSetValues('actividad1', $actividad1);
}
$plantilla->cloneRowAndSetValues('producto', $producto);
$plantilla->cloneRowAndSetValues('obligacion', $obligacion);
$plantilla->cloneRowAndSetValues('pago', $pago);
$plantilla->cloneRowAndSetValues('pago_s', $pago_s);
$plantilla->setValue('plazo', $plazo);
$plantilla->setValue('rubro', $rubro);
$plantilla->setValue('cod_presupuesto', $cod_presupuesto);
$plantilla->setValue('vigencia', $vigencia);
$plantilla->setValue('cpd', $cpd);
$plantilla->setValue('fec_cdp', $fec_cdp);
$plantilla->setValue('tercero', $tercero);
$plantilla->setValue('cedula', number_format($cedula, 0, '', '.'));
$plantilla->setValue('supervisor', $supervisor);
$plantilla->setValue('solicitante', $solicitante);
$plantilla->setValue('dir_tercero', $dir_tercero);
$plantilla->setValue('tel_tercero', $tel_tercero);
$plantilla->setValue('mun_tercero', $mun_tercero);
$plantilla->setValue('dpto_tercero', $dpto_tercero);
$plantilla->setValue('numds', $numds);


$plantilla->saveAs('anexos.docx');
header("Content-Disposition: attachment; Filename=anexos.docx");
echo file_get_contents('anexos.docx');
unlink('anexos.docx');
