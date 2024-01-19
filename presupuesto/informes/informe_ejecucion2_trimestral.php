<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
$vigencia = $_SESSION['vigencia'];
$data = explode(',', file_get_contents("php://input"));
$tipo_pto = $data['0'];
$id_corte = $data['1'];
$fecha_ini = $vigencia . '-01-01';
switch ($id_corte) {
    case 1:
        $fecha_corte = $vigencia . '-03-31';
        $codigo = '10303';
        break;
    case 2:
        $fecha_corte = $vigencia . '-06-30';
        $codigo = '10606';
        break;
    case 3:
        $fecha_corte = $vigencia . '-09-30';
        $codigo = '10909';
        break;
    case 4:
        $fecha_corte = $vigencia . '-03-31';
        $codigo = '11212';
        break;
    default:
        exit();
        break;
}
function pesos($valor)
{
    return '$' . number_format($valor, 2);
}
include '../../conexion.php';
include '../../financiero/consultas.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
// consulto el nombre de la empresa de la tabla seg_empresas
try {
    $sql = "SELECT
                `nombre`
                , `nit`
                , `dig_ver`
            FROM
                `seg_empresas`";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$sqlDelete = "DELETE FROM `tmp_ctb_libaux`";
$deleteResult = $cmd->query($sqlDelete);
// Cargo la informacion de la tabla tmp_ctb_libaux
// Cargar la información del archivo SQL en la tabla tmp_ctb_libaux
$sqlFilePath = "C:/Users/LCM01/Downloads/in2.sql"; // Ruta al archivo SQL
//$sqlFilePath = "/home/admin/in2.sql"; // Ruta al archivo SQL
$sqlContent = file_get_contents($sqlFilePath); // Leer el contenido del archivo

if ($sqlContent !== false) {
    // Ejecutar el contenido del archivo SQL como consulta
    $sqlin = $cmd->exec($sqlContent);

    if ($sqlin === false) {
        echo "Error al cargar datos: " . implode(" ", $cmd->errorInfo());
    }
} else {
    echo "Error al leer el archivo SQL.";
}
try {
    $sql = "SELECT
                `seg_pto_homologa_ingresos`.`id_cgr`
                , `seg_pto_codigo_cgr`.`codigo` AS `codigo_cgr`
                , `seg_pto_cpc`.`codigo` AS `codigo_cpc`
                , `seg_pto_fuente`.`codigo` AS `codigo_fte`
                , `seg_pto_politica`.`codigo` AS `codigo_pol`
                , `seg_pto_terceros`.`codigo` AS `codigo_ter`
                , `seg_pto_situacion`.`id_situacion` AS `codigo_sit`
                , `seg_pto_vigencias`.`id_vigencia` AS `codigo_vig`
                , `seg_pto_cargue`.`cod_pptal`
                , `seg_pto_cargue`.`nom_rubro`
                , `seg_pto_cargue`.`tipo_dato`
                , SUM(`recaudo`) AS `recaudo`
            FROM
                (SELECT
                    `seg_pto_cargue`.`cod_pptal`
                    , `seg_pto_cargue`.`nom_rubro`
                    , CASE `seg_pto_cargue`.`tipo_dato` WHEN 1 THEN 'D' WHEN 0 THEN 'M' END AS `tipo_dato`
                    , IFNULL(`recaudo`.`valor`,0) AS `recaudo`
                FROM
                    `seg_pto_cargue`
                    LEFT JOIN (
                        SELECT 
                            `cod_pptal`
                            , `nom_rubro`
                            , SUM(`valor`) AS `valor` 
                        FROM (	
                            SELECT
                                `seg_pto_cargue`.`cod_pptal`
                                , `seg_pto_cargue`.`nom_rubro`    
                                , `seg_pto_mvto`.`valor` AS `valor`    
                            FROM
                                `seg_pto_cargue`
                                INNER JOIN `seg_pto_mvto` ON (`seg_pto_cargue`.`cod_pptal` = `seg_pto_mvto`.`rubro`)
                                INNER JOIN `seg_pto_documento` ON (`seg_pto_mvto`.`id_pto_doc` = `seg_pto_documento`.`id_pto_doc`)
                                INNER JOIN `seg_pto_presupuestos` ON (`seg_pto_documento`.`id_pto_presupuestos` = `seg_pto_presupuestos`.`id_pto_presupuestos`)
                            WHERE `seg_pto_presupuestos`.`id_pto_tipo` = 1 AND `seg_pto_mvto`.`tipo_mov` = 'REC' AND `seg_pto_documento`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_corte'
                            UNION ALL
                            SELECT
                                `seg_pto_cargue`.`cod_pptal`
                                , `seg_pto_cargue`.`nom_rubro`    
                                , `tmp_ctb_libaux`.`valordeb` AS `valor`    
                            FROM
                                `seg_pto_cargue`
                                INNER JOIN `tmp_ctb_libaux` ON (`tmp_ctb_libaux`.`cuenta`=`seg_pto_cargue`.`cod_pptal`)
                            WHERE `tmp_ctb_libaux`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_corte' AND `tmp_ctb_libaux`.`tipo` = 'REC'
                        ) AS `rec` GROUP BY `cod_pptal`	 
                    ) AS `recaudo` 
                        ON (`seg_pto_cargue`.`cod_pptal` = `recaudo`.`cod_pptal`)                    
                WHERE `vigencia` = '$vigencia') AS `ejecucion`  
                LEFT JOIN `seg_pto_cargue` 
                    ON (`seg_pto_cargue`.`cod_pptal` = `ejecucion`.`cod_pptal`) 
                LEFT JOIN `seg_pto_presupuestos` 
                    ON (`seg_pto_cargue`.`id_pto_presupuestos` = `seg_pto_presupuestos`.`id_pto_presupuestos`) 
                LEFT JOIN `seg_pto_homologa_ingresos` 
                    ON (`seg_pto_homologa_ingresos`.`id_pto` = `seg_pto_cargue`.`id_pto_cargue`) 
                LEFT JOIN `seg_pto_codigo_cgr` 
                    ON (`seg_pto_homologa_ingresos`.`id_cgr` = `seg_pto_codigo_cgr`.`id_cod`)
                LEFT JOIN `seg_pto_cpc` 
                    ON (`seg_pto_homologa_ingresos`.`id_cpc` = `seg_pto_cpc`.`id_cpc`)
                LEFT JOIN `seg_pto_fuente` 
                    ON (`seg_pto_homologa_ingresos`.`id_fuente` = `seg_pto_fuente`.`id_fuente`)
                LEFT JOIN `seg_pto_politica` 
                    ON (`seg_pto_homologa_ingresos`.`id_politica` = `seg_pto_politica`.`id_politica`)
                LEFT JOIN `seg_pto_terceros` 
                    ON (`seg_pto_homologa_ingresos`.`id_tercero` = `seg_pto_terceros`.`id_tercero`)
                LEFT JOIN `seg_pto_situacion` 
                    ON (`seg_pto_homologa_ingresos`.`id_situacion` = `seg_pto_situacion`.`id_situacion`)
                LEFT JOIN `seg_pto_vigencias` 
                    ON (`seg_pto_homologa_ingresos`.`id_vigencia` = `seg_pto_vigencias`.`id_vigencia`)
            WHERE `seg_pto_presupuestos`.`id_pto_tipo` = 1 
            GROUP BY   `seg_pto_cargue`.`cod_pptal` , `seg_pto_cargue`.`nom_rubro` , `seg_pto_cargue`.`tipo_dato`
            ORDER BY `seg_pto_cargue`.`cod_pptal`";
    //echo $sql;
    $res = $cmd->query($sql);
    $rubros = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$data = [];
foreach ($rubros as $fila) {
    $id_cgr = $fila['codigo_cgr'];
    $id_cpc = $fila['codigo_cpc'];
    $id_fte = $fila['codigo_fte'];
    $id_pol = $fila['codigo_pol'];
    $id_ter = $fila['codigo_ter'];
    $id_sit = $fila['codigo_sit'];
    $id_vig = $fila['codigo_vig'];
    $recaudo = $fila['recaudo'];
    if (isset($data[$id_cgr . $id_cpc . $id_fte . $id_pol . $id_ter . $id_sit . $id_vig])) {
        $val_acumulado = $data[$id_cgr . $id_cpc . $id_fte . $id_pol . $id_ter . $id_sit . $id_vig]['recaudo'];
        $val_rec = $recaudo + $val_acumulado;
    } else {
        $val_rec = $recaudo;
    }
    $data[$id_cgr . $id_cpc . $id_fte . $id_pol . $id_ter . $id_sit . $id_vig] = [
        'codigo_cgr' => $id_cgr,
        'codigo_cpc' => $id_cpc,
        'codigo_fte' => $id_fte,
        'codigo_pol' => $id_pol,
        'codigo_ter' => $id_ter,
        'codigo_sit' => $id_sit,
        'codigo_vig' => $id_vig,
        'recaudo' => $val_rec,
    ];
}
?>
<style>
    .resaltar:nth-child(even) {
        background-color: #F8F9F9;
    }

    .resaltar:nth-child(odd) {
        background-color: #ffffff;
    }
</style>
<table style="width:100% !important; border-collapse: collapse;">
    <thead>
        <tr>
            <td rowspan="4" style="text-align:center"><label class="small"><img src="<?php echo $_SESSION['urlin'] ?>/images/logos/logo.png" width="100"></label></td>
            <td colspan="11" style="text-align:center"><?php echo $empresa['nombre']; ?></td>
        </tr>
        <tr>
            <td colspan="11" style="text-align:center"><?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></td>
        </tr>
        <tr>
            <td colspan="11" style="text-align:center"><?php echo 'EJECUCIÓN - INGRESOS'; ?></td>
        </tr>
        <tr>
            <td colspan="11" style="text-align:center"><?php echo 'Fecha de corte: ' . $fecha_corte; ?></td>
        </tr>
        <tr style="background-color: #CED3D3; text-align:center;">
            <td>-</td>
            <td colspan="3">Codigo CGR</td>
            <td>CPC</td>
            <td>Fuente</td>
            <td>Terceros</td>
            <td>Política Pública</td>
            <td>V. Actual Con Situación</td>
            <td>V. Actual Sin Situación</td>
            <td>V. Anterior Con Situación</td>
            <td>V. Anterior Sin Situación</td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="text-align:center">S</td>
            <td colspan="3" style="text-align:center">84300000</td>
            <td style="text-align:center"><?php echo $codigo; ?></td>
            <td style="text-align:center"><?php echo $vigencia; ?></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <?php
        foreach ($data as $key => $d) {

            if ($key != '') {
                if ($d['codigo_vig'] == 1 && $d['codigo_sit'] == 2) {
                    $vacss = $d['recaudo'];
                    $vaccs = '0';
                    $vanss = '0';
                    $vancs = '0';
                } else if ($d['codigo_vig'] == 1 && $d['codigo_sit'] == 1) {
                    $vacss = '0';
                    $vaccs = $d['recaudo'];
                    $vanss = '0';
                    $vancs = '0';
                } else if ($d['codigo_vig'] == 2 && $d['codigo_sit'] == 2) {
                    $vacss = '0';
                    $vaccs = '0';
                    $vanss = $d['recaudo'];
                    $vancs = '0';
                } else if ($d['codigo_vig'] == 2 && $d['codigo_sit'] == 1) {
                    $vacss = '0';
                    $vaccs = '0';
                    $vanss = '0';
                    $vancs = $d['recaudo'];
                }
                echo '<tr class="resaltar">';
                echo '<td >D</td>';
                echo '<td colspan="3">' . $d['codigo_cgr'] . '</td>';
                echo '<td>' . $d['codigo_cpc'] . '</td>';
                echo '<td>' . $d['codigo_fte'] . '</td>';
                echo '<td>' . $d['codigo_pol'] . '</td>';
                echo '<td>' . $d['codigo_ter'] . '</td>';
                echo '<td style="text-align:right">' . $vacss . '</td>';
                echo '<td style="text-align:right">' . $vaccs . '</td>';
                echo '<td style="text-align:right">' . $vanss . '</td>';
                echo '<td style="text-align:right">' . $vancs . '</td>';
                echo '</tr>';
            }
        }
        ?>
    </tbody>
</table>