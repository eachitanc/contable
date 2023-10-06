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
    header("Content-Disposition: attachment; filename=FORMATO_LIBRO_COMPROMIDOS.xls");
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
    `seg_pto_mvto`.`tipo_mov`
    , `seg_pto_documento`.`id_manu`
    , `seg_pto_documento`.`fecha`
    , `seg_pto_documento`.`id_tercero`
    , `seg_pto_mvto`.`id_tercero_api`
    , `seg_pto_documento`.`objeto`
    , `seg_pto_documento`.`num_contrato`
    , `seg_pto_documento`.`id_auto`
    , `seg_pto_mvto`.`rubro`
    , `seg_pto_cargue`.`nom_rubro`
    , `seg_pto_mvto`.`valor`
    , `seg_pto_mvto`.`id_pto_doc`
FROM
    `seg_pto_mvto`
    LEFT JOIN `seg_pto_cargue` 
        ON (`seg_pto_mvto`.`rubro` = `seg_pto_cargue`.`cod_pptal`)
    INNER JOIN `seg_pto_documento` 
        ON (`seg_pto_mvto`.`id_pto_doc` = `seg_pto_documento`.`id_pto_doc`)
WHERE `seg_pto_mvto`.`tipo_mov` ='CRP' AND `seg_pto_documento`.`fecha` <= '$fecha_corte' AND `seg_pto_documento`.`estado` = 0
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
?> <div class="contenedor bg-light" id="areaImprimir">
    <div class="px-2 " style="width:90% !important;margin: 0 auto;">

        </br>
        </br>
        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td colspan="9" style="text-align:center"><?php echo ''; ?></td>
            </tr>

            <tr>
                <td colspan="9" style="text-align:center"><?php echo $empresa['nombre']; ?></td>
            </tr>
            <tr>
                <td colspan="9" style="text-align:center"><?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></td>
            </tr>
            <tr>
                <td colspan="9" style="text-align:center"><?php echo 'RELACION DE CERTIFICADOS DE REGISTRO PRESUPUESTAL'; ?></td>
            </tr>
            <tr>
                <td colspan="9" style="text-align:center"><?php echo 'Fecha de corte: ' . $fecha_corte; ?></td>
            </tr>
            <tr>
                <td colspan="9" style="text-align:center"><?php echo ''; ?></td>
            </tr>
        </table>



        </br>
        <table class="table-bordered bg-light" style="width:100% !important;" border=1>
            <tr>
                <td>Tipo</td>
                <td>No CDP</td>
                <td>Fecha CDP</td>
                <td>No Registro</td>
                <td>Fecha RP</td>
                <td>Tercero</td>
                <td>Cc/Nit</td>
                <td>No Contrato</td>
                <td>Objeto</td>
                <td>Rubro</td>
                <td>Nombre rubro</td>
                <td>Valor</td>

            </tr>
            <?php
            $id_t = [];
            foreach ($causaciones as $ca) {
                if ($ca['id_tercero_api'] == null) {
                    $id_t[] = $ca['id_tercero'];
                } else {
                    $id_t[] = $ca['id_tercero_api'];
                }
            }
            $payload = json_encode($id_t);
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
            foreach ($causaciones as $rp) {
                // Consulta de datos del cdp
                try {
                    $sql = "SELECT `id_manu`, `fecha` FROM `seg_pto_documento` WHERE id_pto_doc ={$rp['id_auto']} ;";
                    $res = $cmd->query($sql);
                    $datos_cdp = $res->fetch();
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
                }
                // Consulto datos de liberación de saldos
                try {
                    $sql = "SELECT
                                `seg_pto_documento`.`tipo_doc`
                                , SUM(`seg_pto_mvto`.`valor`) as liquidado
                            FROM
                                `seg_pto_mvto`
                                INNER JOIN `seg_pto_documento` 
                                    ON (`seg_pto_mvto`.`id_pto_doc` = `seg_pto_documento`.`id_pto_doc`)
                            WHERE (`seg_pto_documento`.`tipo_doc` ='LRP'
                                AND `seg_pto_mvto`.`rubro` ='{$rp['rubro']}'
                                AND `seg_pto_mvto`.`id_auto_crp` ={$rp['id_pto_doc']}
                                AND `seg_pto_documento`.`fecha` <= '$fecha_corte')
                            GROUP BY `seg_pto_mvto`.`rubro`, `seg_pto_mvto`.`id_pto_doc`;";
                    $res = $cmd->query($sql);
                    $liberaciones = $res->fetch();
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
                }
                if ($rp['id_tercero_api'] == null) {
                    $id_tercero = $rp['id_tercero'];
                } else {
                    $id_tercero = $rp['id_tercero_api'];
                }
                $key = array_search($id_tercero, array_column($terceros, 'id_tercero'));
                $tercero = $terceros[$key]['apellido1'] . ' ' .  $terceros[$key]['apellido2'] . ' ' . $terceros[$key]['nombre2'] . ' ' .  $terceros[$key]['nombre1'] . ' ' .  $terceros[$key]['razon_social'];
                $ccnit = $terceros[$key]['cc_nit'];

                $fecha = date('Y-m-d', strtotime($rp['fecha']));
                $fecha_cdp = date('Y-m-d', strtotime($datos_cdp['fecha']));
                $valor = $rp['valor'] + $liberaciones['liquidado'];
                echo "<tr>
                <td class='text'>" . $rp['tipo_mov'] .  "</td>
                <td class='text-left'>" . $datos_cdp['id_manu'] . "</td>
                <td class='text-right'>" .   $fecha_cdp   . "</td>
                <td class='text-left'>" . $rp['id_manu'] . "</td>
                <td class='text-right'>" .   $fecha   . "</td>
                <td class='text-right'>" .   $tercero . "</td>
                <td class='text-right'>" . $ccnit . "</td>
                <td class='text-right'>" . $rp['num_contrato'] . "</td>
                <td class='text-right'>" . $rp['objeto'] . "</td>
                <td class='text'>" . $rp['rubro'] . "</td>
                <td class='text-right'>" .  $rp['nom_rubro'] . "</td>
                <td class='text-right'>" . number_format($valor, 2, ".", ",")  . "</td>
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