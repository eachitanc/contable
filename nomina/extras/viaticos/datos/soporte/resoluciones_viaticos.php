<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../../index.php");</script>';
    exit();
}
$grupo = isset($_POST['grupo']) ? $_POST['grupo'] : exit('Acción no pemitida');
$vigencia = $_SESSION['vigencia'];
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
include '../../../../../conexion.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `grupo`, `id_resol_viat`
            FROM
                `seg_resolucion_viaticos`
            WHERE `grupo` = '$grupo'";
    $rs = $cmd->query($sql);
    $idXgrupo = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$values = [];
$idsresol = '0';
foreach ($idXgrupo as $ig) {
    $idsresol .= ',' . $ig['id_resol_viat'];
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
            `seg_resolucion_viaticos`.`id_resol_viat`
            , `seg_resolucion_viaticos`.`no_resolucion`
            , `seg_empleado`.`genero`
            , `seg_empleado`.`id_empleado`
            , CONCAT_WS(' ', `seg_empleado`.`nombre1`, `seg_empleado`.`nombre2`, `seg_empleado`.`apellido1`, `seg_empleado`.`apellido2`) AS `nombre_empleado`
            , `seg_cargo_empleado`.`descripcion_carg`
            , `seg_sedes_empresa`.`nombre`
            , `seg_empleado`.`no_documento`
            , `seg_resolucion_viaticos`.`destino`
            , `seg_resolucion_viaticos`.`fec_inicia`
            , `seg_resolucion_viaticos`.`fec_final`
            , `seg_resolucion_viaticos`.`tot_dias`
            , `seg_resolucion_viaticos`.`dias_pernocta`
            , `seg_resolucion_viaticos`.`objetivo`
            , `seg_empleado`.`cuenta_bancaria`
            , `seg_bancos`.`nom_banco`
        FROM
            `seg_resolucion_viaticos`
            INNER JOIN `seg_empleado` 
                ON (`seg_resolucion_viaticos`.`id_empleado` = `seg_empleado`.`id_empleado`)
            INNER JOIN `seg_cargo_empleado` 
                ON (`seg_empleado`.`cargo` = `seg_cargo_empleado`.`id_cargo`)
            INNER JOIN `seg_sedes_empresa` 
                ON (`seg_empleado`.`sede_emp` = `seg_sedes_empresa`.`id_sede`)
            INNER JOIN `seg_bancos` 
                ON (`seg_empleado`.`id_banco` = `seg_bancos`.`id_banco`)
        WHERE `seg_resolucion_viaticos`.`id_resol_viat` IN ($idsresol)";
    $rs = $cmd->query($sql);
    $resolucion = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
};
$idsempl = '0';
foreach ($resolucion as $res) {
    $idsempl .= ',' . $res['id_empleado'];
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `id_empleado`, `vigencia`, `salario_basico`
            FROM
                (SELECT  
                    `seg_empleado`.`id_empleado`
                    , `seg_salarios_basico`.`vigencia`
                    , `seg_salarios_basico`.`salario_basico`
                FROM `seg_salarios_basico`
                    INNER JOIN `seg_empleado`
                        ON(`seg_salarios_basico`.`id_empleado` = `seg_empleado`.`id_empleado`)
                WHERE `vigencia` <= '$vigencia' AND `seg_empleado`.`id_empleado` IN ($idsempl)
                ORDER BY `seg_salarios_basico`.`id_empleado`ASC,`id_salario` DESC,`vigencia` ASC) AS t
            GROUP BY `id_empleado`";
    $rs = $cmd->query($sql);
    $salsbases = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
foreach ($resolucion as $resolucion) {
    $key = array_search($resolucion['id_empleado'], array_column($salsbases, 'id_empleado'));
    $salbase = $salsbases[$key]['salario_basico'];
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "SELECT
                `con_vigencias`.`anio`
                , `seg_rango_viaticos`.`val_viatico_dia`
            FROM
                `seg_rango_viaticos`
                INNER JOIN `con_vigencias` 
                    ON (`seg_rango_viaticos`.`vigencia` = `con_vigencias`.`id_vigencia`)
            WHERE `seg_rango_viaticos`.`val_min` <= '$salbase' AND `seg_rango_viaticos`.`val_max` >= '$salbase' AND `con_vigencias`.`anio` <= '$vigencia'
            ORDER BY `con_vigencias`.`anio` DESC LIMIT 1";
        $rs = $cmd->query($sql);
        $valviaticodia = $rs->fetch();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }

    $num_resolucion =  str_pad($resolucion['no_resolucion'], 5, '0', STR_PAD_LEFT);
    $genero = $resolucion['genero'];
    if ($genero == 'F') {
        $adj = 'a la señora ';
        $identidad = 'identificada ';
    } else {
        $adj = 'al señor ';
        $identidad = 'identificado ';
    }

    $nombre = $resolucion['nombre_empleado'];
    $cargo = $resolucion['descripcion_carg'];
    $sede = $resolucion['nombre'];
    $cedula = $resolucion['no_documento'];
    $destino = $resolucion['destino'];
    $fec_inicia = explode('-', $resolucion['fec_inicia']);
    $fec_final = explode('-', $resolucion['fec_final']);
    $mi = intval($fec_inicia[1]);
    $mf = intval($fec_final[1]);
    $tot_dias = $resolucion['tot_dias'];
    $dias_pernocta = $resolucion['dias_pernocta'];
    $valorconp = $valviaticodia['val_viatico_dia'];
    $valorsinp = $valviaticodia['val_viatico_dia'] * 0.5;
    if ($tot_dias == $dias_pernocta) {
        if ($tot_dias == 1) {
            $pernoctado = '(1) día con pernoctada';
        } else {
            $pernoctado = '(' . $tot_dias . ') días con pernoctada';
        }
        $describerazon = 'la suma de ' . pesos($valorconp * $tot_dias);
        $total = $valorconp * $tot_dias;
    } else if ($dias_pernocta == 0) {
        if ($tot_dias == 1) {
            $pernoctado = '(1) día sin pernoctada';
        } else {
            $pernoctado = '(' . $tot_dias . ') días sin pernoctada';
        }
        $describerazon = 'la suma de ' . pesos($valorsinp * $tot_dias);
        $total = $valorsinp * $tot_dias;
    } else {
        $diasinpernoctar = $tot_dias - $dias_pernocta;
        if ($diasinpernoctar == 1) {
            $per = '(1) día sin pernoctada';
        } else {
            $per = '(' . $diasinpernoctar . ') días sin pernoctada';
        }
        if ($dias_pernocta == 1) {
            $pern = '(1) día con pernoctada';
        } else {
            $pern = '(' . $dias_pernocta . ') días con pernoctada';
        }
        $pernoctado = $pern . ' y ' . $per;
        $describerazon = 'la suma de ' . pesos($valorconp * $dias_pernocta) . ' y ' . pesos($valorsinp * $diasinpernoctar) . ' respectivamente ';
        $total = $valorconp * $dias_pernocta + $valorsinp * $diasinpernoctar;
    }
    $meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    if ($tot_dias == 1) {
        $fecha = 'el día ' . $fec_inicia[2] . ' de ' . $meses[$mi] . ' de ' . $fec_inicia[0];
    } else {
        $fecha = 'los días comprendidos entre el ' . $fec_inicia[2] . ' de ' . $meses[$mi] . ' de ' . $fec_inicia[0] . ' y el ' . $fec_final[2] . ' de ' . $meses[$mf] . ' de ' . $fec_final[0];
    }
    $actividad = $resolucion['objetivo'];
    $valpernocta = pesos($valorconp);
    $valsinpernocta = pesos($valorsinp);
    $salario = pesos($salbase);
    $cuenta = $resolucion['cuenta_bancaria'];
    $banco = $resolucion['nom_banco'];
    $val_letras = new NumberFormatter("es", NumberFormatter::SPELLOUT);
    $total_numero = pesos($total);
    try {
        $id_user = $_SESSION['id_user'];
        $date = new DateTime('now', new DateTimeZone('America/Bogota'));
        $id_resolucion = $resolucion['id_resol_viat'];
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "UPDATE `seg_resolucion_viaticos` SET  `val_total` = ? , `id_user_act` = ? ,`fec_act` = ?  WHERE `id_resol_viat` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $total, PDO::PARAM_STR);
        $sql->bindParam(2, $id_user, PDO::PARAM_INT);
        $sql->bindValue(3, $date->format('Y-m-d H:i:s'));
        $sql->bindParam(4, $id_resolucion, PDO::PARAM_INT);
        $sql->execute();
        if (!($sql->rowCount() > 0)) {
            echo $sql->errorInfo()[2];
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    $total_letras = mb_strtoupper($val_letras->format($total, 2));
    $hoy = explode('-', date('Y-m-d'));
    $mexp = intval($hoy[1]);
    if ($hoy[2] == '01') {
        $expedicion = 'el 01 día del mes de ' . $meses[$mexp] . ' de ' . $hoy[0];
    } else {
        $expedicion = 'a los ' . $hoy[2] . ' días del mes de ' . $meses[$mexp] . ' de ' . $hoy[0];
    }
    $values[] = [
        'id' => '',
        'num_resolucion' => $num_resolucion,
        'vigencia' => $vigencia,
        'adj' => $adj,
        'identidad' => $identidad,
        'cargo' => $cargo,
        'nombre' => $nombre,
        'sede' => $sede,
        'cedula' => $cedula,
        'destino' => $destino,
        'fecha' => $fecha,
        'actividad' => $actividad,
        'describepernocta' => $pernoctado,
        'valpernocta' => $valpernocta,
        'valsinpernocta' => $valsinpernocta,
        'describerazon' => $describerazon,
        'valletras' => $total_letras,
        'valnumero' => $total_numero,
        'salario' => $salario,
        'cuentabancaria' => $cuenta,
        'banco' => $banco,
        'expedicion' => $expedicion,
    ];
}
require_once '../../../../../vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

$plantilla = new TemplateProcessor('formato_resolucion.docx');
$plantilla->cloneRowAndSetValues('id', $values);

$archivo = 'resolucion_ grupo_' . $grupo . '.docx';
$plantilla->saveAs($archivo);
header("Content-Disposition: attachment; Filename=" . $archivo);
echo file_get_contents($archivo);
unlink($archivo);
