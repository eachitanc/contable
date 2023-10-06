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
    header("Content-Disposition: attachment; filename=Relacion_tercero_causacion_pago.xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    ?>
</head>
<?php
$vigencia = $_SESSION['vigencia'];
$tercero = $_POST['tercero'];
$fecha_inicial = $_POST['fecha_ini'];
$fecha_final = $_POST['fecha_fin'];
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
                `seg_ctb_doc`.`fecha`
                , IF(`seg_pto_mvto`.`id_tercero_api`,`seg_pto_mvto`.`id_tercero_api`,`seg_ctb_doc`.`id_tercero`) AS id_tercero
                , `seg_ctb_doc`.`detalle`
                , `seg_ctb_doc`.`id_manu`
                , `seg_ctb_factura`.`num_doc`
                , `seg_pto_mvto`.`valor`
                , `seg_pto_mvto`.`rubro`
                , `seg_ctb_doc`.`id_ctb_doc`
            FROM
                `seg_ctb_factura`
                INNER JOIN `seg_ctb_doc` 
                    ON (`seg_ctb_factura`.`id_ctb_doc` = `seg_ctb_doc`.`id_ctb_doc`)
                INNER JOIN `seg_pto_mvto` 
                    ON (`seg_ctb_doc`.`id_ctb_doc` = `seg_pto_mvto`.`id_ctb_doc`)
            WHERE (`seg_ctb_doc`.`fecha` BETWEEN '$fecha_inicial' AND '$fecha_final'
                AND (`seg_ctb_doc`.`tipo_doc` ='NCXP' OR `seg_ctb_doc`.`tipo_doc` ='CNOM') AND
                IF(`seg_pto_mvto`.`id_tercero_api`,`seg_pto_mvto`.`id_tercero_api`,`seg_ctb_doc`.`id_tercero`) = $tercero
                );";
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
?> <div class="contenedor bg-light" id="areaImprimir">
    <div class="px-2 " style="width:90% !important;margin: 0 auto;">

        </br>
        </br>
        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td colspan="14" style="text-align:center"><?php echo ''; ?></td>
            </tr>

            <tr>
                <td colspan="14" style="text-align:center"><?php echo $empresa['nombre']; ?></td>
            </tr>
            <tr>
                <td colspan="14" style="text-align:center"><?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></td>
            </tr>
            <tr>
                <td colspan="14" style="text-align:center"><?php echo 'RELACION DE TERCEROS CON CAUSACION Y PAGOS'; ?></td>
            </tr>
            <tr>
                <td colspan="14" style="text-align:center"><?php echo 'Fecha de inicial: ' . $fecha_inicial . ' Fecha final: ' . $fecha_final; ?></td>
            </tr>
            <tr>
                <td colspan="14" style="text-align:center"><?php echo ''; ?></td>
            </tr>
        </table>



        </br>
        <table class="table-bordered bg-light" style="width:100% !important;" border=1>
            <tr>
                <td>Fecha causación</td>
                <td>No causación</td>
                <td>Tercero</td>
                <td>cc/nit</td>
                <td>Concepto</td>
                <td>Banco</td>
                <td>Cuenta bancaria</td>
                <td>No Factura</td>
                <td>Rubro</td>
                <td>Valor bruto</td>
                <td>Descuentos</td>
                <td>Valor a pagar</td>
                <td>Valor pagado</td>
                <td>Saldo por pagar</td>
            </tr>
            <?php
            $id_t = [];
            foreach ($causaciones as $ca) {
                if ($ca['id_tercero'] !== null) {
                    $id_t[] = $ca['id_tercero'];
                }
            }

            foreach ($causaciones as $rp) {
                $url = $api . 'terceros/datos/res/datos/id/' . $rp['id_tercero'];
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $res_api = curl_exec($ch);
                curl_close($ch);
                $dat_ter = json_decode($res_api, true);
                $tercero = $dat_ter[0]['apellido1'] . ' ' . $dat_ter[0]['apellido2'] . ' ' . $dat_ter[0]['nombre1'] . ' ' . $dat_ter[0]['nombre2'] . ' ' . $dat_ter[0]['razon_social'];
                $ccnit = $dat_ter[0]['cc_nit'];
                // fin api terceros **************************
                $pagos = 0;
                $retenido = 0;
                $val_retenido = 0;
                $val_neto = 0;
                $cxp = 0;
                $saldo = 0;
                // Consulta valor pagado por documento y rubro
                $fecha = date('Y-m-d', strtotime($rp['fecha']));
                $sql = "SELECT
                            SUM(`seg_pto_mvto`.`valor`) as pagado
                            , `seg_pto_mvto`.`rubro`
                        FROM
                            `seg_pto_mvto`
                            INNER JOIN `seg_ctb_doc` 
                                ON (`seg_pto_mvto`.`id_ctb_cop` = `seg_ctb_doc`.`id_ctb_doc`)
                        WHERE (`seg_pto_mvto`.`id_ctb_cop` ='{$rp['id_ctb_doc']}'
                            AND `seg_pto_mvto`.`rubro` ='{$rp['rubro']}'
                            AND `seg_ctb_doc`.`fecha` <='$fecha_final');";
                $res = $cmd->query($sql);
                $pago = $res->fetch();
                $pagos = $pago['pagado'];
                //Consulta cuenta de banco donde se realizó el pago
                $sql = "SELECT
                            `seg_tes_detalle_pago`.`id_ctb_pag` 
                            , `seg_tes_cuentas`.`numero` 
                            , CONCAT(`seg_bancos`.`cod_banco`, ' - ', `seg_bancos`.`nom_banco`) AS banco
                        FROM
                            `seg_tes_cuentas`
                            INNER JOIN `seg_bancos` 
                                ON (`seg_tes_cuentas`.`id_banco` = `seg_bancos`.`id_banco`)
                            INNER JOIN `seg_tes_detalle_pago` 
                                ON (`seg_tes_detalle_pago`.`id_tes_cuenta` = `seg_tes_cuentas`.`id_tes_cuenta`)
                        WHERE (`seg_tes_detalle_pago`.`id_ctb_pag` ='{$rp['id_ctb_doc']}');";
                $res = $cmd->query($sql);
                $cuenta = $res->fetch();
                $banco = $cuenta['banco'];
                // consulto valor retenido a cada documento
                $sql = "SELECT
                                SUM(`seg_ctb_causa_retencion`.`valor_retencion`) as retenido
                        FROM
                            `seg_ctb_causa_retencion`
                            INNER JOIN `seg_ctb_doc` 
                                ON (`seg_ctb_causa_retencion`.`id_ctb_doc` = `seg_ctb_doc`.`id_ctb_doc`)
                        WHERE (`seg_ctb_causa_retencion`.`id_ctb_doc` ={$rp['id_ctb_doc']})
                        GROUP BY `seg_ctb_causa_retencion`.`id_ctb_doc`;";
                $res = $cmd->query($sql);
                $reten = $res->fetch();
                $retenido = $reten['retenido'];
                // Consulto el valor total del pago realizado
                $sql = "SELECT
                        `id_ctb_doc`
                        , `tipo_mov`
                        , SUM(`valor`) as total_pago
                    FROM
                        `seg_pto_mvto`
                    WHERE (`id_ctb_doc` ={$rp['id_ctb_doc']}
                        AND `tipo_mov` ='COP')
                    GROUP BY `id_ctb_doc`;";
                $res = $cmd->query($sql);
                $pago_total = $res->fetch();
                $pago_total = $pago_total['total_pago'];
                // saco el valor retenido proporcional al valor pagado / total pagado
                $val_retenido = $retenido *  ($rp['valor'] / $pago_total);
                // redondear val_retenido
                $val_retenido = round($val_retenido, 0);
                $val_neto = $rp['valor'] - $val_retenido;
                if ($pagos > 0) {
                    $pagos = $pagos - $val_retenido;
                    $cta_banco = $cuenta['numero'];
                    $nom_banco = $cuenta['banco'];
                } else {
                    $pagos = 0;
                    $cta_banco = '';
                    $nom_banco = '';
                }
                $cxp = $val_neto - $pagos;
                $saldo = 1;
                if ($saldo > 0) {
                    echo "<tr>
                    <td class='text-right'>" . $fecha  . "</td>
                    <td class='text-right'>" . $rp['id_manu'] . "</td>
                    <td class='text'>" . $tercero .  "</td>
                    <td class='text-left'>" . $ccnit . "</td>
                    <td class='text-right'>" .  $rp['detalle']  . "</td>
                    <td class='text'>" . $nom_banco .  "</td>
                    <td class='text'>" . $cta_banco .  "</td>
                    <td class='text'>" . $rp['num_doc'] . "</td>
                    <td class='text-right'>" . $rp['rubro'] . "</td>
                    <td class='text-right'>" . $rp['valor']   . "</td>
                    <td class='text-right'>" . $val_retenido . "</td>
                    <td class='text-right'>" . $val_neto . "</td>
                    <td class='text-right'>" . $pagos  . "</td>
                    <td class='text-right'>" . $cxp . "</td>
                    </tr>";
                }
            }
            ?>

        </table>
        </br>
        </br>
        </br>

    </div>

</div>

</html>