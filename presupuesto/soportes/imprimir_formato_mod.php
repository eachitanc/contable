<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<?php include '../../head.php';
header("Content-type: text/html; charset=utf-8");
$vigencia = $_SESSION['vigencia'];
$dto = $_POST['id'];
$filtro_ccred = '';
$filtro_cred = '';
$vertabla = '';
function pesos($valor)
{
    return '$' . number_format($valor, 2);
}
include '../../conexion.php';
include '../../financiero/consultas.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
try {
    $sql = "SELECT
                `seg_pto_documento`.`id_manu`
                , `seg_pto_documento`.`fecha`
                , `seg_pto_documento`.`tipo_doc`
                , `seg_pto_documento`.`objeto`
                , `seg_pto_tipo_modifica`.`tipo`
                , `seg_pto_actos_admin`.`acto`
                ,seg_pto_documento.fec_reg
                ,CONCAT(seg_usuarios.nombre1,' ', seg_usuarios.nombre2,' ',seg_usuarios.apellido1,' ',seg_usuarios.apellido2)as usuario
            FROM
                `seg_pto_documento`
                INNER JOIN `seg_pto_tipo_modifica` ON (`seg_pto_documento`.`tipo_doc` = `seg_pto_tipo_modifica`.`cod`)
                INNER JOIN seg_pto_actos_admin ON (`seg_pto_documento`.tipo_mod= seg_pto_actos_admin.id_pto_actos)
                INNER JOIN seg_usuarios ON (seg_pto_documento.id_user_reg = seg_usuarios.id_usuario)
            WHERE (`seg_pto_documento`.`id_pto_doc` =$dto);";
    $res = $cmd->query($sql);
    $cdp = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Valor total del cdp
try {
    $sql = "SELECT sum(valor) as valor FROM seg_pto_mvto WHERE id_pto_doc =$dto";
    $res = $cmd->query($sql);
    $datos = $res->fetch();
    $total = $datos['valor'];
    if ($cdp['tipo_doc'] != 'APL') {
        $total = $total / 2;
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consulto los rubros del ingreso afectados en la adición o reducción presupuestal si es ADI o RED
if ($cdp['tipo_doc'] == 'ADI' || $cdp['tipo_doc'] == 'RED') {
    $etiqueta1 = 'Presupuesto de ingresos';
    $etiqueta2 = 'Presupuesto de gastos';
    try {
        $sql = "SELECT
    `seg_pto_cargue`.`cod_pptal`
    , `seg_pto_cargue`.`nom_rubro`
    , `seg_pto_cargue`.`tipo_dato`
    FROM
    `seg_pto_cargue`
    INNER JOIN `seg_pto_presupuestos` 
        ON (`seg_pto_cargue`.`id_pto_presupuestos` = `seg_pto_presupuestos`.`id_pto_presupuestos`)
    WHERE (`seg_pto_cargue`.`vigencia` =$vigencia
    AND `seg_pto_presupuestos`.`id_pto_tipo` =1);";
        $res = $cmd->query($sql);
        $ingresos = $res->fetchAll();
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    // Consulto los rubros del gasto afectados en la adición o reducción presupuestal
    try {
        $sql = "SELECT
    `seg_pto_cargue`.`cod_pptal`
    , `seg_pto_cargue`.`nom_rubro`
    , `seg_pto_cargue`.`tipo_dato`
    FROM
    `seg_pto_cargue`
    INNER JOIN `seg_pto_presupuestos` 
        ON (`seg_pto_cargue`.`id_pto_presupuestos` = `seg_pto_presupuestos`.`id_pto_presupuestos`)
    WHERE (`seg_pto_cargue`.`vigencia` =$vigencia
    AND `seg_pto_presupuestos`.`id_pto_tipo` =2);";
        $res = $cmd->query($sql);
        $gastos = $res->fetchAll();
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
}
if ($cdp['tipo_doc'] == 'TRA') {
    $filtro_ccred = 'AND mov =0';
    $filtro_cred = 'AND mov =1';
    $etiqueta1 = 'Contracreditos';
    $etiqueta2 = 'Créditos';

    try {
        $sql = "SELECT
    `seg_pto_cargue`.`cod_pptal`
    , `seg_pto_cargue`.`nom_rubro`
    , `seg_pto_cargue`.`tipo_dato`
    FROM
    `seg_pto_cargue`
    INNER JOIN `seg_pto_presupuestos` 
        ON (`seg_pto_cargue`.`id_pto_presupuestos` = `seg_pto_presupuestos`.`id_pto_presupuestos`)
    WHERE `seg_pto_cargue`.`vigencia` =$vigencia AND `seg_pto_presupuestos`.`id_pto_tipo` =2 ;";
        $res = $cmd->query($sql);
        $ingresos = $res->fetchAll();
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    try {
        $sql = "SELECT
    `seg_pto_cargue`.`cod_pptal`
    , `seg_pto_cargue`.`nom_rubro`
    , `seg_pto_cargue`.`tipo_dato`
    FROM
    `seg_pto_cargue`
    INNER JOIN `seg_pto_presupuestos` 
        ON (`seg_pto_cargue`.`id_pto_presupuestos` = `seg_pto_presupuestos`.`id_pto_presupuestos`)
    WHERE `seg_pto_cargue`.`vigencia` =$vigencia AND `seg_pto_presupuestos`.`id_pto_tipo` =2;";
        $res = $cmd->query($sql);
        $gastos = $res->fetchAll();
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
}
if ($cdp['tipo_doc'] == 'APL') {
    $filtro_ccred = 'AND mov =1';
    $etiqueta1 = 'Presupuesto de gastos';
    $vertabla = 'display:none';
    $gastos = null;
    try {
        $sql = "SELECT
    `seg_pto_cargue`.`cod_pptal`
    , `seg_pto_cargue`.`nom_rubro`
    , `seg_pto_cargue`.`tipo_dato`
    FROM
    `seg_pto_cargue`
    INNER JOIN `seg_pto_presupuestos` 
        ON (`seg_pto_cargue`.`id_pto_presupuestos` = `seg_pto_presupuestos`.`id_pto_presupuestos`)
    WHERE `seg_pto_cargue`.`vigencia` =$vigencia AND `seg_pto_presupuestos`.`id_pto_tipo` =2 ;";
        $res = $cmd->query($sql);
        $ingresos = $res->fetchAll();
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
}
// consulto el nombre de la empresa de la tabla seg_empresas
try {
    $sql = "SELECT `nombre`, `nit`, `dig_ver` FROM `seg_empresas`;";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// Consulto responsable del documento
try {
    $sql = "SELECT
    `seg_fin_respon_doc`.`nombre`
    , `seg_fin_respon_doc`.`cargo`
    , `seg_fin_respon_doc`.`descripcion`
    FROM
    `seg_fin_respon_doc`
    INNER JOIN `seg_fin_maestro_doc` 
        ON (`seg_fin_respon_doc`.`id_maestro_doc` = `seg_fin_maestro_doc`.`id_maestro`)
    WHERE (`seg_fin_maestro_doc`.`tipo_doc` ='CDP'
    AND `seg_fin_respon_doc`.`estado` =1);";
    $res = $cmd->query($sql);
    $responsable = $res->fetch();
    $nom_respon = mb_strtoupper($responsable['nombre'], 'UTF-8');
    $cargo_respon = $responsable['cargo'];
    $descrip_respon = $responsable['descripcion'];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$enletras = numeroLetras($total);
$fecha = date('Y-m-d', strtotime($cdp['fecha']));
?>
<div class="text-right pt-3">
    <a type="button" class="btn btn-primary btn-sm" onclick="imprSelecCdp('areaImprimir');"> Imprimir</a>
    <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cerrar</a>
</div>
<div class="contenedor bg-light" id="areaImprimir">
    <div class="px-2 " style="width:90% !important;margin: 0 auto;">

        </br>
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
        </br>


        <div class="row px-2" style="text-align: center">
            <div class="col-12">
                <div class="col-lg"><label>EL SUSCRITO <?php echo strtoupper($cargo_respon); ?></label></div>
            </div>
        </div>
        </br>
        </br>
        <div class="row">
            <div class="col-12" style="text-align: center">
                <div class="col lead"><label><strong>CERTIFICA:</strong></label></div>
            </div>
        </div>
        </br>
        <div class="row">
            <div class="col-12">
                <div class="text-justify">
                    <p>Que, en el presupuesto de la entidad <strong><?php echo $empresa['nombre']; ?></strong>, aprobado para la vigencia fiscal <?php echo $vigencia; ?>, se realizó una modificación presupuestal de acuerdo al siguiente detalle:</p>
                </div>
            </div>
        </div>
        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td class='text-left'>TIPO:</td>
                <td class='text-left'><label><?php echo $cdp['tipo']; ?></label></td>
            </tr>
            <tr>
                <td class='text-left'>NÚMERO:</td>
                <td class='text-left'><label><?php echo $cdp['acto'] . '-' . $cdp['id_manu']; ?></label></td>
            </tr>
            <tr>
                <td class='text-left' style="width:22%">FECHA:</td>
                <td class='text-left'><?php echo $fecha; ?></td>
            </tr>
            <tr>
                <td class='text-left'>OBJETO:</td>
                <td class='text-left'><?php echo $cdp['objeto']; ?></td>
            </tr>
            <tr>
                <td class='text-left'>VALOR:</td>
                <td class='text-left'><label><?php echo $enletras . "  $" . number_format($total, 2, ",", "."); ?></label></td>
            </tr>

        </table>
        </br>
        <div class="row">
            <div class="col-12">
                <div style="text-align: left">
                    <div><strong><?php echo $etiqueta1; ?> </strong></div>
                </div>
            </div>
        </div>
        <table class="table-bordered bg-light" style="width:100% !important;">
            <tr>
                <td>Código</td>
                <td>Nombre</td>
                <td>Valor</td>
            </tr>
            <?php
            foreach ($ingresos as $rp) {
                $rubro = $rp['cod_pptal'];
                $sql = "SELECT sum(valor) as valor FROM seg_pto_mvto WHERE rubro LIKE '$rubro%' AND id_pto_doc =$dto $filtro_ccred";
                $res = $cmd->query($sql);
                $valor = $res->fetch();
                $afecta = $valor['valor'];
                if ($afecta > 0) {
                    echo "<tr>
                <td class='text-left'>" . $rp['cod_pptal'] . "</td>
                <td class='text-left'>" . $rp['nom_rubro'] . "</td>
                <td class='text-right'>" . number_format($afecta, 2, ",", ".")  . "</td>
                </tr>";
                }
            }
            ?>

        </table>
        </br>
        <div class="row">
            <div class="col-12">
                <div style="text-align: left">
                    <div><strong><?php echo $etiqueta2; ?> </strong></div>
                </div>
            </div>
        </div>
        <table class="table-bordered bg-light" style="width:100% !important;<?php echo $vertabla; ?>">
            <tr>
                <td>Código</td>
                <td>Nombre</td>
                <td>Valor</td>
            </tr>
            <?php
            if ($cdp['tipo_doc'] == 'APL') {
            } else {
                foreach ($gastos as $rp) {
                    $rubro = $rp['cod_pptal'];
                    $sql = "SELECT sum(valor) as valor FROM seg_pto_mvto WHERE rubro LIKE '$rubro%' AND id_pto_doc =$dto $filtro_cred";
                    $res = $cmd->query($sql);
                    $valor = $res->fetch();
                    $afecta = $valor['valor'];
                    if ($afecta > 0) {
                        echo "<tr>
                <td class='text-left'>" . $rp['cod_pptal'] . "</td>
                <td class='text-left'>" . $rp['nom_rubro'] . "</td>
                <td class='text-right'>" . number_format($afecta, 2, ",", ".")  . "</td>
                </tr>";
                    }
                }
            }
            ?>

        </table>
        </br>
        </br>
        </br>

        <div class="row">
            <div class="col-12">
                <div style="text-align: center">
                    <div>___________________________________</div>
                    <div><?php echo $nom_respon; ?> </div>
                    <div><?php echo $cargo_respon; ?> </div>
                    <div><?php echo $descrip_respon; ?> </div>
                </div>
            </div>
        </div>
        </br> </br> </br>
        <table class="table-bordered bg-light" style="width:100% !important;font-size: 10px;">
            <tr>
                <td class='text-left' style="width:33%">
                    <strong>Preparó:</strong>
                    <div><?php echo $cdp['usuario']; ?></div>
                </td>
                <td style="text-align:center" style="width:33%">
                </td>
                <td class='text-center' style="width:33%"><label class="small"></label></td>
            </tr>
        </table>

    </div>

</div>