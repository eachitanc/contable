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
    return '$' . number_format($valor, 0, ",", ".");
}
include '../../conexion.php';
$vigencia = $_SESSION['vigencia'];
$id_nomina = $_POST['id_nomina'];
$cedula = isset($_POST['cedula']) ? $_POST['cedula'] : 0;
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
                CONCAT_WS(' ', `nombre1`, `nombre2`, `apellido1`, `apellido2`) AS `nombre`
            FROM
                `seg_usuarios`
            WHERE (`id_usuario` = $_SESSION[id_user])";
    $res = $cmd->query($sql);
    $usuario = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `seg_novedades_fc`.`id_empleado`
                , `seg_fondo_censan`.`nit_fc`
                , `seg_fondo_censan`.`nombre_fc`
                , `seg_novedades_fc`.`id_novfc`
            FROM
                `seg_novedades_fc`
                INNER JOIN `seg_fondo_censan` 
                    ON (`seg_novedades_fc`.`id_fc` = `seg_fondo_censan`.`id_fc`)
            WHERE `seg_novedades_fc`.`id_novfc` IN (SELECT MAX(`id_novfc`) FROM `seg_novedades_fc` GROUP BY `id_empleado`)";
    $res = $cmd->query($sql);
    $fondo_ces = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `t1`.`id_nomina`
                , `t1`.`id`
                , `t1`.`no_documento`
                , `t1`.`nombre`
                , `t1`.`cargo`
                , `t4`.`neto`
                , CAST(`t1`.`salud_emp` AS UNSIGNED) AS `salud_emp`
                , CAST(`t1`.`salud_patron` AS UNSIGNED) AS `salud_patron`
                , `t1`.`nit`
                , `t1`.`EPS`
                , CAST(`t1`.`pension_emp` AS UNSIGNED) AS `pension_emp`
                , CAST(`t1`.`fsp` AS UNSIGNED) AS `fsp`
                , CAST(`t1`.`pension_patron` AS UNSIGNED) AS `pension_patron`
                , `t1`.`nit_afp`
                , `t1`.`AFP`
                , CAST(`t1`.`riesgo` AS UNSIGNED) AS `riesgo`
                , `t1`.`nit_arl`
                , `t1`.`ARL`
                , CAST(`t2`.`val_sena` AS UNSIGNED) AS `val_sena`
                , CAST(`t2`.`val_icbf` AS UNSIGNED) AS `val_icbf`
                , CAST(`t2`.`val_comfam` AS UNSIGNED) AS `val_comfam`
                , `t3`.`cant_dias`

            FROM 
                (SELECT
                    `seg_liq_segsocial_empdo`.`id_nomina`
                    , `seg_empleado`.`id_empleado` AS `id`
                    , `seg_empleado`.`no_documento`
                    , CONCAT_WS(' ', `seg_empleado`.`nombre1`
                    , `seg_empleado`.`nombre2`
                    , `seg_empleado`.`apellido1`
                    , `seg_empleado`.`apellido2`) AS `nombre` 
                    , `seg_cargo_empleado`.`descripcion_carg` AS `cargo`
                    , `seg_liq_segsocial_empdo`.`aporte_salud_emp` AS `salud_emp` 
                    , `seg_liq_segsocial_empdo`.`aporte_salud_empresa` AS `salud_patron` 
                    , `seg_epss`.`nit`
                    , `seg_epss`.`nombre_eps` AS `EPS` 
                    , `seg_liq_segsocial_empdo`.`aporte_pension_emp` AS `pension_emp` 
                    , `seg_liq_segsocial_empdo`.`aporte_solidaridad_pensional` AS `fsp` 
                    , `seg_liq_segsocial_empdo`.`aporte_pension_empresa` AS `pension_patron` 
                    , `seg_afp`.`nit_afp`
                    , `seg_afp`.`nombre_afp` AS `AFP` 
                    , `seg_liq_segsocial_empdo`.`aporte_rieslab` AS `riesgo` 
                    , `seg_arl`.`nit_arl`
                    , `seg_arl`.`nombre_arl` AS `ARL` 
                FROM
                    `seg_liq_segsocial_empdo`
                    INNER JOIN `seg_arl` 
                        ON (`seg_liq_segsocial_empdo`.`id_arl` = `seg_arl`.`id_arl`)
                    INNER JOIN `seg_epss` 
                        ON (`seg_liq_segsocial_empdo`.`id_eps` = `seg_epss`.`id_eps`)
                    INNER JOIN `seg_afp` 
                        ON (`seg_liq_segsocial_empdo`.`id_afp` = `seg_afp`.`id_afp`)
                    INNER JOIN `seg_empleado` 
                        ON (`seg_liq_segsocial_empdo`.`id_empleado` = `seg_empleado`.`id_empleado`)
                    INNER JOIN `seg_cargo_empleado` 
                        ON (`seg_empleado`.`cargo` = `seg_cargo_empleado`.`id_cargo`)
                WHERE (`seg_liq_segsocial_empdo`.`id_nomina` = $id_nomina)) AS  `t1`
            INNER JOIN 
                (SELECT
                    `seg_liq_parafiscales`.`id_empleado`
                    , `seg_liq_parafiscales`.`val_sena`
                    , `seg_liq_parafiscales`.`val_icbf`
                    , `seg_liq_parafiscales`.`val_comfam`
                FROM
                    `seg_liq_parafiscales`
                WHERE `seg_liq_parafiscales`.`id_nomina` = $id_nomina) AS `t2`
                ON(`t1`.`id` = `t2`.`id_empleado`)
            INNER JOIN 
                (SELECT
                    `seg_liq_dias_lab`.`id_empleado`
                    ,  `seg_liq_dias_lab`.`cant_dias`
                FROM
                    `seg_liq_dias_lab`
                WHERE `seg_liq_dias_lab`.`id_nomina` = $id_nomina) AS `t3`
                ON(`t1`.`id` = `t3`.`id_empleado`)
            INNER JOIN 
                (SELECT
                    `seg_liq_salario`.`id_empleado`
                    ,  `seg_liq_salario`.`val_liq` AS `neto`
                FROM
                    `seg_liq_salario`
                WHERE `seg_liq_salario`.`id_nomina` = $id_nomina) AS `t4`
                ON(`t1`.`id` = `t4`.`id_empleado`)";
    $rs = $cmd->query($sql);
    $obj = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
