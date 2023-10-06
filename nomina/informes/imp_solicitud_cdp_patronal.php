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
                * 
            FROM 
                (SELECT
                    `seg_empleado`.`id_empleado`
                    ,`seg_empleado`.`tipo_cargo`
                    , `seg_liq_segsocial_empdo`.`id_eps`
                    , `seg_liq_segsocial_empdo`.`id_arl`
                    , `seg_liq_segsocial_empdo`.`id_afp`
                    , `seg_epss`.`id_tercero_api`AS `id_api_eps`
                    , `seg_arl`.`id_tercero_api` AS `id_api_arl`
                    , `seg_afp`.`id_tercero_api` AS `id_api_afp`
                    , `seg_liq_segsocial_empdo`.`aporte_salud_emp`
                    , `seg_liq_segsocial_empdo`.`aporte_salud_empresa`
                    , `seg_liq_segsocial_empdo`.`aporte_pension_emp`
                    , `seg_liq_segsocial_empdo`.`aporte_solidaridad_pensional`
                    , `seg_liq_segsocial_empdo`.`aporte_pension_empresa`
                    , `seg_liq_segsocial_empdo`.`aporte_rieslab`
                FROM
                    `seg_empleado`
                    INNER JOIN `seg_liq_segsocial_empdo` 
                        ON (`seg_liq_segsocial_empdo`.`id_empleado` = `seg_empleado`.`id_empleado`)
                    INNER JOIN `seg_epss` 
                        ON (`seg_liq_segsocial_empdo`.`id_eps` = `seg_epss`.`id_eps`)
                    INNER JOIN `seg_arl` 
                        ON (`seg_liq_segsocial_empdo`.`id_arl` = `seg_arl`.`id_arl`)
                    INNER JOIN `seg_afp` 
                        ON (`seg_liq_segsocial_empdo`.`id_afp` = `seg_afp`.`id_afp`)
                WHERE  `seg_liq_segsocial_empdo`.`id_nomina` = $id_nomina) AS `t1`
            LEFT JOIN 
                (SELECT 
                    `seg_liq_parafiscales`.`id_empleado`
                    , `seg_liq_parafiscales`.`val_sena`
                    , `seg_liq_parafiscales`.`val_icbf`
                    , `seg_liq_parafiscales`.`val_comfam`
                    , `seg_liq_parafiscales`.`id_nomina`
                FROM 
                    `seg_liq_parafiscales`
                WHERE `id_nomina` =  $id_nomina) AS `t2`
            ON (`t1`.`id_empleado` = `t2`.`id_empleado`)";
    $rs = $cmd->query($sql);
    $patronales = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$totales = [];
