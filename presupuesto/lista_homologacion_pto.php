<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
include '../conexion.php';
include '../permisos.php';
// Consulta tipo de presupuesto
$id_pto_presupuestos = $_POST['id_pto'];
$vigencia = $_SESSION['vigencia'];
// consulto id_pto_tipo de la tabla seg_pto_presupuestos cuando id_pto_presupuestos es igual a $id_pto_presupuestos
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_pto_cargue`,`cod_pptal`,`nom_rubro`,`tipo_dato` 
            FROM `seg_pto_cargue` 
            WHERE `vigencia` = '$vigencia' AND `id_pto_presupuestos` = $id_pto_presupuestos";
    $rs = $cmd->query($sql);
    $rubros = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_situacion`,
                `concepto`
            FROM `seg_pto_situacion`";
    $rs = $cmd->query($sql);
    $situacion = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `nombre` 
            FROM `seg_pto_presupuestos` 
            WHERE `id_pto_presupuestos`= $id_pto_presupuestos";
    $rs = $cmd->query($sql);
    $nomPresupuestos = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    if ($id_pto_presupuestos == '1') {
        $tabla = '`seg_pto_homologa_ingresos`';
        $campos = '';
        $condicion = '';
    } else if ($id_pto_presupuestos == '2') {
        $tabla = '`seg_pto_homologa_gastos`';
        $campos = ' , `seg_pto_vigencias`.`id_vigencia` AS `codigo_vig`
                    , `seg_pto_vigencias`.`vigencia` AS `nombre_vig`
                    , `seg_pto_homologa_gastos`.`id_seccion`
                    , `seg_pto_seccion`.`id_seccion` AS `codigo_secc`
                    , `seg_pto_seccion`.`seccion` AS `nombre_secc`
                    , `seg_pto_homologa_gastos`.`id_sector`
                    , `seg_pto_sector`.`id_sector` AS `codigo_sect`
                    , `seg_pto_sector`.`sector` AS `nombre_sect`
                    , `seg_pto_homologa_gastos`.`id_csia`
                    , `seg_pto_clase_sia`.`codigo` AS `codigo_csia`
                    , `seg_pto_clase_sia`.`clase_sia` AS `nombre_csia`';
        $condicion = 'INNER JOIN `seg_pto_vigencias` 
                        ON (`seg_pto_homologa_gastos`.`id_vigencia` = `seg_pto_vigencias`.`id_vigencia`)
                    INNER JOIN `seg_pto_seccion` 
                        ON (`seg_pto_homologa_gastos`.`id_vigencia` = `seg_pto_seccion`.`id_seccion`)
                    INNER JOIN `seg_pto_sector` 
                        ON (`seg_pto_homologa_gastos`.`id_vigencia` = `seg_pto_sector`.`id_sector`)
                    INNER JOIN `seg_pto_clase_sia` 
                        ON (`seg_pto_homologa_gastos`.`id_vigencia` = `seg_pto_clase_sia`.`id_csia`)';
    }
    $sql = "SELECT
                $tabla.`id_homologacion`
                , $tabla.`id_pto`
                , $tabla.`id_cgr`
                , `seg_pto_codigo_cgr`.`codigo` AS `codigo_cgr`
                , `seg_pto_codigo_cgr`.`nombre` AS `nombre_cgr`
                , $tabla.`id_cpc`
                , `seg_pto_cpc`.`codigo` AS `codigo_cpc`
                , `seg_pto_cpc`.`division` AS `nombre_cpc`
                , $tabla.`id_fuente`
                , `seg_pto_fuente`.`codigo` AS `codigo_fte`
                , `seg_pto_fuente`.`fuente` AS `nombre_fte`
                , $tabla.`id_tercero`
                , `seg_pto_terceros`.`codigo` AS `codigo_ter`
                , `seg_pto_terceros`.`entidad` AS `nombre_ter`
                , $tabla.`id_politica`
                , `seg_pto_politica`.`codigo` AS `codigo_pol`
                , `seg_pto_politica`.`politica` AS `nombre_pol`
                , $tabla.`id_siho`
                , `seg_pto_siho`.`codigo` AS `codigo_siho`
                , `seg_pto_siho`.`nombre` AS `nombre_siho`
                , $tabla.`id_sia`
                , `seg_pto_sia`.`codigo` AS `codigo_sia`
                , `seg_pto_sia`.`nombre` AS `nombre_sia`
                , $tabla.`id_situacion`
                , `seg_pto_situacion`.`concepto`
                , $tabla.`id_vigencia`
                $campos
            FROM
                $tabla
                INNER JOIN `seg_pto_codigo_cgr` 
                    ON ($tabla.`id_cgr` = `seg_pto_codigo_cgr`.`id_cod`)
                INNER JOIN `seg_pto_cpc` 
                    ON ($tabla.`id_cpc` = `seg_pto_cpc`.`id_cpc`)
                INNER JOIN `seg_pto_fuente` 
                    ON ($tabla.`id_fuente` = `seg_pto_fuente`.`id_fuente`)
                INNER JOIN `seg_pto_politica` 
                    ON ($tabla.`id_politica` = `seg_pto_politica`.`id_politica`)
                INNER JOIN `seg_pto_terceros` 
                    ON ($tabla.`id_tercero` = `seg_pto_terceros`.`id_tercero`)
                INNER JOIN `seg_pto_siho` 
                    ON ($tabla.`id_siho` = `seg_pto_siho`.`id_siho`)
                INNER JOIN `seg_pto_sia` 
                    ON ($tabla.`id_sia` = `seg_pto_sia`.`id_sia`)
                INNER JOIN `seg_pto_situacion` 
                    ON ($tabla.`id_situacion` = `seg_pto_situacion`.`id_situacion`)
                $condicion
                INNER JOIN `seg_pto_cargue` 
                    ON ($tabla.`id_pto` = `seg_pto_cargue`.`id_pto_cargue`)
            WHERE (`seg_pto_cargue`.`vigencia` = '$vigencia' AND `seg_pto_cargue`.`id_pto_presupuestos` = $id_pto_presupuestos)";
    $rs = $cmd->query($sql);
    $homologacion = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$ingreso = empty($homologacion) ? 0 : 1;
