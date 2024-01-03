<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : exit('Acción no permitida');
$tip_salida = $tipo == 2 ? 4 : 2;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `descripcion` FROM  `seg_tipo_entrada` WHERE `id_entrada` = $tipo";
    $rs = $cmd->query($sql);
    $tentradas = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$tipol = $tentradas['descripcion'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `descripcion` FROM  `seg_tipo_salidas` WHERE `id_salida` = $tip_salida";
    $rs = $cmd->query($sql);
    $tsal = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_devolucion`, `consecutivo`, `id_tercero_api`, `acta_remision` 
            FROM `seg_salida_dpdvo`
            WHERE `estado` > 0 AND `id_tipo_salida` = $tip_salida";
    $rs = $cmd->query($sql);
    $salida = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$id_t = [];
$id_t[] = 0;
$id_salida = [];
foreach ($salida as $vc) {
    if ($vc['id_tercero_api'] != '') {
        $id_t[] = $vc['id_tercero_api'];
        $id_salida[] = $vc['id_devolucion'];
    }
}
$idsd = implode(',', $id_salida);
$idsd = $idsd == '' ? 0 : $idsd;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `t1`.`id_devolucion` 
                , `total_sale` - `total_entra` AS `pendiente`

            FROM (SELECT
                `seg_salida_dpdvo`.`id_devolucion`
                , SUM(`seg_salidas_almacen`.`cantidad`) AS `total_sale`
            FROM
                `seg_salidas_almacen`
                INNER JOIN `seg_salida_dpdvo` 
                    ON (`seg_salidas_almacen`.`id_devolucion` = `seg_salida_dpdvo`.`id_devolucion`)
            WHERE (`seg_salida_dpdvo`.`id_devolucion` IN ($idsd))
            GROUP BY `seg_salida_dpdvo`.`id_devolucion`) AS `t1`
            LEFT JOIN
            (SELECT
                `seg_entrada_almacen`.`id_devolucion`
                , SUM(`seg_detalle_entrada_almacen`.`cant_ingresa`) AS `total_entra`
            FROM
                `seg_detalle_entrada_almacen`
                INNER JOIN `seg_entrada_almacen` 
                    ON (`seg_detalle_entrada_almacen`.`id_entra` = `seg_entrada_almacen`.`id_entrada`)
            WHERE (`seg_entrada_almacen`.`id_devolucion` IN ($idsd))) AS `t2`
            ON (`t1`.`id_devolucion` = `t2`.`id_devolucion`)";
    //echo $sql;
    $rs = $cmd->query($sql);
    $pendientes = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
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
$terceros = $terceros != '0' ? $terceros : [];
$tipol = $tentradas['descripcion'];
$tiposalida = $tsal['descripcion'];
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR ENTRADA POR <?php echo $tipol ?></h5>
        </div>
        <div class="px-2">
            <form id="formRegEntraPrestDona">
                <input name="tipoEntrada" hidden value="<?php echo $tipo ?>">
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <?php
                        if (!($tipo == '2' || $tipo == '7')) { ?>

                            <label for="ccnit" class="small">TERCERO</label>
                            <input type="text" id="compleTerecero" class="form-control form-control-sm">
                            <input type="hidden" id="id_tercero_pd" name="id_tercero_pd" value="0">
                        <?php
                        } else {
                        ?>
                            <label for="id_tercero_pd" class="small">SALIDA</label>
                            <select id="id_tercero_pd" name="id_tercero_pd" class="form-control form-control-sm">
                                <option value="0">--Seleccione--</option>
                                <?php
                                foreach ($salida as $v) {
                                    $key = array_search($v['id_tercero_api'], array_column($terceros, 'id_tercero'));
                                    $terc = $key !== false ? $terceros[$key]['nombre1'] . ' ' . $terceros[$key]['nombre2'] . ' ' . $terceros[$key]['apellido1'] . ' ' . $terceros[$key]['apellido2'] . ' ' . $terceros[$key]['razon_social'] : '';
                                    $terc = trim($terc);
                                    $terc = $terc == '' ? $terc : ' -> ' . $terc;
                                    $key = array_search($v['id_devolucion'], array_column($pendientes, 'id_devolucion'));
                                    if ($key !== false && $pendientes[$key]['pendiente'] > 0) {
                                ?>
                                        <option value="<?php echo $v['id_devolucion'] . '|' . $v['id_tercero_api'] ?>"><?php echo $tiposalida . ' ' . str_pad($v['consecutivo'], 5, "0", STR_PAD_LEFT) . $terc ?></option>
                                <?php
                                    }
                                }
                                ?>
                            </select>
                        <?php
                        }
                        ?>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="numActaRem" class="small"># acta y/o remisión</label>
                        <input type="text" id="numActaRem" name="numActaRem" class="form-control form-control-sm">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="fecActRem" class="small">Fecha de acta y/o remisión</label>
                        <input type="date" id="fecActRem" name="fecActRem" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label for="txtObservaEntrada" class="small">Observaciones</label>
                        <textarea class="form-control" id="txtObservaEntrada" name="txtObservaEntrada" rows="3"></textarea>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <button id="regEntraXPrestDona" type="button" class="btn btn-primary btn-sm">Registrar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>