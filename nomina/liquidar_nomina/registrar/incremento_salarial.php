<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
$incremento = isset($_POST['valorIncr']) ? $_POST['valorIncr'] : exit('Acceso denegado');
$vigencia = $_SESSION['vigencia'];
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
    $val_vig = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$key = array_search('1', array_column($val_vig, 'id_concepto'));
$smmlv = $val_vig[$key]['valor'];
$fecIni = $vigencia . '-01-01';
$fecFin = $vigencia . date('-m-d');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_nomina` 
            FROM 
                (SELECT 
                    `id_nomina`,DATE_FORMAT(CONCAT_WS('-', `vigencia`,`mes`,'01'),'%Y-%m-%d') AS `fecha`
                FROM `seg_nominas` 
                WHERE `tipo` = 'N' AND `id_nomina` <> 0) AS `t1`
            WHERE `fecha` BETWEEN  '$fecIni' AND '$fecFin'";
    $rs = $cmd->query($sql);
    $ids_nominas = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$ids_nominas = !empty($ids_nominas) ? implode(',', array_column($ids_nominas, 'id_nomina')) : -1;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_salario`
                , `id_empleado`
                , `salario_basico`
            FROM
                `seg_salarios_basico` 
            WHERE `id_salario` IN (SELECT MAX(`id_salario`) FROM `seg_salarios_basico` GROUP BY `id_empleado`)";
    $rs = $cmd->query($sql);
    $salarios = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                    `seg_empleado`.`id_empleado`
                    , `seg_empleado`.`no_documento`
                    , `seg_empleado`.`apellido1`
                    , `seg_empleado`.`apellido2`
                    , `seg_empleado`.`nombre1`
                    , `seg_empleado`.`nombre2`
                    , `seg_empleado`.`estado`
                    , `seg_cuota_sindical`.`id_sindicato`
                    , `seg_sindicatos`.`nom_sindicato`
                    , `seg_liq_dlab_auxt`.`dias_liq`
                    , `seg_liq_dlab_auxt`.`tipo_liq`
                    , `seg_liq_dlab_auxt`.`mes_liq`
                    , `seg_liq_dlab_auxt`.`anio_liq`
                FROM
                    `seg_empleado`
                    LEFT JOIN `seg_cuota_sindical` 
                        ON (`seg_cuota_sindical`.`id_empleado` = `seg_empleado`.`id_empleado`)
                    LEFT JOIN `seg_sindicatos` 
                        ON (`seg_cuota_sindical`.`id_sindicato` = `seg_sindicatos`.`id_sindicato`)
                    INNER JOIN `seg_liq_dlab_auxt` 
                        ON (`seg_liq_dlab_auxt`.`id_empleado` = `seg_empleado`.`id_empleado`)
                WHERE `seg_liq_dlab_auxt`.`dias_liq` > 0 AND `seg_liq_dlab_auxt`.`id_nomina` IN ($ids_nominas)
                GROUP BY `seg_empleado`.`id_empleado`";
    $rs = $cmd->query($sql);
    $empleados = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$estado = 1;
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$id_user = $_SESSION['id_user'];
$cantidad = 0;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "INSERT INTO `seg_incremento_salario`
                (`porcentaje`, `vigencia`, `fecha`, `estado`, `fec_reg`, `id_user_reg`)
            VALUES (?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $incremento, PDO::PARAM_STR);
    $sql->bindParam(2, $vigencia, PDO::PARAM_INT);
    $sql->bindParam(3, $fecFin, PDO::PARAM_STR);
    $sql->bindParam(4, $estado, PDO::PARAM_INT);
    $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
    $sql->bindParam(6, $id_user, PDO::PARAM_INT);
    $sql->execute();
    $id_inc = $cmd->lastInsertId();
    if ($id_inc > 0) {
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $sql = "INSERT INTO `seg_salarios_basico`
                                (`id_empleado`, `vigencia`, `salario_basico`, `fec_reg`, `id_inc`)
                    VALUES (?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id_empleado, PDO::PARAM_INT);
            $sql->bindParam(2, $vigencia, PDO::PARAM_INT);
            $sql->bindParam(3, $salario, PDO::PARAM_STR);
            $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(5, $id_inc, PDO::PARAM_INT);
            foreach ($empleados as $e) {
                $id_empleado = $e['id_empleado'];
                $key = array_search($id_empleado, array_column($salarios, 'id_empleado'));
                if ($key !== false) {
                    $salario = $salarios[$key]['salario_basico'];
                } else {
                    $salario = 0;
                }
                if ($salario > $smmlv) {
                    $salario = $salario + ($salario * ($incremento / 100));
                    $salario = redondeo($salario);
                    $sql->execute();
                    if ($cmd->lastInsertId() > 0) {
                        $cantidad++;
                    } else {
                        echo $sql->errorInfo()[2];
                    }
                }
            }

            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
    } else {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if ($cantidad > 0) {
    echo 'ok';
} else {
    echo 'No se realizó ningun incremento salarial';
}

function redondeo($numero)
{
    return round($numero, 0, PHP_ROUND_HALF_UP);
}
