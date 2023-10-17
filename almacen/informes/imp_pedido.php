<?php

use Sabberworm\CSS\Value\Value;

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
$id_pedido = $_POST['id'];
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
                `seg_pedidos_almacen`.`id_pedido`
                ,`seg_pedidos_almacen`.`consecutivo`
                , `seg_pedidos_almacen`.`fec_cierre`
                , `seg_pedidos_almacen`.`fec_reg`
                , `seg_pedidos_almacen`.`id_user_reg`
                , `seg_bodega_almacen`.`nombre`
                , `seg_bodega_almacen_1`.`nombre` AS `area_entrega`
                , `seg_tipo_bien_servicio`.`tipo_bn_sv`
                , `seg_bien_servicio`.`bien_servicio`
                , `seg_detalle_pedido`.`cantidad`
                , `seg_bien_servicio`.`id_tipo_bn_sv`
                , `seg_detalle_pedido`.`id_producto`
            FROM
                `seg_detalle_pedido`
                INNER JOIN `seg_pedidos_almacen` 
                    ON (`seg_detalle_pedido`.`id_pedido` = `seg_pedidos_almacen`.`id_pedido`)
                INNER JOIN `seg_bodega_almacen` 
                    ON (`seg_pedidos_almacen`.`id_bodega` = `seg_bodega_almacen`.`id_bodega`)
                INNER JOIN `seg_bodega_almacen` AS `seg_bodega_almacen_1`
                    ON (`seg_pedidos_almacen`.`bod_entrega` = `seg_bodega_almacen_1`.`id_bodega`)
                INNER JOIN `seg_bien_servicio` 
                    ON (`seg_detalle_pedido`.`id_producto` = `seg_bien_servicio`.`id_b_s`)
                INNER JOIN `seg_tipo_bien_servicio` 
                    ON (`seg_bien_servicio`.`id_tipo_bn_sv` = `seg_tipo_bien_servicio`.`id_tipo_b_s`)
            WHERE (`seg_pedidos_almacen`.`id_pedido`  = '$id_pedido')
            ORDER BY `seg_tipo_bien_servicio`.`tipo_bn_sv`,`seg_bien_servicio`.`bien_servicio` DESC";
    $res = $cmd->query($sql);
    $datos = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$user = $datos[0]['id_user_reg'];
try {
    $sql = "SELECT
                CONCAT_WS(' ', `nombre1`, `nombre2`, `apellido1`, `apellido2`) AS `nombre`
            FROM
                `seg_usuarios`
            WHERE (`id_usuario` = $user)";
    $res = $cmd->query($sql);
    $usuario = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$datas = [];
foreach ($datos as $fila) {
    $tps = $fila['tipo_bn_sv'];
    $bs = $fila['bien_servicio'];
    $datas[$tps][$bs]['cantidad'] = $fila['cantidad'];
    $datas[$tps][$bs]['ids'] = $fila['id_tipo_bn_sv'] . '|' . $fila['id_producto'];
}
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$ruta = $_SESSION['urlin'] . '/images/logos/logo.png';
?>
<div class="text-right py-3">
    <div>
        <a type="" id="btnReporteGral" class="btn btn-outline-success btn-sm" value="01" title="Exprotar a Excel">
            <span class="fas fa-file-excel fa-lg" aria-hidden="true"></span>
        </a>
        <a type="button" class="btn btn-primary btn-sm" title="Imprimir" onclick="imprSelecTes('areaImprimir',<?php echo 0; ?>);"><span class="fas fa-print fa-lg" aria-hidden="true"></span></a>
        <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" title="Cerrar"><span class="fas fa-times fa-lg" aria-hidden="true"></span></a>
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
                                <td rowspan="3" class='text-center' style="width:18%"><label class="small"><img src="<?php echo $ruta ?>" width="100"></label></td>
                                <td colspan="7" style="text-align:center">
                                    <strong><?php echo $empresa['nombre']; ?> </strong>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="7" style="text-align:center">
                                    NIT <?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6">
                                    <b>PEDIDO No: <?php echo str_pad($datos[0]['id_pedido'], 5, "0", STR_PAD_LEFT) ?></b>
                                </td>
                                <td colspan="2" style="scale: 0.7; text-align: right;">
                                    <table style="width:100% !important;">
                                        <tr>
                                            <td>Fecha Solicitud.</td>
                                            <td><?php echo date('Y/m/d', strtotime($datos[0]['fec_cierre'] == '' ? $datos[0]['fec_reg'] : $datos[0]['fec_cierre'])) ?></td>
                                        </tr>
                                        <tr>
                                            <td>Fecha Imp.</td>
                                            <td><?php echo $date->format('Y/m/d') ?></td>
                                    </table>
                                </td>
                            </tr>
                            <tr style="font-size: 85%;">
                                <td colspan="2">
                                    Genera: <?php echo $datos[0]['area_entrega'] ?>
                                </td>
                                <td colspan="5">
                                    Solicita: <?php echo $datos[0]['nombre'] ?>
                                </td>
                                <td colspan="1">
                                    <span class="page-number"></span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr style="background-color: #CED3D3; text-align:center">
                    <th>ID</th>
                    <th>Descripción Producto</th>
                    <th>Cant.</th>
                    <th>Val. Unitario</th>
                    <th>Val. Total</th>
                </tr>
            </thead>
            <tbody style="font-size: 60%;">
                <?php
                $row_tipo = '';
                foreach ($datas as $keytb => $bien) {
                    $row_bien = '';
                    if (!empty($bien)) {
                        foreach ($bien as $keybn => $bn) {
                            $ides = explode('|', $bn['ids']);
                            $id_tipo_bn_sv = $ides[0];
                            $id_prod = $ides[1];
                            $row_bien .= '<tr class="resaltar">';
                            $row_bien .= '<td>' . $id_prod . '</td>';
                            $row_bien .= '<td>' . $keybn . '</td>';
                            $row_bien .= '<td style="text-align: right">' . $bn['cantidad'] . '</td>';
                            $row_bien .= '<td></td>';
                            $row_bien .= '<td></td>';
                            $row_bien .= '</tr>';
                        }
                        $row_tipo .= '<tr class="resaltar">
                                        <th>' . $id_tipo_bn_sv . '</th>
                                        <th>' . $keytb . '</th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                    </tr>' . $row_bien;
                    }
                }
                echo $row_tipo;
                ?>
                <tr colspan="5">
                    <td style="height: 30px;"></td>
                </tr>
                <tr>
                    <td colspan="2">
                        Elaboró: <?php echo mb_strtoupper($usuario['nombre']); ?>
                    </td>
                    <td colspan="3">
                        Recibe: _______________________________________________________
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                    </td>
                    <td colspan="3">
                        C.C:
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</div>