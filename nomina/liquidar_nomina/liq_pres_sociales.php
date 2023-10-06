<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}

include '../../conexion.php';
include '../../permisos.php';
date_default_timezone_set('America/Bogota');
$key = array_search('1', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
$ids = isset($_POST['id_empleado']) ? $_POST['id_empleado'] : exit('Acción no permitida');
$ids = implode(',', $ids);
$compensatorios = $_POST['compensatorio'];
$vigencia = $_SESSION['vigencia'];
$id_user = $_SESSION['id_user'];
$diasxempleado = [];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_empleado`, `fec_retiro`, `fech_inicio`, `tipo_empleado`, `seg_empleado`.`representacion`
            FROM
                `seg_empleado`
            WHERE `id_empleado` IN ($ids)";
    $rs = $cmd->query($sql);
    $empleado = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "DELETE FROM `seg_liq_compesatorio` WHERE `id_nomina` IS NULL";
    $sql = $cmd->prepare($sql);
    $sql->execute();
    $sql = "ALTER TABLE `seg_liq_compesatorio` AUTO_INCREMENT = 1";
    $sql = $cmd->prepare($sql);
    $sql->execute();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT 
                `seg_empleado`.`id_empleado`
                , `seg_empleado`.`representacion`
                , `t1`.`val_bsp`
                , `t1`.`mes`
                , `t1`.`anio`
                , `t2`.`corte_ces`
                , `t3`.`val_liq_ps`
                , `t3`.`corte_prim_sv`
                , `t4`.`val_liq_pv`
                , `t4`.`corte_prim_nav`
                , `t5`.`fec_inicial` as `corte_vac`
                , `t5`.`val_liq`
                , `t5`.`val_prima_vac`
                , `t5`.`val_bon_recrea`
            FROM
                `seg_empleado`
                LEFT JOIN  
                (SELECT 
                    `id_empleado`,`val_bsp`,`mes`,`anio`
                FROM `seg_liq_bsp`
                WHERE `id_bonificaciones` IN (SELECT MAX(`id_bonificaciones`) FROM `seg_liq_bsp` WHERE `id_empleado`IN ($ids) GROUP BY `id_empleado`)) AS `t1`
                    ON (`t1`.`id_empleado` = `seg_empleado`.`id_empleado`)
                LEFT JOIN 
                (SELECT 
                    `id_empleado`,`corte` AS `corte_ces`
                FROM `seg_liq_cesantias`
                WHERE `id_liq_cesan`  IN (SELECT MAX(`id_liq_cesan`) FROM `seg_liq_cesantias` WHERE `id_empleado`IN ($ids) GROUP BY `id_empleado`)) AS `t2`
                    ON (`seg_empleado`.`id_empleado` = `t2`.`id_empleado`)
                LEFT JOIN
                (SELECT 
                    `id_empleado`,`val_liq_ps`,`corte` AS `corte_prim_sv`
                FROM `seg_liq_prima`
                WHERE `id_liq_prima` IN (SELECT MAX(`id_liq_prima`) FROM `seg_liq_prima` WHERE `id_empleado`IN ($ids) GROUP BY `id_empleado`)) AS `t3`
                    ON (`seg_empleado`.`id_empleado` = `t3`.`id_empleado`)
                LEFT JOIN 
                (SELECT 
                    `id_empleado`,`val_liq_pv`,`corte` AS `corte_prim_nav`
                FROM `seg_liq_prima_nav`
                WHERE `id_liq_privac` IN (SELECT MAX(`id_liq_privac`) FROM `seg_liq_prima_nav` WHERE `id_empleado`IN ($ids) GROUP BY `id_empleado`)) AS `t4`
                    ON (`seg_empleado`.`id_empleado` = `t4`.`id_empleado`)
                LEFT JOIN 
                (SELECT
                    `seg_vacaciones`.`id_empleado`
                    , `seg_vacaciones`.`fec_inicial`
                    , `seg_liq_vac`.`val_liq`
                    , `seg_liq_vac`.`val_prima_vac`
                    , `seg_liq_vac`.`val_bon_recrea`
                FROM
                    `seg_vacaciones`
                    INNER JOIN `seg_liq_vac` 
                        ON (`seg_liq_vac`.`id_vac` = `seg_vacaciones`.`id_vac`)
                WHERE `seg_vacaciones`.`id_vac` IN (SELECT MAX(`id_vac`) FROM `seg_vacaciones` WHERE `seg_vacaciones`.`id_empleado`IN ($ids) GROUP BY `id_empleado`)) AS `t5`
                    ON (`seg_empleado`.`id_empleado` = `t5`.`id_empleado`)
            WHERE `seg_empleado`.`id_empleado` IN ($ids)";
    $rs = $cmd->query($sql);
    $cortes = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `codigo`, `fin_mes`
            FROM
                `seg_meses`";
    $rs = $cmd->query($sql);
    $meses = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `anio`, `id_concepto`, `valor`
            FROM
                `seg_valxvigencia`
            INNER JOIN `con_vigencias` 
                ON (`seg_valxvigencia`.`id_vigencia` = `con_vigencias`.`id_vigencia`)
            WHERE `anio` = '$vigencia'";
    $rs = $cmd->query($sql);
    $val_vig = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT 
                `id_salario`,`id_empleado`, `salario_basico`  
            FROM
            `seg_salarios_basico`
            WHERE `id_salario` 
                IN (SELECT MAX(`id_salario`) AS `id_salario` FROM `seg_salarios_basico` GROUP BY `id_empleado`)
            AND `id_empleado` IN ($ids)";
    $rs = $cmd->query($sql);
    $salario = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT 
                `id_he_trab`, `id_empleado`, `seg_horas_ex_trab`.`id_he`, `cantidad_he`, `codigo`, `factor`
            FROM
                `seg_horas_ex_trab`
            INNER JOIN `seg_tipo_horaex`
                ON (`seg_horas_ex_trab`.`id_he` = `seg_tipo_horaex`.`id_he`)
            WHERE `id_empleado` IN ($ids) AND `tipo` = 2";
    $rs = $cmd->query($sql);
    $horas = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `seg_novedades_arl`.`id_novarl`
                , `seg_novedades_arl`.`id_empleado`
                , `seg_novedades_arl`.`id_arl`
                , `seg_riesgos_laboral`.`id_rlab`
                , `seg_riesgos_laboral`.`cotizacion`
                , `seg_novedades_arl`.`fec_afiliacion`
            FROM
                `seg_novedades_arl`
                INNER JOIN `seg_riesgos_laboral` 
                    ON (`seg_novedades_arl`.`id_riesgo` = `seg_riesgos_laboral`.`id_rlab`)
            WHERE `seg_novedades_arl`.`id_novarl`
                IN(SELECT MAX(`id_novarl`) AS `id_novarl` FROM `seg_novedades_arl` WHERE SUBSTRING(`fec_afiliacion`, 1, 4)<= '$vigencia' GROUP BY `id_empleado`)
                AND `seg_novedades_arl`.`id_empleado` IN ($ids)";
    $rs = $cmd->query($sql);
    $riesgos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_empleado`, `id_eps`
            FROM
                `seg_novedades_eps`
            WHERE `id_novedad`  IN (SELECT MAX(`id_novedad`) FROM `seg_novedades_eps` GROUP BY `id_empleado`)
                AND `id_empleado` IN ($ids)";
    $rs = $cmd->query($sql);
    $eps = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_empleado`, `id_arl`
            FROM
                `seg_novedades_arl`
            WHERE `id_novarl`  IN (SELECT MAX(`id_novarl`) FROM `seg_novedades_arl` GROUP BY `id_empleado`)
                AND `id_empleado` IN ($ids)";
    $rs = $cmd->query($sql);
    $arl = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_empleado`, `id_afp`
            FROM
                `seg_novedades_afp`
            WHERE `id_novafp`  IN (SELECT MAX(`id_novafp`) FROM `seg_novedades_afp` GROUP BY `id_empleado`)
                AND `id_empleado` IN ($ids)";
    $rs = $cmd->query($sql);
    $afp = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_empleado`, `val_pagoxdep` FROM `seg_pago_dependiente` WHERE `id_empleado` IN ($ids)";
    $rs = $cmd->query($sql);
    $pagoxdpte = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT 
                `id_empleado`,`aporte_salud_emp`,`aporte_pension_emp`,`aporte_solidaridad_pensional`,`aporte_salud_empresa`,`aporte_pension_empresa`,`aporte_rieslab`
            FROM 
                `seg_liq_segsocial_empdo` 
            WHERE `id_liq_empdo` IN (SELECT MAX(`id_liq_empdo`) FROM `seg_liq_segsocial_empdo` WHERE `id_empleado` IN ($ids) GROUP BY `id_empleado`)";
    $rs = $cmd->query($sql);
    $ibcant = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$key = array_search('1', array_column($val_vig, 'id_concepto'));
$smmlv = false !== $key ? $val_vig[$key]['valor'] : 0;
$key = array_search('2', array_column($val_vig, 'id_concepto'));
$auxt_base = false !== $key ? $val_vig[$key]['valor'] : 0;
$key = array_search('3', array_column($val_vig, 'id_concepto'));
$auxali_base = false !== $key ? $val_vig[$key]['valor'] : 0;
$key = array_search('6', array_column($val_vig, 'id_concepto'));
$uvt = false !== $key ? $val_vig[$key]['valor'] : 0;
$key = array_search('7', array_column($val_vig, 'id_concepto'));
$bbs = false !== $key ? $val_vig[$key]['valor'] : 0;
$key = array_search('8', array_column($val_vig, 'id_concepto'));
$repre = false !== $key ? $val_vig[$key]['valor'] : 0;
$key = array_search('9', array_column($val_vig, 'id_concepto'));
$basalim = false !== $key ? $val_vig[$key]['valor'] : 0;
$c = 0;
$tipo = "PS";
if (count($empleado) > 0) {
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    $descripcion = "LIQUIDACIÓN PRESTACIONES SOCIALES";
    $mesreg = date('m');

    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "INSERT INTO `seg_nominas` (`tipo`, `vigencia`, `descripcion`,`fec_reg`, `mes`, `id_user_reg`) VALUES (?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $tipo, PDO::PARAM_STR);
        $sql->bindParam(2, $vigencia, PDO::PARAM_STR);
        $sql->bindParam(3, $descripcion, PDO::PARAM_STR);
        $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
        $sql->bindParam(5, $mesreg, PDO::PARAM_STR);
        $sql->bindParam(6, $id_user, PDO::PARAM_INT);
        $sql->execute();
        $id_nomina = $cmd->lastInsertId();
        if (!($id_nomina > 0)) {
            echo $sql->errorInfo()[2] . 'NOM';
            exit();
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    foreach ($empleado as $e) {
        $id = $e['id_empleado'];
        $key = array_search($id, array_column($salario, 'id_empleado'));
        $salbase = false !== $key ? $salario[$key]['salario_basico'] : 0;
        $tipo_emp = $e['tipo_empleado'];
        $auxt = $salbase > $smmlv * 2 ? 0 : $auxt_base;
        $auxali = $salbase > $basalim ? 0 : $auxali_base;
        $dias_compensa = isset($compensatorios[$id]) ? $compensatorios[$id] : 0;
        $val_compensa = 0;
        $gasrep = $e['representacion'] == 1 ? $repre : 0;
        if ($dias_compensa > 0) {
            $val_compensa = ($salbase / 30) * $dias_compensa;
            $estado = 1;
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "INSERT INTO `seg_liq_compesatorio`(`id_empleado`,`val_compensa`,`dias`,`estado`,`fec_reg`,`id_nomina`)
                        VALUES (?, ?, ?, ?, ?, ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $id, PDO::PARAM_INT);
                $sql->bindParam(2, $val_compensa, PDO::PARAM_STR);
                $sql->bindParam(3, $dias_compensa, PDO::PARAM_INT);
                $sql->bindParam(4, $estado, PDO::PARAM_STR);
                $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(6, $id_nomina, PDO::PARAM_INT);
                $sql->execute();
                if (!($cmd->lastInsertId() > 0)) {
                    echo $sql->errorInfo()[2] . 'COM';
                }
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
        }
        $key = array_search($id, array_column($cortes, 'id_empleado'));
        $datos = false !== $key ? $cortes[$key] : [];
        $fec_retiro = $e['fec_retiro'];
        $explode_fecret = explode('-', $fec_retiro);
        $anio_fecret = $explode_fecret[0];
        $mes_fecret = $explode_fecret[1];
        $dia_fecret = $explode_fecret[2];
        //Boniicación por Servicios Prestados
        $bsp = (($salbase + $gasrep) <= $bbs ? ($salbase + $gasrep) * 0.5 : ($salbase + $gasrep) * 0.35);
        $bsp_dia = $bsp / 360;
        $mes_bsp = $datos['mes'] != '' ? $datos['mes'] : 0;
        if ($mes_bsp == 0) {
            $feci_bsp = $e['fech_inicio'];
            $mes_bsp = $mes_fecret;
        } else {
            $key = array_search($mes_bsp, array_column($meses, 'codigo'));
            $fin_mes = false !== $key ? $meses[$key]['fin_mes'] : 28;
            $feci_bsp = date('Y-m-d', strtotime($datos['anio'] . '-' . $mes_bsp . '-' . $fin_mes . ' + 1 day'));
        }
        $diasToBsp = calcularDias($feci_bsp, $fec_retiro);
        $diasToBsp = $diasToBsp > 360 ? 360 : $diasToBsp;
        $bsp = $bsp_dia * $diasToBsp;
        $bsp_salarial = $bsp;
        if ($bsp > 0) {
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "INSERT INTO `seg_liq_bsp`(`id_empleado`, `val_bsp`, `id_user_reg`, `fec_reg`, `id_nomina`, `mes`, `anio`) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $id, PDO::PARAM_INT);
                $sql->bindParam(2, $bsp, PDO::PARAM_STR);
                $sql->bindParam(3, $id_user, PDO::PARAM_INT);
                $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(5, $id_nomina, PDO::PARAM_INT);
                $sql->bindParam(6, $mes_bsp, PDO::PARAM_STR);
                $sql->bindParam(7, $vigencia, PDO::PARAM_STR);
                $sql->execute();
                if (!($cmd->lastInsertId() > 0)) {
                    echo $sql->errorInfo()[2] . 'BSP';
                }
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
        }
        //horas extras
        if (!empty($horas)) {
            $valhora = $salbase / 240;
            foreach ($horas as $h) {
                if ($h['id_empleado'] == $id) {
                    $idhe = $h['id_he_trab'];
                    if ($h['codigo'] == 3) {
                        $factor = $h['factor'] / 100;
                        $cnthe = $h['cantidad_he'];
                        $valhe = $valhora * $factor * $cnthe;
                    } else {
                        $factor = ($h['factor'] / 100) + 1;
                        $cnthe = $h['cantidad_he'];
                        $valhe = $valhora * $factor * $cnthe;
                    }
                    try {
                        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                        $sql = "INSERT INTO `seg_liq_horex` (`id_he_lab`, `val_liq`, `fec_reg`, `id_nomina`, `tipo_liq`) 
                                VALUES (?, ?, ?, ?, ?)";
                        $sql = $cmd->prepare($sql);
                        $sql->bindParam(1, $idhe, PDO::PARAM_INT);
                        $sql->bindParam(2, $valhe, PDO::PARAM_STR);
                        $sql->bindValue(3, $date->format('Y-m-d H:i:s'));
                        $sql->bindParam(4, $id_nomina, PDO::PARAM_INT);
                        $sql->bindParam(5, $tipo, PDO::PARAM_INT);
                        $sql->execute();
                        if (!($cmd->lastInsertId() > 0)) {
                            echo $sql->errorInfo()[2] . 'HE';
                        } else {
                            $heliq = 0;
                            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                            $sql = "UPDATE `seg_horas_ex_trab` SET  `tipo` = ? WHERE `id_he_trab` = ?";
                            $sql = $cmd->prepare($sql);
                            $sql->bindParam(1, $heliq, PDO::PARAM_STR);
                            $sql->bindParam(2, $idhe, PDO::PARAM_INT);
                            $sql->execute();
                        }
                        $cmd = null;
                    } catch (PDOException $e) {
                        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                    }
                }
            }
        }
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "SELECT `id_empleado`, SUM(`val_liq`) AS `tot_he`
                    FROM 
                        (SELECT `id_empleado`, `val_liq`
                        FROM
                            `seg_liq_horex`
                        INNER JOIN `seg_horas_ex_trab` 
                            ON (`seg_liq_horex`.`id_he_lab` = `seg_horas_ex_trab`.`id_he_trab`)
                        WHERE `id_empleado` = $id AND `id_nomina` = $id_nomina) AS `the`
                    GROUP BY `id_empleado`";
            $rs = $cmd->query($sql);
            $tothe = $rs->fetch(PDO::FETCH_ASSOC);
            $devhe = !empty($tothe) ? $tothe['tot_he'] : 0;
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        $feci_ces = $datos['corte_ces'] != '' ? date('Y-m-d', strtotime($datos['corte_ces'] . ' + 1 day')) : $e['fech_inicio'];
        $feci_priserv = $datos['corte_prim_sv'] != '' ? date('Y-m-d', strtotime($datos['corte_prim_sv'] . ' + 1 day')) : $e['fech_inicio'];
        $primserant = $datos['val_liq_ps'] > 0 ? $datos['val_liq_ps'] : 0;
        $bspant  = $datos['val_bsp'] > 0 ? $datos['val_bsp'] : 0;
        $primavacant = $datos['val_prima_vac'] > 0 ? $datos['val_prima_vac'] : 0;
        $primanavant = $datos['val_liq_pv'] > 0 ? $datos['val_liq_pv'] : 0;
        $diasToCes = calcularDias($feci_ces, $fec_retiro);
        $diasToCes = $diasToCes > 360 ? 360 : $diasToCes;
        $promHorExt = PromedioHoras($feci_ces, $fec_retiro, $id);
        $diasToPriServ = calcularDias($feci_priserv, $fec_retiro);
        $diasToPriServ = $diasToPriServ > 360 ? 360 : $diasToPriServ;
        $feci_pvac = $datos['corte_vac'] == '' ? $e['fech_inicio'] :  $datos['corte_vac'];
        $diasToVac = calcularDias($feci_pvac, $fec_retiro);
        $diasToVac = $diasToVac > 360 ? 360 : $diasToVac;
        $feci_primnav = $datos['corte_prim_nav'] != '' ? date('Y-m-d', strtotime($datos['corte_prim_nav'] . ' + 1 day')) : $e['fech_inicio'];
        $diasToPriNav = calcularDias($feci_primnav, $fec_retiro);
        $diasToPriNav = $diasToPriNav > 360 ? 360 : $diasToPriNav;
        $prima_sv_dia = ($salbase  + $auxt + $auxali + $gasrep + $bspant / 12) / 720;
        //prima de servicios
        $prima_sv = $prima_sv_dia * $diasToPriServ;
        //prima de vacaciones
        $prima_vac_dia = ((($salbase +  $gasrep + $auxt + $auxali + $bspant  / 12 + $primserant / 12) * 15) / 30) / 360;
        $prima_vac = $prima_vac_dia * $diasToVac;
        //liquidacion vacaciones
        $vac_dia  = ((($salbase  + $gasrep + $auxt + $auxali + $bspant  / 12 + $primserant / 12) * 22) / 30) / 360;
        $vacacion = $vac_dia * $diasToVac;
        //Bonificacion de recreacion
        $bonrecrea = (($salbase / 30) * (2 * $diasToVac / 360));
        //prima de navidad
        $prima_nav_dia = (($salbase +  $gasrep + $auxt + $auxali + ($bspant  / 12) + ($primserant / 12) + ($primavacant / 12))) / 360;
        $prima_nav = $prima_nav_dia * $diasToPriNav;
        //cesantia e intereses  cesantia
        $censantia_dia = ($salbase + $gasrep +  $auxt + $auxali + $promHorExt + $bspant  / 12 + $primserant / 12 + $primavacant / 12 + $primanavant / 12) / 360;
        $cesantia = $censantia_dia * $diasToCes;
        $icesantia = $cesantia * 0.12;
        //vacaciones

        try {
            $anticipo = 2;
            $estado = 2;
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO `seg_vacaciones`(`id_empleado`,`anticipo`,`dias_liquidar`,`estado`,`fec_reg`)
                    VALUES (?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id, PDO::PARAM_INT);
            $sql->bindParam(2, $anticipo, PDO::PARAM_INT);
            $sql->bindParam(3, $diasToVac, PDO::PARAM_STR);
            $sql->bindParam(4, $estado, PDO::PARAM_STR);
            $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
            $sql->execute();
            $idvac = $cmd->lastInsertId();
            if ($idvac > 0) {
                $dayvac = (15 * $diasToVac) / 360;
                $sql = "INSERT INTO `seg_liq_vac`
                                (`id_vac`, `dias_liqs`, `val_liq`, `val_prima_vac`, `val_bon_recrea`, `mes_vac`, `anio_vac`, `fec_reg`,`id_nomina`, `tipo_liq`)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $idvac, PDO::PARAM_INT);
                $sql->bindParam(2, $dayvac, PDO::PARAM_STR);
                $sql->bindParam(3, $vacacion, PDO::PARAM_STR);
                $sql->bindParam(4, $prima_vac, PDO::PARAM_STR);
                $sql->bindParam(5, $bonrecrea, PDO::PARAM_STR);
                $sql->bindParam(6, $mes_fecret, PDO::PARAM_STR);
                $sql->bindParam(7, $anio_fecret, PDO::PARAM_STR);
                $sql->bindValue(8, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(9, $id_nomina, PDO::PARAM_INT);
                $sql->bindParam(10, $tipo, PDO::PARAM_INT);
                $sql->execute();
                if (!($cmd->lastInsertId() > 0)) {
                    echo $cdm->errorInfo()[2] . 'VAC';
                }
            }
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        //prima de servicios
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO `seg_liq_prima`(`id_empleado`,`cant_dias`,`val_liq_ps`,`fec_reg`,`id_nomina`, `corte`)
                    VALUES (?, ?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id, PDO::PARAM_INT);
            $sql->bindParam(2, $diasToPriServ, PDO::PARAM_STR);
            $sql->bindParam(3, $prima_sv, PDO::PARAM_STR);
            $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(5, $id_nomina, PDO::PARAM_INT);
            $sql->bindParam(6, $fec_retiro, PDO::PARAM_STR);
            $sql->execute();
            if (!($cmd->lastInsertId() > 0)) {
                echo $cdm->errorInfo()[2] . 'PS';
            }
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        //prima de navidad
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO `seg_liq_prima_nav`(`id_empleado`,`cant_dias`,`val_liq_pv`,`fec_reg`,`id_nomina`, `corte`)
                    VALUES (?, ?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id, PDO::PARAM_INT);
            $sql->bindParam(2, $diasToPriNav, PDO::PARAM_STR);
            $sql->bindParam(3, $prima_nav, PDO::PARAM_STR);
            $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(5, $id_nomina, PDO::PARAM_INT);
            $sql->bindParam(6, $fec_retiro, PDO::PARAM_STR);
            $sql->execute();
            if (!($cmd->lastInsertId() > 0)) {
                echo $sql->errorInfo()[2] . 'PN ';
            }
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        //cesantias
        try {
            $porcentaje = 12;
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO `seg_liq_cesantias`(`id_empleado`,`cant_dias`,`val_cesantias`,`val_icesantias`,`porcentaje_interes`,`fec_reg`,`id_nomina`, `corte`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id, PDO::PARAM_INT);
            $sql->bindParam(2, $diasToCes, PDO::PARAM_STR);
            $sql->bindParam(3, $cesantia, PDO::PARAM_STR);
            $sql->bindParam(4, $icesantia, PDO::PARAM_STR);
            $sql->bindParam(5, $porcentaje, PDO::PARAM_STR);
            $sql->bindValue(6, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(7, $id_nomina, PDO::PARAM_INT);
            $sql->bindParam(8, $fec_retiro, PDO::PARAM_STR);
            $sql->execute();
            if (!($cmd->lastInsertId() > 0)) {
                echo $sql->errorInfo()[2] . 'CES';
            }
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        //salud,pension,arl-> compensatorio, horas extras y bsp 
        $base_ss = $val_compensa + $devhe + $bsp_salarial;
        $key = array_search($id, array_column($ibcant, 'id_empleado'));
        $salud_ant = false !== $key ? $ibcant[$key]['aporte_salud_emp'] : 0;
        $ibc = $salud_ant * 25;
        $base_ps = $base_ss + $ibc;
        $base_ss = $base_ss + $ibc > $smmlv * 25 ? $smmlv * 25 - $ibc : $base_ss;
        $saludempleado = $base_ss * 0.04;
        $pensionempleado = $base_ss * 0.04;
        if ($base_ps < ($smmlv * 4)) {
            $solidpension = 0;
            $porcenps = 0;
        } else if ($base_ps >= ($smmlv * 4)  && $base_ps < ($smmlv * 16)) {
            $solidpension = $base_ps * 0.01;
            $porcenps = 1;
        } else if ($base_ps >= ($smmlv * 16)  && ($base_ps < $smmlv * 17)) {
            $solidpension = $base_ps * 0.012;
            $porcenps = 1.2;
        } else if ($base_ps >= ($smmlv * 17)  && $base_ps < ($smmlv * 18)) {
            $solidpension = $base_ps * 0.014;
            $porcenps = 1.4;
        } else if ($base_ps >= ($smmlv * 18)  && $base_ps < ($smmlv * 19)) {
            $solidpension = $base_ps * 0.016;
            $porcenps = 1.6;
        } else if ($base_ps >= ($smmlv * 19)  && $base_ps < ($smmlv * 20)) {
            $solidpension = $base_ps * 0.018;
            $porcenps = 1.8;
        } else if ($base_ps >= ($smmlv * 20)) {
            $solidpension = $base_ps * 0.02;
            $porcenps = 2;
        }
        $saludempresa = $base_ss * 0.085;
        $pensionempresa = $base_ss * 0.12;
        $key = array_search($id, array_column($riesgos, 'id_empleado'));
        $cot_rlab = $key !== false ? $riesgos[$key]['cotizacion'] : 0;
        $rieslab = $base_ss *  $cot_rlab;
        if ($tipo_emp == 12) {
            $saludempleado = 0;
            $pensionempleado = 0;
            $solidpension = 0;
            $porcenps = 0;
            $saludempresa = 0;
            $pensionempresa = 0;
        }
        if ($tipo_emp == 8) {
            $saludempleado = 0;
            $pensionempleado = 0;
            $solidpension = 0;
            $porcenps = 0;
            $saludempresa = $base_ss * 0.125;
            $pensionempresa = 0;
        }
        $semp = redondeo($saludempleado, 0);
        $pemp = redondeo($pensionempleado, 0);
        $solidpension = redondeo($solidpension, -2);
        $stotal = redondeo($saludempresa + $saludempleado, -2);
        $ptotal = redondeo($pensionempresa + $pensionempleado, -2);
        $rieslab = redondeo($rieslab, -2);
        $saludempleado = $semp;
        $pensionempleado = $pemp;
        $saludempresa = $stotal - $semp;
        $pensionempresa = $ptotal - $pemp;
        $key = array_search($id, array_column($eps, 'id_empleado'));
        $id_eps = false !== $key ? $eps[$key]['id_eps'] : null;
        $key = array_search($id, array_column($afp, 'id_empleado'));
        $id_afp = false !== $key ? $afp[$key]['id_afp'] : null;
        $key = array_search($id, array_column($arl, 'id_empleado'));
        $id_arl = false !== $key ? $arl[$key]['id_arl'] : null;
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO `seg_liq_segsocial_empdo` 
                        (`id_empleado`, `id_eps`, `id_arl`, `id_afp`, `aporte_salud_emp`, `aporte_pension_emp`, 
                        `aporte_solidaridad_pensional`, `porcentaje_ps`, `aporte_salud_empresa`, `aporte_pension_empresa`, 
                        `aporte_rieslab`, `fec_reg`, `id_nomina`, `tipo_liq`,`anio`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id, PDO::PARAM_INT);
            $sql->bindParam(2, $id_eps, PDO::PARAM_INT);
            $sql->bindParam(3, $id_arl, PDO::PARAM_INT);
            $sql->bindParam(4, $id_afp, PDO::PARAM_INT);
            $sql->bindParam(5, $saludempleado, PDO::PARAM_STR);
            $sql->bindParam(6, $pensionempleado, PDO::PARAM_STR);
            $sql->bindParam(7, $solidpension, PDO::PARAM_STR);
            $sql->bindParam(8, $porcenps, PDO::PARAM_STR);
            $sql->bindParam(9, $saludempresa, PDO::PARAM_STR);
            $sql->bindParam(10, $pensionempresa, PDO::PARAM_STR);
            $sql->bindParam(11, $rieslab, PDO::PARAM_STR);
            $sql->bindValue(12, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(13, $id_nomina, PDO::PARAM_INT);
            $sql->bindParam(14, $tipo, PDO::PARAM_INT);
            $sql->bindParam(15, $vigencia, PDO::PARAM_STR);
            $sql->execute();
            if (!($cmd->lastInsertId() > 0)) {
                echo $sql->errorInfo()[2] . 'SS';
            }
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        //Parafiscales
        $base_pf = $base_ss + $vacacion + $prima_sv;
        $sena = $base_pf * 0.02;
        $icbf = $base_pf * 0.03;
        $comfam = $base_pf * 0.04;
        if ($tipo_emp == 12 || $tipo_emp == 8) {
            $sena = 0;
            $icbf = 0;
            $comfam = 0;
        }
        $sena = redondeo($sena, -2);
        $icbf = redondeo($icbf, -2);
        $comfam = redondeo($comfam, -2);

        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO `seg_liq_parafiscales` 
                        (`id_empleado`, `val_sena`, `val_icbf`, `val_comfam`, `fec_reg`,`id_nomina`, `tipo_liq`, `anio_pfis`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id, PDO::PARAM_INT);
            $sql->bindParam(2, $sena, PDO::PARAM_STR);
            $sql->bindParam(3, $icbf, PDO::PARAM_STR);
            $sql->bindParam(4, $comfam, PDO::PARAM_STR);
            $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(6, $id_nomina, PDO::PARAM_INT);
            $sql->bindParam(7, $tipo, PDO::PARAM_INT);
            $sql->bindParam(8, $vigencia, PDO::PARAM_STR);
            $sql->execute();
            if (!($cmd->lastInsertId() > 0)) {
                echo $sql->errorInfo()[2] . 'PF';
            }
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        // Ingresar valores liquidados
        try {
            $cero = 0;
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO `seg_liq_dlab_auxt` 
                        (`id_empleado`, `dias_liq`, `val_liq_dias`, `val_liq_auxt`,`aux_alim`,`g_representa`,`horas_ext`, `fec_reg`, `id_nomina`,`tipo_liq`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id, PDO::PARAM_INT);
            $sql->bindParam(2, $cero, PDO::PARAM_INT);
            $sql->bindParam(3, $cero, PDO::PARAM_STR);
            $sql->bindParam(4, $cero, PDO::PARAM_STR);
            $sql->bindParam(5, $cero, PDO::PARAM_STR);
            $sql->bindParam(6, $gasrep, PDO::PARAM_STR);
            $sql->bindParam(7, $devhe, PDO::PARAM_STR);
            $sql->bindValue(8, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(9, $id_nomina, PDO::PARAM_INT);
            $sql->bindParam(10, $tipo, PDO::PARAM_INT);
            $sql->execute();
            if (!($cmd->lastInsertId() > 0)) {
                echo $sql->errorInfo()[2] . 'LQS';
            }
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        //retencion en la fuente
        $pagoxdependiente = 0;
        $key = array_search($id, array_column($pagoxdpte, 'id_empleado'));
        if (false !== $key) {
            $pagoxdependiente = ($base_ss + $vacacion + $prima_vac + $bonrecrea + $gasrep) * 0.1;
            $maxpagoxdependiente = 32 * $uvt;
            if ($pagoxdependiente > $maxpagoxdependiente) {
                $pagoxdependiente = $maxpagoxdependiente;
            }
        }
        $valrf = $base_ss + $vacacion + $prima_vac + $bonrecrea + $gasrep - $saludempleado - $pensionempleado - $solidpension - $pagoxdependiente;
        $valdpurado =  $valrf - ($valrf * 0.25);
        $inglabuvt = $valdpurado / $uvt;
        if ($inglabuvt < 95) {
            $retencion = 0;
        } else if ($inglabuvt >= 95 && $inglabuvt < 150) {
            $uvtx = $inglabuvt - 95;
            $retencion = $uvt * $uvtx * 0.19;
        } else if ($inglabuvt >= 150 && $inglabuvt < 360) {
            $uvtx = $inglabuvt - 150;
            $retencion = ($uvt * $uvtx * 0.28) + (10 * $uvt);
        } else if ($inglabuvt >= 360 && $inglabuvt < 640) {
            $uvtx = $inglabuvt - 360;
            $retencion = ($uvt * $uvtx * 0.33) + (69 * $uvt);
        } else if ($inglabuvt >= 640 && $inglabuvt < 945) {
            $uvtx = $inglabuvt - 640;
            $retencion = ($uvt * $uvtx * 0.35) +  (162 * $uvt);
        } else if ($inglabuvt >= 945 && $inglabuvt < 2300) {
            $uvtx = $inglabuvt - 945;
            $retencion = ($uvt * $uvtx * 0.37) + (268 * $uvt);
        } else if ($inglabuvt >= 2300) {
            $uvtx = $inglabuvt - 2300;
            $retencion = ($uvt * $uvtx * 0.39) + (770 * $uvt);
        }

        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO `seg_retencion_fte` (`id_empleado`, `val_ret`, `fec_reg`, `base`,`id_nomina`) 
                        VALUES (?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id, PDO::PARAM_INT);
            $sql->bindParam(2, $retencion, PDO::PARAM_STR);
            $sql->bindValue(3, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(4, $valdpurado, PDO::PARAM_STR);
            $sql->bindParam(5, $id_nomina, PDO::PARAM_INT);
            $sql->execute();
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        //neto a pagar
        $salarioneto = $devhe + $val_compensa + $bsp_salarial + $vacacion + $prima_vac + $bonrecrea + $gasrep + $prima_nav + $prima_sv + $cesantia + $icesantia - $saludempleado - $pensionempleado - $solidpension - $retencion;
        $fpag = '1';
        $mpag = '47';
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO `seg_liq_salario` (`id_empleado`, `val_liq`, `forma_pago`, `metodo_pago`, `fec_reg`, `id_nomina`) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id, PDO::PARAM_INT);
            $sql->bindParam(2, $salarioneto, PDO::PARAM_STR);
            $sql->bindParam(3, $fpag, PDO::PARAM_STR);
            $sql->bindParam(4, $mpag, PDO::PARAM_STR);
            $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(6, $id_nomina, PDO::PARAM_INT);
            $sql->execute();
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO `seg_empleados_retirados`(`id_empleado`, `fec_liq`, `id_user_reg`, `fec_reg`, `id_nomina`)
                    VALUES (?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id, PDO::PARAM_INT);
            $sql->bindValue(2, $date->format('Y-m-d'));
            $sql->bindParam(3, $id_user, PDO::PARAM_STR);
            $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(5, $id_nomina, PDO::PARAM_INT);
            $sql->execute();
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
        $c++;
    }
} else {
    echo 'No hay empleados para liquidar';
}
if ($c > 0) {
    echo 'ok';
} else {
    echo 'No se liquidó ningún empleado';
}
function calcularDias($fechaInicial, $fechaFinal)
{
    $fechaInicial = strtotime($fechaInicial);
    $fechaFinal = strtotime($fechaFinal);
    $dias360 = 0;
    if (!($fechaInicial > $fechaFinal)) {
        while ($fechaInicial < $fechaFinal) {
            $dias360 += 30; // Agregar 30 días por cada mes
            $fechaInicial = strtotime('+1 month', $fechaInicial);
        }

        // Agregar los días restantes después del último mes completo
        $dias360 += ($fechaFinal - $fechaInicial) / (60 * 60 * 24);
        $dias360 = $dias360 + 1;
    }
    return $dias360;
}
function redondeo($value, $places)
{
    $mult = pow(10, abs($places));
    return $places < 0 ? ceil($value / $mult) * $mult : ceil($value * $mult) / $mult;
}


function PromedioHoras($feci, $fecf, $id)
{
    include '../../conexion.php';
    $promedio = 0;
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "SELECT 
                    `id_nomina`
                FROM 
                    (SELECT
                        `id_nomina`
                        , CONCAT_WS('-', `vigencia`
                        , `mes`, '01') AS `fecha`
                        , `estado`
                        , `tipo`
                    FROM
                        `seg_nominas`
                    WHERE (`estado` >= 5 AND `tipo` = 'N' AND `id_nomina` > 0)) AS `t1`
                WHERE `t1`.`fecha` BETWEEN '$feci' AND '$fecf'";
        $rs = $cmd->query($sql);
        $ids = $rs->fetchAll();

        if (!empty($ids)) {
            $total = count($ids);
            $ids = implode(',', array_column($ids, 'id_nomina'));
            $sql = "SELECT 
                        SUM(`liquidado`) AS `total`
                    FROM 
                        (SELECT
                            SUM(`seg_liq_horex`.`val_liq`) AS `liquidado`
                            , `seg_liq_horex`.`id_nomina`
                        FROM
                            `seg_liq_horex`
                            INNER JOIN `seg_horas_ex_trab` 
                                ON (`seg_liq_horex`.`id_he_lab` = `seg_horas_ex_trab`.`id_he_trab`)
                        WHERE (`seg_horas_ex_trab`.`id_empleado` = $id AND `seg_liq_horex`.`id_nomina` IN ($ids))
                    GROUP BY `seg_liq_horex`.`id_nomina`) AS `t2`";
            $rs = $cmd->query($sql);
            $valor = $rs->fetch();
            if (!empty($valor)) {
                $promedio = $valor['total'] / $total;
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    return $promedio;
}