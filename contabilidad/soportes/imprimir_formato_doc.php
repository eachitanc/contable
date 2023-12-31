<?php
session_start();
set_time_limit(3600);
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../../head.php';
$vigencia = $_SESSION['vigencia'];
$dto = $_POST['id'];
$prefijo = '';
function pesos($valor)
{
    return '$' . number_format($valor, 2);
}
include '../../conexion.php';
include '../../financiero/consultas.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
try {
    $sql = "SELECT detalle,fecha,id_manu,id_tercero,fec_reg,tipo_doc FROM seg_ctb_doc WHERE id_ctb_doc =$dto";
    $res = $cmd->query($sql);
    $cdp = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$ccnit = $cdp['id_tercero'];
if ($cdp['tipo_doc'] == 'CNOM') {
    $tercero = 'NOMINA DE EMPLEADOS';
    $num_doc = '';
    // Consulta terceros en la api ********************************************* API
} else {
    // fin api terceros ******************************************************** 
    try {
        $sql = "SELECT no_doc FROM seg_terceros WHERE id_tercero_api =$ccnit";
        $res = $cmd->query($sql);
        $nit = $res->fetch();
        $num_doc = $nit['no_doc'];
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    $prefijo = '';
    $url = $api . 'terceros/datos/res/datos/id/' . $ccnit;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res_api = curl_exec($ch);
    curl_close($ch);
    $dat_ter = json_decode($res_api, true);
    $tercero = $dat_ter[0]['apellido1'] . ' ' . $dat_ter[0]['apellido2'] . ' ' . $dat_ter[0]['nombre1'] . ' ' . $dat_ter[0]['nombre2'] . ' ' . $dat_ter[0]['razon_social'];
}
// Valor total del cdp
try {
    $sql = "SELECT sum(debito) as valor FROM seg_ctb_libaux WHERE id_ctb_doc =$dto";
    $res = $cmd->query($sql);
    $datos = $res->fetch();
    $total = $datos['valor'];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$enletras = numeroLetras($total);
try {
    $sql = "SELECT
    `seg_pto_documento`.`id_manu`
    , `seg_pto_mvto`.`rubro`
    , `seg_pto_cargue`.`nom_rubro`
    , `seg_pto_mvto`.`valor`
    , `seg_pto_mvto`.`id_tercero_api`
    , `seg_pto_mvto`.`tipo_mov`
FROM
    `seg_pto_mvto`
    INNER JOIN `seg_pto_cargue` 
        ON (`seg_pto_mvto`.`rubro` = `seg_pto_cargue`.`cod_pptal`)
    INNER JOIN `seg_pto_documento` 
        ON (`seg_pto_mvto`.`id_pto_doc` = `seg_pto_documento`.`id_pto_doc`)
WHERE (`seg_pto_mvto`.`id_ctb_doc` =$dto
    AND `seg_pto_cargue`.`vigencia` AND `seg_pto_mvto`.`tipo_mov` ='COP') ;";
    $res = $cmd->query($sql);
    $rubros = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Datos de la factura 
try {
    $sql = "SELECT
    `seg_ctb_factura`.`id_ctb_doc`
    , `seg_ctb_tipodoc`.`tipo` as tipo
    , `seg_ctb_factura`.`tipo_doc`
    , `seg_ctb_factura`.`num_doc`
    , `seg_ctb_factura`.`fecha_fact`
    , `seg_ctb_factura`.`fecha_ven`
    , `seg_ctb_factura`.`valor_pago`
    , `seg_ctb_factura`.`valor_iva`
    , `seg_ctb_factura`.`valor_base`
    FROM
    `seg_ctb_factura`
    INNER JOIN `seg_ctb_tipodoc` 
        ON (`seg_ctb_factura`.`tipo_doc` = `seg_ctb_tipodoc`.`id_ctb_tipodoc`)
    WHERE (`seg_ctb_factura`.`id_ctb_doc` =$dto);";
    $res = $cmd->query($sql);
    $factura = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Movimiento contable
try {
    $sql = "SELECT
    `seg_ctb_libaux`.`cuenta` as cuenta
    , `seg_ctb_pgcp`.`nombre`
    , `seg_ctb_libaux`.`debito` as debito
    , `seg_ctb_libaux`.`credito` as credito
    , `seg_ctb_libaux`.`id_tercero`
    FROM
    `seg_ctb_libaux`
    INNER JOIN `seg_ctb_pgcp` 
        ON (`seg_ctb_libaux`.`cuenta` = `seg_ctb_pgcp`.`cuenta`)
    WHERE (`seg_ctb_libaux`.`id_ctb_doc` =$dto)
    ORDER BY `seg_ctb_libaux`.`cuenta` DESC;";
    $res = $cmd->query($sql);
    $movimiento = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// consulta para motrar cuadro de retenciones
try {
    $sql = "SELECT
    `seg_ctb_causa_retencion`.`id_causa_retencion`
    , `seg_ctb_causa_retencion`.`id_ctb_doc`
    , `seg_ctb_retencion_tipo`.`tipo`
    , `seg_ctb_retenciones`.`nombre_retencion`
    , `seg_ctb_causa_retencion`.`valor_base`
    , `seg_ctb_causa_retencion`.`tarifa`
    , `seg_ctb_causa_retencion`.`valor_retencion`
    ,`seg_ctb_causa_retencion`.`id_terceroapi`
FROM
    `seg_ctb_causa_retencion`
    INNER JOIN `seg_ctb_retenciones` 
        ON (`seg_ctb_causa_retencion`.`id_retencion` = `seg_ctb_retenciones`.`id_retencion`)
    INNER JOIN `seg_ctb_retencion_tipo` 
        ON (`seg_ctb_retencion_tipo`.`id_retencion_tipo` = `seg_ctb_retenciones`.`id_retencion_tipo`)
WHERE (`seg_ctb_causa_retencion`.`id_ctb_doc` =$dto);";
    $rs = $cmd->query($sql);
    $retenciones = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// consulto el nombre de la empresa de la tabla seg_empresas
try {
    $sql = "SELECT `nombre`, `nit`, `dig_ver` FROM `seg_empresas`;";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// consulto el tipo de control del documento
try {
    $sql = "SELECT `tipo_doc`, `control_doc`,`nombre` FROM `seg_fin_maestro_doc` WHERE (`tipo_doc` ='{$cdp['tipo_doc']}');";
    $res = $cmd->query($sql);
    $control = $res->fetch();
    $num_control = $control['control_doc'];
    $nombre_doc = $control['nombre'];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// consulto el tipo de control del documento
try {
    $sql = "SELECT
    `seg_fin_respon_doc`.`cargo`
    , `seg_fin_respon_doc`.`tipo_control`
    FROM
    `seg_fin_respon_doc`
    INNER JOIN `seg_fin_maestro_doc` 
        ON (`seg_fin_respon_doc`.`id_maestro_doc` = `seg_fin_maestro_doc`.`id_maestro`)
    WHERE (`seg_fin_maestro_doc`.`tipo_doc` ='{$cdp['tipo_doc']}'
    AND `seg_fin_respon_doc`.`estado` =1)
    ORDER BY `seg_fin_respon_doc`.`tipo_control` ASC;";
    $res = $cmd->query($sql);
    $firmas = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$fecha = date('Y-m-d', strtotime($cdp['fecha']));
$hora = date('H:i:s', strtotime($cdp['fec_reg']));
// fechas para factua
$fecha_fact = date('Y-m-d', strtotime($factura['fecha_fact']));
$fecha_ven = date('Y-m-d', strtotime($factura['fecha_ven']));
if ($empresa['nit'] == 844001355 && $factura['tipo_doc'] == 3) {
    $prefijo = 'RSC-';
}
?>
<div class="text-right pt-3">
    <a type="button" class="btn btn-primary btn-sm" onclick="imprSelecDoc('areaImprimir',<?php echo $dto; ?>);"> Imprimir</a>
    <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cerrar</a>
</div>
<div class="contenedor bg-light" id="areaImprimir">
    <div class="px-2 " style="width:90% !important;margin: 0 auto;">

        </br>
        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td class='text-center' style="width:18%"><label class="small"><img src="../images/logos/logo.png" width="100"></label></td>
                <td style="text-align:center">
                    <strong><?php echo $empresa['nombre']; ?> </strong>
                    <div>NIT <?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></div>
                </td>
            </tr>
        </table>

        </br>


        <div class="row px-2" style="text-align: center">
            <div class="col-12">
                <div class="col lead"><label><strong><?php echo $nombre_doc . ': ' . $cdp['id_manu']; ?></strong></label></div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div style="text-align: left">
                    <div><strong>Datos generales: </strong></div>
                </div>
            </div>
        </div>
        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td class='text-left' style="width:18%">FECHA:</td>
                <td class='text-left'><?php echo $fecha . ' ' . $hora; ?></td>
            </tr>
            <tr>
                <td class='text-left' style="width:18%">TERCERO:</td>
                <td class='text-left'><?php echo $tercero; ?></td>
            </tr>
            <tr>
                <td class='text-left' style="width:18%">CC/NIT:</td>
                <td class='text-left'><?php echo $num_doc; ?></td>
            </tr>
            <tr>
                <td class='text-left'>OBJETO:</td>
                <td class='text-left'><?php echo $cdp['detalle']; ?></td>
            </tr>
            <tr>
                <td class='text-left'>VALOR:</td>
                <td class='text-left'><label><?php echo $enletras . "  $" . number_format($total, 2, ",", "."); ?></label></td>
            </tr>
        </table>

        <?php if ($cdp['tipo_doc'] == 'NCXP' || $cdp['tipo_doc'] == 'CNOM') { ?>
            </br>
            <div class="row">
                <div class="col-12">
                    <div style="text-align: left">
                        <div><strong>Imputación presupuestal: </strong></div>
                    </div>
                </div>
            </div>
            <table class="table-bordered" style="width:100% !important; border-collapse: collapse; " cellspacing="2">
                <tr>
                    <?php
                    if ($cdp['tipo_doc'] == 'CNOM') {
                    ?>
                        <td style="text-align: left;border: 1px solid black ">Número Rp </td>
                        <td style="text-align: left;border: 1px solid black ">Cc/nit</td>
                        <td style="border: 1px solid black ">Código</td>
                        <td style="border: 1px solid black ">Nombre</td>
                        <td style="border: 1px solid black;text-align:center">Valor</td>
                    <?php
                    } else {
                    ?>
                        <td style="text-align: left;border: 1px solid black ">Número Rp</td>
                        <td style="border: 1px solid black ">Código</td>
                        <td style="border: 1px solid black ">Nombre</td>
                        <td style="border: 1px solid black;text-align:center">Valor</td>
                    <?php
                    }
                    ?>
                </tr>
                <?php
                $total_pto = 0;
                if ($cdp['tipo_doc'] == 'CNOM') {
                    $id_t = [];
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

                    foreach ($rubros as $rp) {
                        $key = array_search($rp['id_tercero_api'], array_column($terceros, 'id_tercero'));
                        if ($rp['tipo_mov'] == 'COP') {
                            echo "<tr>
                    <td class='text-left' style='border: 1px solid black '>" . $rp['id_manu'] . "</td>
                    <td class='text-left' style='border: 1px solid black '>" . $terceros[$key]['cc_nit'] . "</td>
                    <td class='text-left' style='border: 1px solid black '>" . $rp['rubro'] . "</td>
                    <td class='text-left' style='border: 1px solid black '>" . $rp['nom_rubro'] . "</td>
                    <td class='text-right' style='border: 1px solid black; text-align: right'>" . number_format($rp['valor'], 2, ",", ".")  . "</td>
                    </tr>";
                            $total_pto += $rp['valor'];
                        }
                    }
                } else {
                    foreach ($rubros as $rp) {
                        if ($rp['tipo_mov'] == 'COP') {
                            echo "<tr>
                    <td class='text-left' style='border: 1px solid black '>" . $rp['id_manu'] . "</td>
                    <td class='text-left' style='border: 1px solid black '>" . $rp['rubro'] . "</td>
                    <td class='text-left' style='border: 1px solid black '>" . $rp['nom_rubro'] . "</td>
                    <td class='text-right' style='border: 1px solid black; text-align: right'>" . number_format($rp['valor'], 2, ",", ".")  . "</td>
                    </tr>";
                            $total_pto += $rp['valor'];
                        }
                    }
                }
                ?>
                <?php
                if ($cdp['tipo_doc'] == 'CNOM') {
                ?>
                    <tr>
                        <td colspan="4" style="text-align:left;border: 1px solid black ">Total</td>
                        <td style="text-align: right;border: 1px solid black "><?php echo number_format($total_pto, 2, ",", "."); ?></td>
                    </tr>
                <?php
                } else {
                ?>
                    <tr>
                        <td colspan="3" style="text-align:left;border: 1px solid black ">Total</td>
                        <td style="text-align: right;border: 1px solid black "><?php echo number_format($total_pto, 2, ",", "."); ?></td>
                    </tr>
                <?php
                }
                ?>
            </table>
            </br>
            <?php
            if ($cdp['tipo_doc'] != 'CNOM') {
            ?>
                <div class="row">
                    <div class="col-12">
                        <div style="text-align: left">
                            <div><strong>Datos de la factura: </strong></div>
                        </div>
                    </div>
                </div>
                <table class="table-bordered bg-light" style="width:100% !important;">
                    <tr>
                        <td style="text-align: left">Documento</td>
                        <td>Número</td>
                        <td>Fecha</td>
                        <td>Vencimiento</td>
                    </tr>
                    <tr>
                        <td style="text-align: left"><?php echo $factura['tipo']; ?></td>
                        <td><?php echo $prefijo . $factura['num_doc']; ?></td>
                        <td><?php echo $fecha_fact; ?></td>
                        <td><?php echo $fecha_ven; ?></td>
                    </tr>
                    <tr>
                        <td style="text-align: left">Valor factura</td>
                        <td>Valor IVA</td>
                        <td>Base</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><?php echo number_format($factura['valor_pago'], 2, ',', '.'); ?></td>
                        <td><?php echo  number_format($factura['valor_iva'], 2, ',', '.');; ?></td>
                        <td><?php echo number_format($factura['valor_base'], 2, ',', '.'); ?></td>
                        <td></td>
                    </tr>
                </table>
                </br>
                <div class="row">
                    <div class="col-12">
                        <div style="text-align: left">
                            <div><strong>Retenciones y descuentos: </strong></div>
                        </div>
                    </div>
                </div>
                <table class="table-bordered bg-light" style="width:100% !important;border-collapse: collapse;">
                    <tr>
                        <td style="text-align: left;border: 1px solid black">Entidad</td>
                        <td style='border: 1px solid black'>Descuento</td>
                        <td style='border: 1px solid black'>Valor base</td>
                        <td style='border: 1px solid black'>Valor rete</td>
                    </tr>
                    <?php
                    $total_rete = 0;
                    foreach ($retenciones as $re) {
                        // Consulto el valor del tercero de la api
                        // Consulta terceros en la api ********************************************* API
                        $url = $api . 'terceros/datos/res/datos/id/' . $re['id_terceroapi'];
                        $ch = curl_init($url);
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $res_api = curl_exec($ch);
                        curl_close($ch);
                        $dat_ter = json_decode($res_api, true);
                        $tercero = $dat_ter[0]['apellido1'] . ' ' . $dat_ter[0]['apellido2'] . ' ' . $dat_ter[0]['nombre1'] . ' ' . $dat_ter[0]['nombre2'] . ' ' . $dat_ter[0]['razon_social'];
                        // fin api terceros **************************
                        echo "<tr>
                <td style='text-align: left;border: 1px solid black'>" . $tercero . "</td>
                <td style='text-align: left;border: 1px solid black'>" . $re['nombre_retencion'] . "</td>
                <td style='text-align: right;border: 1px solid black'>" . number_format($re['valor_base'], 2, ',', '.') . "</td>
                <td style='text-align: right;border: 1px solid black'>" . number_format($re['valor_retencion'], 2, ',', '.') . "</td>
                </tr>";
                        $total_rete += $re['valor_retencion'];
                    }
                    ?>
                    <tr>
                        <td colspan="3" style="text-align:left;border: 1px solid black ">Total</td>
                        <td style="text-align: right;border: 1px solid black "><?php echo number_format($total_rete, 2, ",", "."); ?></td>
                    </tr>

                </table>
            <?php
            }
            ?>
        <?php } ?>


        </br>
        <div class="row">
            <div class="col-12">
                <div style="text-align: left">
                    <div><strong>Movimiento contable: </strong></div>
                </div>
            </div>
        </div>
        <table class="table-bordered bg-light" style="width:100% !important; border-collapse: collapse;">
            <?php
            if ($cdp['tipo_doc'] == 'CNOM') {
            ?>
                <tr>
                    <td style="text-align: left;border: 1px solid black">Cuenta</td>
                    <td style='border: 1px solid black'>Nombre</td>
                    <td style='border: 1px solid black'>Terceros</td>
                    <td style='border: 1px solid black'>Nombre</td>
                    <td style='border: 1px solid black'>Debito</td>
                    <td style='border: 1px solid black'>Crédito</td>
                </tr>
                <?php
                $tot_deb = 0;
                $tot_cre = 0;
                foreach ($movimiento as $mv) {
                    // Consulta terceros en la api ********************************************* API
                    $key = array_search($mv['id_tercero'], array_column($terceros, 'id_tercero'));
                    $ccnit = $terceros[$key]['cc_nit'];
                    $nom_ter =  $terceros[$key]['apellido1'] . ' ' .  $terceros[$key]['apellido2'] . ' ' .  $terceros[$key]['nombre1'] . ' ' .  $terceros[$key]['nombre2'] . ' ' .  $terceros[$key]['razon_social'];

                    echo "<tr style='border: 1px solid black'>
                <td class='text-left' style='border: 1px solid black'>" . $mv['cuenta'] . "</td>
                <td class='text-left' style='border: 1px solid black'>" . $mv['nombre'] .  "</td>
                <td class='text-left' style='border: 1px solid black'>" . $ccnit . "</td>
                <td class='text-left' style='border: 1px solid black'>" . $nom_ter . "</td>
                <td class='text-right' style='border: 1px solid black;text-align: right'>" . number_format($mv['debito'], 2, ",", ".")  . "</td>
                <td class='text-right' style='border: 1px solid black;text-align: right'>" . number_format($mv['credito'], 2, ",", ".")  . "</td>
                </tr>";
                    $tot_deb += $mv['debito'];
                    $tot_cre += $mv['credito'];
                }
                ?>
                <tr>
                    <td style="text-align: left;border: 1px solid black" colspan="4">Sumas iguales</td>
                    <td class='text-right' style='border: 1px solid black;text-align: right'><?php echo number_format($tot_deb, 2, ",", "."); ?></td>
                    <td class='text-right' style='border: 1px solid black;text-align: right'><?php echo number_format($tot_cre, 2, ",", "."); ?> </td>
                </tr>
            <?php
            } else {
            ?>
                <tr>
                    <td style="text-align: left;border: 1px solid black">Cuenta</td>
                    <td style='border: 1px solid black'>Nombre</td>
                    <td style='border: 1px solid black'>Debito</td>
                    <td style='border: 1px solid black'>Crédito</td>
                </tr>
                <?php

                $tot_deb = 0;
                $tot_cre = 0;
                foreach ($movimiento as $mv) {
                    // Consulta terceros en la api ********************************************* API


                    echo "<tr style='border: 1px solid black'>
            <td class='text-left' style='border: 1px solid black'>" . $mv['cuenta'] . "</td>
            <td class='text-left' style='border: 1px solid black'>" . $mv['nombre'] .  "</td>
            <td class='text-right' style='border: 1px solid black;text-align: right'>" . number_format($mv['debito'], 2, ",", ".")  . "</td>
            <td class='text-right' style='border: 1px solid black;text-align: right'>" . number_format($mv['credito'], 2, ",", ".")  . "</td>
            </tr>";
                    $tot_deb += $mv['debito'];
                    $tot_cre += $mv['credito'];
                }
                ?>
                <tr>
                    <td style="text-align: left;border: 1px solid black" colspan="2">Sumas iguales</td>
                    <td class='text-right' style='border: 1px solid black;text-align: right'><?php echo number_format($tot_deb, 2, ",", "."); ?></td>
                    <td class='text-right' style='border: 1px solid black;text-align: right'><?php echo number_format($tot_cre, 2, ",", "."); ?> </td>
                </tr>
            <?php
            }
            ?>
        </table>
        </br>
        </br>
        <?php if ($num_control == 1) { ?>

            <table class="table-bordered bg-light firmas" style="width:100% !important;" rowspan="8">
                <tr>
                    <td style="text-align: center;height: 70px;">
                        <div>__________________________</div>
                        <div>Elaboró</div>
                        <div>&nbsp;</div>
                    </td>
                    <td style="text-align: center;">
                        <div>__________________________</div>
                        <div>Revisó contabilidad</div>
                        <div>&nbsp;</div>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: center;">
                        <div>__________________________</div>
                        <div>Jefe financiero</div>
                        <div>Aprobó</div>
                    </td>
                    <td style="text-align: center;height: 70px;">
                        <div>__________________________</div>
                        <div>Ordenador del pago</div>
                        <div></div>
                    </td>
                </tr>
            </table>
        <?php } else { ?>
            <table class="table-bordered bg-light firmas" style="width:100% !important;" rowspan="8">
                <tr>
                    <?php foreach ($firmas as $mv) {
                        echo '
                    <td style="text-align: center;height: 70px;">
                        <div>__________________________</div>
                        <div>' . $mv['cargo'] . '</div>
                    </td>';
                    }
                    ?>
                </tr>
            </table>
        <?php } ?>
        </br> </br> </br>
    </div>

</div>