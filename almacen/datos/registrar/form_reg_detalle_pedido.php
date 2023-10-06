<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISRTAR DETALLE DE PEDIDO</h5>
        </div>
        <div class="px-2">
            <form id="formRegDetPedido">
                <div class=" form-row">
                    <div class="form-group col-md-9">
                        <label for="buscaBienAlmacen" class="small">Buscar bien o producto</label>
                        <input type="text" class="form-control form-control-sm" id="buscaBienAlmacen">
                        <input type="hidden" id="id_prod" name="id_prod" value="0">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="cantidad" class="small">Cantidad</label>
                        <input type="number" class="form-control form-control-sm" id="numCanProd" name="numCanProd">
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btnRegDetPedido">Registrar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>