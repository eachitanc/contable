<?php
session_start();
set_time_limit(5600);
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>CONTAFACIL</title>
    <style>
        .text {
            mso-number-format: "\@"
        }
    </style>
    <?php

    header("Content-type: application/vnd.ms-excel charset=utf-8");
    header("Content-Disposition: attachment; filename=FORMATO_201101_F07_AGR.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    ?>
</head>
<?php
$vigencia = $_SESSION['vigencia'];
$fecha_corte = $_POST['fecha'];
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
    `seg_pto_cargue`.`cod_pptal`
    , `seg_pto_cargue`.`nom_rubro`
    , `seg_pto_cargue`.`tipo_dato`
    FROM
    `seg_pto_cargue`
    INNER JOIN `seg_pto_presupuestos` 
        ON (`seg_pto_cargue`.`id_pto_presupuestos` = `seg_pto_presupuestos`.`id_pto_presupuestos`)
    WHERE (`seg_pto_cargue`.`vigencia` =$vigencia
    AND `seg_pto_presupuestos`.`id_pto_tipo` =2)
    ORDER BY `cod_pptal` ASC;";
    $res = $cmd->query($sql);
    $rubros = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
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
<div class="contenedor bg-light" id="areaImprimir">
    <div class="px-2 " style="width:90% !important;margin: 0 auto;">

        </br>
        </br>
        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td colspan="15" style="text-align:center"><?php echo ''; ?></td>
            </tr>

            <tr>
                <td colspan="15" style="text-align:center"><?php echo $empresa['nombre']; ?></td>
            </tr>
            <tr>
                <td colspan="15" style="text-align:center"><?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></td>
            </tr>
            <tr>
                <td colspan="15" style="text-align:center"><?php echo 'EJECUCION PRESUPUESTAL DE GASTOS'; ?></td>
            </tr>
            <tr>
                <td colspan="15" style="text-align:center"><?php echo 'Fecha de corte: ' . $fecha_corte; ?></td>
            </tr>
            <tr>
                <td colspan="15" style="text-align:center"><?php echo ''; ?></td>
            </tr>
        </table>



        </br>
        <table class="table-bordered bg-light" style="width:100% !important;" border=1>
            <tr>
                <td>Rubro</td>
                <td>Descripcion</td>
                <td>Tipo</td>
                <td>Presupuesto inicial</td>
                <td>Adiciones</td>
                <td>Reducciones</td>
                <td>Cr&eacute;ditos</td>
                <td>Contracreditos</td>
                <td>Presupuesto definitivo</td>
                <td>Comprometido</td>
                <td>Obligaciones</td>
                <td>Pagos enero</td>
                <td>Pagos febrero</td>
                <td>Pagos Marzo</td>
                <td>Total</td>
            </tr>
            <?php
            foreach ($rubros as $rp) {
                $rubro = $rp['cod_pptal'] . '%';
                if ($rp['cod_pptal'] == '245020901') {
                    $rubro = $rp['cod_pptal'];
                }
                // Para cargue inicial
                $sql = "SELECT
                SUM(`ppto_aprob`) AS inicial
                , `cod_pptal`
                , `tipo_dato`
        
                 FROM
                `seg_pto_cargue` 
                WHERE (`cod_pptal`LIKE '$rubro' AND `tipo_dato`=1);";
                $res = $cmd->query($sql);
                $inicial = $res->fetch();
                $inicial = $inicial['inicial'];
                // Para valor adicion
                $sql = "SELECT
                SUM(`seg_pto_mvto`.`valor`) as valor
                ,`seg_pto_documento`.`estado`
                 FROM
                 `seg_pto_mvto`
                 INNER JOIN `seg_pto_cargue` 
                     ON (`seg_pto_mvto`.`rubro` = `seg_pto_cargue`.`cod_pptal`)
                 INNER JOIN `seg_pto_documento` 
                     ON (`seg_pto_mvto`.`id_pto_doc` = `seg_pto_documento`.`id_pto_doc`)
                WHERE (`seg_pto_documento`.`estado` =0
                 AND `seg_pto_documento`.`fecha` <='$fecha_corte'
                 AND `seg_pto_mvto`.`tipo_mov` ='ADI'
                 AND `seg_pto_mvto`.`mov` =0
                 AND `seg_pto_mvto`.`rubro` LIKE '$rubro')";
                $res = $cmd->query($sql);
                $adicion = $res->fetch();
                $adicion = $adicion['valor'];

                // Para valor reduccion
                $sql = "SELECT
                 SUM(`seg_pto_mvto`.`valor`) as valor
                  FROM
                  `seg_pto_mvto`
                  INNER JOIN `seg_pto_cargue` 
                      ON (`seg_pto_mvto`.`rubro` = `seg_pto_cargue`.`cod_pptal`)
                  INNER JOIN `seg_pto_documento` 
                      ON (`seg_pto_mvto`.`id_pto_doc` = `seg_pto_documento`.`id_pto_doc`)
                 WHERE (`seg_pto_documento`.`fecha` <='$fecha_corte'
                  AND `seg_pto_mvto`.`tipo_mov` ='RED'
                  AND `seg_pto_mvto`.`mov` =1
                  AND `seg_pto_mvto`.`rubro` LIKE '$rubro')
                 GROUP BY `seg_pto_mvto`.`valor`;";
                $res = $cmd->query($sql);
                $reduc = $res->fetch();
                $reducion = $reduc['valor'];
                // Para valor credito
                $sql = "SELECT
                SUM(`seg_pto_mvto`.`valor`) as valor
             FROM
                 `seg_pto_mvto`
                 INNER JOIN `seg_pto_cargue` 
                     ON (`seg_pto_mvto`.`rubro` = `seg_pto_cargue`.`cod_pptal`)
                 INNER JOIN `seg_pto_documento` 
                     ON (`seg_pto_mvto`.`id_pto_doc` = `seg_pto_documento`.`id_pto_doc`)
             WHERE (`seg_pto_documento`.`fecha` <='$fecha_corte'
                 AND `seg_pto_mvto`.`tipo_mov` ='TRA'
                 AND `seg_pto_mvto`.`mov` =1
                 AND `seg_pto_mvto`.`rubro` LIKE '$rubro')";
                $res = $cmd->query($sql);
                $credito = $res->fetch();
                $val_credito = $credito['valor'];
                // Para valor contracredito
                $sql = "SELECT
                SUM(`seg_pto_mvto`.`valor`) as valor
             FROM
                 `seg_pto_mvto`
                 INNER JOIN `seg_pto_cargue` 
                     ON (`seg_pto_mvto`.`rubro` = `seg_pto_cargue`.`cod_pptal`)
                 INNER JOIN `seg_pto_documento` 
                     ON (`seg_pto_mvto`.`id_pto_doc` = `seg_pto_documento`.`id_pto_doc`)
             WHERE (`seg_pto_documento`.`fecha` <='$fecha_corte'
                 AND `seg_pto_mvto`.`tipo_mov` ='TRA'
                 AND `seg_pto_mvto`.`mov` =0
                 AND `seg_pto_mvto`.`rubro` LIKE '$rubro');";
                $res = $cmd->query($sql);
                $credito = $res->fetch();
                $val_ccred = $credito['valor'];
                // Para valor ejecutado con CDP
                $sql = "SELECT sum(valor) as valor FROM seg_pto_mvto WHERE rubro LIKE '$rubro'";
                $sql = "SELECT
                `seg_pto_documento`.`fecha`
                , SUM(`seg_pto_mvto`.`valor`) AS valor
                FROM
                `seg_pto_mvto`
                INNER JOIN `seg_pto_documento` 
                    ON (`seg_pto_mvto`.`id_pto_doc` = `seg_pto_documento`.`id_pto_doc`)
                WHERE ((`seg_pto_mvto`.`tipo_mov` ='CDP' OR `seg_pto_mvto`.`tipo_mov` ='LCD')
                AND `seg_pto_documento`.`fecha` <='$fecha_corte'
                AND `seg_pto_mvto`.`rubro` LIKE '$rubro');";
                $res = $cmd->query($sql);
                $valorcdp = $res->fetch();
                $cdp = $valorcdp['valor'];
                // Para valor ejecutado con RP
                $sql = "SELECT sum(valor) as valor FROM seg_pto_mvto WHERE rubro LIKE '$rubro'";
                $sql = "SELECT
                `seg_pto_documento`.`fecha`
                , SUM(`seg_pto_mvto`.`valor`) AS valor
                FROM
                `seg_pto_mvto`
                INNER JOIN `seg_pto_documento` 
                    ON (`seg_pto_mvto`.`id_pto_doc` = `seg_pto_documento`.`id_pto_doc`)
                WHERE (`seg_pto_mvto`.`tipo_mov` ='CRP'
                AND `seg_pto_documento`.`fecha` <='$fecha_corte'
                AND `seg_pto_mvto`.`rubro` LIKE '$rubro');";
                $res = $cmd->query($sql);
                $valorcrp = $res->fetch();
                $crp = $valorcrp['valor'];
                // Para valor ejecutado con obligado
                $sql = "SELECT
                `seg_pto_mvto`.`rubro`
                , SUM(`seg_pto_mvto`.`valor`) as valor
                
                FROM
                `seg_ctb_doc`
                INNER JOIN `seg_pto_mvto` 
                    ON (`seg_ctb_doc`.`id_ctb_doc` = `seg_pto_mvto`.`id_ctb_doc`)
                WHERE (`seg_pto_mvto`.`rubro` LIKE '$rubro'
                AND `seg_ctb_doc`.`fecha`  <='$fecha_corte'
                AND `seg_pto_mvto`.`tipo_mov` ='COP');";
                $res = $cmd->query($sql);
                $valorcop = $res->fetch();
                $cop = $valorcop['valor'];
                // Para valor ejecutado con pagos
                $sql = "SELECT
                `seg_pto_mvto`.`rubro`
                , SUM(`seg_pto_mvto`.`valor`) as valor
                FROM
                `seg_ctb_doc`
                INNER JOIN `seg_pto_mvto` 
                    ON (`seg_ctb_doc`.`id_ctb_doc` = `seg_pto_mvto`.`id_ctb_doc`)
                WHERE (`seg_pto_mvto`.`rubro` LIKE '$rubro'
                AND `seg_ctb_doc`.`fecha` BETWEEN '2023-01-01' AND '2023-01-31'
                AND `seg_pto_mvto`.`estado` =0
                AND `seg_pto_mvto`.`tipo_mov` ='PAG');";
                $res = $cmd->query($sql);
                $valorpag = $res->fetch();
                $ene = $valorpag['valor'];
                // Consulta para febrero
                // Para valor ejecutado con pagos
                $sql = "SELECT
                `seg_pto_mvto`.`rubro`
                , SUM(`seg_pto_mvto`.`valor`) as valor
                FROM
                `seg_ctb_doc`
                INNER JOIN `seg_pto_mvto` 
                    ON (`seg_ctb_doc`.`id_ctb_doc` = `seg_pto_mvto`.`id_ctb_doc`)
                WHERE (`seg_pto_mvto`.`rubro` LIKE '$rubro'
                AND `seg_ctb_doc`.`fecha` BETWEEN '2023-02-01' AND '2023-02-28'
                AND `seg_pto_mvto`.`estado` =0
                AND `seg_pto_mvto`.`tipo_mov` ='PAG');";
                $res = $cmd->query($sql);
                $valorpag = $res->fetch();
                $feb = $valorpag['valor'];
                // Para valor ejecutado con pagos
                // Para valor ejecutado con pagos
                $sql = "SELECT
                `seg_pto_mvto`.`rubro`
                , SUM(`seg_pto_mvto`.`valor`) as valor
                FROM
                `seg_ctb_doc`
                INNER JOIN `seg_pto_mvto` 
                    ON (`seg_ctb_doc`.`id_ctb_doc` = `seg_pto_mvto`.`id_ctb_doc`)
                WHERE (`seg_pto_mvto`.`rubro` LIKE '$rubro'
                AND `seg_ctb_doc`.`fecha` BETWEEN '2023-03-01' AND '2023-03-31'
                AND `seg_pto_mvto`.`estado` =0
                AND `seg_pto_mvto`.`tipo_mov` ='PAG');";
                $res = $cmd->query($sql);
                $valorpag = $res->fetch();
                $mar = $valorpag['valor'];
                // Suma total trimestre
                $total_mes = $ene + $feb + $mar;
                $def = $inicial + $val_credito - $val_ccred + $adicion - $reducion;
                if ($rp['tipo_dato'] == 1) {
                    $tipo_rubro = 'D';
                } else {
                    $tipo_rubro = 'M';
                }
                echo "<tr>
                <td class='text'>" . $rp['cod_pptal'] .  "</td>
                <td class='text-left'>" . $rp['nom_rubro'] . "</td>
                <td class='text-left'>" . $tipo_rubro . "</td>
                <td class='text-right'>" . number_format($inicial, 2, ".", ",")  . "</td>
                <td class='text-right'>" . number_format($adicion, 2, ".", ",")  . "</td>
                <td class='text-right'>" . number_format($reducion, 2, ".", ",")  . "</td>
                <td class='text-right'>" . number_format($val_credito, 2, ".", ",")  . "</td>
                <td class='text-right'>" . number_format($val_ccred, 2, ".", ",")  . "</td>
                <td class='text-right'>" . number_format($def, 2, ".", ",")  . "</td>
                <td class='text-right'>" . number_format($crp, 2, ".", ",")  . "</td>
                <td class='text-right'>" . number_format($cop, 2, ".", ",")  . "</td>
                <td class='text-right'>" . number_format($ene, 2, ".", ",")  . "</td>
                <td class='text-right'>" . number_format($feb, 2, ".", ",")  . "</td>
                <td class='text-right'>" . number_format($mar, 2, ".", ",")  . "</td>
                <td class='text-right'>" . number_format($total_mes, 2, ".", ",")  . "</td>
                </tr>";
            }
            ?>

        </table>
        </br>
        </br>
        </br>

    </div>

</div>