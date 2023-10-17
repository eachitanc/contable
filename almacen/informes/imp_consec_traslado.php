<?php

session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
include '../../conexion.php';
$vigencia = isset($_POST['vigencia']) ? $_POST['vigencia'] : $_SESSION['vigencia'];
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
// consulto el nombre de la empresa de la tabla seg_empresas
try {
    $sql = "SELECT `nombre`, `nit`, `dig_ver` FROM `seg_empresas`";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `anio`
            FROM
                `con_vigencias`";
    $res = $cmd->query($sql);
    $vigencias = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `seg_traslados_almacen`.`id_trasl_alm`
                , `seg_bodega_almacen`.`nombre` AS `bg_sale`
                , `seg_bodega_almacen_1`.`nombre` AS `bg_entra`
                , `seg_traslados_almacen`.`fec_traslado`
                , `seg_traslados_almacen`.`estado`
                , `seg_traslados_almacen`.`vigencia`
            FROM
                `seg_traslados_almacen`
                INNER JOIN `seg_bodega_almacen` 
                    ON (`seg_traslados_almacen`.`id_bodega_sale` = `seg_bodega_almacen`.`id_bodega`)
                INNER JOIN `seg_bodega_almacen` AS `seg_bodega_almacen_1`
                    ON (`seg_traslados_almacen`.`id_bodega_entra` = `seg_bodega_almacen_1`.`id_bodega`)
            WHERE (`seg_traslados_almacen`.`vigencia` = '$vigencia')
            ORDER BY `seg_traslados_almacen`.`id_trasl_alm` DESC";
    $res = $cmd->query($sql);
    $datos = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
?>
<div class="form-row">
    <div class="form-group col-md-4">
        <label for="slcVigenia" class="small">Vigencia</label>
        <select name="slcVigencia" id="slcVigenia" class="form-control form-control-sm">
            <?php
            foreach ($vigencias as $vg) {
                $slc = $vg['anio'] == $vigencia ? 'selected' : '';
                echo '<option value="' . $vg['anio'] . '" ' . $slc . '>' . $vg['anio'] . '</option>';
            }
            ?>
        </select>
    </div>
    <div class="form-group col-md-2 offset-md-6 text-right">
        <label class="small">&nbsp;</label>
        <div>
            <a type="" id="btnReporteGral" class="btn btn-outline-success btn-sm" value="01" title="Exprotar a Excel">
                <span class="fas fa-file-excel fa-lg" aria-hidden="true"></span>
            </a>
            <a type="button" class="btn btn-primary btn-sm" title="Imprimir" onclick="imprSelecTes('areaImprimir',<?php echo 0; ?>);"><span class="fas fa-print fa-lg" aria-hidden="true"></span></a>
            <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" title="Cerrar"><span class="fas fa-times fa-lg" aria-hidden="true"></span></a>
        </div>
    </div>
</div>
<div class="contenedor bg-light" id="areaImprimir">
    <style>
        @media print {
            body {
                font-family: Arial, sans-serif;
            }
        }

        .resaltar:nth-child(even) {
            background-color: #F8F9F9;
        }

        .resaltar:nth-child(odd) {
            background-color: #ffffff;
        }
    </style>
    <div class="p-4 text-left ">
        <table class="page_break_avoid" style="width:100% !important;">
            <thead style="background-color: white !important;font-size:80%">
                <tr style="padding: bottom 3px; color:black">
                    <td colspan="5">
                        <table style="width:100% !important;">
                            <tr>
                                <td rowspan="3" class='text-center' style="width:18%"><span class="small"><img src="<?php echo $_SESSION['urlin'] ?>/images/logos/logo.png" width="100"></span></td>
                                <td colspan="9" style="text-align:center">
                                    <strong><?php echo $empresa['nombre']; ?> </strong>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="9" style="text-align:center">
                                    NIT <?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="7">
                                    <b>CONSECUTIVOS DE TRASLADOS</b>
                                </td>
                                <td colspan="2" style="scale: 0.7; text-align: right;">
                                    <table style="width:100% !important;">
                                        <tr>
                                            <td>Fecha Imp.</td>
                                            <td><?php echo $date->format('Y/m/d') ?></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr style="background-color: #CED3D3; text-align:center">
                    <th>ID</th>
                    <th>Bodega Sale</th>
                    <th>Bodega Entra</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody style="font-size: 80%;">
                <?php
                if (!empty($datos)) {
                    foreach ($datos as $dt) {
                        switch ($dt['estado']) {
                            case 0:
                                $estado = '<span class="badge badge-pill badge-secondary">ANULADO</span>';
                            case 1:
                                $estado = '<span class="badge badge-pill badge-info">PENDIENTE</span>';
                                break;
                            case 2:
                                $estado = '<span class="badge badge-pill badge-primary">ENTREGADO</span>';
                                break;
                            case 3:
                                $estado = '<span class="badge badge-pill badge-success">CERRADO</span>';
                                break;
                            default:
                                $estado = '<span class="badge badge-pill badge-warning">OTRO</span>';
                        }
                        echo '<tr class="resaltar">';
                        echo '<td>' . $dt['id_trasl_alm'] . '</td>';
                        echo '<td>' . $dt['bg_sale'] . '</td>';
                        echo '<td>' . $dt['bg_entra'] . '</td>';
                        echo '<td>' . $dt['fec_traslado'] . '</td>';
                        echo '<td style="text-align:center">' . $estado . '</td>';
                    }
                } else {
                    echo '<tr><td colspan="5" style="text-align:center">No hay datos</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

</div>