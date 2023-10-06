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
    `seg_ctb_doc`.`tipo_doc`
    , `seg_ctb_doc`.`fecha`
    , `seg_ctb_doc`.`id_manu`
    , `seg_ctb_doc`.`fecha`
    , `seg_ctb_libaux`.`id_tercero` AS id_tercero_api
    , `seg_ctb_doc`.`id_tercero`
    , `seg_ctb_doc`.`detalle`
    , `seg_ctb_doc`.`id_ctb_doc`
    , `seg_ctb_libaux`.`id_crp`
    , `seg_pto_mvto`.`rubro`
    , `seg_pto_mvto`.`id_ctb_cop`
FROM
    `seg_ctb_libaux`
    INNER JOIN `seg_ctb_doc` 
        ON (`seg_ctb_libaux`.`id_ctb_doc` = `seg_ctb_doc`.`id_ctb_doc`)
    LEFT JOIN `seg_pto_mvto`
	ON (`seg_ctb_libaux`.`id_ctb_doc` = `seg_pto_mvto`.`id_ctb_doc`)
WHERE (`seg_ctb_doc`.`tipo_doc` ='CEVA' )
GROUP BY `seg_ctb_doc`.`id_manu`
ORDER BY `seg_ctb_doc`.`id_manu` ASC ;
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
?> <div class="contenedor bg-light" id="areaImprimir">
    <div class="px-2 " style="width:90% !important;margin: 0 auto;">

        </br>
        </br>
        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td colspan="8" style="text-align:center"><?php echo ''; ?></td>
            </tr>

            <tr>
                <td colspan="8" style="text-align:center"><?php echo $empresa['nombre']; ?></td>
            </tr>
            <tr>
                <td colspan="8" style="text-align:center"><?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></td>
            </tr>
            <tr>
                <td colspan="8" style="text-align:center"><?php echo 'RELACION DE PAGOS REALIZADOS'; ?></td>
            </tr>
            <tr>
                <td colspan="8" style="text-align:center"><?php echo 'Fecha de corte: ' . $fecha_corte; ?></td>
            </tr>
            <tr>
                <td colspan="8" style="text-align:center"><?php echo ''; ?></td>
            </tr>
        </table>



        </br>
        <table class="table-bordered bg-light" style="width:100% !important;" border=1>
            <tr>
                <td>Fecha</td>
                <td>No Egreso</td>
                <td>Tercero</td>
                <td>cc/nit</td>
                <td>detalle</td>
                <td>Rubro</td>
                <td>Causación</td>
                <td>Fecha causación</td>
                <td>Cuenta Banco</td>
                <td>valor causado</td>
                <td>Valor retenido</td>
                <td>Pagado</td>
                <td>Saldo por pagar</td>
            </tr>
            <?php
            $id_t = [];
            foreach ($causaciones as $ca) {
                if ($ca['id_tercero'] !== null) {
                    $id_t[] = $ca['id_tercero'];
                }
            }
            $payload = json_encode($id_t);
            //API URL
            $url = $api . 'terceros/datos/res/datos/cuenta_bancaria';
            $ch = curl_init($url);
            //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);
            $bancos = json_decode($result, true);

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
                $id_ctb_doc = $rp['id_ctb_doc'];
                $fecha = date('Y-m-d', strtotime($rp['fecha']));
                $sql = "SELECT
                            SUM(`valor`) as valor
                            , `id_ctb_cop`
                            , `id_ctb_doc`
                        FROM
                            `seg_pto_mvto`
                        WHERE (`id_ctb_doc` =$id_ctb_doc)";
                $res = $cmd->query($sql);
                $causa = $res->fetch();
                $causado = $causa['valor'];
                $id_cop = $causa['id_ctb_cop'];
                // Suma de valor pagado cuando no afecta presupuesto
                if ($rp['id_crp'] == 0) {
                    $sql = "SELECT
                            SUM(`debito`) as pagos
                            , `id_ctb_doc`
                        FROM
                            `seg_ctb_libaux`
                        WHERE (`id_ctb_doc` =$id_ctb_doc);";
                    $res = $cmd->query($sql);
                    $pago = $res->fetch();
                    $causado = $pago['pagos'];
                }
                // consulto valor retenido a cada documento
                if ($id_cop > 0) {
                    $sql = "SELECT
                        SUM(`valor_retencion`) as retenido
                        , `id_ctb_doc`
                        FROM
                        `seg_ctb_causa_retencion`
                        WHERE (`id_ctb_doc` =$id_cop)";
                    $res = $cmd->query($sql);
                    $reten = $res->fetch();
                    $retenido = $reten['retenido'];
                } else {
                    $retenido = 0;
                }
                // Consulto la cuenta bancaria 
                try {
                    $sql = "SELECT
                    `seg_tes_detalle_pago`.`id_ctb_doc`
                    , `seg_tes_cuentas`.`nombre`
                    , `seg_tes_cuentas`.`cta_contable`
                FROM
                    `seg_tes_detalle_pago`
                    INNER JOIN `seg_tes_cuentas` 
                        ON (`seg_tes_detalle_pago`.`id_tes_cuenta` = `seg_tes_cuentas`.`id_tes_cuenta`)
                WHERE (`seg_tes_detalle_pago`.`id_ctb_doc` ='$id_ctb_doc');";
                    $res = $cmd->query($sql);
                    $cuenta_ban = $res->fetch();
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
                }
                // Consulto la fecha de la causación
                if ($rp['id_ctb_cop'] > 0) {
                    try {
                        $sql = "SELECT
                    `fecha`
                    , `id_manu`
                FROM
                    `seg_ctb_doc`
                WHERE `id_ctb_doc` ={$rp['id_ctb_cop']};";
                        $res = $cmd->query($sql);
                        $causa = $res->fetch();
                    } catch (PDOException $e) {
                        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
                    }
                    $fecha_causa = date('Y-m-d', strtotime($causa['fecha']));
                    $id_manu = $causa['id_manu'];
                } else {
                    $fecha_causa = '';
                    $id_manu = '';
                }
                $pagado = $causado - $retenido;
                $saldo = $pagado - $pagado;
                echo "<tr>
                <td class='text'>" . $fecha .  "</td>
                <td class='text-left'>" . $rp['id_manu'] . "</td>
                <td class='text-right'>" .   $tercero   . "</td>
                <td class='text-right'>" . $ccnit . "</td>
                <td class='text-right'>" . $rp['detalle']   . "</td>
                <td class='text-right'>" . $rp['rubro']   . "</td>
                <td class='text-right'>" . $id_manu   . "</td>
                <td class='text-right'>" . $fecha_causa   . "</td>
                <td class='text-right'>" . $cuenta_ban['cta_contable'] . $cuenta_ban['nombre']   . "</td>
                <td class='text-right'>" . number_format($causado, 2, ".", ",")  . "</td>
                <td class='text-right'>" . number_format($retenido, 2, ".", ",")  . "</td>
                <td class='text-right'>" . number_format($pagado, 2, ".", ",")  . "</td>
                <td class='text-right'>" . number_format($saldo, 2, ".", ",")  . "</td>
                </tr>";
            }
            ?>

        </table>
        </br>
        </br>
        </br>

    </div>

</div>

</html>