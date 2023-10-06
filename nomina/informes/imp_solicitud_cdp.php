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
$id_nomina = $_POST['id'];

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
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `seg_empleado`.`id_empleado`
                , `seg_empleado`.`sede_emp`
                , `seg_empleado`.`no_documento`
                , `seg_empleado`.`tipo_cargo`
                , `seg_liq_dlab_auxt`.`val_liq_dias`
                , `seg_liq_dlab_auxt`.`val_liq_auxt`
                , `seg_liq_dlab_auxt`.`aux_alim`
                , `seg_liq_dlab_auxt`.`g_representa`
                , `seg_liq_dlab_auxt`.`horas_ext`
            FROM
                `seg_liq_dlab_auxt`
                INNER JOIN `seg_empleado` 
                    ON (`seg_liq_dlab_auxt`.`id_empleado` = `seg_empleado`.`id_empleado`)
            WHERE (`seg_liq_dlab_auxt`.`id_nomina` = $id_nomina)";
    $rs = $cmd->query($sql);
    $sueldoBasico = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_tipo_rubro_nomina`.`id_rubro`
                , `seg_rel_rubro_nomina`.`id_tipo`
                , `seg_tipo_rubro_nomina`.`nombre`
                , `seg_rel_rubro_nomina`.`r_admin`
                , `seg_rel_rubro_nomina`.`r_operativo`
                , `seg_rel_rubro_nomina`.`vigencia`
            FROM
                `seg_rel_rubro_nomina`
                INNER JOIN `seg_tipo_rubro_nomina` 
                    ON (`seg_rel_rubro_nomina`.`id_tipo` = `seg_tipo_rubro_nomina`.`id_rubro`)
            WHERE (`seg_rel_rubro_nomina`.`vigencia` = '$vigencia')";
    $rs = $cmd->query($sql);
    $rubros = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_vacaciones`.`id_empleado`, `seg_liq_vac`.`val_liq`, `seg_liq_vac`.`val_prima_vac`, `seg_liq_vac`.`val_bon_recrea`
            FROM
                `seg_liq_vac`
                INNER JOIN `seg_vacaciones` 
                    ON (`seg_liq_vac`.`id_vac` = `seg_vacaciones`.`id_vac`)
            WHERE (`seg_liq_vac`.`id_nomina` = $id_nomina)";
    $rs = $cmd->query($sql);
    $vacaciones = $rs->fetchAll(PDO::FETCH_ASSOC);
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
                `id_nomina`, `descripcion`, `mes`, `vigencia`, `tipo`, `estado`
            FROM
                `seg_nominas`
            WHERE (`id_nomina` = $id_nomina)";
    $rs = $cmd->query($sql);
    $nomina = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_nomina` FROM `seg_cdp_nomina` WHERE (`id_nomina` = $id_nomina AND `tipo` = 'N')";
    $rs = $cmd->query($sql);
    $val_cdp = $rs->fetch(PDO::FETCH_ASSOC);
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
                , `seg_liq_prima`.`val_liq_ps`
                , `seg_liq_prima`.`id_nomina`
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
    $sql = "SELECT COUNT(`id_empleado`) FROM `seg_liq_salario`  WHERE `id_nomina` = $id_nomina";
    $cantidad_empleados = $cmd->query($sql)->fetchColumn();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_indemniza_vac`.`id_empleado`
                , `seg_liq_indemniza_vac`.`val_liq`
                , `seg_liq_indemniza_vac`.`id_nomina`
            FROM
                `seg_liq_indemniza_vac`
                INNER JOIN `seg_indemniza_vac` 
                    ON (`seg_liq_indemniza_vac`.`id_indemnizacion` = `seg_indemniza_vac`.`id_indemniza`)
            WHERE (`seg_liq_indemniza_vac`.`id_nomina` = $id_nomina)";
    $rs = $cmd->query($sql);
    $indemnizacion = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_incapacidad`.`id_empleado`
                , `seg_liq_incap`.`pago_empresa`
                , `seg_liq_incap`.`id_nomina`
            FROM
                `seg_liq_incap`
                INNER JOIN `seg_incapacidad` 
                    ON (`seg_liq_incap`.`id_incapacidad` = `seg_incapacidad`.`id_incapacidad`)
            WHERE (`seg_liq_incap`.`id_nomina` = $id_nomina)";
    $rs = $cmd->query($sql);
    $incapacidades = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$meses = array(
    '01' => 'Enero',
    '02' => 'Febrero',
    '03' => 'Marzo',
    '04' => 'Abril',
    '05' => 'Mayo',
    '06' => 'Junio',
    '07' => 'Julio',
    '08' => 'Agosto',
    '09' => 'Septiembre',
    '10' => 'Octubre',
    '11' => 'Noviembre',
    '12' => 'Diciembre'
);
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$iduser = $_SESSION['id_user'];
if (empty($val_cdp)) {
    foreach ($sueldoBasico as $sb) {
        $basico = $sb['val_liq_dias']; //1
        $extras = $sb['horas_ext']; //2
        $repre = $sb['g_representa']; //3
        $auxtras = $sb['val_liq_auxt']; //6
        $auxalim = $sb['aux_alim'];
        $id_empleado = $sb['id_empleado'];
        $id_sede = $sb['sede_emp'];
        $tipoCargo = $sb['tipo_cargo'];
        $carcater = 'N';
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO `seg_cdp_nomina` (`rubro`, `valor`, `id_nomina`, `tipo`) 
                VALUES (?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $rubro, PDO::PARAM_STR);
            $sql->bindParam(2, $valorCdp, PDO::PARAM_STR);
            $sql->bindParam(3, $id_nomina, PDO::PARAM_INT);
            $sql->bindParam(4, $carcater, PDO::PARAM_STR);
            foreach ($rubros as $rb) {
                $tipo = $rb['id_tipo'];
                if ($tipoCargo == '1') {
                    $rubro = $rb['r_admin'];
                } else {
                    $rubro = $rb['r_operativo'];
                }
                $valorCdp = 0;
                switch ($tipo) {
                    case 1:
                        $key = array_search($id_empleado, array_column($compensatorios, 'id_empleado'));
                        $compensa = $key !== false ? $compensatorios[$key]['val_compensa'] : 0;
                        $valorCdp = $basico + $compensa;
                        break;
                    case 2:
                        $valorCdp = $extras;
                        break;
                    case 3:
                        $valorCdp = $repre;
                        break;
                    case 4:
                        $key = array_search($id_empleado, array_column($vacaciones, 'id_empleado'));
                        $valorCdp = $key !== false ? $vacaciones[$key]['val_bon_recrea'] : 0;
                        break;
                    case 5:
                        $key = array_search($id_empleado, array_column($bsp, 'id_empleado'));
                        $valorCdp = $key !== false ? $bsp[$key]['val_bsp'] : 0;
                        break;
                    case 6:
                        $valorCdp = $auxtras;
                        break;
                    case 7:
                        $valorCdp = $auxalim;
                        break;
                    case 9:
                        $key = array_search($id_empleado, array_column($indemnizacion, 'id_empleado'));
                        $valorCdp = $key !== false ? $indemnizacion[$key]['val_liq'] : 0;
                        break;
                    case 17:
                        $key = array_search($id_empleado, array_column($vacaciones, 'id_empleado'));
                        $valorCdp = $key !== false ? $vacaciones[$key]['val_liq'] : 0;
                        break;
                    case 18:
                        $key = array_search($id_empleado, array_column($cesantias, 'id_empleado'));
                        $valorCdp = $key !== false ? $cesantias[$key]['val_cesantias'] : 0;
                        break;
                    case 19:
                        $key = array_search($id_empleado, array_column($cesantias, 'id_empleado'));
                        $valorCdp = $key !== false ? $cesantias[$key]['val_icesantias'] : 0;
                        break;
                    case 20:
                        $key = array_search($id_empleado, array_column($vacaciones, 'id_empleado'));
                        $valorCdp = $key !== false ? $vacaciones[$key]['val_prima_vac'] : 0;
                        break;
                    case 21:
                        $key = array_search($id_empleado, array_column($prima_nav, 'id_empleado'));
                        $valorCdp = $key !== false ? $prima_nav[$key]['val_liq_pv'] : 0;
                        break;
                    case 22:
                        $key = array_search($id_empleado, array_column($prima_sv, 'id_empleado'));
                        $valorCdp = $key !== false ? $prima_sv[$key]['val_liq_ps'] : 0;
                        break;
                    case 32:
                        $key = array_search($id_empleado, array_column($incapacidades, 'id_empleado'));
                        $valorCdp = $key !== false ? $incapacidades[$key]['pago_empresa'] : 0;
                        break;
                    default:
                        $valorCdp = 0;
                        break;
                }
                if ($valorCdp > 0) {
                    $sql->execute();
                }
            }
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
    }
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_pto_cargue`.`cod_pptal`
                , `seg_pto_cargue`.`nom_rubro`
                , `seg_pto_cargue`.`tipo_dato`
            FROM
                `seg_pto_cargue`
            INNER JOIN `seg_pto_presupuestos` 
                ON (`seg_pto_cargue`.`id_pto_presupuestos` = `seg_pto_presupuestos`.`id_pto_presupuestos`)
            WHERE (`seg_pto_cargue`.`vigencia` = $vigencia
            AND `seg_pto_presupuestos`.`id_pto_tipo` =2);";
    $res = $cmd->query($sql);
    $rubros = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
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
                                <td rowspan="3" class='text-center' style="width:18%"><label class="small"><img src="../../../images/logos/logo.png" width="100"></label></td>
                                <td colspan="7" style="text-align:center; font-size: 20px">
                                    <strong><?php echo $empresa['nombre']; ?> </strong>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="7" style="text-align:center">
                                    NIT <?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="7" style="text-align:center">
                                    <b>SOLICITUD DE CDP</b>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="8" style="text-align: right; font-size: 14px">
                                    Estado: <?php echo $nomina['estado'] == 1 ? 'PARCIAL' : 'DEFINITIVA' ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <?php
                if ($nomina['tipo'] == 'N') {
                    $cual = 'MENSUAL';
                } else if ($nomina['tipo'] == 'PS') {
                    $cual = 'DE PRESTACIONES SOCIALES';
                }
                $nom_mes = isset($meses[$nomina['mes']]) ? 'MES DE ' . mb_strtoupper($meses[$nomina['mes']]) : '';
                ?>
                <tr style="color: black">
                    <th colspan="1">OBJETO: </th>
                    <th colspan="7" style="text-align: left;">PAGO NOMINA <?php echo $cual ?> N° <?php echo $nomina['id_nomina'] . ' ' . $nom_mes ?> VIGENCIA <?php echo  $nomina['vigencia'] ?>, ADMINISTRATIVO-ASISTENCIAL, <?php echo $cantidad_empleados ?> EMPLEADOS ADSCRITOS A <?php echo $empresa['nombre']; ?></th>
                </tr>
                <tr style="background-color: #CED3D3; text-align:center;">
                    <th colspan="1">Código</th>
                    <th colspan="5">Nombre</th>
                    <th colspan="2">Valor</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($rubros as $rp) {
                    $rubro = $rp['cod_pptal'];
                    $sql = "SELECT sum(`valor`) as `valor` FROM `seg_cdp_nomina` WHERE `rubro` LIKE '$rubro%' AND `id_nomina` = $id_nomina AND `tipo` = 'N'";
                    $res = $cmd->query($sql);
                    $valor = $res->fetch();
                    $afecta = $valor['valor'];
                    if ($afecta > 0) {
                        echo "<tr>
                    <td colspan='1' class='text-left'>" . $rp['cod_pptal'] . "</td>
                    <td colspan='5'class='text-left'>" . $rp['nom_rubro'] . "</td>
                    <td colspan='2'class='text-right'>" . number_format($afecta, 2, ",", ".")  . "</td>
                    </tr>";
                    }
                }
                ?>
                <tr>
                    <td colspan="8" style="padding: 15px;"></td>
                </tr>
                <tr>
                    <td colspan="8" style="padding: 15px;"></td>
                </tr>
                <tr>
                    <td colspan="8" style="text-align: center;">
                        ______________________________________________
                    </td>
                </tr>
                <tr>
                    <td colspan="8" style="text-align: center;">
                        <?php echo mb_strtoupper($usuario['nombre']); ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="8" style="text-align: center;">
                        Técnico Administrativo
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
<?php
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "DELETE FROM `seg_cdp_nomina`";
    $sql = $cmd->prepare($sql);
    $sql->execute();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>