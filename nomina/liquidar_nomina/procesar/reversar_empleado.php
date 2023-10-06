<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_nomina = isset($_POST['id_nomina']) ? $_POST['id_nomina'] : exit('Acción no permitida');
$id_empleado = $_POST['id_empleado'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `tipo` FROM  `seg_nominas` WHERE (`id_nomina`  = $id_nomina) LIMIT 1";
    $rs = $cmd->query($sql);
    $nomina = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `seg_vacaciones`.`id_empleado`
                , `seg_liq_vac`.`id_vac`
                , `seg_liq_vac`.`id_nomina`
            FROM
                `seg_liq_vac`
                INNER JOIN `seg_vacaciones` 
                    ON (`seg_liq_vac`.`id_vac` = `seg_vacaciones`.`id_vac`)
            WHERE (`seg_vacaciones`.`id_empleado` = $id_empleado AND `seg_liq_vac`.`id_nomina`  = $id_nomina)";
    $rs = $cmd->query($sql);
    $idVacs = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$ids = [];
if (!empty($idVacs)) {
    foreach ($idVacs as $iv) {
        $ids[] = $iv['id_vac'] != '' ? $iv['id_vac'] : 0;
    }
}
$idVacs = implode(',', $ids);
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `seg_indemniza_vac`.`id_empleado`
                , `seg_liq_indemniza_vac`.`id_indemnizacion`
                , `seg_liq_indemniza_vac`.`id_nomina`
            FROM
                `seg_liq_indemniza_vac`
                INNER JOIN `seg_indemniza_vac` 
                    ON (`seg_liq_indemniza_vac`.`id_indemnizacion` = `seg_indemniza_vac`.`id_indemniza`)
            WHERE (`seg_indemniza_vac`.`id_empleado` = $id_empleado AND `seg_liq_indemniza_vac`.`id_nomina`  = $id_nomina)";
    $rs = $cmd->query($sql);
    $idIndemVac = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$ids = [];
if (!empty($idIndemVac)) {
    foreach ($idIndemVac as $iv) {
        $ids[] = $iv['id_indemnizacion'] != '' ? $iv['id_indemnizacion'] : 0;
    }
}
$id_inom = 0;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `seg_liq_compesatorio` SET `estado` = 1, `id_nomina` = 0 WHERE `id_nomina`  = $id_nomina AND `id_empleado` = $id_empleado";
    $sql = $cmd->prepare($sql);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `seg_liq_horex`.`id_he_lab`
                , `seg_liq_horex`.`id_nomina`
                , `seg_horas_ex_trab`.`id_empleado`
            FROM
                `seg_liq_horex`
                INNER JOIN `seg_horas_ex_trab` 
                    ON (`seg_liq_horex`.`id_he_lab` = `seg_horas_ex_trab`.`id_he_trab`)
            WHERE (`seg_liq_horex`.`id_nomina`  = $id_nomina AND `seg_horas_ex_trab`.`id_empleado` = $id_empleado)";
    $rs = $cmd->query($sql);
    $horas = $rs->fetchAll(PDO::FETCH_ASSOC);
    $idhe = [];
    foreach ($horas as $h) {
        $idhe[] = $h['id_he_lab'] != '' ? $h['id_he_lab'] : 0;
    }
    if (!empty($idhe)) {
        $idhe = implode(',', $idhe);
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            if ($nomina['tipo'] == 'N') {
                $estado = 1;
            } else {
                $estado = 2;
            }
            $sql = "UPDATE `seg_horas_ex_trab` SET `tipo` = $estado WHERE `id_he_trab` IN ($idhe)";
            $sql = $cmd->prepare($sql);
            $sql->execute();
            if (!($sql->rowCount() > 0)) {
                echo $sql->errorInfo()[2];
            }
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
    } else {
        $idhe = 0;
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `seg_licenciasmp`.`id_empleado`
                , `seg_liq_licmp`.`id_licmp`
                , `seg_liq_licmp`.`id_nomina`
            FROM
                `seg_liq_licmp`
                INNER JOIN `seg_licenciasmp` 
                    ON (`seg_liq_licmp`.`id_licmp` = `seg_licenciasmp`.`id_licmp`)
            WHERE (`seg_licenciasmp`.`id_empleado` = $id_empleado AND `seg_liq_licmp`.`id_nomina`  = $id_nomina)";
    $rs = $cmd->query($sql);
    $licmp = $rs->fetchAll(PDO::FETCH_ASSOC);
    $idlicmp = [];
    foreach ($licmp as $l) {
        $idlicmp[] = $l['id_licmp'] != '' ? $l['id_licmp'] : 0;
    }
    if (!empty($idlicmp)) {
        $idlicmp = implode(',', $idlicmp);
    } else {
        $idlicmp = 0;
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `seg_licenciasnr`.`id_empleado`
                , `seg_liq_licnr`.`id_licnr`
                , `seg_liq_licnr`.`id_nomina`
            FROM
                `seg_liq_licnr`
                INNER JOIN `seg_licenciasnr` 
                    ON (`seg_liq_licnr`.`id_licnr` = `seg_licenciasnr`.`id_licnr`)
            WHERE (`seg_licenciasnr`.`id_empleado` = $id_empleado
                AND `seg_liq_licnr`.`id_nomina` = $id_nomina)";
    $rs = $cmd->query($sql);
    $licnr = $rs->fetchAll(PDO::FETCH_ASSOC);
    $idlicnr = [];
    foreach ($licnr as $l) {
        $idlicnr[] = $l['id_licnr'] != '' ? $l['id_licnr'] : 0;
    }
    if (!empty($idlicnr)) {
        $idlicnr = implode(',', $idlicnr);
    } else {
        $idlicnr = 0;
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `seg_incapacidad`.`id_empleado`
                , `seg_liq_incap`.`id_incapacidad`
                , `seg_liq_incap`.`id_nomina`
            FROM
                `seg_liq_incap`
                INNER JOIN `seg_incapacidad` 
                    ON (`seg_liq_incap`.`id_incapacidad` = `seg_incapacidad`.`id_incapacidad`)
            WHERE (`seg_incapacidad`.`id_empleado` = $id_empleado AND `seg_liq_incap`.`id_nomina` = $id_nomina)";
    $rs = $cmd->query($sql);
    $incap = $rs->fetchAll(PDO::FETCH_ASSOC);
    $idincap = [];
    foreach ($incap as $ic) {
        $idincap[] = $ic['id_incapacidad'] != '' ? $ic['id_incapacidad'] : 0;
    }
    if (!empty($idincap)) {
        $idincap = implode(',', $idincap);
    } else {
        $idincap = 0;
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `seg_libranzas`.`id_empleado`
                , `seg_liq_libranza`.`id_libranza`
                , `seg_liq_libranza`.`id_nomina`
            FROM
                `seg_liq_libranza`
                INNER JOIN `seg_libranzas` 
                    ON (`seg_liq_libranza`.`id_libranza` = `seg_libranzas`.`id_libranza`)
            WHERE (`seg_libranzas`.`id_empleado` = $id_empleado AND `seg_liq_libranza`.`id_nomina` = $id_nomina)";
    $rs = $cmd->query($sql);
    $lib = $rs->fetchAll(PDO::FETCH_ASSOC);
    $idlib = [];
    foreach ($lib as $lb) {
        $idlib[] = $lb['id_libranza'] != '' ? $lb['id_libranza'] : 0;
    }
    if (!empty($idlib)) {
        $idlib = implode(',', $idlib);
    } else {
        $idlib = 0;
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `seg_embargos`.`id_empleado`
                , `seg_liq_embargo`.`id_embargo`
                , `seg_liq_embargo`.`id_nomina`
            FROM
                `seg_liq_embargo`
                INNER JOIN `seg_embargos` 
                    ON (`seg_liq_embargo`.`id_embargo` = `seg_embargos`.`id_embargo`)
            WHERE (`seg_embargos`.`id_empleado` = $id_empleado AND `seg_liq_embargo`.`id_nomina` = $id_nomina)";
    $rs = $cmd->query($sql);
    $emb = $rs->fetchAll(PDO::FETCH_ASSOC);
    $idemb = [];
    foreach ($emb as $eb) {
        $idemb[] = $eb['id_embargo'] != '' ? $eb['id_embargo'] : 0;
    }
    if (!empty($idemb)) {
        $idemb = implode(',', $idemb);
    } else {
        $idemb = 0;
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `seg_cuota_sindical`.`id_empleado`
                , `seg_liq_sindicato_aportes`.`id_cuota_sindical`
                , `seg_liq_sindicato_aportes`.`id_nomina`
            FROM
                `seg_liq_sindicato_aportes`
                INNER JOIN `seg_cuota_sindical` 
                    ON (`seg_liq_sindicato_aportes`.`id_cuota_sindical` = `seg_cuota_sindical`.`id_cuota_sindical`)
            WHERE (`seg_cuota_sindical`.`id_empleado` = $id_empleado AND `seg_liq_sindicato_aportes`.`id_nomina` = $id_nomina)";
    $rs = $cmd->query($sql);
    $sind = $rs->fetchAll(PDO::FETCH_ASSOC);
    $idsind = [];
    foreach ($sind as $sn) {
        $idsind[] = $sn['id_cuota_sindical'] != '' ? $sn['id_cuota_sindical'] : 0;
    }
    if (!empty($idsind)) {
        $idsind = implode(',', $idsind);
    } else {
        $idsind = 0;
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$idsIndemVac = implode(',', $ids);
if (!empty($idVacs)) {
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        if ($nomina['tipo'] == 'N' || $nomina['tipo'] == 'VC') {
            $sql = "UPDATE `seg_vacaciones` SET `estado` = '1' WHERE `id_vac` IN ($idVacs)";
        } else {
            $sql = "DELETE FROM `seg_vacaciones` WHERE `id_vac` IN ($idVacs)";
        }
        if ($nomina['tipo'] != 'RA') {
            $sql = $cmd->prepare($sql);
            $sql->execute();
            if (!($sql->rowCount() > 0)) {
                echo $sql->errorInfo()[2];
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
} else {
    $idVacs = 0;
}
if (!empty($idsIndemVac)) {
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "UPDATE `seg_indemniza_vac` SET `estado` = '1' WHERE `id_indemniza` IN ($idsIndemVac)";
        $sql = $cmd->prepare($sql);
        $sql->execute();
        if (!($sql->rowCount() > 0)) {
            echo $sql->errorInfo()[2];
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
} else {
    $idsIndemVac = 0;
}
$dels = 0;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "DELETE FROM `seg_liq_bsp` WHERE `id_nomina` = $id_nomina AND `id_empleado`= $id_empleado";
    $sql = $cmd->prepare($sql);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        $dels++;
    }
    $sql = "DELETE FROM `seg_liq_horex` WHERE `id_nomina` = $id_nomina AND `id_he_lab` IN ($idhe)";
    $sql = $cmd->prepare($sql);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        $dels++;
    }
    $sql = "DELETE FROM `seg_liq_licmp` WHERE `id_nomina` = $id_nomina AND `id_licmp` IN ($idlicmp)";
    $sql = $cmd->prepare($sql);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        $dels++;
    }
    $sql = "DELETE FROM `seg_liq_licnr` WHERE `id_nomina` = $id_nomina AND `id_licnr` IN ($idlicnr)";
    $sql = $cmd->prepare($sql);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        $dels++;
    }
    $sql = "DELETE FROM `seg_liq_vac` WHERE `id_nomina` = $id_nomina AND `id_vac` IN ($idVacs)";
    $sql = $cmd->prepare($sql);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        $dels++;
    }
    $sql = "DELETE FROM `seg_liq_incap` WHERE `id_nomina` = $id_nomina AND `id_incapacidad` IN ($idincap)";
    $sql = $cmd->prepare($sql);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        $dels++;
    }
    $sql = "DELETE FROM `seg_liq_parafiscales` WHERE `id_nomina` = $id_nomina AND `id_empleado`= $id_empleado";
    $sql = $cmd->prepare($sql);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        $dels++;
    }
    $sql = "DELETE FROM `seg_liq_dias_lab` WHERE `id_nomina` = $id_nomina AND `id_empleado`= $id_empleado";
    $sql = $cmd->prepare($sql);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        $dels++;
    }
    $sql = "DELETE FROM `seg_liq_segsocial_empdo` WHERE `id_nomina` = $id_nomina AND `id_empleado`= $id_empleado";
    $sql = $cmd->prepare($sql);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        $dels++;
    }
    $sql = "DELETE FROM `seg_liq_prestaciones_sociales` WHERE `id_nomina` = $id_nomina AND `id_empleado`= $id_empleado";
    $sql = $cmd->prepare($sql);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        $dels++;
    }
    $sql = "DELETE FROM `seg_liq_libranza` WHERE `id_nomina` = $id_nomina AND `id_libranza` IN ($idlib)";
    $sql = $cmd->prepare($sql);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        $dels++;
    }
    $sql = "DELETE FROM `seg_liq_embargo` WHERE `id_nomina` = $id_nomina AND `id_embargo` IN ($idemb)";
    $sql = $cmd->prepare($sql);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        $dels++;
    }
    $sql = "DELETE FROM `seg_liq_sindicato_aportes` WHERE `id_nomina` = $id_nomina AND `id_cuota_sindical` IN ($idsind)";
    $sql = $cmd->prepare($sql);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        $dels++;
    }
    $sql = "DELETE FROM `seg_retencion_fte` WHERE `id_nomina` = $id_nomina AND `id_empleado`= $id_empleado";
    $sql = $cmd->prepare($sql);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        $dels++;
    }
    $sql = "DELETE FROM `seg_liq_salario` WHERE `id_nomina` = $id_nomina AND `id_empleado`= $id_empleado";
    $sql = $cmd->prepare($sql);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        $dels++;
    }
    $sql = "DELETE FROM `seg_liq_indemniza_vac` WHERE `id_nomina` = $id_nomina AND `id_indemnizacion` IN ($idsIndemVac)";
    $sql = $cmd->prepare($sql);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        $dels++;
    }
    $sql = "DELETE FROM `seg_liq_prima` WHERE `id_nomina` = $id_nomina AND `id_empleado`= $id_empleado";
    $sql = $cmd->prepare($sql);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        $dels++;
    }
    $sql = "DELETE FROM `seg_empleados_retirados` WHERE `id_nomina` = $id_nomina AND `id_empleado`= $id_empleado";
    $sql = $cmd->prepare($sql);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        $dels++;
    }
    $sql = "DELETE FROM `seg_liq_prima_nav` WHERE `id_nomina` = $id_nomina AND `id_empleado`= $id_empleado";
    $sql = $cmd->prepare($sql);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        $dels++;
    }
    $sql = "DELETE FROM `seg_liq_dlab_auxt` WHERE `id_nomina` = $id_nomina AND `id_empleado`= $id_empleado";
    $sql = $cmd->prepare($sql);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        $dels++;
    }
    if ($dels > 0) {
        echo 'ok';
    } else {
        echo 'No se ha podido eliminar el registro solicitado en la nómina';
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
