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
    `seg_pto_mvto`.`tipo_mov`
    , `seg_ctb_doc`.`id_manu`
    , `seg_ctb_doc`.`fecha`
    , IF(`seg_pto_mvto`.`id_tercero_api`,`seg_pto_mvto`.`id_tercero_api`,`seg_ctb_doc`.`id_tercero`) AS id_terceroapi
    , `seg_ctb_doc`.`detalle`
    , `seg_pto_mvto`.`rubro`
    , `seg_pto_cargue`.`nom_rubro`
    , `seg_pto_mvto`.`valor`
    , `seg_pto_mvto`.`id_ctb_cop`
FROM
    `seg_pto_mvto`
    INNER JOIN `seg_ctb_doc` 
        ON (`seg_pto_mvto`.`id_ctb_doc` = `seg_ctb_doc`.`id_ctb_doc`)
    INNER JOIN `seg_pto_cargue` 
        ON (`seg_pto_mvto`.`rubro` = `seg_pto_cargue`.`cod_pptal`)
WHERE `seg_ctb_doc`.`fecha` <='$fecha_corte' AND `seg_pto_mvto`.`tipo_mov` = 'PAG' AND `seg_pto_mvto`.`estado` <> 5
ORDER BY `seg_ctb_doc`.`fecha` ASC;
";
    $res = $cmd->query($sql);
    $causaciones = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$sql2 = $sql;
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
                <td colspan="11" style="text-align:center"><?php echo ''; ?></td>
            </tr>

            <tr>
                <td colspan="11" style="text-align:center"><?php echo $empresa['nombre']; ?></td>
            </tr>
            <tr>
                <td colspan="11" style="text-align:center"><?php echo $empresa['nit'] . '-' . $empresa['dig_ver'] . $sql2; ?></td>
            </tr>
            <tr>
                <td colspan="11" style="text-align:center"><?php echo 'RELACION DE EGRESOS PRESUPUESTALES'; ?></td>
            </tr>
            <tr>
                <td colspan="11" style="text-align:center"><?php echo 'Fecha de corte: ' . $fecha_corte; ?></td>
            </tr>
            <tr>
                <td colspan="11" style="text-align:center"><?php echo ''; ?></td>
            </tr>
        </table>



        </br>
        <table class="table-bordered bg-light" style="width:100% !important;" border=1>
            <tr>
                <td>Tipo</td>
                <td>No Registro</td>
                <td>No Egreso</td>
                <td>Fecha</td>
                <td>Tercero</td>
                <td>Cc/Nit</td>
                <td>Objeto</td>
                <td>Rubro</td>
                <td>Nombre rubro</td>
                <td>Valor</td>
            </tr>
            <?php
            // Consulto los id de terceros creado en la tabla seg_ctb_doc
            try {
                $sql = "SELECT DISTINCT
                `id_tercero_api`
            FROM
                `seg_terceros`;";
                $res = $cmd->query($sql);
                $id_terceros = $res->fetchAll();
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
            }
            $id_t = [];
            foreach ($id_terceros as $ter) {
                $id_t[] = $ter['id_tercero_api'];
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
                $key = array_search($rp['id_terceroapi'], array_column($terceros, 'id_tercero'));
                $tercero = $terceros[$key]['apellido1'] . ' ' .  $terceros[$key]['apellido2'] . ' ' . $terceros[$key]['nombre2'] . ' ' .  $terceros[$key]['nombre1'] . ' ' .  $terceros[$key]['razon_social'];
                $ccnit = $terceros[$key]['cc_nit'];
                if ($tercero == null) {
                    $recero = 'NOMINA DE EMPLEADOS';
                }
                //Consultar el numero del registro
                try {
                    $sql = "SELECT
                    `seg_pto_documento`.`id_manu`
                    , `seg_pto_mvto`.`id_ctb_cop`
                FROM
                    `seg_ctb_libaux`
                    INNER JOIN `seg_pto_documento` 
                        ON (`seg_ctb_libaux`.`id_crp` = `seg_pto_documento`.`id_pto_doc`)
                    INNER JOIN `seg_pto_mvto` 
                        ON (`seg_pto_mvto`.`id_ctb_cop` = `seg_ctb_libaux`.`id_ctb_doc`)
                WHERE `seg_pto_mvto`.`id_ctb_cop` =$rp[id_ctb_cop] LIMIT 1;";
                    $res = $cmd->query($sql);
                    $registro = $res->fetch();
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
                }

                $fecha = date('Y-m-d', strtotime($rp['fecha']));
                echo "<tr>
                    <td class='text'>" . $rp['tipo_mov'] .  "</td>
                    <td class='text-left'>" . $registro['id_manu'] . "</td>
                    <td class='text-left'>" . $rp['id_manu'] . "</td>
                    <td class='text-right'>" .   $fecha   . "</td>
                    <td class='text-right'>" .   $tercero . "</td>
                    <td class='text-right'>" . $ccnit . "</td>
                    <td class='text-right'>" . $rp['detalle'] . "</td>
                    <td class='text'>" . $rp['rubro'] . "</td>
                    <td class='text-right'>" .  $rp['nom_rubro'] . "</td>
                    <td class='text-right'>" . number_format($rp['valor'], 2, ".", ",")  . "</td>
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