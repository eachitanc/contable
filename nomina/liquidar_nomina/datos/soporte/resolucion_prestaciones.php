<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
$datos = isset($_POST['datos']) ? explode('|', $_POST['datos']) : exit('Acción no permitida');
$id_empdo = $datos[0];
$corte = $datos[1];
include '../../../../conexion.php';
include '../../../../permisos.php';
require_once '../../../../vendor/autoload.php';

function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_liq`, `id_empleado`, `corte`, `no_resolucion`, `fec_inicio`, `fec_fin`, `sal_base`
            FROM
                `seg_liq_empleado`
            WHERE `id_empleado` = '$id_empdo' AND `corte` = '$corte' limit 1";
    $rs = $cmd->query($sql);
    $obj = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `seg_empleado`.`id_empleado`
                , `seg_empleado`.`tipo_doc`
                , `seg_empleado`.`no_documento`
                , `seg_empleado`.`genero`
                , `seg_empleado`.`nombre1`
                , `seg_empleado`.`nombre2`
                , `seg_empleado`.`apellido1`
                , `seg_empleado`.`apellido2`
                , `seg_empleado`.`fech_inicio`
                , `seg_empleado`.`fec_retiro`
                , `seg_empleado`.`cargo`
                , `seg_empleado`.`tipo_cta`
                , `seg_tipo_cta`.`tipo_cta`
                , `seg_bancos`.`nom_banco`
                , `seg_empleado`.`cuenta_bancaria`
                , `seg_liq_empleado`.`corte`
                , `seg_liq_empleado`.`no_resolucion`
                , `seg_liq_empleado`.`fec_inicio`
                , `seg_liq_empleado`.`fec_fin`
                , `seg_liq_empleado`.`sal_base`
            FROM
                `seg_liq_empleado`
                INNER JOIN `seg_empleado` 
                    ON (`seg_liq_empleado`.`id_empleado` = `seg_empleado`.`id_empleado`)
                INNER JOIN `seg_bancos` 
                    ON (`seg_empleado`.`id_banco` = `seg_bancos`.`id_banco`)
                INNER JOIN `seg_tipo_cta` 
                    ON (`seg_empleado`.`tipo_cta` = `seg_tipo_cta`.`id_tipo_cta`)
            WHERE `seg_empleado`.`id_empleado`= '$id_empdo' AND `seg_liq_empleado`.`corte` ='$corte' limit 1";
    $rs = $cmd->query($sql);
    $liquidacion = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_empleado`, `cant_dias`, `val_cesantias`, `val_icesantias`, `porcentaje_interes`, `corte`
            FROM
                `seg_liq_cesantias`
            WHERE `id_empleado` = '$id_empdo' AND `corte` = '$corte' limit 1";
    $rs = $cmd->query($sql);
    $cesantias = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_empleado`, `cant_dias`, `val_liq_ps`, `corte`
            FROM
                `seg_liq_prima`
            WHERE `id_empleado` = '$id_empdo' AND `corte` = '$corte' limit 1";
    $rs = $cmd->query($sql);
    $prima_sv = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_empleado`, `cant_dias`, `val_liq_pv`, `corte`
            FROM
                `seg_liq_prima_nav`
            WHERE `id_empleado` = '$id_empdo' AND `corte` = '$corte' limit 1";
    $rs = $cmd->query($sql);
    $prima_nav = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `seg_vacaciones`.`id_empleado`
                , `seg_vacaciones`.`corte`
                , `seg_liq_vac`.`dias_liqs`
                , `seg_liq_vac`.`val_liq`
                , `seg_liq_vac`.`val_bsp`
                , `seg_liq_vac`.`val_prima_vac`
                , `seg_liq_vac`.`val_bon_recrea`
            FROM
                `seg_liq_vac`
                INNER JOIN `seg_vacaciones` 
                    ON (`seg_liq_vac`.`id_vac` = `seg_vacaciones`.`id_vac`)
            WHERE `seg_vacaciones`.`id_empleado` = '$id_empdo' AND `seg_vacaciones`.`corte` = '$corte' LIMIT 1 ";
    $rs = $cmd->query($sql);
    $vacaciones = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (!empty($cesantias)) {
    $val_cesantias = $cesantias['val_cesantias'];
    $val_icesantias = $cesantias['val_icesantias'];
    $dias_cesantias = $cesantias['cant_dias'];
} else {
    $val_cesantias = 0;
    $val_icesantias = 0;
    $dias_cesantias = 0;
}
if (!empty($prima_sv)) {
    $val_prima_sv = $prima_sv['val_liq_ps'];
    $dias_prima_sv = $prima_sv['cant_dias'];
} else {
    $val_prima_sv = 0;
    $dias_prima_sv = 0;
}
if (!empty($prima_nav)) {
    $val_prima_nav = $prima_nav['val_liq_pv'];
    $dias_prima_nav = $prima_nav['cant_dias'];
}
if (!empty($vacaciones)) {
    $val_vacaciones = $vacaciones['val_liq'];
    $val_bsp = $vacaciones['val_bsp'];
    $val_prima_vac = $vacaciones['val_prima_vac'];
    $val_bon_recrea = $vacaciones['val_bon_recrea'];
    $dias_vacaciones = $vacaciones['dias_liqs'];
} else {
    $val_vacaciones = 0;
    $val_bsp = 0;
    $val_prima_vac = 0;
    $val_bon_recrea = 0;
    $dias_vacaciones = 0;
}
$total = $val_cesantias + $val_icesantias + $val_prima_sv + $val_prima_nav + $val_vacaciones + $val_bsp + $val_prima_vac + $val_bon_recrea;
$deducido = 0;
$neto = $total - $deducido;
$meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$no_resolucion = str_pad($liquidacion['no_resolucion'], 5, '0', STR_PAD_LEFT);
$identificacion = $liquidacion['genero'] == 'M' ? 'identificado' : 'identificada';
$no_doc = $liquidacion['no_documento'];
$nombre = mb_strtoupper($liquidacion['nombre1'] . ' ' . $liquidacion['nombre2'] . ' ' . $liquidacion['apellido1'] . ' ' . $liquidacion['apellido2']);
$inicia = explode('-', $liquidacion['fech_inicio']);
$termina = explode('-', $liquidacion['fec_retiro']);
$fec_inicia = $inicia[2] . ' de ' . $meses[intval($inicia[1])] . ' de ' . $inicia[0];
$fec_termina = $termina[2] . ' de ' . $meses[intval($termina[1])] . ' de ' . $termina[0];
$sal_basico = pesos($liquidacion['sal_base']);
$numtolet = new NumberFormatter("es", NumberFormatter::SPELLOUT);
$val_letras = mb_strtoupper($numtolet->format($liquidacion['sal_base'], 2));
$val_num = $sal_basico;
$hoy = explode('-', date('Y-m-d'));
$mexp = intval($hoy[1]);
if ($hoy[2] == '01') {
    $expedicion = 'el 01 día del mes de ' . $meses[$mexp] . ' de ' . $hoy[0];
} else {
    $expedicion = 'a los ' . $hoy[2] . ' días del mes de ' . $meses[$mexp] . ' de ' . $hoy[0];
}
$fondo_cesantias = 'Porvenir';
$anio = $_SESSION['vigencia'];
$sede = 'Ipiales';

