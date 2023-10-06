<?php

session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
$anio = $_SESSION['vigencia'];
$mes = isset($_POST['slcMesLiqNom']) ? $_POST['slcMesLiqNom'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, salario_integral, no_documento, CONCAT(nombre1, ' ', nombre2, ' ', apellido1, ' ', apellido2) as nombre 
            FROM seg_empleado";
    $rs = $cmd->query($sql);
    $empleado = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, CONCAT(anio, periodo) AS periodo
            FROM seg_liq_prima_nav
            WHERE anio = '$anio'";
    $rs = $cmd->query($sql);
    $primliq = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$ini = '01';
$fin = '11';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_empleado`, SUM(`cant_dias`) AS `diasxsem`
            FROM
                (SELECT `id_empleado`, `cant_dias`, `mes`, `anio`
                FROM `seg_liq_dias_lab`
                WHERE `mes` BETWEEN '$ini' AND '$fin') AS t
            GROUP BY `id_empleado`";
    $rs = $cmd->query($sql);
    $diaslab = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT anio, id_concepto, valor
            FROM
                seg_valxvigencia
            INNER JOIN con_vigencias 
                ON (seg_valxvigencia.id_vigencia = con_vigencias.id_vigencia)
            WHERE anio = '$anio'";
    $rs = $cmd->query($sql);
    $valxvig = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$er = '';
$er .= '
  <div class="table-responsive w-100">
  <table class="table table-striped table-bordered table-sm">
  <thead>
    <tr>
      <th scope="col">Documento</th>
      <th scope="col">Nombre</th>
      <th scope="col">Estado</th>
    </tr>
  </thead>
  <tbody>';
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$liquidados = 0;
$perido = 2;
if (isset($_REQUEST['check'])) {
    $list_liquidar = $_REQUEST['check'];
    foreach ($list_liquidar as $i) {
        $key = array_search($i, array_column($empleado, 'id_empleado'));
        if (false !== $key) {
            $sal_integ = $empleado[$key]['salario_integral'];
        } else {
            $sal_integ = null;
        }
        if ($sal_integ != 1) {
            $key = array_search($i, array_column($primliq, 'id_empleado'));
            if (false === $key) {
                $salbase = $_POST['numSalBas_' . $i];
                $key = array_search($i, array_column($diaslab, 'id_empleado'));
                $diaxsem = false !== $key ? $diaslab[$key]['diasxsem'] : 0;
                $key = array_search('1', array_column($valxvig, 'id_concepto'));
                $smmlv = false !== $key ? $valxvig[$key]['valor'] : 0;
                $key = array_search('2', array_column($valxvig, 'id_concepto'));
                if (false !== $key) {
                    $auxtrans = $valxvig[$key]['valor'];
                }
                if ($salbase >= $smmlv * 2) {
                    $auxtrans = 0;
                }
                $key = array_search('3', array_column($valxvig, 'id_concepto'));
                $auxali = false !== $key ? $valxvig[$key]['valor'] : 0;
                $key = array_search('7', array_column($valxvig, 'id_concepto'));
                $bbs = false !== $key ? $valxvig[$key]['valor'] : 0;
                $gasrep = 0; //si hay gastos de representacion se debe hacer la consulta de ese valor para cada empleado
                $bsp = (($salbase + $gasrep) <= $bbs ? ($salbase + $gasrep) * 0.5 : ($salbase + $gasrep) * 0.35);
                $primservicio = ($salbase + $auxtrans + $auxali + $bsp / 12) / 2;
                $primvacacion  = (($salbase + $gasrep + $auxtrans + $auxali + $bsp / 12 + $primservicio / 12) * 15) / 30;
                $primanavidad = $salbase + $gasrep + $auxtrans + $auxali + ($bsp / 12) + ($primservicio / 12) + ($primvacacion / 12);
                $totdays = $diaxsem + 30;
                $primanav = ($primanavidad / 360) * $totdays;
                $corte = date('Y-m-d', strtotime($anio . '-12-31'));
                try {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                    $sql = "INSERT INTO `seg_liq_prima_nav` (`id_empleado`, `cant_dias`, `val_liq_pv`, `periodo`, `anio`, `corte`, `fec_reg`)
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $i, PDO::PARAM_INT);
                    $sql->bindParam(2, $totdays, PDO::PARAM_STR);
                    $sql->bindParam(3, $primanav, PDO::PARAM_STR);
                    $sql->bindParam(4, $perido, PDO::PARAM_STR);
                    $sql->bindParam(5, $anio, PDO::PARAM_STR);
                    $sql->bindParam(6, $corte, PDO::PARAM_STR);
                    $sql->bindValue(7, $date->format('Y-m-d H:i:s'));
                    $sql->execute();
                    $key = array_search($i, array_column($empleado, 'id_empleado'));
                    if (false !== $key) {
                        $cc = $empleado[$key]['no_documento'];
                        $nombre = $empleado[$key]['nombre'];
                    } else {
                        $cc = '';
                        $nombre = '';
                    }
                    $er .= '<tr>'
                        . '<td>' . $cc . '</td>'
                        . '<td>' . mb_strtoupper($nombre) . '</td>';
                    if ($cmd->lastInsertId() > 0) {
                        $liquidados++;
                        $er .= '<td>Liquidado</td>';
                    } else {
                        $er .=  '<td>' . print_r($cmd->errorInfo()) . '</td>';
                    }
                    $er .= '</tr>';
                    $cmd = null;
                } catch (PDOException $e) {
                    $res = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                }
            }
        }
    }
}
$er .= '</tbody>
        </table> 
        <center><a id="btnDetallesLiqs" class="btn btn-link" href="detalles_prima_navidad.php?per=' . $perido . '">Detalles</a></center>';
if ($liquidados == 0) {
    echo '0';
} else {
    echo $er;
}
