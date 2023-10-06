<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_bg = $_POST['id'];
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
try {
    $sql = "SELECT
                `id_sede`
                , `nombre`
            FROM
                `seg_sedes_empresa`
            WHERE `nombre` <> 'CONVENIOS'";
    $res = $cmd->query($sql);
    $bodega = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

try {
    $sql = "SELECT
                `id_sede`
                , `nombre`
            FROM
                `seg_sedes_empresa`
            WHERE `nombre` <> 'CONVENIOS'";
    $res = $cmd->query($sql);
    $sedes = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT `id_sede`,`nombre` FROM `seg_bodega_almacen` WHERE `id_bodega` = $id_bg";
    $res = $cmd->query($sql);
    $datos = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">ACTUALIZAR BODEGA</h5>
        </div>
        <div class="px-2">
            <form id="formUpBodega">
                <div class=" form-row">
                    <input type="hidden" name="id_bodega" value="<?php echo $id_bg; ?>">
                    <div class="form-group col-md-5">
                        <label for="slcIdSede" class="small">SEDE</label>
                        <select class="form-control form-control-sm" id="slcIdSede" name="slcIdSede">
                            <?php
                            foreach ($sedes as $bg) {
                                $slc = $datos['id_sede'] == $bg['id_sede'] ? 'selected' : '';
                                echo '<option value="' . $bg['id_sede'] . '" ' . $slc . '>' . $bg['nombre'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-7">
                        <label for="txtNewBodega" class="small">Nueva Bodega</label>
                        <input type="text" class="form-control form-control-sm" id="txtNewBodega" name="txtNewBodega" value="<?php echo $datos['nombre']; ?>">
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btnUpBodega">Actualizar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>