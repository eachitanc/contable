<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
$id_entrada = isset($_POST['id_entrada']) ? $_POST['id_entrada'] : exit('Acción no permitida');
include '../../../conexion.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_detalle_entrada_almacen`.`id_entrada`
                , `seg_bien_servicio`.`bien_servicio`
                , `seg_detalle_entrada_almacen`.`id_prod`
                , `seg_detalle_entrada_almacen`.`id_tipo_entrada`
                , `seg_detalle_entrada_almacen`.`cant_ingresa`
                , `seg_detalle_entrada_almacen`.`iva`
                , `seg_detalle_entrada_almacen`.`valu_ingresa`
                , `seg_detalle_entrada_almacen`.`val_prom`
                , `seg_detalle_entrada_almacen`.`lote`
                , `seg_detalle_entrada_almacen`.`fecha_vence`
                , `seg_detalle_entrada_almacen`.`id_marca`
                , `seg_marcas` .`descripcion` AS `marca`
                , `seg_detalle_entrada_almacen`.`invima`
                , `seg_detalle_entrada_almacen`.`existencia`
            FROM
                `seg_detalle_entrada_almacen`
                INNER JOIN `seg_bien_servicio` 
                    ON (`seg_detalle_entrada_almacen`.`id_prod` = `seg_bien_servicio`.`id_b_s`)
                LEFT JOIN `seg_marcas` 
                    ON (`seg_detalle_entrada_almacen`.`id_marca` = `seg_marcas`.`id_marca`)    
            WHERE `seg_detalle_entrada_almacen`.`id_entrada` = '$id_entrada'";
    $rs = $cmd->query($sql);
    $entrada = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if ($entrada['id_tipo_entrada'] == 3) {
    $tipol = 'DONACIÓN';
} else if ($entrada['id_tipo_entrada'] == 2) {
    $tipol = 'PRÉSTAMO';
} else {
    $tipol = 'OTRA';
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">ACTUALIZAR ENTRADA POR <?php echo $tipol ?></h5>
        </div>
        <div class="px-2">
            <form id="formUpEntraPrestDona">
                <input type="hidden" id="id_predon" name="id_entradaK" value="<?php echo $id_entrada ?>">
                <div class="form-row text-center">
                    <div class="form-group col-md-12">
                        <label for="buscProd" class="small">Bien y/o producto</label>
                        <input id="buscProd" type="text" class="form-control form-control-sm" placeholder="Buscar" value="<?php echo $entrada['bien_servicio'] ?>">
                        <input type="hidden" id="id_bnsvc" name="id_bnsvc" value="<?php echo $entrada['id_prod'] ?>">
                        <input type="hidden" name="id_bnsvc_ant" value="<?php echo $entrada['id_prod'] ?>">
                    </div>
                </div>
                <div class="form-row text-center">
                    <div class="form-group col-md-3">
                        <label for="numCantRecb" class="small">cantidad</label>
                        <input type="number" id="numCantRecb" name="numCantRecb" class="form-control form-control-sm" value="<?php echo $entrada['cant_ingresa'] ?>">
                        <input type="hidden" name="numCantRecb_ant" value="<?php echo $entrada['cant_ingresa'] ?>">
                        <input type="hidden" name="numCantExistencia" value="<?php echo $entrada['existencia'] ?>">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="numValUnita" class="small">Val. Und</label>
                        <input type="number" id="numValUnita" name="numValUnita" class="form-control form-control-sm" placeholder="Valor sin IVA" value="<?php echo $entrada['valu_ingresa'] ?>">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="numIvaProd" class="small">% IVA</label>
                        <select name="numIvaProd" id="numIvaProd" class="form-control form-control-sm">
                            <option value="0" <?php echo $entrada['iva'] == 0 ? 'selected' : '' ?>>0%</option>
                            <option value="5" <?php echo $entrada['iva'] == 5 ? 'selected' : '' ?>>5%</option>
                            <option value="19" <?php echo $entrada['iva'] == 19 ? 'selected' : '' ?>>19%</option>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="lote" class="small">lote</label>
                        <input type="text" id="lote" name="lote" class="form-control form-control-sm" value="<?php echo $entrada['lote'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="fec_vence" class="small">fecha vencimiento</label>
                        <input type="date" id="fec_vence" name="fec_vence" class="form-control form-control-sm" value="<?php echo $entrada['fecha_vence'] ?>">
                    </div>
                </div>
                <div class="form-row text-center">
                    <div class="form-group col-md-3">
                        <label for="invima" class="small">INVIIMA</label>
                        <input type="text" id="invima" name="invima" class="form-control form-control-sm" value="<?php echo $entrada['invima'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txtMarcaI" class="small">MARCA</label>
                        <input type="text" id="txtMarcaI" name="txtMarcaI" class="form-control form-control-sm" value="<?php echo $entrada['marca'] ?>">
                        <input type="hidden" id="idMarcaI" name="idMarcaI" value="<?php echo $entrada['id_marca'] ?>">
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <button id="modEntraXPrestDona" type="button" class="btn btn-primary btn-sm">Actualizar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>