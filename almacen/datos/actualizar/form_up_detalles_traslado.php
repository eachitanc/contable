<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_traslado = isset($_POST['id_trasl']) ? $_POST['id_trasl'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_detalles_traslado`.`id_det_traslado`
                , `seg_detalles_traslado`.`id_entrada`
                , `seg_detalles_traslado`.`id_producto`
                , `seg_detalles_traslado`.`cantidad`
                , `seg_detalles_traslado`.`observacion`
                , `seg_detalle_entrada_almacen`.`lote`
                , `seg_detalle_entrada_almacen`.`cant_ingresa`
                , `seg_traslados_almacen`.`id_sede_sale`
                , `seg_traslados_almacen`.`id_bodega_sale`
            FROM
                `seg_detalles_traslado`
                INNER JOIN `seg_detalle_entrada_almacen` 
                    ON (`seg_detalles_traslado`.`id_entrada` = `seg_detalle_entrada_almacen`.`id_entrada`)
                INNER JOIN `seg_traslados_almacen` 
                    ON (`seg_detalles_traslado`.`id_traslado` = `seg_traslados_almacen`.`id_trasl_alm`)
            WHERE `seg_detalles_traslado`.`id_det_traslado` =  '$id_traslado'";
    $rs = $cmd->query($sql);
    $detal_Trasl = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
include '../../../conexion.php';
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">ACTUALIZAR O MODIFICAR DETALLE DE TRASLADO</h5>
        </div>
        <div class="px-2">
            <form id="formUpDetalleTrasl">
                <input type="hidden" id="id_sede_sale" name="id_sede_sale" value="<?php echo $detal_Trasl['id_sede_sale'] ?>">
                <input type="hidden" id="id_bodega_sale" name="id_bodega_sale" value="<?php echo $detal_Trasl['id_bodega_sale'] ?>">
                <div class="form-row text-center">
                    <div class="form-group col-md-6">
                        <label for="numLoteSedeBodega" class="small">buscar # lote</label>
                        <input type="text" id="numLoteSedeBodega" class="form-control form-control-sm" value="<?php echo $detal_Trasl['lote'] ?>">
                        <input type="hidden" id="id_proTras" name="id_proTras" value="<?php echo $detal_Trasl['id_producto'] ?>">
                        <input type="hidden" id="id_entrada_Tras" name="id_entradaTras" value="<?php echo $detal_Trasl['id_entrada'] ?>">
                        <input type="hidden" name="id_entradaTras_ant" value="<?php echo $detal_Trasl['id_entrada'] ?>">
                        <input type="hidden" name="id_up_tra_alm" value="<?php echo $id_traslado ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="numCantTras" class="small">cantidad</label>
                        <input type="number" id="numCantTras" name="numCantTras" min="1" max="<?php echo $detal_Trasl['cant_ingresa'] ?>" class="form-control form-control-sm" value="<?php echo $detal_Trasl['cantidad'] ?>">
                    </div>
                </div>
                <div class="form-row text-center">
                    <div class="form-group col-md-12">
                        <label for="txtaObservacionTras" class="small">Observaciones</label>
                        <textarea class="form-control" id="txtaObservacionTras" name="txtaObservacionTras" rows="3"><?php echo $detal_Trasl['observacion'] ?></textarea>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="text-center pt-3">
    <button id="btnUpDetallesTrasl" type="button" class="btn btn-primary btn-sm">Actualizar</button>
    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
</div>