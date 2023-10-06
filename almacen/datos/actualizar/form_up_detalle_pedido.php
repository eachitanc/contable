<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_detalle = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_detalle_pedido`.`id_detalle`
                , `seg_detalle_pedido`.`id_producto`
                , `seg_bien_servicio`.`bien_servicio`
                , `seg_detalle_pedido`.`cantidad`
            FROM
                `seg_detalle_pedido`
                INNER JOIN `seg_bien_servicio` 
                    ON (`seg_detalle_pedido`.`id_producto` = `seg_bien_servicio`.`id_b_s`)
            WHERE `seg_detalle_pedido`.`id_detalle` = $id_detalle";
    $rs = $cmd->query($sql);
    $detalle = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">ACTUALIZAR DETALLE DE PEDIDO</h5>
        </div>
        <div class="px-2">
            <form id="formUpDetPedido">
                <input type="hidden" id="id_detalle" name="id_detalle" value="<?php echo $id_detalle ?>">
                <div class=" form-row">
                    <div class="form-group col-md-9">
                        <label for="buscaBienAlmacen" class="small">Buscar bien o producto</label>
                        <input type="text" class="form-control form-control-sm" id="buscaBienAlmacen" value="<?php echo $detalle['bien_servicio'] ?>">
                        <input type="hidden" id="id_prod" name="id_prod" value="<?php echo $detalle['id_producto'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="cantidad" class="small">Cantidad</label>
                        <input type="number" class="form-control form-control-sm" id="numCanProd" name="numCanProd" value="<?php echo $detalle['cantidad'] ?>">
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btnUpDetPedido">Actualizar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>