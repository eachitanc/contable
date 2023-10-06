<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
function pesos($valor)
{
    return '$' . number_format($valor, 0, ",", ".");
}
function pesos2($valor)
{
    return number_format($valor, 2, ",", ".");
}

include '../../../conexion.php';
include '../../../permisos.php';
$key = array_search('1', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
$empleado = isset($_POST['noDocTercero']) ? $_POST['noDocTercero'] : exit('Acción no permitida');
$vigencia = $_SESSION['vigencia'];
$fecIni = $_POST['fecInicia'] == '' ? $vigencia . '-01-01' : $_POST['fecInicia'];
$fecFin = $_POST['fecFin'] == '' ? $vigencia . '-12-31' : $_POST['fecFin'];
$res = [];
$res['status'] = '0';
$id_user = $_SESSION['id_user'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `seg_empleado`.`id_empleado`
                , `seg_empleado`.`tipo_doc`
                , `seg_empleado`.`no_documento`
                , `seg_empleado`.`apellido1`
                , `seg_empleado`.`apellido2`
                , `seg_empleado`.`nombre1`
                , `seg_empleado`.`nombre2`
                , `seg_empleado`.`representacion`
                , `seg_empleado`.`fech_inicio`
                , `seg_tipos_documento`.`codigo_ne`
                , `seg_municipios`.`codigo_municipio`
                , `seg_cargo_empleado`.`descripcion_carg`
                , `seg_cargo_empleado`.`codigo`
                , `seg_tipo_contrato`.`descripcion` as `nombramiento`
                ,  `seg_terceros`.`id_tercero_api`
            FROM
                `seg_empleado`
                INNER JOIN `seg_tipos_documento` 
                    ON (`seg_empleado`.`tipo_doc` = `seg_tipos_documento`.`id_tipodoc`)
                INNER JOIN `seg_cargo_empleado` 
                    ON (`seg_empleado`.`cargo` = `seg_cargo_empleado`.`id_cargo`)
                LEFT JOIN `seg_municipios` 
                    ON (`seg_empleado`.`city_exp` = `seg_municipios`.`id_municipio`)
                INNER JOIN `seg_tipo_contrato` 
                    ON (`seg_empleado`.`tipo_contrato` = `seg_tipo_contrato`.`id_tip_contrato`)
                LEFT JOIN `seg_terceros` 
                    ON (`seg_terceros`.`no_doc` = `seg_empleado`.`no_documento`)
            WHERE `seg_empleado`.`no_documento` IN ($empleado)";
    $rs = $cmd->query($sql);
    $list_empdo = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$id_contratista = $list_empdo[0]['id_tercero_api'];
if (empty($list_empdo)) {
    $res['msg'] = 'Tercero no tiene registros para el periodo seleccionado';
    echo json_encode($res);
    exit();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_empleado`, `salario_basico`
            FROM
                `seg_salarios_basico`
            WHERE `id_salario` = (SELECT  MAX(`id_salario`) FROM `seg_salarios_basico` WHERE `id_empleado` = (SELECT `id_empleado` FROM `seg_empleado` WHERE `no_documento` = '$empleado'))";
    $rs = $cmd->query($sql);
    $salario = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `seg_usuarios`.`id_usuario`
                , CONCAT_WS(' ', `seg_usuarios`.`nombre1`
                , `seg_usuarios`.`nombre2`
                , `seg_usuarios`.`apellido1`
                , `seg_usuarios`.`apellido2`) AS `nombre`
                , `seg_usuarios`.`documento`
                , `seg_cargo_empleado`.`descripcion_carg`
            FROM
                `seg_usuarios`
                LEFT JOIN `seg_empleado` 
                    ON (`seg_usuarios`.`documento` = `seg_empleado`.`no_documento`)
                LEFT JOIN `seg_cargo_empleado` 
                    ON (`seg_empleado`.`cargo` = `seg_cargo_empleado`.`id_cargo`)
            WHERE (`seg_usuarios`.`id_usuario` = $id_user) LIMIT 1";
    $rs = $cmd->query($sql);
    $usuario = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$id_t = [];
$id_t[] = $id_contratista;
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
$key = array_search($id_contratista, array_column($terceros, 'id_tercero'));
if ($key !== false) {
    $nombre = mb_strtoupper(trim($terceros[$key]['nombre1'] . ' ' . $terceros[$key]['nombre2'] . ' ' . $terceros[$key]['apellido1'] . ' ' . $terceros[$key]['apellido2'] . ' ' . $terceros[$key]['razon_social']));
    $cedula = $terceros[$key]['cc_nit'];
    $genero = $terceros[$key]['genero'];
    $tipodoc = $terceros[$key]['tipo_doc'];
}
$jefe = "CARMEN EMILIA GALVAN TAMAYO";
$consecutivo = 100;

if ($genero == 'M') {
    $gentilicio = 'el señor';
    $genero = 'o';
    $interesad = 'del interesado';
} else if ($genero == 'F') {
    $gentilicio = 'la señora';
    $genero = 'a';
    $interesad = 'de la interesada';
} else {
    $gentilicio = 'la empresa';
    $genero = 'a';
    $interesad = 'de la interesada';
}
if ($tipodoc == '1') {
    $tipodoc = 'cédula de ciudadanía';
} else if ($tipodoc == '2') {
    $tipodoc = 'cédula de extranjería';
} else if ($tipodoc == '3') {
    $tipodoc = 'tarjeta de identidad';
} else if ($tipodoc == '4') {
    $tipodoc = 'pasaporte';
} else if ($tipodoc == '5') {
    $tipodoc = 'NIT';
} else {
    $tipodoc = 'XXXXXXXX';
}
$meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$expedicion = explode('-', $date->format('Y-m-d'));

$dia = intval($expedicion[2]);
$mes = intval($expedicion[1]);
$anio = $expedicion[0];
$letras = new NumberFormatter('es', NumberFormatter::SPELLOUT);
if ($dia == 1) {
    $expide = "el primer (01) día del mes de {$meses[$mes]} de $anio";
} else {
    $numtolet = $letras->format($dia);
    $expide = "a los $numtolet ($dia) días del mes de {$meses[$mes]} de $anio";
}
$munexpide = $list_empdo[0]['codigo_municipio'] == '' ? 'XXXXXXXXXX' : $list_empdo[0]['codigo_municipio'];
$fingreso = $list_empdo[0]['fech_inicio'];
$inicia = explode('-', $fingreso);
$fecinicia = $inicia[2] . ' de ' . $meses[intval($inicia[1])] . ' de ' . $inicia[0];
$cargo = ucfirst($list_empdo[0]['descripcion_carg']);
$codcargo = $list_empdo[0]['codigo'];
$nombramiento = $list_empdo[0]['nombramiento'];
$letsalario = mb_strtoupper($letras->format($salario['salario_basico']));
$numsalario = pesos($salario['salario_basico']);
$proyecto = $usuario['nombre'];
$cargoproyecto = $usuario['descripcion_carg'] == '' ? 'XXXXXXXXXX' : $usuario['descripcion_carg'];
require_once '../../../vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

$plantilla = new TemplateProcessor('plantilla_clnomina.docx');

$res['status'] = 'ok';
$plantilla->setValue('consecutivo', $consecutivo);
$plantilla->setValue('vigencia', $vigencia);
$plantilla->setValue('gentilicio', $gentilicio);
$plantilla->setValue('nombre', $nombre);
$plantilla->setValue('genero', $genero);
$plantilla->setValue('tipodoc', $tipodoc);
$plantilla->setValue('cedula', number_format($cedula, 0, '', '.'));
$plantilla->setValue('interesad', $interesad);
$plantilla->setValue('expide', $expide);
$plantilla->setValue('munexpide', $munexpide);
$plantilla->setValue('fecinicia', $fecinicia);
$plantilla->setValue('cargo', $cargo);
$plantilla->setValue('codcargo', $codcargo);
$plantilla->setValue('nombramiento', $nombramiento);
$plantilla->setValue('letsalario', $letsalario);
$plantilla->setValue('numsalario', $numsalario);
$plantilla->setValue('jefe', $jefe);
$plantilla->setValue('proyecto', $proyecto);
$plantilla->setValue('cargoproyecto', $cargoproyecto);
$archivo = 'CL-' . $consecutivo . '-' . $vigencia . '.docx';
$respth = 'LUDY ELIANA CELY JULIO';
$plantilla->setValue('respth', $respth);
$plantilla->saveAs($archivo);
$res['msg'] = base64_encode(file_get_contents($archivo));
$res['name'] = $archivo;
unlink($archivo);
echo json_encode($res);
