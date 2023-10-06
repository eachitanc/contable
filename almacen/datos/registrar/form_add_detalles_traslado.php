<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_trasl_alma = isset($_POST['id_trasl_alma']) ? $_POST['id_trasl_alma'] : exit('Accion no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_tipo_traslado_almacen`.`descripcion`
                , `seg_traslados_almacen`.`id_trasl_alm`
                , `seg_traslados_almacen`.`id_tipo_trasl`
                , `seg_traslados_almacen`.`acta_remision`
                , `seg_traslados_almacen`.`observacion`
                , `seg_traslados_almacen`.`id_sede_sale`
                , `seg_traslados_almacen`.`id_bodega_sale`
                , `seg_traslados_almacen`.`id_sede_entra`
                , `seg_traslados_almacen`.`id_bodega_entra`
                , `seg_traslados_almacen`.`fec_traslado`
                , `seg_traslados_almacen`.`estado`
            FROM
                `seg_traslados_almacen`
                INNER JOIN `seg_tipo_traslado_almacen` 
                    ON (`seg_traslados_almacen`.`id_tipo_trasl` = `seg_tipo_traslado_almacen`.`id_traslado`)
            WHERE `seg_traslados_almacen`.`id_trasl_alm` = '$id_trasl_alma'";
    $rs = $cmd->query($sql);
    $ttraslado = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR DETALLE DE TRASLADO ENTRE <?php echo $ttraslado['descripcion'] ?></h5>
        </div>
        <div class="px-2">
            <form id="formAddDetalleTrasl">
                <div class="form-row text-center">
                    <input type="hidden" id="id_sede_sale" name="id_sede_sale" value="<?php echo $ttraslado['id_sede_sale'] ?>">
                    <input type="hidden" id="id_bodega_sale" name="id_bodega_sale" value="<?php echo $ttraslado['id_bodega_sale'] ?>">
                    <input type="hidden" name="id_sede_entra" value="<?php echo $ttraslado['id_sede_entra'] ?>">
                    <input type="hidden" name="id_bodega_entra" value="<?php echo $ttraslado['id_bodega_entra'] ?>">
                    <div class="form-group col-md-6">
                        <label for="numLoteSedeBodega" class="small">buscar # lote</label>
                        <input type="text" id="numLoteSedeBodega" class="form-control form-control-sm">
                        <input type="hidden" id="id_proTras" name="id_proTras" value="0">
                        <input type="hidden" id="id_entrada_Tras" name="id_entradaTras" value="0">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="numCantTras" class="small">cantidad</label>
                        <input type="number" id="numCantTras" name="numCantTras" min="1" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="form-row text-center">
                    <div class="form-group col-md-12">
                        <label for="txtaObservacionTras" class="small">Observaciones</label>
                        <textarea class="form-control" id="txtaObservacionTras" name="txtaObservacionTras" rows="3"></textarea>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="text-center pt-3">
    <button id="btnAddDetallesTrasl" type="button" class="btn btn-primary btn-sm">Registrar</button>
    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
</div>