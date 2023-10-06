<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_bodega = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISRTAR RESPONSABLE DE BODEGA</h5>
        </div>
        <div class="px-2">
            <form id="formRegResposable">
                <input type="hidden" id="id_bodega" name="id_bodega" value="<?php echo $id_bodega ?>">
                <div class=" form-row">
                    <div class="form-group col-md-12">
                        <label for="buscaUserResposable" class="small">usuario resposable</label>
                        <input type="text" class="form-control form-control-sm" id="buscaUserResposable" placeholder="Nombre o documento" required>
                        <input type="hidden" id="id_user_resp" name="id_user_resp" value="0">
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btnRegResposable">Registrar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>