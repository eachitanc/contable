<?php
session_start();
set_time_limit(5600);
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
$vigencia = $_SESSION['vigencia'];
$fecha_corte = file_get_contents("php://input");
function pesos($valor)
{
    return '$' . number_format($valor, 2);
}
include '../../conexion.php';
include '../../financiero/consultas.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
//
try {
    $sql = "SELECT
    `seg_pto_mvto`.`tipo_mov`
    , `seg_pto_documento`.`id_manu`
    , `seg_pto_documento`.`fecha`
    , `seg_pto_documento`.`objeto`
    , `seg_pto_mvto`.`rubro`
    , `seg_pto_cargue`.`nom_rubro`
    , sum(`seg_pto_mvto`.`valor`) as valor
    , `seg_pto_mvto`.`id_pto_doc`
    FROM
        `seg_pto_mvto`
        LEFT JOIN `seg_pto_cargue` 
            ON (`seg_pto_mvto`.`rubro` = `seg_pto_cargue`.`cod_pptal`)
        INNER JOIN `seg_pto_documento` 
            ON (`seg_pto_mvto`.`id_pto_doc` = `seg_pto_documento`.`id_pto_doc`)
    WHERE ((`seg_pto_mvto`.`tipo_mov` ='CDP' OR `seg_pto_mvto`.`tipo_mov`='LCD') AND `seg_pto_documento`.`fecha` <= '$fecha_corte' AND `seg_pto_documento`.`estado`=0)
    GROUP BY `seg_pto_mvto`.`tipo_mov`,`seg_pto_mvto`.`rubro`, `seg_pto_mvto`.`id_pto_doc`
    ORDER BY `seg_pto_documento`.`fecha` ASC;
";
    $res = $cmd->query($sql);
    $causaciones = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// consulto el nombre de la empresa de la tabla seg_empresas
try {
    $sql = "SELECT
    `nombre`
    , `nit`
    , `dig_ver`
FROM
    `seg_empresas`;";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
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
            <td colspan="7" style="text-align:center"><?php echo $empresa['nombre']; ?></td>
        </tr>
        <tr>
            <td colspan="7" style="text-align:center"><?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></td>
        </tr>
        <tr>
            <td colspan="7" style="text-align:center"><?php echo 'RELACION DE CERTIFICADOS DE DISPONIBILIDAD PRESUPUESTAL'; ?></td>
        </tr>
        <tr>
            <td colspan="7" style="text-align:center"><?php echo 'Fecha de corte: ' . $fecha_corte; ?></td>
        </tr>
        <tr style="background-color: #CED3D3; text-align:center;font-size:9px;">
            <th>Tipo</th>
            <th>No disponibilidad</th>
            <th>Fecha</th>
            <th>Objeto</th>
            <th>Rubro</th>
            <th>Nombre rubro</th>
            <th>Valor</th>
            <th>Saldo</th>
        </tr>
    </thead>
    <tbody style="font-size:9px;">
        <?php
        foreach ($causaciones as $rp) {
            // consulto el valor registrado de cada cdp y rubro
            $sql = "SELECT
                SUM(`seg_pto_mvto`.`valor`) AS `valor_rp`
            FROM
                `seg_pto_mvto`
                INNER JOIN `seg_pto_documento` 
                    ON (`seg_pto_mvto`.`id_pto_doc` = `seg_pto_documento`.`id_pto_doc`)
            WHERE `seg_pto_mvto`.`rubro` ='{$rp['rubro']}'
                AND `seg_pto_documento`.`fecha` <='$fecha_corte'
                AND `seg_pto_mvto`.`id_auto_dep` ={$rp['id_pto_doc']}
                AND `seg_pto_mvto`.`tipo_mov`='CRP' 
            GROUP BY `seg_pto_mvto`.`rubro`;";
            $res = $cmd->query($sql);
            $reg2 = $res->fetch();
            // consulto el valor registrado de cada cdp y rubro
            $sql = "SELECT
     SUM(`seg_pto_mvto`.`valor`) AS `valor_rp`
 FROM
     `seg_pto_mvto`
     INNER JOIN `seg_pto_documento` 
         ON (`seg_pto_mvto`.`id_pto_doc` = `seg_pto_documento`.`id_pto_doc`)
 WHERE `seg_pto_mvto`.`rubro` ='{$rp['rubro']}'
     AND `seg_pto_documento`.`fecha` <='$fecha_corte'
     AND `seg_pto_mvto`.`id_auto_dep` ={$rp['id_pto_doc']}
     AND `seg_pto_mvto`.`tipo_mov`='LRP' 
 GROUP BY `seg_pto_mvto`.`rubro`;";
            $res = $cmd->query($sql);
            $reg3 = $res->fetch();
            // consulto el valor anulado de cada cdp y rubro
            $sql = "SELECT
                SUM(`seg_pto_mvto`.`valor`) AS `valor_lcd`
            FROM
                `seg_pto_mvto`
                INNER JOIN `seg_pto_documento` 
                    ON (`seg_pto_mvto`.`id_pto_doc` = `seg_pto_documento`.`id_pto_doc`)
            WHERE `seg_pto_mvto`.`rubro` ='{$rp['rubro']}'
                AND `seg_pto_documento`.`fecha` <='$fecha_corte'
                AND `seg_pto_mvto`.`id_auto_dep` ={$rp['id_pto_doc']}
                AND `seg_pto_mvto`.`tipo_mov`='LCD' 
            GROUP BY `seg_pto_mvto`.`rubro`;";
            $sql2 = $sql;
            $res = $cmd->query($sql);
            $reg = $res->fetch();
            $valor_cdp = $rp['valor'] +  $reg['valor_lcd'];
            $saldo =  $valor_cdp - $reg2['valor_rp'] - $reg3['valor_rp'];
            $fecha = date('Y-m-d', strtotime($rp['fecha']));
            if ($valor_cdp >= 0) {
                echo "<tr>
                        <td style='text-aling:left'>" . $rp['tipo_mov'] .  "</td>
                        <td style='text-aling:left'>" . $rp['id_manu'] . "</td>
                        <td style='text-aling:left'>" .   $fecha   . "</td>
                        <td style='text-aling:left'>" . $rp['objeto'] . "</td>
                        <td style='text-aling:left'" . $rp['rubro'] . "</td>
                        <td style='text-aling:left'>" .  $rp['nom_rubro'] . "</td>
                        <td style='text-aling:right'>" . number_format($valor_cdp, 2, ".", ",")  . "</td>
                        <td style='text-aling:right'>" . number_format($saldo, 2, ".", ",") . "</td>
                    </tr>";
                $saldo = 0;
                $valor_cdp = 0;
            }
        }
        ?>
    </tbody>
</table>