$totales['comfam'] = 0;
$totales['icbf'] = 0;
$totales['sena'] = 0;
foreach ($patronales as $p) {
    $id_eps = $p['id_eps'];
    $id_arl = $p['id_arl'];
    $id_afp = $p['id_afp'];
    $totales['comfam'] += $p['val_comfam'];
    $totales['icbf'] += $p['val_icbf'];
    $totales['sena'] += $p['val_sena'];
    $valeps = isset($totales['eps'][$id_eps]) ? $totales['eps'][$id_eps] : 0;
    $valarl = isset($totales['arl'][$id_arl]) ? $totales['arl'][$id_arl] : 0;
    $valafp = isset($totales['afp'][$id_afp]) ? $totales['afp'][$id_afp] : 0;
    $totales['eps'][$id_eps] = $p['aporte_salud_empresa'] + $valeps;
    $totales['arl'][$id_arl] = $p['aporte_rieslab'] + $valarl;
    $totales['afp'][$id_afp] = $p['aporte_pension_empresa'] + $valafp;
}
$descuentos = [];
foreach ($patronales as $p) {
    $id_eps = $p['id_eps'];
    $id_afp = $p['id_afp'];
    $valeps = isset($descuentos['eps'][$id_eps]) ? $descuentos['eps'][$id_eps] : 0;
    $valafp = isset($descuentos['afp'][$id_afp]) ? $descuentos['afp'][$id_afp] : 0;
    $descuentos['eps'][$id_eps] = $p['aporte_salud_emp'] + $valeps;
    $descuentos['afp'][$id_afp] = $p['aporte_pension_emp'] + $valafp + $p['aporte_solidaridad_pensional'];
}
$valore = [];
foreach ($patronales as $p) {
    if ($p['tipo_cargo'] == 1) {
        $tipo = 'administrativo';
    } else if ($p['tipo_cargo'] == 2) {
        $tipo = 'operativo';
    }
    $id_eps = $p['id_eps'];
    $id_arl = $p['id_arl'];
    $id_afp = $p['id_afp'];
    $totsena = isset($valores[$tipo]['sena']) ? $valores[$tipo]['sena'] : 0;
    $toticbf = isset($valores[$tipo]['icbf']) ? $valores[$tipo]['icbf'] : 0;
    $totcomfam = isset($valores[$tipo]['comfam']) ? $valores[$tipo]['comfam'] : 0;
    $valores[$tipo]['sena'] = $p['val_sena'] + $totsena;
    $valores[$tipo]['icbf'] = $p['val_icbf'] + $toticbf;
    $valores[$tipo]['comfam'] = $p['val_comfam'] + $totcomfam;
    $valeps = isset($valores[$tipo]['eps'][$id_eps]) ? $valores[$tipo]['eps'][$id_eps] : 0;
    $valarl = isset($valores[$tipo]['arl'][$id_arl]) ? $valores[$tipo]['arl'][$id_arl] : 0;
    $valafp = isset($valores[$tipo]['afp'][$id_afp]) ? $valores[$tipo]['afp'][$id_afp] : 0;
    $valores[$tipo]['eps'][$id_eps] = $p['aporte_salud_empresa'] + $valeps;
    $valores[$tipo]['arl'][$id_arl] = $p['aporte_rieslab'] + $valarl;
    $valores[$tipo]['afp'][$id_afp] = $p['aporte_pension_empresa'] + $valafp;
}
$administrativo = isset($valores['administrativo']) ? $valores['administrativo'] : ['sena' => 0, 'icbf' => 0, 'comfam' => 0, 'eps' => 0, 'arl' => 0, 'afp' => 0,];
$operativo = isset($valores['operativo']) ? $valores['operativo'] : ['sena' => 0, 'icbf' => 0, 'comfam' => 0, 'eps' => 0, 'arl' => 0, 'afp' => 0,];
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
    $sql = "SELECT `id_nomina` FROM `seg_cdp_nomina` WHERE (`id_nomina` = $id_nomina AND `tipo` = 'P')";
    $rs = $cmd->query($sql);
    $val_cdp = $rs->fetch(PDO::FETCH_ASSOC);
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
    try {
        $carcater = 'P';
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $query = "INSERT INTO `seg_cdp_nomina` (`rubro`, `valor`, `id_nomina`, `tipo`) 
                VALUES (?, ?, ?, ?)";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $rubro, PDO::PARAM_STR);
        $query->bindParam(2, $valorCdp, PDO::PARAM_STR);
        $query->bindParam(3, $id_nomina, PDO::PARAM_INT);
        $query->bindParam(4, $carcater, PDO::PARAM_STR);
        foreach ($rubros as $rb) {
            $tipo = $rb['id_tipo'];
            $valorCdp = 0;
            switch ($tipo) {
                case 11:
                    $valorCdp = $administrativo['comfam'] > 0 ? $administrativo['comfam'] : 0;
                    $rubro = $rb['r_admin'];
                    if ($valorCdp > 0) {
                        $query->execute();
                        if (!($cmd->lastInsertId() > 0)) {
                            echo $query->errorInfo()[2];
                        }
                    }
                    $rubro = $rb['r_operativo'];
                    $valorCdp = $operativo['comfam'] > 0 ? $operativo['comfam'] : 0;
                    if ($valorCdp > 0) {
                        $query->execute();
                        if (!($cmd->lastInsertId() > 0)) {
                            echo $query->errorInfo()[2];
                        }
                    }
                    break;
                case 12:
                    if (!empty($administrativo['eps'])) {
                        $rubro = $rb['r_admin'];
                        $epss = $administrativo['eps'];
                        foreach ($epss as $key => $value) {
                            $valorCdp = $value;
                            if ($valorCdp > 0) {
                                $query->execute();
                                if (!($cmd->lastInsertId() > 0)) {
                                    echo $query->errorInfo()[2];
                                }
                            }
                        }
                    }
                    if (!empty($operativo['eps'])) {
                        $rubro = $rb['r_operativo'];
                        $epss = $operativo['eps'];
                        foreach ($epss as $key => $value) {
                            $valorCdp = $value;
                            if ($valorCdp > 0) {
                                $query->execute();
                                if (!($cmd->lastInsertId() > 0)) {
                                    echo $query->errorInfo()[2];
                                }
                            }
                        }
                    }
                    break;
                case 13:
                    if (!empty($administrativo['arl'])) {
                        $rubro = $rb['r_admin'];
                        $arls = $administrativo['arl'];
                        foreach ($arls as $key => $value) {
                            $valorCdp = $value;
                            if ($valorCdp > 0) {
                                $query->execute();
                                if (!($cmd->lastInsertId() > 0)) {
                                    echo $query->errorInfo()[2];
                                }
                            }
                        }
                    }
                    if (!empty($operativo['arl'])) {
                        $rubro = $rb['r_operativo'];
                        $arls = $operativo['arl'];
                        foreach ($arls as $key => $value) {
                            $valorCdp = $value;
                            if ($valorCdp > 0) {
                                $query->execute();
                                if (!($cmd->lastInsertId() > 0)) {
                                    echo $query->errorInfo()[2];
                                }
                            }
                        }
                    }
                    break;
                case 14:
                    if (!empty($administrativo['afp'])) {
                        $rubro = $rb['r_admin'];
                        $afps = $administrativo['afp'];
                        foreach ($afps as $key => $value) {
                            $valorCdp = $value;
                            if ($valorCdp > 0) {
                                $query->execute();
                                if (!($cmd->lastInsertId() > 0)) {
                                    echo $query->errorInfo()[2];
                                }
                            }
                        }
                    }
                    if (!empty($operativo['afp'])) {
                        $rubro = $rb['r_operativo'];
                        $afps = $operativo['afp'];
                        foreach ($afps as $key => $value) {
                            $valorCdp = $value;
                            if ($valorCdp > 0) {
                                $query->execute();
                                if (!($cmd->lastInsertId() > 0)) {
                                    echo $query->errorInfo()[2];
                                }
                            }
                        }
                    }
                    break;
                case 15:
                    $valorCdp = $administrativo['icbf'] > 0 ? $administrativo['icbf'] : 0;
                    $rubro = $rb['r_admin'];
                    if ($valorCdp > 0) {
                        $query->execute();
                        if (!($cmd->lastInsertId() > 0)) {
                            echo $query->errorInfo()[2];
                        }
                    }
                    $rubro = $rb['r_operativo'];
                    $valorCdp = $operativo['icbf'] > 0 ? $operativo['icbf'] : 0;
                    if ($valorCdp > 0) {
                        $query->execute();
                        if (!($cmd->lastInsertId() > 0)) {
                            echo $query->errorInfo()[2];
                        }
                    }
                    break;
                case 16:
                    $valorCdp = $administrativo['sena'] > 0 ? $administrativo['sena'] : 0;
                    $rubro = $rb['r_admin'];
                    if ($valorCdp > 0) {
                        $query->execute();
                        if (!($cmd->lastInsertId() > 0)) {
                            echo $query->errorInfo()[2];
                        }
                    }
                    $rubro = $rb['r_operativo'];
                    $valorCdp = $operativo['sena'] > 0 ? $operativo['sena'] : 0;
                    if ($valorCdp > 0) {
                        $query->execute();
                        if (!($cmd->lastInsertId() > 0)) {
                            echo $query->errorInfo()[2];
                        }
                    }
                    break;
                default:
                    $valorCdp = 0;
                    break;
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
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
            WHERE (`seg_pto_cargue`.`vigencia` =$vigencia
            AND `seg_pto_presupuestos`.`id_pto_tipo` =2);";
    $res = $cmd->query($sql);
    $rubros = $res->fetchAll();
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
    $sql = "SELECT COUNT(`id_empleado`) FROM `seg_liq_salario`  WHERE `id_nomina` = $id_nomina";
    $cantidad_empleados = $cmd->query($sql)->fetchColumn();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
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
                    <th colspan="7" style="text-align: left;">PAGO NOMINA PATRONAL <?php echo $cual ?> N° <?php echo $nomina['id_nomina'] . ' ' . $nom_mes ?> VIGENCIA <?php echo  $nomina['vigencia'] ?>, <?php echo $cantidad_empleados ?> EMPLEADOS ADSCRITOS A <?php echo $empresa['nombre']; ?></th>
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
                    $sql = "SELECT sum(`valor`) as `valor` FROM `seg_cdp_nomina` WHERE `rubro` LIKE '$rubro%' AND `id_nomina` = $id_nomina AND `tipo` = 'P'";
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