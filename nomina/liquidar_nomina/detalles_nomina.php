<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
$anio = $_SESSION['vigencia'];
if (isset($_POST['id'])) {
    $id_nomina = $_POST['id'];
} else if (isset($_POST['id'])) {
    $id_nomina = $_POST['id'];
} else {
    header('Location: listempliquidar.php');
    exit();
}

function pesos($valor)
{
    //$valor = $valor > 0 ? $valor : 0;
    return '$' . number_format($valor, 2, ",", ".");
}

include '../../conexion.php';
include '../../permisos.php';
$key = array_search('1', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `id_empleado`, `vigencia`, `salario_basico`, `no_documento`, `estado`, CONCAT_WS(' ', `apellido1`, `apellido2`, `nombre1`, `nombre2`) AS `nombre`, `representacion`, `sede_emp`, `sede`, `cargo`, `id_nomina`
            FROM
                (SELECT  
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
                    , `seg_salarios_basico`.`id_salario`
                    , `seg_salarios_basico`.`vigencia`
                    , `seg_salarios_basico`.`salario_basico`
                    , `seg_liq_salario`.`id_nomina`
                    , `seg_cargo_empleado`.`descripcion_carg` AS `cargo`
                    , `seg_sedes_empresa`.`nombre` AS `sede`
                FROM `seg_salarios_basico`
                    INNER JOIN `seg_empleado`
                        ON(`seg_salarios_basico`.`id_empleado` = `seg_empleado`.`id_empleado`)
                    INNER JOIN `seg_liq_salario` 
                        ON (`seg_liq_salario`.`id_empleado` = `seg_empleado`.`id_empleado`)
                    LEFT JOIN `seg_cargo_empleado` 
                        ON (`seg_empleado`.`cargo` = `seg_cargo_empleado`.`id_cargo`)
                    LEFT JOIN `seg_sedes_empresa` 
                        ON (`seg_empleado`.`sede_emp` = `seg_sedes_empresa`.`id_sede`)
                WHERE `seg_salarios_basico`.`id_salario` 
                    IN(SELECT MAX(`id_salario`) FROM `seg_salarios_basico` WHERE `vigencia` <= '$anio' GROUP BY `id_empleado`)) AS t
            WHERE `id_nomina` = $id_nomina
            GROUP BY `id_empleado`";
    $rs = $cmd->query($sql);
    $obj = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_nomina`, `estado`, `planilla`, `mes`, `tipo`  FROM `seg_nominas` WHERE `id_nomina` = $id_nomina LIMIT 1";
    $rs = $cmd->query($sql);
    $id_nom = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$mes = $id_nom['mes'] != '' ? $id_nom['mes'] : '00';
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
            WHERE `id_concepto` = 8 AND `anio` = '$anio' LIMIT 1";
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
                `id_empleado`, `mes`, `anios`, `dias_liq`, `pago_empresa`, `pago_eps`, `pago_arl`
            FROM
                `seg_liq_incap`
            INNER JOIN `seg_incapacidad` 
                ON (`seg_liq_incap`.`id_incapacidad` = `seg_incapacidad`.`id_incapacidad`)
            WHERE `seg_liq_incap`.`id_nomina` = $id_nomina";
    $rs = $cmd->query($sql);
    $incap = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_empleado`, `mes_lic`, `anio_lic`, `dias_liqs`, `val_liq`
            FROM
                `seg_liq_licmp`
            INNER JOIN `seg_licenciasmp` 
                ON (`seg_liq_licmp`.`id_licmp` = `seg_licenciasmp`.`id_licmp`)
            WHERE `seg_liq_licmp`.`id_nomina` = $id_nomina";
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
                `seg_licenciasnr`.`id_empleado`
                , `seg_liq_licnr`.`dias_licnr`
            FROM
                `seg_liq_licnr`
                INNER JOIN `seg_licenciasnr` 
                    ON (`seg_liq_licnr`.`id_licnr` = `seg_licenciasnr`.`id_licnr`)
            WHERE `seg_liq_licnr`.`id_nomina` = $id_nomina";
    $rs = $cmd->query($sql);
    $licnr = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_licencia_luto`.`id_empleado`
                , `seg_liq_licluto`.`dias_licluto`
                , `seg_liq_licluto`.`val_liq`
                , `seg_liq_licluto`.`id_nomina`
            FROM
                `seg_liq_licluto`
                INNER JOIN `seg_licencia_luto` 
                    ON (`seg_liq_licluto`.`id_licluto` = `seg_licencia_luto`.`id_licluto`)
            WHERE `seg_liq_licluto`.`id_nomina` = $id_nomina";
    $rs = $cmd->query($sql);
    $licluto = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_empleado`, `mes_vac`, `anio_vac`, `dias_liqs`, `val_liq`,`val_prima_vac`,`val_bon_recrea`
            FROM
                `seg_liq_vac`
            INNER JOIN `seg_vacaciones`
                ON (`seg_liq_vac`.`id_vac` = `seg_vacaciones`.`id_vac`)
            WHERE `seg_liq_vac`.`id_nomina` = $id_nomina";
    $rs = $cmd->query($sql);
    $vac = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_empleado`, `mes_liq`, `anio_liq`, `dias_liq`, `val_liq_dias`, `val_liq_auxt`, `aux_alim`
            FROM
                `seg_liq_dlab_auxt`
            WHERE `id_nomina` = $id_nomina";
    $rs = $cmd->query($sql);
    $dlab = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT *
            FROM
                `seg_liq_prestaciones_sociales`
            WHERE `id_nomina` = $id_nomina";
    $rs = $cmd->query($sql);
    $presoc = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT *
            FROM
                `seg_liq_segsocial_empdo`
            WHERE `id_nomina` = $id_nomina";
    $rs = $cmd->query($sql);
    $segsoc = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_empleado`, `val_mes_lib`
            FROM
                `seg_liq_libranza`
            INNER JOIN `seg_libranzas` 
                ON (`seg_liq_libranza`.`id_libranza` = `seg_libranzas`.`id_libranza`)
            WHERE `seg_liq_libranza`.`id_nomina` = $id_nomina";
    $rs = $cmd->query($sql);
    $lib = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_empleado`, `val_mes_embargo`
            FROM
                `seg_liq_embargo`
            INNER JOIN `seg_embargos`
                ON (`seg_liq_embargo`.`id_embargo` = `seg_embargos`.`id_embargo`)
            WHERE `seg_liq_embargo`.`id_nomina` = $id_nomina";
    $rs = $cmd->query($sql);
    $emb = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_empleado`, `val_aporte`
            FROM
                `seg_liq_sindicato_aportes`
            INNER JOIN `seg_cuota_sindical`
                ON (`seg_liq_sindicato_aportes`.`id_cuota_sindical` = `seg_cuota_sindical`.`id_cuota_sindical`)
            WHERE `seg_liq_sindicato_aportes`.`id_nomina` = $id_nomina";
    $rs = $cmd->query($sql);
    $sind = $rs->fetchAll();
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
                WHERE `seg_liq_horex`.`id_nomina` = $id_nomina) AS t
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
    $sql = "SELECT `id_empleado`, `val_liq`, `fec_reg`
            FROM `seg_liq_salario`
            WHERE `id_nomina` = $id_nomina";
    $rs = $cmd->query($sql);
    $saln = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT *
            FROM `seg_liq_parafiscales`
            WHERE `seg_liq_parafiscales`.`id_nomina` = $id_nomina";
    $rs = $cmd->query($sql);
    $pfis = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT *
            FROM `seg_meses`
            WHERE `codigo` = '$mes'";
    $rs = $cmd->query($sql);
    $nombmes = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_rte_fte`, `id_empleado`, `val_ret`, `mes`, `anio`
            FROM
                `seg_retencion_fte`
            WHERE `id_nomina` = $id_nomina";
    $rs = $cmd->query($sql);
    $retfte = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_empleado`, `val_bsp`
            FROM
                `seg_liq_bsp`
            WHERE (`id_nomina` = $id_nomina)";
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
                `seg_liq_indemniza_vac`.`id_liq`
                , `seg_indemniza_vac`.`cant_dias`
                , `seg_indemniza_vac`.`id_empleado`
                , `seg_liq_indemniza_vac`.`val_liq`
            FROM
                `seg_liq_indemniza_vac`
                INNER JOIN `seg_indemniza_vac` 
                    ON (`seg_liq_indemniza_vac`.`id_indemnizacion` = `seg_indemniza_vac`.`id_indemniza`)
            WHERE (`seg_liq_indemniza_vac`.`id_nomina` = $id_nomina)";
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
                `seg_empleado`.`id_empleado`
                , `seg_liq_prima`.`val_liq_ps`
                , `seg_liq_prima`.`cant_dias`
            FROM
                `seg_liq_prima`
                LEFT JOIN `seg_empleado` 
                    ON (`seg_liq_prima`.`id_empleado` = `seg_empleado`.`id_empleado`)
            WHERE (`seg_liq_prima`.`id_nomina` = $id_nomina)";
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
                , `seg_liq_prima_nav`.`val_liq_pv`
                , `seg_liq_prima_nav`.`id_nomina`
            FROM
                `seg_liq_prima_nav`
                INNER JOIN `seg_empleado` 
                    ON (`seg_liq_prima_nav`.`id_empleado` = `seg_empleado`.`id_empleado`)
            WHERE (`seg_liq_prima_nav`.`id_nomina` = $id_nomina)";
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
                , `seg_liq_cesantias`.`val_icesantias`
                , `seg_liq_cesantias`.`val_cesantias`
                , `seg_liq_cesantias`.`id_nomina`
            FROM
                `seg_liq_cesantias`
                INNER JOIN `seg_empleado` 
                    ON (`seg_liq_cesantias`.`id_empleado` = `seg_empleado`.`id_empleado`)
            WHERE (`seg_liq_cesantias`.`id_nomina` = $id_nomina)";
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
                , `seg_liq_compesatorio`.`val_compensa`
                , `seg_liq_compesatorio`.`id_nomina`
            FROM
                `seg_liq_compesatorio`
                INNER JOIN `seg_empleado` 
                    ON (`seg_liq_compesatorio`.`id_empleado` = `seg_empleado`.`id_empleado`)
            WHERE (`seg_liq_compesatorio`.`id_nomina` = $id_nomina)";
    $rs = $cmd->query($sql);
    $compensatorios = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="es">
