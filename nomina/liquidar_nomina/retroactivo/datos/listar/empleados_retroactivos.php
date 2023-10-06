<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../../index.php");</script>';
    exit();
}
include '../../../../../conexion.php';
include '../../../../../permisos.php';
$id_retroactivo = isset($_POST['id_reac']) ? $_POST['id_reac'] : exit('Acceso no permitido');
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_retroactivo`, `fec_inicio`, `fec_final`, `meses`, `porcentaje`, `observaciones`, `seg_retroactivos`.`vigencia`,`seg_retroactivos`.`estado`
            FROM
            `seg_retroactivos`
            INNER JOIN `seg_incremento_salario` 
                ON (`seg_retroactivos`.`id_incremento` = `seg_incremento_salario`.`id_inc`)
            WHERE `id_retroactivo` = $id_retroactivo";
    $rs = $cmd->query($sql);
    $retroactivo = $rs->fetch();
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
    $val_vig = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$key = array_search('1', array_column($val_vig, 'id_concepto'));
$smmlv = $val_vig[$key]['valor'];
$fecIni = $retroactivo['fec_inicio'];
$fecFin = $retroactivo['fec_final'];
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
if (!empty($retroactivo)) {
    $fec_inicio = explode('-', $retroactivo['fec_inicio']);
    $fec_final = explode('-', $retroactivo['fec_final']);
    $mes_ini = $fec_inicio[1];
    $mes_fin = $fec_final[1];
    $vigencia = $retroactivo['vigencia'];
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
} else {
    exit('retroactivo no existe');
}

$datos = [];
if (!empty($empleados)) {
    foreach ($empleados as $e) {
        $key = array_search($e['id_empleado'], array_column($salarios, 'id_empleado'));
        if ($key !== false) {
            $salario = $salarios[$key]['salario_basico'];
        } else {
            $salario = 0;
        }
        if ($salario > $smmlv) {
            if ($e['estado'] == '1') {
                $estado = '<span class="badge badge-success">Activo</span>';
            } else {
                $estado = '<span class="badge badge-secondary">Inactivo</span>';
            }
            $datos[] = array(
                'check' => '<div class="text-center listado"><input type="checkbox" name="id_empleado[]" value="' . $e['id_empleado'] . '" checked></div>',
                'doc' => $e['no_documento'],
                'nombre' => mb_strtoupper($e['apellido1'] . ' ' . $e['apellido2'] . ' ' . $e['nombre1'] . ' ' . $e['nombre2']),
                'estado' => '<div class="text-center">' . mb_strtoupper($estado) . '</div>',
                'sindicato' => mb_strtoupper($e['nom_sindicato']),
            );
        }
    }
}
$data = [
    'data' => $datos
];
echo json_encode($data);