$gasto = empty($homologacion) ? 0 : 1;;
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../head.php'; ?>

<body class="sb-nav-fixed <?php if ($_SESSION['navarlat'] === '1') {
                                echo 'sb-sidenav-toggled';
                            } ?>">
    <?php include '../navsuperior.php' ?>
    <div id="layoutSidenav">
        <?php include '../navlateral.php' ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid p-2">
                    <div class="card mb-4">
                        <div class="card-header" id="divTituloPag">
                            <div class="row">
                                <div class="col-md-11">
                                    <i class="fas fa-users fa-lg" style="color:#1D80F7"></i>
                                    HOMOLOGACIONES A <?php echo strtoupper($nomPresupuestos['nombre'])  ?>
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div class="table-responsive">
                                <form id="formDataHomolPto">
                                    <input type="hidden" id="id_pto_ppto" name="id_pto_ppto" value="<?php echo $id_pto_presupuestos ?>">
                                    <input type="hidden" id="peReg" value="<?php echo $permisos['registrar']; ?>">
                                    <table id="tableHomologaPto" class="table table-striped table-bordered table-sm nowrap shadow" style="width:100%">
                                        <thead>
                                            <tr class="text-center">
                                                <?php
                                                if ($id_pto_presupuestos == 1) {
                                                ?>
                                                    <th>Código</th>
                                                    <th>Nombre</th>
                                                    <th>
                                                        <div class="center-block px-4">
                                                            <input type="checkbox" id="desmarcar" title="Desmarcar Todos">
                                                            <input type="hidden" value="<?php echo $ingreso ?>" name="ingreso">
                                                        </div>
                                                    </th>
                                                    <th>Código CGR</th>
                                                    <th>Vigencia</th>
                                                    <th>CPC</th>
                                                    <th>Fuente</th>
                                                    <th>Terceros</th>
                                                    <th>Política<br>Pública</th>
                                                    <th>SIHO</th>
                                                    <th>SIA</th>
                                                    <th>Situación<br>Fondos</th>
                                                <?php
                                                } else if ($id_pto_presupuestos == 2) {
                                                ?>
                                                    <th>Código</th>
                                                    <th>Nombre</th>
                                                    <th>
                                                        <div class="center-block px-4">
                                                            <input type="checkbox" id="desmarcar" title="Desmarcar Todos">
                                                            <input type="hidden" value="<?php echo $gasto ?>" name="gasto">
                                                        </div>
                                                    </th>
                                                    <th>Codigo CGR</th>
                                                    <th>Vigencia</th>
                                                    <th>Sección<br>Presupuesto</th>
                                                    <th>Sector</th>
                                                    <th>CPC</th>
                                                    <th>Fuente</th>
                                                    <th>Terceros</th>
                                                    <th>Política<br>Pública</th>
                                                    <th>SIHO</th>
                                                    <th>SIA</th>
                                                    <th>Clase<br>SIA</th>
                                                    <th>Situación<br>Fondos</th>

                                                <?php
                                                }
                                                ?>
                                            </tr>
                                        </thead>
                                        <tbody id="modificaHomologaPto">
                                            <?php
                                            foreach ($rubros as $rb) {
                                                $tp_cta = $rb['tipo_dato'] == 0 ? 'M' : 'D';
                                                echo "<tr>";
                                                echo "<td>" . $rb['cod_pptal'] . "</td>";
                                                if ($id_pto_presupuestos == 1) {
                                                    $colspan = $tp_cta == 'D' ? 0 : 11;
                                                    $centrar = $tp_cta == 'D' ? '' : '';
                                                    echo "<td colspan='" . $colspan . "' class='" . $centrar . "'>" . $rb['nom_rubro'] . "</td>";
                                                    if ($tp_cta == 'D') {
                                                        $key = array_search($rb['id_pto_cargue'], array_column($homologacion, 'id_pto'));
                                                        echo "<td class='text-center'>
                                                            <div class='center-block'>
                                                                <input type='checkbox' class='dupLine' value='" . $rb['id_pto_cargue'] . "' title='Copiar datos de otra linea'>
                                                                <input type='hidden' value='" . ($key !== false ? $homologacion[$key]['id_homologacion'] : 0) . "' name='idHomol[" . $rb['id_pto_cargue'] . "]'>
                                                            </div>
                                                        </td>";
                                                        echo "<td class='p-0'>
                                                                <input tipo='1' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='uno[" . $rb['id_pto_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_cgr'] . ' -> ' . $homologacion[$key]['nombre_cgr'] : '') . "'>
                                                                <input type='hidden' class='validaPto' name='codCgr[" . $rb['id_pto_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['id_cgr'] : 0) . "'>
                                                            </td>";
                                                        $val_vig = $key !== false ? $homologacion[$key]['id_vigencia'] : 0;
                                                        echo "<td class='p-0'>
                                                            <select class='form-control form-control-sm py-0 px-1 validaPto homologaPTO'  name='vigencia[" . $rb['id_pto_cargue'] . "]'>
                                                                <option value='0' " . ($val_vig == 0 ? 'selected' : '') . ">--Seleccionar--</option>
                                                                <option value='1' " . ($val_vig == 1 ? 'selected' : '') . ">ACTUAL</option>
                                                                <option value='2' " . ($val_vig == 2 ? 'selected' : '') . ">ANTERIOR</option>";
                                                        echo "</select>
                                                        </td>";
                                                        echo "<td class='p-0'>
                                                                <input tipo='5' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='cinco[" . $rb['id_pto_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_cpc'] . ' -> ' . $homologacion[$key]['nombre_cpc'] : '') . "'>
                                                                <input type='hidden' class='validaPto' name='cpc[" . $rb['id_pto_cargue'] . "]'  value='" . ($key !== false ? $homologacion[$key]['id_cpc'] : 0) . "'>
                                                            </td>";
                                                        echo "<td class='p-0'>
                                                                <input tipo='6' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='seis[" . $rb['id_pto_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_fte'] . ' -> ' . $homologacion[$key]['nombre_fte'] : '') . "'>
                                                                <input type='hidden' class='validaPto' name='fuente[" . $rb['id_pto_cargue'] . "]'  value='" . ($key !== false ? $homologacion[$key]['id_fuente'] : 0) . "'>
                                                            </td>";
                                                        echo "<td class='p-0'>
                                                                <input tipo='7' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='siete[" . $rb['id_pto_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_ter'] . ' -> ' . $homologacion[$key]['nombre_ter'] : '') . "'>
                                                                <input type='hidden' class='validaPto' name='tercero[" . $rb['id_pto_cargue'] . "]'  value='" . ($key !== false ? $homologacion[$key]['id_tercero'] : 0) . "'>
                                                            </td>";
                                                        echo "<td class='p-0'>
                                                                <input tipo='8' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='ocho[" . $rb['id_pto_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_pol'] . ' -> ' . $homologacion[$key]['nombre_pol'] : '') . "'>
                                                                <input type='hidden' class='validaPto' name='polPub[" . $rb['id_pto_cargue'] . "]'  value='" . ($key !== false ? $homologacion[$key]['id_politica'] : 0) . "'>
                                                            </td>";
                                                        echo "<td class='p-0'>
                                                            <input tipo='9' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='nueve[" . $rb['id_pto_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_siho'] . ' -> ' . $homologacion[$key]['nombre_siho'] : '') . "'>
                                                            <input type='hidden' class='validaPto' name='siho[" . $rb['id_pto_cargue'] . "]'  value='" . ($key !== false ? $homologacion[$key]['id_siho'] : 0) . "'>
                                                        </td>";
                                                        echo "<td class='p-0'>
                                                            <input tipo='10' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='diez[" . $rb['id_pto_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_sia'] . ' -> ' . $homologacion[$key]['nombre_sia'] : '') . "'>
                                                            <input type='hidden' class='validaPto' name='sia[" . $rb['id_pto_cargue'] . "]'  value='" . ($key !== false ? $homologacion[$key]['id_sia'] : 0) . "'>
                                                        </td>";
                                                        echo "<td class='p-0'>
                                                                <select class='form-control form-control-sm py-0 px-1 homologaPTO validaPto'  name='situacion[" . $rb['id_pto_cargue'] . "]'>
                                                                    <option value='0'>--Seleccionar--</option>";

                                                        foreach ($situacion as $s) {
                                                            $val_sit = $key !== false ? $homologacion[$key]['id_situacion'] : 0;
                                                            $slc = $val_sit == $s['id_situacion'] ? 'selected' : '';
                                                            echo '<option value="' . $s['id_situacion'] . '" ' . $slc . '>' . $s['concepto'] . '</option>';
                                                        }
                                                        echo        "</select>
                                                            </td>";
                                                    }
                                                } else if ($id_pto_presupuestos == 2) {
                                                    $colspan = $tp_cta == 'D' ? 1 : 14;
                                                    $centrar = $tp_cta == 'D' ? '' : '';
                                                    echo "<td colspan='" . $colspan . "' class='" . $centrar . "'>" . $rb['nom_rubro'] . "</td>";
                                                    if ($tp_cta == 'D') {
                                                        $key = array_search($rb['id_pto_cargue'], array_column($homologacion, 'id_pto'));
                                                        echo "<td class='text-center'>
                                                            <div class='center-block'>
                                                                <input type='checkbox' class='dupLine' value='" . $rb['id_pto_cargue'] . "' title='Copiar datos de otra linea'>
                                                                <input type='hidden' value='" . ($key !== false ? $homologacion[$key]['id_homologacion'] : 0) . "' name='idHomol[" . $rb['id_pto_cargue'] . "]'>
                                                            </div>
                                                        </td>";
                                                        echo "<td class='p-0'>
                                                                <input tipo='1' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='uno[" . $rb['id_pto_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_cgr'] . ' -> ' . $homologacion[$key]['nombre_cgr'] : '') . "'>
                                                                <input type='hidden' class='validaPto' name='codCgr[" . $rb['id_pto_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['id_cgr'] : 0) . "'>
                                                            </td>";
                                                        echo "<td class='p-0'>
                                                                <input tipo='2' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='dos[" . $rb['id_pto_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_vig'] . ' -> ' . $homologacion[$key]['nombre_vig'] : '') . "'>
                                                                <input type='hidden' class='validaPto' name='vigencia[" . $rb['id_pto_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['id_vigencia'] : 0) . "'>
                                                            </td>";
                                                        echo "<td class='p-0'>
                                                                <input tipo='3' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='tres[" . $rb['id_pto_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_secc'] . ' -> ' . $homologacion[$key]['nombre_secc'] : '') . "'>
                                                                <input type='hidden' class='validaPto' name='seccion[" . $rb['id_pto_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['id_seccion'] : 0) . "'>
                                                            </td>";
                                                        echo "<td class='p-0'>
                                                                <input tipo='4' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='cuatro[" . $rb['id_pto_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_sect'] . ' -> ' . $homologacion[$key]['nombre_sect'] : '') . "'>
                                                                <input type='hidden' class='validaPto' name='sector[" . $rb['id_pto_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['id_sector'] : 0) . "'>
                                                            </td>";
                                                        echo "<td class='p-0'>
                                                                <input tipo='5' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='cinco[" . $rb['id_pto_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_cpc'] . ' -> ' . $homologacion[$key]['nombre_cpc'] : '') . "'>
                                                                <input type='hidden' class='validaPto' name='cpc[" . $rb['id_pto_cargue'] . "]'  value='" . ($key !== false ? $homologacion[$key]['id_cpc'] : 0) . "'>
                                                            </td>";
                                                        echo "<td class='p-0'>
                                                                <input tipo='6' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='seis[" . $rb['id_pto_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_fte'] . ' -> ' . $homologacion[$key]['nombre_fte'] : '') . "'>
                                                                <input type='hidden' class='validaPto' name='fuente[" . $rb['id_pto_cargue'] . "]'  value='" . ($key !== false ? $homologacion[$key]['id_fuente'] : 0) . "'>
                                                            </td>";
                                                        echo "<td class='p-0'>
                                                                <input tipo='7' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='siete[" . $rb['id_pto_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_ter'] . ' -> ' . $homologacion[$key]['nombre_ter'] : '') . "'>
                                                                <input type='hidden' class='validaPto' name='tercero[" . $rb['id_pto_cargue'] . "]'  value='" . ($key !== false ? $homologacion[$key]['id_tercero'] : 0) . "'>
                                                            </td>";
                                                        echo "<td class='p-0'>
                                                                <input tipo='8' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='ocho[" . $rb['id_pto_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_pol'] . ' -> ' . $homologacion[$key]['nombre_pol'] : '') . "'>
                                                                <input type='hidden' class='validaPto' name='polPub[" . $rb['id_pto_cargue'] . "]'  value='" . ($key !== false ? $homologacion[$key]['id_politica'] : 0) . "'>
                                                            </td>";
                                                        echo "<td class='p-0'>
                                                            <input tipo='9' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='nueve[" . $rb['id_pto_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_siho'] . ' -> ' . $homologacion[$key]['nombre_siho'] : '') . "'>
                                                            <input type='hidden' class='validaPto' name='siho[" . $rb['id_pto_cargue'] . "]'  value='" . ($key !== false ? $homologacion[$key]['id_siho'] : 0) . "'>
                                                        </td>";
                                                        echo "<td class='p-0'>
                                                            <input tipo='10' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='diez[" . $rb['id_pto_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_sia'] . ' -> ' . $homologacion[$key]['nombre_sia'] : '') . "'>
                                                            <input type='hidden' class='validaPto' name='sia[" . $rb['id_pto_cargue'] . "]'  value='" . ($key !== false ? $homologacion[$key]['id_sia'] : 0) . "'>
                                                        </td>";
                                                        echo "<td class='p-0'>
                                                            <input tipo='11' type='text' class='form-control form-control-sm py-0 px-1 homologaPTO' name='once[" . $rb['id_pto_cargue'] . "]' value='" . ($key !== false ? $homologacion[$key]['codigo_csia'] . ' -> ' . $homologacion[$key]['nombre_csia'] : '') . "'>
                                                            <input type='hidden' class='validaPto' name='csia[" . $rb['id_pto_cargue'] . "]'  value='" . ($key !== false ? $homologacion[$key]['id_csia'] : 0) . "'>
                                                        </td>";
                                                        echo "<td class='p-0'>
                                                                <select class='form-control form-control-sm py-0 px-1 homologaPTO validaPto'  name='situacion[" . $rb['id_pto_cargue'] . "]'>
                                                                    <option value='0'>--Seleccionar--</option>";

                                                        foreach ($situacion as $s) {
                                                            $val_sit = $key !== false ? $homologacion[$key]['id_situacion'] : 0;
                                                            $slc = $val_sit == $s['id_situacion'] ? 'selected' : '';
                                                            echo '<option value="' . $s['id_situacion'] . '" ' . $slc . '>' . $s['concepto'] . '</option>';
                                                        }
                                                        echo        "</select>
                                                            </td>";
                                                    } else {
                                                        echo "<td colspan='13'></td>";
                                                    }
                                                }
                                                echo "</tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </form>
                            </div>
                            <div class="text-center pt-4">
                                <a type="button" class="btn btn-secondary" style="width: 7rem;" href="lista_presupuestos.php">Regresar</a>
                                <button type="button" class="btn btn-success" style="width: 7rem;" id="setHomologacionPto">Modificar</button>
                            </div>
                        </div>

                    </div>
                </div>
            </main>
            <?php include '../footer.php' ?>
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
    </div>
    <?php include '../scripts.php' ?>
</body>

</html>