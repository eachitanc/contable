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
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
// consulto el nombre de la empresa de la tabla seg_empresas
$fecini = isset($_POST['inicia']) ?  $_POST['inicia'] : '2022-01-01';
$fecini = $fecini == '' ? '2022-01-01' : $fecini;
$fecfin =  isset($_POST['fin']) ? $_POST['fin'] : date('Y-m-d');
$fecfin = $fecfin == '' ? date('Y-m-d') : $fecfin;
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : 1;
try {
    $sql = "SELECT `nombre`, `nit`, `dig_ver` FROM `seg_empresas`";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                CONCAT_WS(' ', `nombre1`, `nombre2`, `apellido1`, `apellido2`) AS `nombre`
            FROM
                `seg_usuarios`
            WHERE (`id_usuario` = $_SESSION[id_user])";
    $res = $cmd->query($sql);
    $usuario = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$datas = [];
try {
    $sql = "SELECT 
                `id_tercero_api`
                , `id_tipo_b_s`
                , `tipo_bn_sv`
                , SUM(`valor`) AS `valor` 
            FROM 
                (SELECT
                    `seg_entrada_almacen`.`id_entrada`
                    , `seg_entrada_almacen`.`id_tercero_api`
                    , `seg_entrada_almacen`.`estado`
                    , `seg_entrada_almacen`.`fec_entrada`
                    , `seg_tipo_bien_servicio`.`id_tipo_b_s`
                    , `seg_tipo_bien_servicio`.`tipo_bn_sv`
                    , `seg_detalle_entrada_almacen`.`valu_ingresa` * (1+ `seg_detalle_entrada_almacen`.`iva`/100) * `seg_detalle_entrada_almacen`.`cant_ingresa` AS `valor`
                FROM
                    `seg_detalle_entrada_almacen`
                    INNER JOIN `seg_entrada_almacen` 
                        ON (`seg_detalle_entrada_almacen`.`id_entra` = `seg_entrada_almacen`.`id_entrada`)
                    INNER JOIN `seg_bien_servicio` 
                        ON (`seg_detalle_entrada_almacen`.`id_prod` = `seg_bien_servicio`.`id_b_s`)
                    INNER JOIN `seg_tipo_bien_servicio` 
                        ON (`seg_bien_servicio`.`id_tipo_bn_sv` = `seg_tipo_bien_servicio`.`id_tipo_b_s`)
                WHERE (`seg_entrada_almacen`.`estado` > 0 AND `seg_entrada_almacen`.`fec_entrada` BETWEEN '$fecini' AND '$fecfin')) AS `t1`
        GROUP BY `id_tercero_api`,`id_tipo_b_s`";
    $res = $cmd->query($sql);
    $datos = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$datas = [];
foreach ($datos as $dt) {
    $datas[$dt['id_tercero_api']][] = $dt;
}
$ids = array_unique(array_column($datos, 'id_tercero_api'));
$payload = json_encode($ids);
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
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
?>

<div class="form-row">
    <div class="form-group col-md-4">
        <label for="fecInicia" class="small">Inicia</label>
        <input type="date" class="form-control form-control-sm" id="fecInicia" value="<?php echo $fecini; ?>">
    </div>
    <div class="form-group col-md-4">
        <label for="fecFin" class="small">Termina</label>
        <input type="date" class="form-control form-control-sm" id="fecFin" value="<?php echo $fecfin; ?>">
    </div>
    <div class="form-group col-md-1 text-left">
        <label class="small">&nbsp;</label>
        <div>
            <button class="btn btn-outline-info btn-sm" id="btnLisEntradaXTercero"><span class="fas fa-search fa-lg" aria-hidden="true"></span></button>
        </div>
    </div>
    <div class="form-group col-md-3 text-right">
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
<div class="form-row">
    <div class="form-group col-md-4 text-center">
        <div class="form-control form-control-sm">
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="tipo" id="detalle" value="1" <?php echo $tipo == 1 ? 'checked' : '' ?>>
                <label class="form-check-label text-secondary" for="detalle">DETALLE</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="tipo" id="consolida" value="2" <?php echo $tipo == 2 ? 'checked' : '' ?>>
                <label class="form-check-label text-secondary" for="consolida">CONSOLIDADO</label>
            </div>
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
    <div class="px-2 text-lef pagina">
        <table class="page_break_avoid" style="width:100% !important; border-collapse: collapse;">
            <thead style="background-color: white !important;font-size:80%">
                <tr style="padding: bottom 3px; color:black">
                    <td colspan="9">
                        <table id="lista" class="bg-light" style="width:100% !important;">
                            <tr>
                                <td rowspan="2" class='text-center' style="width:18%"><label class="small"><img src="<?php echo $_SESSION['urlin'] ?>/images/logos/logo.png" width="100"></label></td>
                                <td colspan="8" style="text-align:center">
                                    <strong><?php echo $empresa['nombre']; ?> </strong>
                                    <div>NIT <?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4">
                                    <center>ENTRADAS POR TERCERO, ENTRE EL <?php echo $fecini . ' Y EL ' . $fecfin ?></center>
                                </td>
                                <td colspan="4" style="text-align: right; font-size:70%">Imp. <?php echo $date->format('d/m/Y H:i') ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr style="background-color: #CED3D3; text-align:center">
                    <th colspan="3">TERCERO</th>
                    <th colspan="2">DIRECCIÓN</th>
                    <th colspan="2">DPTO/MCPIO</th>
                    <th colspan="2">VALOR</th>
                </tr>
            </thead>
            <tbody style="font-size: 70%;">
                <?php
                $lista = '';
                $total = 0;
                foreach ($datas as $key => $value) {
                    $indice = array_search($key, array_column($terceros, 'id_tercero'));
                    if ($indice !== false) {
                        $tipo_doc = $terceros[$indice]['tipo_doc'] == 5 ? 'NIT ' : 'C.C ';
                        $nit = $terceros[$indice]['cc_nit'] . ' ';
                        $nombre = trim($terceros[$indice]['nombre1'] . ' ' . $terceros[$indice]['nombre2'] . ' ' . $terceros[$indice]['apellido1'] . ' ' . $terceros[$indice]['apellido2'] . ' ' . $terceros[$indice]['razon_social']);
                        $direccion = $terceros[$indice]['direccion'];
                        $dpto = $terceros[$indice]['codigo_dpto'];
                        $municipio = $terceros[$indice]['codigo_municipio'];
                    } else {
                        $tipo_doc = 'N/A ';
                        $nit = $key;
                        $nombre = ' OTROS';
                        $direccion = '--';
                        $dpto = '--';
                        $municipio = '---';
                    }
                    $row_tipo = '';
                    $total_tercero = 0;
                    foreach ($value as $vl) {
                        $row_tipo .= '<tr class="resaltar">
                            <td colspan="1" style="text-align: right;  padding-right: 6px">' . $vl['id_tipo_b_s'] . '</td>
                            <td colspan="6" style="text-align: left;">' . $vl['tipo_bn_sv'] . '</td>
                            <td colspan="2" style="text-align: right;">' . pesos($vl['valor']) . '</td>
                        </tr>';
                        $total_tercero += $vl['valor'];
                    }
                    if ($tipo == 2) {
                        $row_tipo = '';
                    }
                    $row_tercero = '<tr class="resaltar">
                        <th colspan="1" style="text-align: left; width:12%">' . $tipo_doc . $nit . '</th>
                        <th colspan="2" style="text-align: left;">' . $nombre . '</th>
                        <th colspan="2" style="text-align: left;">' . $direccion . '</th>
                        <th colspan="2" style="text-align: center;">' . $dpto . ' ' . $municipio . '</th>
                        <th colspan="2" style="text-align: right;"><u>' . pesos($total_tercero) . '</u></th>
                    </tr>';
                    $total += $total_tercero;
                    $lista .= $row_tercero . $row_tipo;
                }
                $lista .= '<tr>
                    <th colspan="7" style="text-align: center;">TOTAL</th>
                    <th colspan="2" style="text-align: right;"><p style="border-top: 2px double #000;">' . pesos($total) . '</p></th>
                </tr>';
                echo $lista;
                ?>
            </tbody>
        </table>
    </div>

</div>