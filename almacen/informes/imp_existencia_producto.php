<?php

use Sabberworm\CSS\Value\Value;

session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
function pesos($valor)
{
    return number_format($valor, 0, ",", ".");
}
include '../../conexion.php';
$id_prod = isset($_POST['id']) ? $_POST['id'] : 0;
$nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
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
                `t3`.`id_entrada`
                , `t3`.`existencia`
                , `seg_detalle_entrada_almacen`.`id_prod`
                , `seg_detalle_entrada_almacen`.`valu_ingresa`
                , `seg_detalle_entrada_almacen`.`iva`
                , `seg_detalle_entrada_almacen`.`invima`
                ,`seg_detalle_entrada_almacen`.`lote`
                , `seg_detalle_entrada_almacen`.`fecha_vence`
                , `seg_detalle_entrada_almacen`.`marca`
            FROM `seg_detalle_entrada_almacen`
            INNER JOIN 
                (SELECT 
                    `t1`.`id_entrada`,`t1`.`cant_ingresa` - IFNULL(`t2`.`sale`,0) AS `existencia`
                FROM 
                    (SELECT 
                        `id_entrada`,`cant_ingresa`
                    FROM `seg_detalle_entrada_almacen`) AS  `t1`
                LEFT JOIN
                    (SELECT
                        `seg_detalles_traslado`.`id_entrada`
                        , SUM(`seg_detalles_traslado`.`cantidad`) AS `sale`
                    FROM
                        `seg_detalles_traslado`
                        INNER JOIN `seg_traslados_almacen` 
                            ON (`seg_detalles_traslado`.`id_traslado` = `seg_traslados_almacen`.`id_trasl_alm`)
                    WHERE (`seg_traslados_almacen`.`id_bodega_entra` <> 1)
                    GROUP BY `seg_detalles_traslado`.`id_entrada`) AS `t2`
                    ON (t1.`id_entrada` = `t2`.`id_entrada`))AS `t3`
                ON (`seg_detalle_entrada_almacen`.`id_entrada` = `t3`.`id_entrada`)
            WHERE `seg_detalle_entrada_almacen`.`id_prod` IN ($id_prod) AND `t3`.`existencia` > 0
            ORDER BY `seg_detalle_entrada_almacen`.`fecha_vence` ASC";
    $res = $cmd->query($sql);
    $datos = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
?>
<div class="text-right py-3">
    <div class=" form-row">
        <div class="form-group col-md-8">
            <input type="text" class="form-control form-control-sm" id="buscaBienAlmacen" placeholder="Artículo o producto a listar" value="<?php echo $nombre ?>">
            <input type="hidden" id="id_prod" value="<?php echo $id_prod ?>">
        </div>
        <div class="form-group col-md-1">
            <button type="button" class="btn btn-light btn-sm" id="filtraBusqueda">Filtrar</button>
        </div>
        <div class="form-group col-md-3">
            <a type="button" class="btn btn-primary btn-sm" onclick="imprSelecTes('areaImprimir',<?php echo 0; ?>);"> Imprimir</a>
            <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cerrar</a>
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
        <div class="p-4 text-left">
            <table class="page_break_avoid" style="width:100% !important;">
                <thead style="background-color: white !important;font-size:80%">
                    <tr style="padding: bottom 3px; color:black">
                        <td colspan="10">
                            <table style="width:100% !important;">
                                <tr>
                                    <td rowspan="3" class='text-center' style="width:18%"><label class="small"><img src="<?php echo $_SESSION['urlin'] ?>/images/logos/logo.png" width="100"></label></td>
                                    <td colspan="9" style="text-align:center">
                                        <strong><?php echo $empresa['nombre']; ?> </strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="9" style="text-align:center">
                                        NIT <?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr style="background-color: #CED3D3; text-align:center">
                        <th rowspan="2">Lote</th>
                        <th rowspan="2">Vence</th>
                        <th rowspan="2">Invima</th>
                        <th rowspan="2">Marca</th>
                        <th rowspan="2">Val.Und.</th>
                        <th colspan="2">Existe</th>
                    </tr>
                </thead>
                <tbody style="font-size: 80%;">
                    <?php
                    if (!empty($datos)) {
                        foreach ($datos as $d) {
                            $valu = pesos($d['valu_ingresa'] * (1 + ($d['iva'] / 100)));
                            echo "  <tr class='resaltar'>
                                        <td>$d[lote]</td>
                                        <td>$d[fecha_vence]</td>
                                        <td>$d[invima]</td>
                                        <td>$d[marca]</td>
                                        <td style='text-align:right'>$valu</td>
                                        <td style='text-align:right'>$d[existencia]</td>
                                    </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='10' class='text-center'>No hay datos para mostrar</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>