use PhpOffice\PhpWord\TemplateProcessor;

$plantilla = new TemplateProcessor('formato_resolucion_prestaciones.docx');
$plantilla->setValue('no_resolucion', $no_resolucion);
$plantilla->setValue('identificacion', $identificacion);
$plantilla->setValue('empleado', $nombre);
$plantilla->setValue('no_doc', $no_doc);
$plantilla->setValue('fec_inicia', $fec_inicia);
$plantilla->setValue('fec_termina', $fec_termina);
$plantilla->setValue('sal_basico', $sal_basico);
$plantilla->setValue('val_letras', $val_letras);
$plantilla->setValue('val_num', $val_num);
$plantilla->setValue('fondo_cesantias', $fondo_cesantias);
$plantilla->setValue('anio', $anio);
$plantilla->setValue('sede', $sede);
$plantilla->setValue('val_c', pesos($val_cesantias));
$plantilla->setValue('val_ic', pesos($val_icesantias));
$plantilla->setValue('day_c', $dias_cesantias);
$plantilla->setValue('val_ps', pesos($val_prima_sv));
$plantilla->setValue('day_ps', $dias_prima_sv);
$plantilla->setValue('val_pn', pesos($val_prima_nav));
$plantilla->setValue('day_pn', $dias_prima_nav);
$plantilla->setValue('val_v', pesos($val_vacaciones));
$plantilla->setValue('val_bsp', pesos($val_bsp));
$plantilla->setValue('val_pv', pesos($val_prima_vac));
$plantilla->setValue('val_br', pesos($val_bon_recrea));
$plantilla->setValue('day_v', $dias_vacaciones);
$plantilla->setValue('total', pesos($total));
$plantilla->setValue('deducido', pesos($deducido));
$plantilla->setValue('neto', pesos($neto));
$plantilla->setValue('expedicion', $expedicion);

$archivo = 'resolucion_' . $no_resolucion . '.docx';
$filepdf = 'resolucion_' . $no_resolucion . '.pdf';
$plantilla->saveAs($archivo);
header("Content-Disposition: attachment; Filename=" . $archivo);
echo file_get_contents($archivo);
/*
$tempLibreOfficeProfile = sys_get_temp_dir() . "\\LibreOfficeProfile" . rand(100000, 999999);
$convertir = '"C:\Program Files\LibreOffice\program\soffice.exe" "-env:UserInstallation=file:///' . str_replace("\\", "/", $tempLibreOfficeProfile) . '" --headless --convert-to pdf "' . $archivo . '" --outdir "' . str_replace("\\", "/", dirname($filepdf)) . '"';
exec($convertir);
header("Content-Disposition: attachment; Filename=" . $filepdf);
echo file_get_contents($filepdf);
unlink($filepdf);
*/
unlink($archivo);
