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
$trimestre = isset($_POST['trimestre']) ? $_POST['trimestre'] : 0;
$acumulado = isset($_POST['acumulado']) ? $_POST['acumulado'] : 0;
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
// consulto el nombre de la empresa de la tabla seg_empresas
switch ($trimestre) {
    case 1:
        $nomes = 'ENERO - MARZO';
        $finicia = $vigencia . '-01-01';
        $ffinal = $vigencia . '-03-31';
        break;
    case 2:
        $nomes = $acumulado == 0 ? 'ABRIL - JUNIO' : 'ENERO - JUNIO';
        $ffinal = $vigencia . '-06-30';
        $finicia = $vigencia . '-04-01';
        break;
    case 3:
        $nomes = $acumulado == 0 ? 'JULIO - SEPTIEMBRE' : 'ENERO - SEPTIEMBRE';
        $ffinal = $vigencia . '-09-30';
        $finicia = $vigencia . '-07-01';
        break;
    case 4:
        $nomes = $acumulado == 0 ? 'OCTUBRE - DICIEMBRE' : 'ENERO - DICIEMBRE';
        $ffinal = $vigencia . '-12-31';
        $finicia = $vigencia . '-10-01';
        break;
    default:
        $nomes = '';
        $finicia = $vigencia . '-01-01';
        $ffinal = $vigencia . '-12-31';
        break;
}
if ($acumulado == 1) {
    $finicia = $vigencia . '-01-01';
}
try {
    $sql = "SELECT `nombre`, `nit`, `dig_ver` FROM `seg_empresas`";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT 
                `id_nomina` 
            FROM 
                (SELECT `id_nomina`, CONCAT_WS('-',`vigencia`,`mes`,'01') AS `fecha` FROM `seg_nominas` WHERE `estado`>=5) AS `t1`  
            WHERE `fecha` BETWEEN '$finicia' AND '$ffinal'";
    $res = $cmd->query($sql);
    $nominas = $res->fetchAll();
    $id_nomina = [];
    if (!empty($nominas)) {
        foreach ($nominas as $nomina) {
            $id_nomina[] = $nomina['id_nomina'];
        }
    } else {
        $id_nomina[] = -1;
    }
    $id_nomina = implode(',', $id_nomina);
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
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT  
                `seg_empleado`.`id_empleado`
                ,`seg_empleado`.`sede_emp`
                , `seg_empleado`.`tipo_doc`
                , `seg_empleado`.`no_documento`
                , `seg_empleado`.`genero`
                , `seg_empleado`.`apellido1`
                , `seg_empleado`.`apellido2`
                , `seg_empleado`.`nombre2`
                , `seg_empleado`.`nombre1`
                , `seg_empleado`.`representacion`
                , `seg_empleado`.`estado`
                , `seg_empleado`.`tipo_cargo`
                , `seg_cargo_empleado`.`descripcion_carg` AS `cargo`
                , `seg_sedes_empresa`.`nombre` AS `sede`
            FROM `seg_empleado`
                LEFT JOIN `seg_cargo_empleado` 
                    ON (`seg_empleado`.`cargo` = `seg_cargo_empleado`.`id_cargo`)
                LEFT JOIN `seg_sedes_empresa` 
                    ON (`seg_empleado`.`sede_emp` = `seg_sedes_empresa`.`id_sede`)";
    $rs = $cmd->query($sql);
    $obj = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
            `con_vigencias`.`anio`
                , `seg_valxvigencia`.`valor`
                , `seg_valxvigencia`.`id_concepto`
            FROM
                `seg_valxvigencia`
                INNER JOIN `con_vigencias` 
                    ON (`seg_valxvigencia`.`id_vigencia` = `con_vigencias`.`id_vigencia`)
            WHERE `id_concepto` = 8 AND `anio` = '$vigencia' LIMIT 1";
    $rs = $cmd->query($sql);
    $grepre = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `id_empleado`,  SUM(`pago_empresa`) AS `pago`
            FROM
                `seg_liq_incap`
            INNER JOIN `seg_incapacidad` 
                ON (`seg_liq_incap`.`id_incapacidad` = `seg_incapacidad`.`id_incapacidad`)
            WHERE `seg_liq_incap`.`id_nomina` IN ($id_nomina)
            GROUP BY `id_empleado`";
    $rs = $cmd->query($sql);
    $incap = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_empleado`, SUM(`val_liq`) AS `pago`
            FROM
                `seg_liq_licmp`
            INNER JOIN `seg_licenciasmp` 
                ON (`seg_liq_licmp`.`id_licmp` = `seg_licenciasmp`.`id_licmp`)
            WHERE `seg_liq_licmp`.`id_nomina` IN ($id_nomina)
            GROUP BY `id_empleado`";
    $rs = $cmd->query($sql);
    $lic = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_licencia_luto`.`id_empleado`, SUM(`seg_liq_licluto`.`val_liq`) AS `pago`
            FROM
                `seg_liq_licluto`
                INNER JOIN `seg_licencia_luto` 
                    ON (`seg_liq_licluto`.`id_licluto` = `seg_licencia_luto`.`id_licluto`)
            WHERE `seg_liq_licluto`.`id_nomina` IN ($id_nomina)
            GROUP BY `seg_licencia_luto`.`id_empleado`";
    $rs = $cmd->query($sql);
    $licluto = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_empleado`, SUM(`val_liq` + `val_prima_vac` + `val_bon_recrea`) AS `vacacion`
            FROM
                `seg_liq_vac`
            INNER JOIN `seg_vacaciones`
                ON (`seg_liq_vac`.`id_vac` = `seg_vacaciones`.`id_vac`)
            WHERE `seg_liq_vac`.`id_nomina` IN ($id_nomina)
            GROUP BY `id_empleado`";
    $rs = $cmd->query($sql);
    $vac = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_empleado`, SUM(`val_liq_dias` + `val_liq_auxt` + `aux_alim` + `g_representa`) AS `laborado`
            FROM
                `seg_liq_dlab_auxt`
            WHERE `id_nomina` IN ($id_nomina)";
    $rs = $cmd->query($sql);
    $dlab = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_empleado`
                , SUM(`val_cesantia` + `val_interes_cesantia` + `val_prima` + `val_prima_vac` + `val_prima_nav` + `val_bonifica_recrea`) AS `presoc`
            FROM
                `seg_liq_prestaciones_sociales`
            WHERE (`id_nomina` IN ($id_nomina))
            GROUP BY `id_empleado`";
    $rs = $cmd->query($sql);
    $presoc = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_empleado`
                , SUM(`aporte_salud_empresa` + `aporte_pension_empresa` + `aporte_rieslab`) AS `segsoc`
            FROM
                `seg_liq_segsocial_empdo`
            WHERE `id_nomina` IN ($id_nomina)
            GROUP BY `id_empleado`";
    $rs = $cmd->query($sql);
    $segsoc = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_empleado`, SUM(`val_liq`) AS `tot_he`
            FROM
                (SELECT `id_empleado`,`val_liq`, `mes_he`, `anio_he`
                FROM
                    `seg_liq_horex`
                INNER JOIN `seg_horas_ex_trab` 
                    ON (`seg_liq_horex`.`id_he_lab` = `seg_horas_ex_trab`.`id_he_trab`)
                WHERE `seg_liq_horex`.`id_nomina` IN ($id_nomina)) AS t
            GROUP BY `id_empleado`";
    $rs = $cmd->query($sql);
    $hoex = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_empleado`
                , SUM(`val_sena` + `val_icbf` + `val_comfam`)AS `parafis` 
            FROM
                `seg_liq_parafiscales`
            WHERE (`id_nomina` IN ($id_nomina))
            GROUP BY `id_empleado`";
    $rs = $cmd->query($sql);
    $pfis = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_empleado`, SUM(`val_bsp`) AS `valor_bsp`
            FROM
                `seg_liq_bsp`
            WHERE (`id_nomina` IN ($id_nomina))
            GROUP BY `id_empleado`";
    $rs = $cmd->query($sql);
    $bsp = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_indemniza_vac`.`id_empleado`
                , SUM(`seg_liq_indemniza_vac`.`val_liq`) AS `pago`
            FROM
                `seg_liq_indemniza_vac`
                INNER JOIN `seg_indemniza_vac` 
                    ON (`seg_liq_indemniza_vac`.`id_indemnizacion` = `seg_indemniza_vac`.`id_indemniza`)
            WHERE (`seg_liq_indemniza_vac`.`id_nomina` IN ($id_nomina))
            GROUP BY `seg_indemniza_vac`.`id_empleado`";
    $rs = $cmd->query($sql);
    $indemnizaciones = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_empleado`.`id_empleado`, SUM(`seg_liq_prima`.`val_liq_ps`) AS `pago`
            FROM
                `seg_liq_prima`
                LEFT JOIN `seg_empleado` 
                    ON (`seg_liq_prima`.`id_empleado` = `seg_empleado`.`id_empleado`)
            WHERE (`seg_liq_prima`.`id_nomina` IN ($id_nomina))
            GROUP BY `seg_empleado`.`id_empleado`";
    $rs = $cmd->query($sql);
    $prima_sv = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_empleado`.`id_empleado`
                , SUM( `seg_liq_prima_nav`.`val_liq_pv`) AS `pago`
            FROM
                `seg_liq_prima_nav`
                INNER JOIN `seg_empleado` 
                    ON (`seg_liq_prima_nav`.`id_empleado` = `seg_empleado`.`id_empleado`)
            WHERE (`seg_liq_prima_nav`.`id_nomina` IN ($id_nomina))
            GROUP BY `seg_empleado`.`id_empleado`";
    $rs = $cmd->query($sql);
    $prima_nav = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_empleado`.`id_empleado`
                , SUM(`seg_liq_cesantias`.`val_icesantias` +  `seg_liq_cesantias`.`val_cesantias`) AS `pago`
            FROM
                `seg_liq_cesantias`
                INNER JOIN `seg_empleado` 
                    ON (`seg_liq_cesantias`.`id_empleado` = `seg_empleado`.`id_empleado`)
            WHERE (`seg_liq_cesantias`.`id_nomina` IN ($id_nomina))
            GROUP BY `seg_empleado`.`id_empleado`";
    $rs = $cmd->query($sql);
    $cesantias = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_empleado`.`id_empleado`
                , SUM(`seg_liq_compesatorio`.`val_compensa`) AS `pago`
            FROM
                `seg_liq_compesatorio`
                INNER JOIN `seg_empleado` 
                    ON (`seg_liq_compesatorio`.`id_empleado` = `seg_empleado`.`id_empleado`)
            WHERE (`seg_liq_compesatorio`.`id_nomina` IN ($id_nomina))
            GROUP BY `seg_empleado`.`id_empleado`";
    $rs = $cmd->query($sql);
    $compensatorios = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
?>
<div class="form-row" py-3>
    <input type="hidden" id="acumulado" value="<?php echo $acumulado ?>">
    <div class="form-group col-md-5">
        <label for="slcTrimestre" class="small">TRIMESTRE</label>
        <div class="input-group input-group-sm">
            <div class="input-group-prepend">
                <div class="input-group-text">
                    <input type="checkbox" aria-label="Checkbox for following text input" id="chAcumula" title="Marcar para generar reporte acumulado" <?php echo $acumulado == 1 ? 'checked' : '' ?>>
                </div>
            </div>
            <select class="form-control" id="slcTrimestre" name="slcTrimestre">
                <option value="0" <?php echo $trimestre == '0' ? 'selected' : '' ?>>--Seleccionar--</option>
                <option value="1" <?php echo $trimestre == '1' ? 'selected' : '' ?>>PRIMERO</option>
                <option value="2" <?php echo $trimestre == '2' ? 'selected' : '' ?>>SEGUNDO</option>
                <option value="3" <?php echo $trimestre == '3' ? 'selected' : '' ?>>TERCERO</option>
                <option value="4" <?php echo $trimestre == '4' ? 'selected' : '' ?>>CUARTO</option>
            </select>
        </div>
    </div>
    <div class="form-group col-md-1">
        <label for="btnGenSiho" class="small">&nbsp;</label>
        <button type="button" class="btn btn-light btn-sm btn-block" id="btnGenSiho">Filtrar</button>
    </div>
    <div class="form-group col-md-5">
        <label for="btnReporteGral" class="small">&nbsp;</label>
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
                                            TRIMESTRE No.:  $trimestre 
                                        </td>
                                        <td colspan="2">
                                            CORTE: $nomes 
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
                                <b>REPORTE SIHO</b>
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
            <table class="w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>No. Doc.</th>
                        <th>Nombre</th>
                        <th>Cargo</th>
                        <th>Tipo Cargo</th>
                        <th>Devengado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $fila = '';
                    $granTotal = 0;
                    foreach ($obj as $empleado) {
                        $total = 0;
                        $id = $empleado['id_empleado'];
                        $key  = array_search($id, array_column($incap, 'id_empleado'));
                        $incapacidad = $key !== false ? $incap[$key]['pago'] : 0;
                        $key  = array_search($id, array_column($lic, 'id_empleado'));
                        $licencia = $key !== false ? $lic[$key]['pago'] : 0;
                        $key  = array_search($id, array_column($licluto, 'id_empleado'));
                        $licencia_luto = $key !== false ? $licluto[$key]['pago'] : 0;
                        $key  = array_search($id, array_column($vac, 'id_empleado'));
                        $vacacion = $key !== false ? $vac[$key]['vacacion'] : 0;
                        $key  = array_search($id, array_column($dlab, 'id_empleado'));
                        $laborado = $key !== false ? $dlab[$key]['laborado'] : 0;
                        $key  = array_search($id, array_column($presoc, 'id_empleado'));
                        $presocial = $key !== false ? $presoc[$key]['presoc'] : 0;
                        $key  = array_search($id, array_column($segsoc, 'id_empleado'));
                        $seguridad_social = $key !== false ? $segsoc[$key]['segsoc'] : 0;
                        $key  = array_search($id, array_column($hoex, 'id_empleado'));
                        $horas_extras = $key !== false ? $hoex[$key]['tot_he'] : 0;
                        $key  = array_search($id, array_column($pfis, 'id_empleado'));
                        $parafiscales = $key !== false ? $pfis[$key]['parafis'] : 0;
                        $key  = array_search($id, array_column($bsp, 'id_empleado'));
                        $bsp_val = $key !== false ? $bsp[$key]['valor_bsp'] : 0;
                        $key  = array_search($id, array_column($indemnizaciones, 'id_empleado'));
                        $indemniza = $key !== false ? $indemnizaciones[$key]['pago'] : 0;
                        $key  = array_search($id, array_column($prima_sv, 'id_empleado'));
                        $prima_servicio = $key !== false ? $prima_sv[$key]['pago'] : 0;
                        $key  = array_search($id, array_column($prima_nav, 'id_empleado'));
                        $prima_navidad = $key !== false ? $prima_nav[$key]['pago'] : 0;
                        $key  = array_search($id, array_column($cesantias, 'id_empleado'));
                        $cesant = $key !== false ? $cesantias[$key]['pago'] : 0;
                        $key  = array_search($id, array_column($compensatorios, 'id_empleado'));
                        $compensa = $key !== false ? $compensatorios[$key]['pago'] : 0;
                        $total = $incapacidad + $licencia + $licencia_luto + $vacacion + $laborado + $presocial + $seguridad_social + $horas_extras + $parafiscales + $bsp_val + $indemniza + $prima_servicio + $prima_navidad + $cesant + $compensa;
                        $nombre = $empleado['nombre1'] . ' ' . $empleado['nombre2'] . ' ' . $empleado['apellido1'] . ' ' . $empleado['apellido2'];
                        $tipo_cargo = $empleado['tipo_cargo'] == '1' ? 'ADMINISTRATIVO' : 'ASISTENCIAL';
                        $granTotal += $total;
                        if ($total > 0) {
                            $fila .=
                                '<tr>
                                    <td>' . $id . '</td>
                                    <td>' . $empleado['no_documento'] . '</td>
                                    <td>' . $nombre . '</td>
                                    <td>' . $empleado['cargo'] . '</td>
                                    <td>' . $tipo_cargo . '</td>
                                    <td>' . number_format($total, 2, ',', '.') . '</td>
                            </tr>';
                        }
                    }
                    echo $fila;
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>