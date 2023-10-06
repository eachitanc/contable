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
$res = [];
$res['status'] = '0';
if (isset($_POST['contrato'])) {
    $contratos = $_POST['contrato'];
} else {
    $res['msg'] = 'No se ha seleccionado ningún contrato';
    echo json_encode($res);
    exit();
}
$vigencia = $_SESSION['vigencia'];
$fecIni = $_POST['fecInicia'] == '' ? $vigencia . '-01-01' : $_POST['fecInicia'];
$fecFin = $_POST['fecFin'] == '' ? $vigencia . '-12-31' : $_POST['fecFin'];
$ids_contratos = [];
foreach ($contratos as $c => $v) {
    $ids_contratos[] = $c;
}
$contratos = implode(',', $ids_contratos);
$id_user = $_SESSION['id_user'];
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
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `seg_contrato_compra`.`id_contrato_compra`
                , `seg_contrato_compra`.`fec_ini`
                , `seg_contrato_compra`.`fec_fin`
                , `seg_contrato_compra`.`val_contrato`
                , `seg_contrato_compra`.`num_contrato`
                , `seg_adquisiciones`.`objeto`
                , `seg_terceros`.`id_tercero_api`
                , `seg_contrato_compra`.`id_supervisor`
                , `seg_area_c`.`area`
                , `seg_tipo_contrata`.`tipo_contrato`
                , `seg_estudios_previos`.`act_especificas`
                , `seg_estudios_previos`.`obligaciones`
            FROM
                `seg_contrato_compra`
                INNER JOIN `seg_adquisiciones` 
                    ON (`seg_contrato_compra`.`id_compra` = `seg_adquisiciones`.`id_adquisicion`)
                INNER JOIN `seg_area_c` 
                    ON (`seg_adquisiciones`.`id_area` = `seg_area_c`.`id_area`)
                INNER JOIN `seg_tipo_bien_servicio` 
                    ON (`seg_adquisiciones`.`id_tipo_bn_sv` = `seg_tipo_bien_servicio`.`id_tipo_b_s`)
                INNER JOIN `seg_tipo_contrata` 
                    ON (`seg_tipo_bien_servicio`.`id_tipo_cotrato` = `seg_tipo_contrata`.`id_tipo`)
                INNER JOIN `seg_estudios_previos` 
                    ON (`seg_estudios_previos`.`id_compra` = `seg_adquisiciones`.`id_adquisicion`)
                INNER JOIN `seg_terceros` 
                    ON (`seg_adquisiciones`.`id_tercero` = `seg_terceros`.`id_tercero`)
            WHERE (`seg_contrato_compra`.`id_contrato_compra` IN ($contratos))";
    $rs = $cmd->query($sql);
    $datos = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cant_contratos = count($datos);
    $id_contratista = $datos[0]['id_tercero_api'];
    $id_supervisor = $datos[0]['id_supervisor'];
    $id_t[] = $id_contratista;
    $id_t[] = $id_supervisor;
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
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
$key = array_search($id_contratista, array_column($terceros, 'id_tercero'));
if ($key !== false) {
    $nombre = trim($terceros[$key]['nombre1'] . ' ' . $terceros[$key]['nombre2'] . ' ' . $terceros[$key]['apellido1'] . ' ' . $terceros[$key]['apellido2'] . ' ' . $terceros[$key]['razon_social']);
    $cedula = $terceros[$key]['cc_nit'];
    $genero = $terceros[$key]['genero'];
    $tipodoc = $terceros[$key]['tipo_doc'];
}
$key = array_search($id_supervisor, array_column($terceros, 'id_tercero'));
$jefe = $key !== false ? trim($terceros[$key]['nombre1'] . ' ' . $terceros[$key]['nombre2'] . ' ' . $terceros[$key]['apellido1'] . ' ' . $terceros[$key]['apellido2'] . ' ' . $terceros[$key]['razon_social']) : '';
$consecutivo = 100;
$area = $datos[0]['area'];
if ($cant_contratos > 1) {
    $plural = 'los siguientes contratos';
} else {
    $plural = 'el siguiente contrato';
}
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
$proyecto = $usuario['nombre'];
$cargoproyecto = $usuario['descripcion_carg'] == '' ? 'XXXXXXXXXX' : $usuario['descripcion_carg'];
require_once '../../../vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

$plantilla = new TemplateProcessor('plantilla_claboral.docx');

