<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_pedido = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
$rol = $_SESSION['rol'];
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
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
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_bodega`, `bod_entrega` FROM `seg_pedidos_almacen` WHERE `id_pedido` = $id_pedido";
    $rs = $cmd->query($sql);
    $ar = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">ACTUALIZA ENCABEZADO DE PEDIDO</h5>
        </div>
        <div class="px-2">
            <form id="formUpPedido">
                <input type="hidden" name="idPedido" value="<?php echo $id_pedido; ?>">
                <div class=" form-row">
                    <div class="form-group col-md-6">
                        <label for="idAreaEntrega" class="small">Area entrega</label>
                        <select class="form-control form-control-sm" id="idAreaEntrega" name="idAreaEntrega">
                            <?php
                            foreach ($pedirA as $pa) {
                                $slc = $pa['id_bodega'] == $ar['bod_entrega'] ? 'selected' : '';
                                echo '<option value="' . $pa['id_bodega'] . '" ' . $slc . '>' . $pa['nombre'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="idAreaPide" class="small">Area solicitante</label>
                        <select class="form-control form-control-sm" id="idAreaPide" name="idAreaPide">
                            <?php
                            foreach ($bodega as $bg) {
                                $slc = $bg['id_bodega'] == $ar['id_bodega'] ? 'selected' : '';
                                echo '<option value="' . $bg['id_bodega'] . '" ' . $slc . '>' . $bg['nombre'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btnUpPedido">Actualizar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>