<?php include '../../head.php' ?>

<body class="sb-nav-fixed <?php
                            if ($_SESSION['navarlat'] == '1') {
                                echo 'sb-sidenav-toggled';
                            }
                            ?>">
    <?php include '../../navsuperior.php' ?>
    <div id="layoutSidenav">
        <?php include '../../navlateral.php' ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid p-2">
                    <div class="card mb-4">
                        <?php
                        if (!empty($saln)) {
                        ?>
                            <div class="card-header" id="divTituloPag">
                                <div class="row">
                                    <div class="col-md-6">
                                        <i class="fas fa-users fa-lg" style="color:#1D80F7"></i>
                                        LISTA DE EMPLEADOS NOMINA LIQUIDADA <b> <?php echo $nombmes['nom_mes'] ?></b>

                                        <input type="text" id="fecLiqNomElec" value="<?php echo date('Y-m-d', strtotime($saln[0]['fec_reg'])) ?>" hidden>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <div>
                                            <input type="hidden" id="mesNomElec" value="<?php echo $mes ?>">
                                            <div>
                                                <input type="hidden" id="id_nomina" value="<?php echo $id_nom['id_nomina'] ?>">
                                                <!--<a type="button" id="btnExportaExcelNE" class="btn btn-outline-success btn-sm" value="<?php //echo $mes 
                                                                                                                                            ?>" title="Exprotar a Excel">
                                                    <span class="fas fa-file-excel fa-lg"></span>
                                                </a>-->

                                                <?php
                                                if ($id_nom['estado'] == 1) {
                                                ?>
                                                    <button id="btnReversaNomina" class="btn btn-outline-secondary btn-sm px-2" value="<?php echo $mes ?>" title="ANULAR">
                                                        <span class="fas fa-backspace fa-lg"></span>&nbsp;&nbsp;ANULAR
                                                    </button>
                                                <?php
                                                }
                                                if ($id_nom['estado'] == 1) {
                                                    //
                                                ?>
                                                    <button id="btnConfirmaNomina" class="btn btn-outline-warning btn-sm px-2" value="<?php echo $mes ?>" title="DEFINITIVA">
                                                        <i class="fas fa-certificate"></i>&nbsp;&nbsp;</span>DEFINITIVA
                                                    </button>
                                                <?php
                                                } else if ($id_nom['estado'] == 2) {
                                                ?>
                                                    <button class="btn btn-outline-success btn-sm px-2" title="DEFINITIVA" disabled>
                                                        <i class="fas fa-certificate"></i>&nbsp;&nbsp;</span>DEFINITIVA
                                                    </button>
                                                <?php
                                                } else {
                                                ?>
                                                    <button class="btn btn-outline-info btn-sm px-2" value="<?php echo $mes ?>" title="REPORTADA" disabled>
                                                        <i class="fas fa-certificate"></i>&nbsp;&nbsp;</span>REPORTADA
                                                    </button>
                                                <?php
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body" id="divCuerpoPag">
                                <div class="">
                                    <table id="dataTableLiqNom" class="table-bordered table-sm  order-column nowrap" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th rowspan="2" style="background-color: #16A085" class="text-center centro-vertical">Nombre completo</th>
                                                <th rowspan="2" class="text-center centro-vertical">No. Doc.</th>
                                                <th rowspan="2" class="text-center centro-vertical">Sal. Base</th>
                                                <th rowspan="2" class="text-center centro-vertical">Sede</th>
                                                <th rowspan="2" class="text-center centro-vertical">Cargo</th>
                                                <th colspan="5" class="text-center centro-vertical">Días</th>
                                                <th colspan="5" class="text-center centro-vertical">Valor</th>
                                                <th rowspan="2" class="text-center centro-vertical">Aux. Transp.</th>
                                                <th rowspan="2" class="text-center centro-vertical">Aux. Alim.</th>
                                                <th rowspan="2" class="text-center centro-vertical">Val. HoEx</th>
                                                <th rowspan="2" class="text-center centro-vertical">BSP</th>
                                                <th rowspan="2" class="text-center centro-vertical">Prima Vac.</th>
                                                <th rowspan="2" class="text-center centro-vertical">Representa</th>
                                                <th rowspan="2" class="text-center centro-vertical">Bon. Recrea</th>
                                                <th rowspan="2" class="text-center centro-vertical">Prima<br>Servicio</th>
                                                <th rowspan="2" class="text-center centro-vertical">Prima<br>Navidad</th>
                                                <th rowspan="2" class="text-center centro-vertical">Cesantia</th>
                                                <th rowspan="2" class="text-center centro-vertical">I. Cesantia</th>
                                                <th rowspan="2" class="text-center centro-vertical">Compensatorio</th>
                                                <th rowspan="2" class="text-center centro-vertical">DEVENGADO</th>
                                                <th colspan="3" class="text-center centro-vertical">Parafiscales</th>
                                                <th colspan="4" class="text-center centro-vertical">Apropiaciones</th>
                                                <th colspan="6" class="text-center centro-vertical">Seguridad Social</th>
                                                <th colspan="4" class="text-center centro-vertical">Deducciones</th>
                                                <th rowspan="2" class="text-center centro-vertical">DEDUCIDO</th>
                                                <th rowspan="2" class="text-center centro-vertical">NETO</th>
                                                <th rowspan="2" class="text-center centro-vertical">ACCIÓN</th>
                                            </tr>
                                            <tr>
                                                <th>Incap.</th>
                                                <th>Lic.</th>
                                                <th>Vac.</th>
                                                <th>Otros</th>
                                                <th>Lab.</th>
                                                <th>Incap.</th>
                                                <th>Lic.</th>
                                                <th>Vac.</th>
                                                <th>Otros</th>
                                                <th>Lab.</th>
                                                <th>SENA</th>
                                                <th>ICBF</th>
                                                <th>COMFAM</th>
                                                <th>Vac.</th>
                                                <th>Cesan.</th>
                                                <th>ICesan.</th>
                                                <th>Prima</th>
                                                <th>Salud</th>
                                                <th>Riesgos</th>
                                                <th>Pensión</th>
                                                <th>SaludEmpresa</th>
                                                <th>PensiónEmpresa</th>
                                                <th>Pensión Solid.</th>
                                                <th>Libranza</th>
                                                <th>Embargo</th>
                                                <th>Sindicato</th>
                                                <th>Ret. Fte.</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($obj as $o) {
                                                $devengado = $deducido = 0;
                                                $id = $o["id_empleado"];
                                                $keysaln = array_search($id, array_column($saln, 'id_empleado'));
                                                $status = true;
                                                if ($o['estado'] == '0') {
                                                    if ($keysaln === false) {
                                                        $status = false;
                                                    }
                                                }
                                                if ($status) {
                                            ?>
                                                    <tr>
                                                        <td> <?php echo mb_strtoupper($o['nombre']) ?> </td>
                                                        <td><?php echo $o['no_documento'] ?></td>
                                                        <td class="text-right"><?php echo pesos($o['salario_basico']) ?></td>
                                                        <?php
                                                        $keyincap = array_search($id, array_column($incap, 'id_empleado'));
                                                        $keylic = array_search($id, array_column($lic, 'id_empleado'));
                                                        $keylicluto = array_search($id, array_column($licluto, 'id_empleado'));
                                                        if ($keylicluto !== false) {
                                                            $dialcluto = $licluto[$keylicluto]['dias_licluto'];
                                                            $valluto = $licluto[$keylicluto]['val_liq'];
                                                        } else {
                                                            $dialcluto = 0;
                                                            $valluto = 0;
                                                        }
                                                        $keylicnr = array_search($id, array_column($licnr, 'id_empleado'));
                                                        $keyvac = array_search($id, array_column($vac, 'id_empleado'));
                                                        $keydlab = array_search($id, array_column($dlab, 'id_empleado'));
                                                        $keypresoc = array_search($id, array_column($presoc, 'id_empleado'));
                                                        $keysegsoc = array_search($id, array_column($segsoc, 'id_empleado'));
                                                        $keyemb = array_search($id, array_column($emb, 'id_empleado'));
                                                        $keysind = array_search($id, array_column($sind, 'id_empleado'));
                                                        $keyhoex = array_search($id, array_column($hoex, 'id_empleado'));
                                                        $keypfis = array_search($id, array_column($pfis, 'id_empleado'));
                                                        $keybsp = array_search($id, array_column($bsp, 'id_empleado'));
                                                        $keyIndem = array_search($id, array_column($indemnizaciones, 'id_empleado'));
                                                        ?>
                                                        <td><?php echo $o['sede'] ?></td>
                                                        <td><?php echo $o['cargo'] ?></td>
                                                        <td><?php
                                                            $dIncap = 0;
                                                            if (false !== $keyincap) {
                                                                $filtro = [];
                                                                $filtro = array_filter($incap, function ($incap) use ($id) {
                                                                    return ($incap['id_empleado'] == $id);
                                                                });
                                                                foreach ($filtro as $f) {
                                                                    $dIncap += $f['dias_liq'];
                                                                }
                                                                echo $dIncap;
                                                            } else {
                                                                echo '0';
                                                            } ?></td>
                                                        <td><?php
                                                            if (false !== $keylicnr) {
                                                                $dialnr = $licnr[$keylicnr]['dias_licnr'] + $dialcluto;
                                                            } else {
                                                                $dialnr = 0;
                                                            }
                                                            if (false !== $keylic) {
                                                                echo $lic[$keylic]['dias_liqs'] + $dialnr + $dialcluto;
                                                            } else {
                                                                echo 0 + $dialnr + $dialcluto;
                                                            } ?></td>
                                                        <td><?php
                                                            if (false !== $keyvac) {
                                                                echo $vac[$keyvac]['dias_liqs'];
                                                            } else {
                                                                echo '0';
                                                            } ?></td>
                                                        <td class="text-right">
                                                            <?php
                                                            $keyps = array_search($id, array_column($prima_sv, 'id_empleado'));
                                                            if ($id_nom['tipo'] == 'PV') {
                                                                $dias_psv = false !== $keyps ? $prima_sv[$keyps]['cant_dias'] : 0;
                                                                echo $dias_psv;
                                                            } else {
                                                                if (false !== $keyIndem) {
                                                                    echo $indemnizaciones[$keyIndem]['cant_dias'];
                                                                    $d2 = $indemnizaciones[$keyIndem]['cant_dias'];
                                                                } else {
                                                                    echo '0';
                                                                    $d2 = 0;
                                                                }
                                                            } ?>
                                                        </td>
                                                        <td><?php
                                                            if (false !== $keydlab) {
                                                                echo $dlab[$keydlab]['dias_liq'];
                                                            } else {
                                                                echo '0';
                                                            } ?></td>
                                                        <td class="text-right">
                                                            <?php
                                                            $a = 0;
                                                            if (false !== $keyincap) {
                                                                $filtro = [];
                                                                $filtro = array_filter($incap, function ($incap) use ($id) {
                                                                    return ($incap['id_empleado'] == $id);
                                                                });
                                                                foreach ($filtro as $f) {
                                                                    $a += $f['pago_empresa'] + $f['pago_eps'] + $f['pago_arl'];
                                                                }
                                                            }
                                                            echo pesos($a);
                                                            ?>
                                                        </td>
                                                        <td class="text-right">
                                                            <?php
                                                            $b = false !== $keylic ? $lic[$keylic]['val_liq'] : 0;
                                                            echo pesos($b + $valluto)
                                                            ?>
                                                        </td>
                                                        <td class="text-right">
                                                            <?php
                                                            if (false !== $keyvac) {
                                                                echo pesos($vac[$keyvac]['val_liq']);
                                                                $c = $vac[$keyvac]['val_liq'];
                                                            } else {
                                                                echo '$0.00';
                                                                $c = 0;
                                                            } ?></td>
                                                        <td class="text-right">
                                                            <?php
                                                            if (false !== $keyIndem) {
                                                                echo pesos($indemnizaciones[$keyIndem]['val_liq']);
                                                                $d1 = $indemnizaciones[$keyIndem]['val_liq'];
                                                            } else {
                                                                echo '$0.00';
                                                                $d1 = 0;
                                                            } ?>
                                                        </td>
                                                        <td class="text-right">
                                                            <?php
                                                            $d = false !== $keydlab ? $dlab[$keydlab]['val_liq_dias'] : 0;
                                                            echo pesos($d - $valluto);
                                                            ?>
                                                        </td>
                                                        <td class="text-right">
                                                            <?php
                                                            if (false !== $keydlab) {
                                                                echo pesos($dlab[$keydlab]['val_liq_auxt']);
                                                                $e = $dlab[$keydlab]['val_liq_auxt'];
                                                            } else {
                                                                echo '$0.00';
                                                                $e = 0;
                                                            } ?>
                                                        </td>
                                                        <td class="text-right">
                                                            <?php
                                                            if (false !== $keydlab) {
                                                                echo pesos($dlab[$keydlab]['aux_alim']);
                                                                $e1 = $dlab[$keydlab]['aux_alim'];
                                                            } else {
                                                                echo '$0.00';
                                                                $e1 = 0;
                                                            } ?>
                                                        </td>
                                                        <td class="text-right">
                                                            <?php
                                                            if (false !== $keyhoex) {
                                                                echo pesos($hoex[$keyhoex]['tot_he']);
                                                                $f = $hoex[$keyhoex]['tot_he'];
                                                            } else {
                                                                echo '$0.00';
                                                                $f = 0;
                                                            } ?>
                                                        </td>
                                                        <td class="text-right">
                                                            <?php
                                                            if (false !== $keybsp) {
                                                                echo pesos($bsp[$keybsp]['val_bsp']);
                                                                $c3 = $bsp[$keybsp]['val_bsp'];
                                                            } else {
                                                                echo '$0.00';
                                                                $c3 = 0;
                                                            } ?></td>
                                                        <td class="text-right">
                                                            <?php
                                                            if (false !== $keyvac) {
                                                                echo pesos($vac[$keyvac]['val_prima_vac']);
                                                                $c4 = $vac[$keyvac]['val_prima_vac'];
                                                            } else {
                                                                echo '$0.00';
                                                                $c4 = 0;
                                                            } ?></td>
                                                        <td class="text-right">
                                                            <?php
                                                            if ($o['representacion'] == 1) {
                                                                $gr = $grepre['valor'];
                                                            } else {
                                                                $gr = 0;
                                                            }
                                                            echo pesos($gr);
                                                            ?>
                                                        </td>
                                                        <td class="text-right">
                                                            <?php
                                                            if (false !== $keyvac) {
                                                                echo pesos($vac[$keyvac]['val_bon_recrea']);
                                                                $c5 = $vac[$keyvac]['val_bon_recrea'];
                                                            } else {
                                                                echo '$0.00';
                                                                $c5 = 0;
                                                            }
                                                            $ps = false !== $keyps ? $prima_sv[$keyps]['val_liq_ps'] : 0;
                                                            $keypn = array_search($id, array_column($prima_nav, 'id_empleado'));
                                                            $pn = false !== $keypn ? $prima_nav[$keypn]['val_liq_pv'] : 0;
                                                            $keyces = array_search($id, array_column($cesantias, 'id_empleado'));
                                                            $ces = false !== $keyces ? $cesantias[$keyces]['val_cesantias'] : 0;
                                                            $ices = false !== $keyces ? $cesantias[$keyces]['val_icesantias'] : 0;
                                                            $keycomp = array_search($id, array_column($compensatorios, 'id_empleado'));
                                                            $comp = false !== $keycomp ? $compensatorios[$keycomp]['val_compensa'] : 0;
                                                            ?></td>
                                                        <td class="text-right"><?php echo pesos($ps); ?></td>
                                                        <td class="text-right"><?php echo pesos($pn); ?></td>
                                                        <td class="text-right"><?php echo pesos($ces); ?></td>
                                                        <td class="text-right"><?php echo pesos($ices); ?></td>
                                                        <td class="text-right"><?php echo pesos($comp); ?></td>
                                                        <td class="text-right">
                                                            <?php
                                                            $devengado = $a + $b + $valluto + $c + $d1 + $d + $e + $e1 + $f + $c3 + $c4 + $c5 + $ps + $pn + $ces + $ices + $comp;
                                                            echo pesos($devengado);
                                                            ?>
                                                        </td>
                                                        <?php
                                                        if (false !== $keypfis) {
                                                            $valsena = $pfis[$keypfis]['val_sena'];
                                                            $valicbf = $pfis[$keypfis]['val_icbf'];
                                                            $valconfam = $pfis[$keypfis]['val_comfam'];
                                                        } else {
                                                            $valsena = 0;
                                                            $valicbf = 0;
                                                            $valconfam = 0;
                                                        } ?>
                                                        <td class="text-right"><?php echo pesos($valsena) ?></td>
                                                        <td class="text-right"><?php echo pesos($valicbf) ?></td>
                                                        <td class="text-right"><?php echo pesos($valconfam) ?></td>
                                                        <?php
                                                        if (false !== $keypresoc) {
                                                            $valvac = $presoc[$keypresoc]['val_vacacion'];
                                                            $valces = $presoc[$keypresoc]['val_cesantia'];
                                                            $valices = $presoc[$keypresoc]['val_interes_cesantia'];
                                                            $valpri = $presoc[$keypresoc]['val_prima'];
                                                        } else {
                                                            $valvac = 0;
                                                            $valces = 0;
                                                            $valices = 0;
                                                            $valpri = 0;
                                                        } ?>
                                                        <td class="text-right"><?php echo pesos($valvac); ?></td>
                                                        <td class="text-right"><?php echo pesos($valces); ?></td>
                                                        <td class="text-right"><?php echo pesos($valices); ?></td>
                                                        <td class="text-right"><?php echo pesos($valpri); ?></td>
                                                        <?php
                                                        if (false !== $keysegsoc) {
                                                            $g = $segsoc[$keysegsoc]['aporte_salud_emp'];
                                                            $ge = $segsoc[$keysegsoc]['aporte_salud_empresa'];
                                                            $rl = $segsoc[$keysegsoc]['aporte_rieslab'];
                                                            $i = $segsoc[$keysegsoc]['aporte_pension_emp'];
                                                            $ie = $segsoc[$keysegsoc]['aporte_pension_empresa'];
                                                            $j = $segsoc[$keysegsoc]['aporte_solidaridad_pensional'];
                                                        } else {
                                                            $g = '0';
                                                            $ge = '0';
                                                            $rl = '0';
                                                            $i = '0';
                                                            $ie = '0';
                                                            $j = '0';
                                                        } ?>
                                                        <td class="text-right"><?php echo pesos($g); ?></td>
                                                        <td class="text-right"><?php echo pesos($rl); ?></td>
                                                        <td class="text-right"><?php echo pesos($i); ?></td>
                                                        <td class="text-right"><?php echo pesos($ge); ?></td>
                                                        <td class="text-right"><?php echo pesos($ie); ?></td>
                                                        <td class="text-right"><?php echo pesos($j); ?></td>
                                                        <td class="text-right">
                                                            <?php
                                                            $k = 0;
                                                            foreach ($lib as $lb) {
                                                                if ($lb['id_empleado'] == $id) {
                                                                    $k += $lb['val_mes_lib'];
                                                                }
                                                            }
                                                            echo pesos($k);
                                                            ?></td>
                                                        <td class="text-right">
                                                            <?php
                                                            $l = 0;
                                                            foreach ($emb as $e) {
                                                                if ($e['id_empleado'] == $id) {
                                                                    $l += $e['val_mes_embargo'];
                                                                }
                                                            }
                                                            echo pesos($l);
                                                            ?>
                                                        </td>
                                                        <td class="text-right">
                                                            <?php
                                                            if (false !== $keysind) {
                                                                echo pesos($sind[$keysind]['val_aporte']);
                                                                $m = $sind[$keysind]['val_aporte'];
                                                            } else {
                                                                echo '$0.00';
                                                                $m = 0;
                                                            } ?>
                                                        </td>
                                                        <td class="text-right">
                                                            <?php
                                                            $keyretfte = array_search($id, array_column($retfte, 'id_empleado'));
                                                            $n =  false !== $keyretfte ? $retfte[$keyretfte]['val_ret'] : 0;
                                                            echo pesos($n);
                                                            ?>
                                                        </td>
                                                        <td class="text-right">
                                                            <?php
                                                            $deducido = $g + $i + $j + $k + $l + $m + $n;
                                                            echo pesos($deducido);
                                                            ?>
                                                        </td>
                                                        <td class="text-right">
                                                            <?php
                                                            echo pesos($devengado - $deducido);
                                                            ?>
                                                        </td>
                                                        <?php
                                                        if ($id_nom['estado'] == 1 && $id_nom['planilla'] == 1) {
                                                        ?>
                                                            <td class="text-center">
                                                                <a value="<?php echo $id ?>" class="btn btn-outline-danger btn-sm btn-circle shadow-gb anular" title="Anular Empleado"><span class="fas fa-ban fa-lg"></span></a>
                                                            </td>
                                                        <?php
                                                        } else {
                                                            echo '<td></td>';
                                                        }
                                                        ?>
                                                    </tr>
                                            <?php }
                                            } ?>
                                        </tbody>
                                    </table>
                                    <div class="center-block">
                                        <div class="form-group">
                                            <a type="button" class="btn btn-secondary" href="/<?php echo $_SESSION['urlin'] ?>/nomina/liquidar_nomina/mostrar/liqxmes.php"> Regresar</a>
                                            <a type="button" class="btn btn-secondary " href="../../inicio.php"> Cancelar</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </main>
            <?php include '../../footer.php' ?>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="divModalError" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header" id="divModalHeader">
                    <h5 class="modal-title" id="exampleModalLongTitle">
                        <i class="fas fa-exclamation-circle fa-lg" style="color:red"></i>
                        ¡Error!
                    </h5>
                </div>
                <div class="modal-body text-center" id="divMsgError">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success btn-sm" data-dismiss="modal">Aceptar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <!-- Modal -->
    <div class="modal fade" id="divModalConfDel" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header" id="divModalHeaderConfir">
                    <h5 class="modal-title" id="exampleModalLongTitle">
                        <i class="fas fa-exclamation-triangle fa-lg" style="color: #E67E22;"></i>
                        ¡Confirmar!
                    </h5>
                </div>
                <div class="modal-body" id="divMsgConfdel">

                </div>
                <div class="modal-footer" id="divBtnsModalDel">
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="divModalDone" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header" id="divDone">
                    <h5 class="modal-title" id="exampleModalLongTitle">
                        <i class="fas fa-check-circle fa-lg" style="color:#2FDA49"></i>
                        ¡Correcto!
                    </h5>
                </div>
                <div class="modal-body text-center" id="divMsgDone">

                </div>
                <div class="modal-footer">
                    <a type="button" class="btn btn-primary btn-sm" data-dismiss="modal"> Aceptar</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <!-- Modal -->
    <div class="modal fade" id="divModalForms" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div id="divTamModalForms" class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body text-center" id="divForms">

                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <!-- Modal -->
    <div class="modal fade" id="divModalReg" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div id="divTamModalReg" class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body text-center" id="divFormsReg">

                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="divModalEspera" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header" id="divDone">
                    <h5 class="modal-title" id="exampleModalLongTitle">
                        <i class="fas fa-check-circle fa-lg" style="color:#2FDA49"></i>
                        Procesando...
                    </h5>
                </div>
                <div class="modal-body text-center" id="divMsgExito">
                    <div class="spinner-grow text-warning" role="status">
                        <span class="sr-only">Procesando...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <?php include '../../scripts.php' ?>
    <style>
        .DTFC_LeftBodyLiner {
            overflow-y: unset !important
        }

        .DTFC_RightBodyLiner {
            overflow-y: unset !important
        }
    </style>
</body>

</html>