?>
<div class="form-row" py-3>
    <div class="form-group col-md-12">
        <label for="buscar" class="small">&nbsp;</label>
        <div class="text-right">
            <a type="button" id="btnReporteGral" class="btn btn-outline-success btn-sm" value="01" title="Exprotar a Excel">
                <span class="fas fa-file-excel fa-lg" aria-hidden="true"></span>
            </a>
            <a type="button" class="btn btn-primary btn-sm" onclick="imprSelecTes('areaImprimir',<?php echo 0; ?>);"> Imprimir</a>
            <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cerrar</a>
        </div>
    </div>
</div>
<div class="contenedor bg-light" id="areaImprimir">
    <style>
        .resaltar:nth-child(even) {
            background-color: #F8F9F9;
        }

        .resaltar:nth-child(odd) {
            background-color: #ffffff;
        }
    </style>
    <div class="p-4 text-left">
        <?php
        $nomes =  '';
        $emision = $date->format('d/m/Y');
        $encabezadoo = <<<EOT
        <table style="width:100% !important; font-size:10px !important;">
            <tr>
                <td colspan="8">
                    <table style="width:100% !important;">
                        <tr>
                            <td rowspan="3" class="text-center" style="width:18%"><img src="../../images/logos/logo.jpg" width="100"></td>
                            <td colspan="7" style="text-align:center;">
                                <strong> $empresa[nombre] </strong>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="7" style="text-align:center">
                                NIT  $empresa[nit] - $empresa[dig_ver] 
                            </td>
                        </tr>
                        <tr style="text-align:left !important;">
                            <td colspan="7">
                                <table style="width: 100%;">
                                    <tr>
                                        <td colspan="2">
                                            NÓMINA No.:  $id_nomina 
                                        </td>
                                        <td colspan="2">
                                            MES: $nomes 
                                        </td>
                                        <td colspan="2">
                                            AÑO:  $_SESSION[vigencia] 
                                        </td>
                                        <td colspan="2">
                                            EMISIÓN: $emision 
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="8" style="text-align:center">
                                <b>REPORTE POR CONCEPTOS DE NÓMINA</b>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="8">
                                <div style="border-top: 3px solid black; margin: 5px 0;"></div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