$res['status'] = 'ok';
$plantilla->setValue('consecutivo', $consecutivo);
$plantilla->setValue('vigencia', $vigencia);
$plantilla->setValue('area', $area);
$plantilla->setValue('gentilicio', $gentilicio);
$plantilla->setValue('nombre', $nombre);
$plantilla->setValue('genero', $genero);
$plantilla->setValue('tipodoc', $tipodoc);
$plantilla->setValue('cedula', number_format($cedula, 0, '', '.'));
$plantilla->setValue('plural', $plural);
$plantilla->setValue('interesad', $interesad);
$plantilla->setValue('expide', $expide);
$plantilla->setValue('jefe', $jefe);
$plantilla->setValue('proyecto', $proyecto);
$plantilla->setValue('cargoproyecto', $cargoproyecto);
$archivo = 'CL-' . $consecutivo . '-' . $vigencia . '.docx';
$respth = 'LUDY ELIANA CELY JULIO';
$tabla = [];
/*  
`seg_contrato_compra`.`id_contrato_compra`
, `seg_contrato_compra`.`fec_ini`
, `seg_contrato_compra`.`fec_fin`
, `seg_contrato_compra`.`val_contrato`
, `seg_contrato_compra`.`num_contrato`
, `seg_adquisiciones`.`objeto`
, `seg_terceros`.`id_tercero_api`
, `seg_contrato_compra`.`id_supervisor`
, `seg_area_c`.`area`
, `seg_tipo_contrata`.`tipo_contrato`
, `seg_estudios_previos`.`act_especificas`
, `seg_estudios_previos`.`obligaciones`
*/
$hoy = $date->format('Y-m-d');
foreach ($datos as $dt) {
    $start = new DateTime($dt['fec_ini']);
    $end = new DateTime($dt['fec_fin']);
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
    if ($dt['fec_fin'] < $hoy) {
        $estado = 'Liquidado';
    } else {
        $estado = 'En ejecución';
    }
    $plazo = $p_mes == '' ? $p_dia : $p_mes . $y . $p_dia;
    $inicia = explode('-', $dt['fec_ini']);
    $fin = explode('-', $dt['fec_fin']);
    $actividades = explode('||', $dt['act_especificas']);
    $texto = '';
    $contador = 1;
    foreach ($actividades as $actv) {
        $texto .= $contador . '. ' . $actv . '<w:br/>';
        $contador++;
    }
    $tabla[] = ['id' => 'CONTRATO', 'descripcion' => ucfirst(mb_strtolower($dt['tipo_contrato'])) . ' No. ' . $dt['num_contrato'] . ' del ' . $letras->format($inicia[2]) . ' (' . $inicia[2] . ') de ' . $meses[intval($inicia[1])] . ' de ' . $inicia[0]];
    $tabla[] = ['id' => 'Objeto', 'descripcion' => mb_strtoupper($dt['objeto'])];
    if ($_POST['slcTipoCertf'] == 3) {
        $tabla[] = ['id' => 'Actividades', 'descripcion' => $texto];
    }
    $tabla[] = ['id' => 'Fecha de inicio', 'descripcion' => ucfirst($letras->format($inicia[2])) . ' (' . $inicia[2] . ') de ' . $meses[intval($inicia[1])] . ' de ' . $inicia[0]];
    $tabla[] = ['id' => 'Plazo inicial', 'descripcion' => $plazo];
    $tabla[] = ['id' => 'Valor', 'descripcion' => ucfirst($letras->format($dt['val_contrato'])) . ' pesos M/CTE. (' . pesos($dt['val_contrato']) . ')'];
    $tabla[] = ['id' => 'Fecha de terminación', 'descripcion' => ucfirst($letras->format($fin[2])) . ' (' . $fin[2] . ') de ' . $meses[intval($fin[1])] . ' de ' . $fin[0]];
    $tabla[] = ['id' => 'Tipo de Contrato', 'descripcion' => ucfirst(mb_strtolower($dt['tipo_contrato']))];
    $tabla[] = ['id' => 'Estado actual', 'descripcion' => $estado];
}
$plantilla->cloneRowAndSetValues('id', $tabla);
$plantilla->setValue('respth', $respth);
$plantilla->saveAs($archivo);
$res['msg'] = base64_encode(file_get_contents($archivo));
$res['name'] = $archivo;
unlink($archivo);
echo json_encode($res);
