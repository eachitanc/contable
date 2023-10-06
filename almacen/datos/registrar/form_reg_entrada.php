<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
$campos = isset($_POST['campos']) ? explode('|', $_POST['campos']) : exit('Acción no permitida');
$id_prod = $campos[0];
$id_api = $campos[1];
$bnsv = $campos[2];
$cantidad = $campos[3];
$val_uni = $campos[4];
$fecha_min = $campos[5];
$estado = trim($campos[6]);
$id_entrada = $_POST['id_entra']
?>
<input id="dateFecMin" type="hidden" value="<?php echo $fecha_min ?>">
<input id="numCantMax" type="hidden" value="<?php echo $cantidad ?>">
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">RECEPCIONAR ENTRADA</h5>
        </div>
        <div class="px-2">
            <?php
            if (strcasecmp($estado, 'PENDIENTE') == 0) {
            ?>
                <form id="formCantRegAlmacen">
                    <input id="idApi" name="idApi" hidden value="<?php echo $id_api ?>">
                    <input id="idProd" name="idProd" hidden value="<?php echo $id_prod ?>">
                    <input id="numCantMax" name="numCantMax" type="hidden" value="<?php echo $cantidad ?>">
                    <input id="idtentrada" name="idtentrada" type="hidden" value="1">
                    <input id="id_entrada" name="id_entrada" type="hidden" value="<?php echo $id_entrada ?>">
                    <div class=" form-row">
                        <div class="form-group col-md-12">
                            <label for="nom_prod" class="small">Descripción</label>
                            <input type="text" id="nom_prod" class="form-control form-control-sm" value="<?php echo $bnsv ?>" readonly>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-2">
                            <label for="cantidad" class="small">CANTIDAD</label>
                            <input type="number" id="cantidad" name="cantidad[]" class="form-control form-control-sm" min="0" max="<?php echo $cantidad ?>" value="<?php echo $cantidad ?>">
                        </div>
                        <div class="form-group col-md-2">
                            <label for="valorUnitario" class="small">Valor unitario</label>
                            <input id="valorUnitario" name="valor" class="form-control form-control-sm" value="<?php echo $val_uni ?>" readonly>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="lote" class="small">lote</label>
                            <input type="text" id="lote" name="lote[]" class="form-control form-control-sm">
                        </div>
                        <div class="form-group col-md-2">
                            <label for="fec_vence" class="small">fecha vence</label>
                            <input type="date" id="fec_vence" name="fec_vence[]" class="form-control form-control-sm" min="<?php echo $fecha_min ?>">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="txtMarca" class="small">Marca</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control buscaMarca">
                                <input type="hidden" name="txtMarca[]" value="0">
                                <div class="input-group-append">
                                    <a id="btnMasEntradas" class="btn btn-outline-info"><span class="fas fa-plus-circle fa-lg"></span></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="divMasInputs">
                    </div>
                </form>
            <?php
            } else {
                echo '<div class="alert alert-danger" role="alert">ELEMENTO YA RECEPCIONADO</div><br>';
            }
            ?>
        </div>
    </div>
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm gchanges">Recepcionar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>