EOT;
        echo $encabezadoo;
        if (empty($obj)) {
            echo '<div class="alert alert-warning text-center" role="alert">
                    <strong>NO SE ENCONTRARON REGISTROS</strong>
                </div>';
            exit();
        }
        ?>
        <div class="overflow">
            <table>
                <tr>
                    <th>id_nomina</th>
                    <th>id</th>
                    <th>no_documento</th>
                    <th>nombre</th>
                    <th>cargo</th>
                    <th>neto</th>
                    <th>salud_emp</th>
                    <th>salud_patron</th>
                    <th>nit</th>
                    <th>EPS</th>
                    <th>pension_emp</th>
                    <th>fsp</th>
                    <th>pension_patron</th>
                    <th>nit_afp</th>
                    <th>AFP</th>
                    <th>nit_cesantia</th>
                    <th>Fondo_cesantias</th>
                    <th>riesgo</th>
                    <th>nit_arl</th>
                    <th>ARL</th>
                    <th>val_sena</th>
                    <th>val_icbf</th>
                    <th>val_comfam</th>
                    <th>cant_dias</th>
                </tr>
                <?php
                foreach ($obj as $o) {
                    $id_nomina = $o['id_nomina'];
                    $id = $o['id'];
                    $no_documento = $o['no_documento'];
                    $nombre = $o['nombre'];
                    $cargo = $o['cargo'];
                    $neto = $o['neto'];
                    $salud_emp = $o['salud_emp'];
                    $salud_patron = $o['salud_patron'];
                    $nit = $o['nit'];
                    $EPS = $o['EPS'];
                    $pension_emp = $o['pension_emp'];
                    $fsp = $o['fsp'];
                    $pension_patron = $o['pension_patron'];
                    $nit_afp = $o['nit_afp'];
                    $AFP = $o['AFP'];
                    $riesgo = $o['riesgo'];
                    $nit_arl = $o['nit_arl'];
                    $ARL = $o['ARL'];
                    $val_sena = $o['val_sena'];
                    $val_icbf = $o['val_icbf'];
                    $val_comfam = $o['val_comfam'];
                    $cant_dias = $o['cant_dias'];
                    $key = array_search($id, array_column($fondo_ces, 'id_empleado'));
                    if ($key !== false) {
                        $nit_fc = $fondo_ces[$key]['nit_fc'];
                        $nombre_fc = $fondo_ces[$key]['nombre_fc'];
                    } else {
                        $nit_fc = '';
                        $nombre_fc = '';
                    }
                    $tr = <<<EOT
                <tr class="resaltar">
                    <td>$id_nomina</td>
                    <td>$id</td>
                    <td>$no_documento</td>
                    <td>$nombre</td>
                    <td>$cargo</td>
                    <td>$neto</td>
                    <td>$salud_emp</td>
                    <td>$salud_patron</td>
                    <td>$nit</td>
                    <td>$EPS</td>
                    <td>$pension_emp</td>
                    <td>$fsp</td>
                    <td>$pension_patron</td>
                    <td>$nit_afp</td>
                    <td>$AFP</td>
                    <td>$nit_fc</td>
                    <td>$nombre_fc</td>
                    <td>$riesgo</td>
                    <td>$nit_arl</td>
                    <td>$ARL</td>
                    <td>$val_sena</td>
                    <td>$val_icbf</td>
                    <td>$val_comfam</td>
                    <td>$cant_dias</td>
                </tr>
EOT;
                    echo $tr;
                }
                ?>
            </table>
        </div>
    </div>
</div>