<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISRTAR MARCAS DE PRODUCTO</h5>
        </div>
        <div class="px-2">
            <form id="formRegMarca">
                <div class=" form-row">
                    <div class="form-group col-md-12">
                        <label for="txtMarca" class="small">Nombre</label>
                        <input type="text" class="form-control form-control-sm" id="txtMarca" name="txtMarca" placeholder="Nombre de marca" required>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btnRegMarca">Registrar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>