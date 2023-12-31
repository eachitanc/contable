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
                `seg_pto_documento`.`id_pto_doc`
                , `seg_pto_mvto`.`id_auto_dep`
                , `seg_pto_mvto`.`tipo_mov`
                , `seg_pto_mvto`.`rubro`
                , sum(`seg_pto_mvto`.`valor`) as valor
                , IF(`seg_pto_mvto`.`id_tercero_api`,`seg_pto_mvto`.`id_tercero_api`,`seg_pto_documento`.`id_tercero`) AS id_tercero
                , `seg_pto_documento`.`id_manu`
                , `seg_pto_documento`.`objeto`
                , `seg_pto_documento`.`fecha`
            FROM
                `seg_pto_mvto`
                INNER JOIN `seg_pto_documento` 
                    ON (`seg_pto_mvto`.`id_pto_doc` = `seg_pto_documento`.`id_pto_doc`)
            WHERE `seg_pto_documento`.`fecha` <='$fecha_corte' AND `seg_pto_mvto`.`tipo_mov` ='CRP' 
            GROUP BY id_pto_doc,rubro;
";
    $res = $cmd->query($sql);
    $cdp = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consulto los valores unicos id_tercero de la tabla seg_pto_documento
try {
    $sql = "SELECT DISTINCT `id_tercero` FROM `seg_pto_documento` WHERE `id_tercero` IS NOT NULL;";
    $res = $cmd->query($sql);
    $id_terceros = $res->fetchAll();
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
<table style="width:100% !important; border-collapse: collapse;">
    <thead>
        <tr>
            <td rowspan="4" style="text-align:center"><label class="small"><img src="<?php echo $_SESSION['urlin'] ?>/images/logos/logo.png" width="100"></label></td>
            <td colspan="12" style="text-align:center"><?php echo $empresa['nombre']; ?></td>
        </tr>
        <tr>
            <td colspan="12" style="text-align:center"><?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></td>
        </tr>
        <tr>
            <td colspan="12" style="text-align:center"><?php echo 'ESTADO DE CUENTAS POR PAGAR'; ?></td>
        </tr>
        <tr>
            <td colspan="12" style="text-align:center"><?php echo 'Fecha de corte: ' . $fecha_corte; ?></td>
        </tr>
        <tr style="background-color: #CED3D3; text-align:center;font-size:9px;">
            <th>Fecha</th>
            <th>No CDP</th>
            <th>No CRP</th>
            <th>Tercero</th>
            <th>cc/nit</th>
            <th>detalle</th>
            <th>Rubro</th>
            <th>Valor registrado</th>
            <th>Valor causado</th>
            <th>Valor Pagado</th>
            <th>Compromisos por pagar</th>
            <th>Cuentas por pagar</th>
            <th>auxiliar</th>
        </tr>
    </thead>
    <tbody style="font-size:9px;">
        <?php
        $id_t = [];
        foreach ($id_terceros as $ca) {
            if ($ca['id_tercero'] !== null) {
                $id_t[] = $ca['id_tercero'];
            }
        }
        $payload = json_encode($id_t);
        //API URL
        //API URL
        $url = $api . 'terceros/datos/res/lista/terceros';
        $ch = curl_init($url);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        $terceros = json_decode($result, true);
        foreach ($cdp as $rp) {
            $valor = 0;
            $fecha = date('Y-m-d', strtotime($rp['fecha']));

            // Consultar el valor registrado por rubro y cdp 

            $key = array_search($rp['id_tercero'], array_column($terceros, 'id_tercero'));
            $tercero = $terceros[$key]['nombre1'] . ' ' . $terceros[$key]['nombre2'] . ' ' . $terceros[$key]['apellido1'] . ' ' . $terceros[$key]['apellido2'] . ' ' . $terceros[$key]['razon_social'];
            $cc_nit = $terceros[$key]['cc_nit'];
            // consulto el valor liquidado
            $sql = "SELECT
                SUM(`seg_pto_mvto`.`valor`) AS valor
                FROM
                    `seg_pto_mvto`
                    INNER JOIN `seg_pto_documento` ON (`seg_pto_mvto`.`id_pto_doc` = `seg_pto_documento`.`id_pto_doc`)
                WHERE `seg_pto_documento`.`fecha` <='$fecha_corte' 
                AND `seg_pto_mvto`.`tipo_mov` ='LRP' 
                AND `seg_pto_mvto`.`id_auto_crp` =$rp[id_pto_doc] 
                AND `seg_pto_mvto`.`rubro` ='{$rp['rubro']}' 
                AND `seg_pto_mvto`.`estado`=0
                GROUP BY `seg_pto_mvto`.id_pto_doc,rubro;";
            $res = $cmd->query($sql);
            $lrp = $res->fetch();

            // Consulto el valor causado
            $sql = "SELECT
                        SUM(`seg_pto_mvto`.`valor`) AS valor
                    FROM
                        `seg_pto_mvto`
                        INNER JOIN `seg_ctb_doc` ON (`seg_pto_mvto`.`id_ctb_doc` = `seg_ctb_doc`.`id_ctb_doc`)
                    WHERE `seg_pto_mvto`.`tipo_mov` ='COP'
                        AND `seg_pto_mvto`.`id_pto_doc` =$rp[id_pto_doc]
                        AND `seg_pto_mvto`.`rubro` ='{$rp['rubro']}'
                        AND `seg_pto_mvto`.`estado`=0
                        AND `seg_ctb_doc`.`fecha` <='$fecha_corte'
                    group by tipo_mov,id_pto_doc, rubro ;";
            $res = $cmd->query($sql);
            $cop = $res->fetch();
            // Consulto el valor pagado
            $sql = "SELECT
                        SUM(`seg_pto_mvto`.`valor`) AS valor
                    FROM
                        `seg_pto_mvto`
                        INNER JOIN `seg_ctb_doc` ON (`seg_pto_mvto`.`id_ctb_doc` = `seg_ctb_doc`.`id_ctb_doc`)
                    WHERE (`seg_pto_mvto`.`tipo_mov` ='PAG'
                        AND `seg_pto_mvto`.`id_pto_doc` =$rp[id_pto_doc]
                        AND `seg_pto_mvto`.`rubro` ='{$rp['rubro']}'
                        AND `seg_pto_mvto`.`estado`=0
                        AND `seg_ctb_doc`.`fecha` <='$fecha_corte')
                        group by tipo_mov,id_pto_doc, rubro ;";
            $res = $cmd->query($sql);
            $pag = $res->fetch();
            $valor = $rp['valor'] + $lrp['valor'];
            if ($valor > 0) {
                echo "<tr>
                <td style='text-aling:left'>" . $fecha .  "</td>
                <td style='text-aling:left'>" . $rp['id_manu'] . "</td>
                <td style='text-aling:left'>" . $rp['id_manu'] . "</td>
                <td style='text-aling:left'>" .     $tercero  . "</td>
                <td style='text-aling:left'>" .   $cc_nit  . "</td>
                <td style='text-aling:left'>" .  $rp['objeto'] . "</td>
                <td style='text-aling:left'>" . $rp['rubro']   . "</td>
                <td style='text-aling:right'>" . number_format($valor, 2, ".", ",")   . "</td>
                <td style='text-aling:right'>" .  number_format($cop['valor'], 2, ".", ",")  . "</td>
                <td style='text-aling:right'>" .  number_format($pag['valor'], 2, ".", ",")  . "</td>
                <td style='text-aling:right'>" .  number_format(($rp['valor'] - $cop['valor']), 2, ".", ",")  . "</td>
                <td style='text-aling:right'>" .  number_format(($cop['valor'] - $pag['valor']), 2, ".", ",")  . "</td>
                </tr>";
            }
        }
        ?>
    </tbody>
</table>




<div class="contenedor bg-light" id="areaImprimir">
    <div class="px-2 " style="width:90% !important;margin: 0 auto;">

        </br>
        </br>
        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td colspan="13" style="text-align:center"><?php echo ''; ?></td>
            </tr>

            <tr>
                <td colspan="13" style="text-align:center"><?php echo $empresa['nombre']; ?></td>
            </tr>
            <tr>
                <td colspan="13" style="text-align:center"><?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></td>
            </tr>
            <tr>
                <td colspan="13" style="text-align:center"><?php echo ' '; ?></td>
            </tr>
            <tr>
                <td colspan="13" style="text-align:center"><?php echo 'Fecha de corte: ' . $fecha_corte; ?></td>
            </tr>
            <tr>
                <td colspan="13" style="text-align:center"><?php echo ''; ?></td>
            </tr>
        </table>



        </br>
        <table class="table-bordered bg-light" style="width:100% !important;" border=1>
            <tr>
                <td>Fecha</td>
                <td>No CDP</td>
                <td>No CRP</td>
                <td>Tercero</td>
                <td>cc/nit</td>
                <td>detalle</td>
                <td>Rubro</td>
                <td>Valor registrado</td>
                <td>Valor causado</td>
                <td>Valor Pagado</td>
                <td>Compromisos por pagar</td>
                <td>Cuentas por pagar</td>
                <td>auxiliar</td>
            </tr>
            <?php


            ?>

        </table>
        </br>
        </br>
        </br>

    </div>

</div>

</html>