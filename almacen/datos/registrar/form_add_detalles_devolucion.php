<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_tipo_sal_det = isset($_POST['id_tipo_sal_det']) ? $_POST['id_tipo_sal_det'] : exit('Accion no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_salida`,`descripcion` FROM `seg_tipo_salidas` WHERE `id_salida` = '$id_tipo_sal_det'";
    $rs = $cmd->query($sql);
    $tsalida = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR DETALLE DE <?php echo $tsalida['descripcion'] ?></h5>
        </div>
        <div class="px-2">
            <form id="formAddDetalleDevol">
                <div class="form-row text-center">
                    <div class="form-group col-md-12">
                        <label for="muestraExistencias " class="small">Bien y/o producto</label>
                        <input id="muestraExistencias" type="text" class="form-control form-control-sm" placeholder="Buscar">
                        <input type="hidden" id="id_prod" name="id_prod" value="0">
                    </div>
                </div>
                <div id="existencias">

                </div>
            </form>
        </div>
    </div>
</div>
<div class="text-center pt-3">
    <button id="btnAddDetallesDevol" type="button" class="btn btn-primary btn-sm">Registrar</button>
    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
</div>