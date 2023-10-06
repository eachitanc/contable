<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$rol = $_SESSION['rol'];

if ($rol == 1 || $rol == 3) {
    $bodegaxresp = '';
} else {
    try {
        $sql = "SELECT `id_usuario`,`id_bodega` FROM `seg_responsable_bodega` WHERE `id_usuario` = $_SESSION[id_user]
                AND `id_resp` IN (SELECT MAX(`id_resp`) FROM `seg_responsable_bodega` GROUP BY (`id_bodega`))";
        $res = $cmd->query($sql);
        $bgxresp = $res->fetchAll();
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    $ids = [];
    if (!empty($bgxresp)) {
        foreach ($bgxresp as $br) {
            $ids[] = $br['id_bodega'];
        }
    }
    $bodegaxresp = 'WHERE (`seg_bodega_almacen`.`id_bodega` IN (' . implode(',', $ids) . '))';
}
try {
    $sql = "SELECT
                `id_bodega`, `nombre`, `id_sede`
            FROM
                `seg_bodega_almacen` " . $bodegaxresp;
    $res = $cmd->query($sql);
    $bodega = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `id_bodega`, `nombre`, `id_sede`
            FROM
                `seg_bodega_almacen`";
    $res = $cmd->query($sql);
    $pedirA = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISRTAR ENCABEZADO DE PEDIDO</h5>
        </div>
        <div class="px-2">
            <form id="formRegPedido">
                <div class=" form-row">
                    <div class="form-group col-md-6">
                        <label for="idAreaEntrega" class="small">Area entrega</label>
                        <select class="form-control form-control-sm" id="idAreaEntrega" name="idAreaEntrega">
                            <option value="0">--Seleccione--</option>
                            <?php
                            foreach ($pedirA as $pa) {
                                $slc = $pa['id_bodega'] == 1 ? 'selected' : '';
                                echo '<option value="' . $pa['id_bodega'] . '" ' . $slc . '>' . $pa['nombre'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="idAreaPide" class="small">Area solicitante</label>
                        <select class="form-control form-control-sm" id="idAreaPide" name="idAreaPide">
                            <option value="0">--Seleccione--</option>
                            <?php
                            foreach ($bodega as $bg) {
                                echo '<option value="' . $bg['id_bodega'] . '">' . $bg['nombre'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btnRegPedido">Registrar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>