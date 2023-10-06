<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_entrada = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_entrada_almacen`.`id_tercero_api`
                , `seg_tipo_entrada`.`descripcion`
                , `seg_entrada_almacen`.`no_factura`
                , `seg_entrada_almacen`.`acta_remision`
                , `seg_entrada_almacen`.`fec_entrada`
                , `seg_detalle_entrada_almacen`.`id_prod`
                , `seg_bien_servicio`.`bien_servicio`
                , `seg_detalle_entrada_almacen`.`cant_ingresa`
                , `seg_detalle_entrada_almacen`.`valu_ingresa`
            FROM
                `seg_detalle_entrada_almacen`
                INNER JOIN `seg_entrada_almacen` 
                    ON (`seg_detalle_entrada_almacen`.`id_entra` = `seg_entrada_almacen`.`id_entrada`)
                INNER JOIN `seg_tipo_entrada` 
                    ON (`seg_entrada_almacen`.`id_tipo_entrada` = `seg_tipo_entrada`.`id_entrada`)
                INNER JOIN `seg_bien_servicio` 
                    ON (`seg_detalle_entrada_almacen`.`id_prod` = `seg_bien_servicio`.`id_b_s`)
            WHERE `seg_detalle_entrada_almacen`.`id_entra` = '$id_entrada'";
    $rs = $cmd->query($sql);
    $factura = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
};
require_once '../../../vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

$meses = ["", "enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
if (!empty($factura)) {
    $id_ter = $factura[0]['id_tercero_api'];
    $remision = $factura[0]['acta_remision'] . ' ' . $factura[0]['no_factura'];
    $tipo_entrada = $factura[0]['descripcion'];
    $feccha = explode('-', $factura[0]['fec_entrada']);
    $fec_recibe = $feccha[2] . ' de ' . $meses[intval($feccha[1])] . ' de ' . $feccha[0];
    $url = $api . 'terceros/datos/res/datos/id/' . $id_ter;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res_api = curl_exec($ch);
    curl_close($ch);
    $dat_ter = json_decode($res_api, true);
    $tipo_identificacion = $dat_ter[0]['tipo_doc'] == '1' ? 'Cédula de Ciudadanía' : 'NIT';
    $identificacion = $dat_ter[0]['genero'] == 'M' ? 'identificado' : 'identificada';
    $cc_nit = $dat_ter[0]['cc_nit'];
    $tercero = $dat_ter[0]['apellido1'] . ' ' . $dat_ter[0]['apellido2'] . ' ' . $dat_ter[0]['nombre2'] . ' ' . $dat_ter[0]['nombre1'] . ' ' . $dat_ter[0]['razon_social'];
    $values = [];
    $val_total = 0;
    foreach ($factura as $f) {
        $values[] = [
            'id' => $f['id_prod'],
            'describe_pro' => $f['bien_servicio'],
            'val_u' => pesos($f['valu_ingresa']),
            'cant' =>   $f['cant_ingresa'],
            'total' => pesos($f['valu_ingresa'] * $f['cant_ingresa'])
        ];
        $val_total = $val_total + $f['valu_ingresa'] * $f['cant_ingresa'];
    }
    $ciudada = 'Ipiales';
    $fec_exp = date('Y-m-d');
    $fec_exp = explode('-', $fec_exp);
    $expedicion = $fec_exp[2] . ' de ' . $meses[intval($fec_exp[1])] . ' de ' . $fec_exp[0];
    $plantilla = new TemplateProcessor('plantilla_certfica.docx');
    $plantilla->setValue('remision', $remision);
    $plantilla->setValue('tipo_entrada', $tipo_entrada);
    $plantilla->setValue('fec_recibe', $fec_recibe);
    $plantilla->setValue('tercero', $tercero);
    $plantilla->setValue('tipo_identificacion', $tipo_identificacion);
    $plantilla->setValue('identificacion', $identificacion);
    $plantilla->setValue('cc_nit', $cc_nit);
    $plantilla->setValue('ciudada', $ciudada);
    $plantilla->setValue('expedicion', $expedicion);
    $plantilla->setValue('val_total', pesos($val_total));
    $plantilla->cloneRowAndSetValues('id', $values);

    $nombre = 'certificado_' . $id_entrada . '.docx';
    $plantilla->saveAs($nombre);
    $filepdf = 'certificado_' . $id_entrada . '.pdf';
    $tempLibreOfficeProfile = sys_get_temp_dir() . "\\LibreOfficeProfile" . rand(100000, 999999);
    $convertir = '"C:\Program Files\LibreOffice\program\soffice.exe" "-env:UserInstallation=file:///' . str_replace("\\", "/", $tempLibreOfficeProfile) . '" --headless --convert-to pdf "' . $nombre . '" --outdir "' . str_replace("\\", "/", dirname($filepdf)) . '"';
    exec($convertir);
    header("Content-Disposition: attachment; Filename=" . $filepdf);
    echo file_get_contents($filepdf);
    unlink($filepdf);
    unlink($nombre);
} else {
    exit('No se encontraron registros');
}
