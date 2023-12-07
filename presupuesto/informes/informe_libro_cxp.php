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
                `seg_pto_documento`.`fecha`
                , `seg_pto_documento`.`id_tercero`
                , `seg_pto_mvto`.`id_tercero_api`
                , `seg_pto_documento`.`id_manu`
                , `seg_pto_documento`.`objeto`
                , `seg_pto_mvto`.`rubro`
                , SUM(`seg_pto_mvto`.`valor`) as valor
                , `seg_pto_mvto`.`tipo_mov`
                , `seg_pto_mvto`.`id_pto_doc`
            FROM
                `seg_pto_mvto`
                INNER JOIN `seg_pto_documento` 
                    ON (`seg_pto_mvto`.`id_pto_doc` = `seg_pto_documento`.`id_pto_doc`)
            WHERE (`seg_pto_documento`.`fecha` <='$fecha_corte'
                AND `seg_pto_mvto`.`tipo_mov` ='CDP')
                GROUP BY `seg_pto_mvto`.`id_pto_doc`,`seg_pto_mvto`.`rubro`;
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
            <td colspan="13" style="text-align:center"><?php echo $empresa['nombre']; ?></td>
        </tr>
        <tr>
            <td colspan="13" style="text-align:center"><?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></td>
        </tr>
        <tr>
            <td colspan="13" style="text-align:center"><?php echo 'ESTADO DE CUENTAS POR PAGAR'; ?></td>
        </tr>
        <tr>
            <td colspan="13" style="text-align:center"><?php echo 'Fecha de corte: ' . $fecha_corte; ?></td>
        </tr>
        <tr style="background-color: #CED3D3; text-align:center;font-size:9px;">
            <th>Fecha</th>
            <th>No CDP</th>
            <th>No CRP</th>
            <th>Fecha causacion</th>
            <th>Tercero</th>
            <th>cc/nit</th>
            <th>detalle</th>
            <th>Rubro</th>
            <th>Valor Disponibilidad</th>
            <th>Valor registrado</th>
            <th>Valor causado</th>
            <th>Valor Pagado</th>
            <th>Compromisos por pagar</th>
            <th>Cuentas por pagar</th>
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
            $fecha = date('Y-m-d', strtotime($rp['fecha']));

            // Consultar el valor registrado por rubro y cdp 

            $sql = "SELECT
                        `seg_pto_mvto`.`tipo_mov`
                        , `seg_pto_mvto`.`rubro`
                        , SUM(`seg_pto_mvto`.`valor`) as valor
                        , `seg_pto_documento`.`id_tercero`
                        , `seg_pto_documento`.`id_manu`
                    FROM
                        `seg_pto_mvto`
                        INNER JOIN `seg_pto_documento` 
                            ON (`seg_pto_mvto`.`id_pto_doc` = `seg_pto_documento`.`id_pto_doc`)
                    WHERE (`seg_pto_mvto`.`tipo_mov` ='CRP'
                        AND `seg_pto_mvto`.`rubro` ='{$rp['rubro']}'
                        AND `seg_pto_documento`.`fecha` <='$fecha_corte'
                        AND `seg_pto_mvto`.`id_auto_dep` =$rp[id_pto_doc]);";
            $res = $cmd->query($sql);
            $crp = $res->fetch();
            if ($crp['id_tercero'] == '') {
                $tercero = '';
                $cc_nit = '';
            } else {
                $key = array_search($crp['id_tercero'], array_column($terceros, 'id_tercero'));
                $tercero = $terceros[$key]['nombre1'] . ' ' . $terceros[$key]['nombre2'] . ' ' . $terceros[$key]['apellido1'] . ' ' . $terceros[$key]['apellido2'] . ' ' . $terceros[$key]['razon_social'];
                $cc_nit = $terceros[$key]['cc_nit'];
            }
            // Consulto el valor causado
            /*
            $sql = "SELECT
                        `tipo_mov`
                        , `rubro`
                        ,  SUM(`valor`) as valor
                        , `id_auto_dep`
                    FROM
                        `seg_pto_mvto`
                    WHERE (`tipo_mov` ='COP'
                        AND `rubro` ='{$rp['rubro']}'
                        AND `id_auto_dep` =$rp[id_pto_doc]);";
            */
            $sql = "SELECT
                        SUM(`seg_pto_mvto`.`valor`) as valor
                        , `seg_ctb_doc`.`fecha`
                    FROM
                        `seg_pto_mvto`
                        INNER JOIN `seg_ctb_doc` 
                            ON (`seg_pto_mvto`.`id_ctb_doc` = `seg_ctb_doc`.`id_ctb_doc`)
                    WHERE (`seg_pto_mvto`.`tipo_mov` ='COP'
                        AND `seg_pto_mvto`.`rubro` ={$rp['rubro']}
                        AND `seg_pto_mvto`.`id_auto_dep` =$rp[id_pto_doc]
                        AND `seg_ctb_doc`.`fecha` <='$fecha_corte');";
            $res = $cmd->query($sql);
            $cop = $res->fetch();
            // Consulto el valor pagado
            $sql = "SELECT
                        `tipo_mov`
                        , `rubro`
                        ,  SUM(`valor`) as valor
                        , `id_auto_dep`
                    FROM
                        `seg_pto_mvto`
                        INNER JOIN `seg_ctb_doc` 
                            ON (`seg_pto_mvto`.`id_ctb_doc` = `seg_ctb_doc`.`id_ctb_doc`)
                    WHERE (`seg_pto_mvto`.`tipo_mov` ='PAG'
                        AND `seg_pto_mvto`.`rubro` ={$rp['rubro']}
                        AND `seg_pto_mvto`.`id_auto_dep` =$rp[id_pto_doc]
                        AND `seg_ctb_doc`.`fecha` <='$fecha_corte');";
            $res = $cmd->query($sql);
            $pag = $res->fetch();
            if ($cop['fecha'] == null) {
                $fecha_causa = '';
            } else {
                $fecha_causa = date('Y-m-d', strtotime($cop['fecha']));
            }
            echo "<tr>
            <td style='text-aling:left'>" . $fecha .  "</td>
            <td style='text-aling:left'>" . $rp['id_manu'] . "</td>
            <td style='text-aling:left'>" . $crp['id_manu'] . "</td>
            <td style='text-aling:left'>" . $fecha_causa . "</td>
            <td style='text-aling:left'>" .     $tercero  . "</td>
            <td style='text-aling:left'>" .   $cc_nit  . "</td>
            <td style='text-aling:left'>" .  $rp['objeto'] . "</td>
            <td style='text-aling:left'>" . $rp['rubro']   . "</td>
            <td style='text-aling:right'>" . number_format($rp['valor'], 2, ".", ",")  . "</td>
            <td style='text-aling:right'>" . number_format($crp['valor'], 2, ".", ",")   . "</td>
            <td style='text-aling:right'>" .  number_format($cop['valor'], 2, ".", ",")  . "</td>
            <td style='text-aling:right'>" .  number_format($pag['valor'], 2, ".", ",")  . "</td>
            <td style='text-aling:right'>" .  number_format(($crp['valor'] - $cop['valor']), 2, ".", ",")  . "</td>
            <td style='text-aling:right'>" .  number_format(($cop['valor'] - $pag['valor']), 2, ".", ",")  . "</td>
            </tr>";
            $tercero = '';
            $cc_nit = '';
        }
        ?>
    </tbody>
</table>