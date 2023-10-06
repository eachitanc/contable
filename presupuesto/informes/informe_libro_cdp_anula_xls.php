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
    header("Content-Disposition: attachment; filename=FORMATO_LIBRO_DISPONIBILIDADES.xls");
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
    , `seg_pto_documento`.`fecha` as fecha
    , `seg_pto_documento`.`objeto`
    , `seg_pto_mvto`.`rubro`
    , `seg_pto_cargue`.`nom_rubro`
    , sum(`seg_pto_mvto`.`valor`) as valor
    , `seg_pto_mvto`.`id_pto_doc`
    , seg_pto_anula.fecha as fecha_anula
    ,seg_pto_anula.concepto
    ,CONCAT(seg_usuarios.nombre1,' ', seg_usuarios.nombre2,' ',seg_usuarios.apellido1,' ',seg_usuarios.apellido2)as usuario
    FROM
        `seg_pto_mvto`
        LEFT JOIN `seg_pto_cargue` ON (`seg_pto_mvto`.`rubro` = `seg_pto_cargue`.`cod_pptal`)
        INNER JOIN `seg_pto_documento` ON (`seg_pto_mvto`.`id_pto_doc` = `seg_pto_documento`.`id_pto_doc`)
        INNER JOIN seg_pto_anula ON (seg_pto_documento.id_pto_doc = seg_pto_anula.id_pto_doc)
        INNER JOIN seg_usuarios ON (seg_pto_anula.id_user_reg = seg_usuarios.id_usuario)
    WHERE ((`seg_pto_mvto`.`tipo_mov` ='CDP' OR `seg_pto_mvto`.`tipo_mov`='LCD') AND `seg_pto_documento`.`fecha` <= '$fecha_corte' AND `seg_pto_documento`.`estado`=5)
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
?> <div class="contenedor bg-light" id="areaImprimir">
    <div class="px-2 " style="width:90% !important;margin: 0 auto;">

        </br>
        </br>
        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td colspan="7" style="text-align:center"><?php echo ''; ?></td>
            </tr>

            <tr>
                <td colspan="7" style="text-align:center"><?php echo $empresa['nombre']; ?></td>
            </tr>
            <tr>
                <td colspan="7" style="text-align:center"><?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></td>
            </tr>
            <tr>
                <td colspan="7" style="text-align:center"><?php echo 'RELACION DE CERTIFICADOS DE DISPONIBILIDAD PRESUPUESTAL ANULADOS'; ?></td>
            </tr>
            <tr>
                <td colspan="7" style="text-align:center"><?php echo 'Fecha de corte: ' . $fecha_corte; ?></td>
            </tr>
            <tr>
                <td colspan="7" style="text-align:center"><?php echo ''; ?></td>
            </tr>
        </table>



        </br>
        <table class="table-bordered bg-light" style="width:100% !important;" border=1>
            <tr>
                <td>Tipo</td>
                <td>No disponibilidad</td>
                <td>Fecha</td>
                <td>Objeto</td>
                <td>Rubro</td>
                <td>Nombre rubro</td>
                <td>Valor</td>
                <td>Saldo</td>
                <td>Fecha anulación</td>
                <td>Concepto anulación</td>
                <td>Usuario anulación</td>

            </tr>
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
                $saldo =  $valor_cdp - $reg2['valor_rp'];
                $fecha = date('Y-m-d', strtotime($rp['fecha']));
                $fecha_anula = date('Y-m-d', strtotime($rp['fecha_anula']));
                if ($saldo >= 0) {
                    echo "<tr>
                <td class='text'>" . $rp['tipo_mov'] .  "</td>
                <td class='text-left'>" . $rp['id_manu'] . "</td>
                <td class='text-right'>" .   $fecha   . "</td>
                <td class='text-right'>" . $rp['objeto'] . "</td>
                <td class='text'>" . $rp['rubro'] . "</td>
                <td class='text-right'>" .  $rp['nom_rubro'] . "</td>
                <td class='text-right'>" . number_format($valor_cdp, 2, ".", ",")  . "</td>
                <td class='text-right'>" . number_format($saldo, 2, ".", ",") . "</td>
                <td class='text-right'>" .   $fecha_anula . "</td>
                <td class='text-right'>" .  $rp['concepto'] . "</td>
                <td class='text-right'>" .  $rp['usuario'] . "</td>
                </tr>";
                    $saldo = 0;
                    $valor_cdp = 0;
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