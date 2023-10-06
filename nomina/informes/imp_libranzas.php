<?php

use Sabberworm\CSS\Value\Value;

session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../../head.php';
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
include '../../conexion.php';
$vigencia = $_SESSION['vigencia'];
$id_nomina = $_POST['id_nomina'];

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
// consulto el nombre de la empresa de la tabla seg_empresas
try {
    $sql = "SELECT `nombre`, `nit`, `dig_ver` FROM `seg_empresas`";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `id_nomina`, `descripcion`, `mes`, `vigencia`, `tipo`, `estado`, `id_user_reg`
            FROM
                `seg_nominas`
            WHERE (`id_nomina` = $id_nomina)";
    $rs = $cmd->query($sql);
    $usereg = $rs->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $sql = "SELECT
                CONCAT_WS(' ', `nombre1`, `nombre2`, `apellido1`, `apellido2`) AS `nombre`
            FROM
                `seg_usuarios`
            WHERE (`id_usuario` = $usereg[id_user_reg])";
    $res = $cmd->query($sql);
    $usuario = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `seg_empleado`.`no_documento`
                , CONCAT_WS(' ', `seg_empleado`.`nombre1`
                , `seg_empleado`.`nombre2`
                , `seg_empleado`.`apellido1`
                , `seg_empleado`.`apellido2`) AS `nombre`
                , `seg_bancos`.`nit_banco`
                , `seg_bancos`.`nom_banco`
                , `seg_liq_libranza`.`val_mes_lib` AS `val_mes`
                , `seg_nominas`.`id_nomina`
                , `seg_nominas`.`descripcion`
                , `seg_nominas`.`mes`
                , `seg_nominas`.`vigencia`
                , `seg_nominas`.`tipo`
                , `seg_nominas`.`estado`
                , `seg_meses`.`nom_mes`
            FROM
                `seg_libranzas`
                INNER JOIN `seg_empleado` 
                    ON (`seg_libranzas`.`id_empleado` = `seg_empleado`.`id_empleado`)
                INNER JOIN `seg_liq_libranza` 
                    ON (`seg_liq_libranza`.`id_libranza` = `seg_libranzas`.`id_libranza`)
                INNER JOIN `seg_bancos` 
                    ON (`seg_libranzas`.`id_banco` = `seg_bancos`.`id_banco`)
                INNER JOIN `seg_nominas` 
                    ON (`seg_liq_libranza`.`id_nomina` = `seg_nominas`.`id_nomina`)
                INNER JOIN `seg_meses` 
                    ON (`seg_nominas`.`mes` = `seg_meses`.`codigo`)
            WHERE (`seg_nominas`.`id_nomina` = $id_nomina)
            ORDER BY `nom_banco`,`nombre`, `val_mes` ASC";
    $res = $cmd->query($sql);
    $datos = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if (empty($datos)) {
    echo '
    <div class="text-right py-3">
        <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cerrar</a>
    </div>';
    echo '<div class="alert alert-danger text-center" role="alert">
            <strong>No hay datos relacionados a esta nómina</strong>
        </div>';
    exit();
}
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$libranzas = [];
foreach ($datos as $dt) {
    $doc = $dt['nit_banco'];
    $libranzas[$doc][] = [
        'doc' => $dt['no_documento'],
        'nombre' => $dt['nombre'],
        'valor' => $dt['val_mes']
    ];
}
?>
<div class="text-right py-3">
    <a type="button" id="btnReporteGral" class="btn btn-outline-success btn-sm" value="01" title="Exprotar a Excel">
        <span class="fas fa-file-excel fa-lg" aria-hidden="true"></span>
    </a>
    <a type="button" class="btn btn-primary btn-sm" onclick="imprSelecTes('areaImprimir',<?php echo 0; ?>);"> Imprimir</a>
    <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cerrar</a>
</div>
<div class="contenedor bg-light" id="areaImprimir">

    <head>
        <style>
            @media print {
                .page_break_avoid {
                    page-break-inside: avoid;
                }

                @page {
                    size: auto;
                    margin: 2cm;
                }
            }
        </style>
    </head>
    <div class="p-4 text-left">
        <table class="page_break_avoid" style="width:100% !important;">
            <thead style="background-color: white !important;">
                <tr style="padding: bottom 3px; color:black">
                    <td colspan="8">
                        <table style="width:100% !important;">
                            <tr>
                                <td rowspan="3" class='text-center' style="width:18%"><label class="small"><img src="../../images/logos/logo.png" width="100"></label></td>
                                <td colspan="7" style="text-align:center; font-size: 20px">
                                    <strong><?php echo $empresa['nombre']; ?> </strong>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="7" style="text-align:center">
                                    NIT <?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?>
                                </td>
                            </tr>
                            <tr style="text-align:left; font-size: 14px">
                                <td colspan="2">
                                    NOMINA No.: <?php echo $id_nomina; ?>
                                </td>
                                <td colspan="3">
                                    <?php echo $datos[0]['descripcion']; ?>
                                </td>
                                <td colspan="2">
                                    <?php echo $datos[0]['nom_mes'] . '-' . $datos[0]['vigencia']; ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="8" style="text-align:center">
                                    <b>LISTADO DE LIBRANZAS</b>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="8" style="text-align: right; font-size: 14px">
                                    Estado: <?php echo $datos[0]['estado'] == 1 ? 'PARCIAL' : 'DEFINITIVA' ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr style="background-color: #CED3D3; text-align:center;">
                    <th colspan="2">Documento</th>
                    <th colspan="4">Nombre</th>
                    <th colspan="2">Valor</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $row_emp = '';
                foreach ($libranzas as $nit => $lib) {
                    $row_lib = '';
                    $tot_lib_banco = 0;
                    foreach ($lib as $l) {
                        $row_lib .= '<tr style="font-size :14px;;">
                                        <td colspan="2" style="text-align: left;">' . $l['doc'] . '</td>
                                        <td colspan="4" style="text-align: left;">' . $l['nombre'] . '</td>
                                        <td colspan="2" style="text-align: right;">' . number_format($l['valor'], 0, ',', '.') . '</td>
                                    </tr>';
                        $tot_lib_banco += $l['valor'];
                    }
                    $key = array_search($nit, array_column($datos, 'nit_banco'));
                    $nom_banco = $datos[$key]['nom_banco'];
                    $row_emp .= '<tr>
                                    <th colspan="2" style="text-align: left;">' . $nit . '</td>
                                    <th colspan="4" style="text-align: left;">' . $nom_banco . '</td>
                                    <th colspan="2" style="text-align: right;">' . number_format($tot_lib_banco, 0, ',', '.') . '</th>
                                </tr>' . $row_lib;
                }
                echo $row_emp;
                ?>
                <tr style="font-size: 10px;">
                    <td colspan="8">
                        <table style="width: 100%;">
                            <tr>
                                <td colspan="2" style="text-align: center;">
                                </td>
                                <td colspan="2" style="text-align: center;">
                                    ___________________________________
                                </td>
                                <td colspan="2" style="text-align: center;">
                                </td>
                                <td colspan="2" style="text-align: center;">
                                    ___________________________________
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" style="text-align: center;">
                                </td>
                                <td colspan="2" style="text-align: center;">
                                    Elaboro:<?php echo $usuario['nombre']; ?>
                                </td>
                                <td colspan="2" style="text-align: center;">
                                </td>
                                <td colspan="2" style="text-align: center;">
                                    Revisó
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" style="text-align: center;">
                                </td>
                                <td colspan="2" style="text-align: center;">
                                    Técnico administrativo
                                </td>
                                <td colspan="2" style="text-align: center;">
                                </td>
                                <td colspan="2" style="text-align: center;">
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </tbody>
            <tfoot style="background-color: white !important;">
                <tr>
                    <td colspan="8" style="text-align:right;font-size:70%;color:black">Fecha Imp: <?php echo $date->format('Y-m-d H:m:s') . ' CRONHIS' ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

</div>