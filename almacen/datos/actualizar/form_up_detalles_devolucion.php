<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_salida = isset($_POST['id_sal']) ? $_POST['id_sal'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_salidas_almacen`.`id_salida`
                , `seg_salidas_almacen`.`id_entrada`
                , `seg_salidas_almacen`.`id_devolucion`
                , `seg_salidas_almacen`.`id_producto`
                , `seg_salidas_almacen`.`cantidad`
                , `seg_salidas_almacen`.`observacion`
                , `seg_detalle_entrada_almacen`.`lote`
                , `seg_salidas_almacen`.`existencia`
            FROM
                `seg_salidas_almacen`
                INNER JOIN `seg_detalle_entrada_almacen` 
                    ON (`seg_salidas_almacen`.`id_entrada` = `seg_detalle_entrada_almacen`.`id_entrada`)
            WHERE `seg_salidas_almacen`.`id_salida` = '$id_salida'";
    $rs = $cmd->query($sql);
    $detalles_dev = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
include '../../../conexion.php';
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">ACTUALIZAR O MODIFICAR DETALLE DE DEVOLUCIÓN</h5>
        </div>
        <div class="px-2">
            <form id="formAddDetalleDevol">
                <div class="form-row text-center">
                    <div class="form-group col-md-6">
                        <label for="txtLoteDev" class="small">buscar # lote</label>
                        <input type="text" id="numLoteDev" class="form-control form-control-sm" value="<?php echo $detalles_dev['lote'] ?>">
                        <input type="hidden" id="id_proDev" name="id_proDev" value="<?php echo $detalles_dev['id_producto'] ?>">
                        <input type="hidden" name="id_proDev_ant" value="<?php echo $detalles_dev['id_producto'] ?>">
                        <input type="hidden" name="num_Existencia_Dev" value="<?php echo $detalles_dev['existencia'] ?>">
                        <input type="hidden" id="id_entrada_dev" name="id_entradaDev" value="<?php echo $detalles_dev['id_entrada'] ?>">
                        <input type="hidden" id="id_entrada_ant" name="id_entrada_ant" value="<?php echo $detalles_dev['id_entrada'] ?>">
                        <input type="hidden" id="id_dev" name="id_dev" value="<?php echo $detalles_dev['id_devolucion'] ?>">
                        <input type="hidden" id="id_salida" name="id_salida" value="<?php echo $id_salida ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="numCantDev" class="small">cantidad</label>
                        <input type="number" id="numCantDev" name="numCantDev" min="1" class="form-control form-control-sm" value="<?php echo $detalles_dev['cantidad'] ?>">
                        <input type="hidden" name="numCantDev_ant" value="<?php echo $detalles_dev['cantidad'] ?>">
                    </div>
                </div>
                <div class="form-row text-center">
                    <div class="form-group col-md-12">
                        <label for="txtaObservacionDev" class="small">Observaciones</label>
                        <textarea class="form-control" id="txtaObservacionDev" name="txtaObservacionDev" rows="3"><?php echo $detalles_dev['observacion'] ?></textarea>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="text-center pt-3">
    <button id="btnUpDetallesDevol" type="button" class="btn btn-primary btn-sm">Actualizar</button>
    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
</div>