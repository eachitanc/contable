<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_bodega = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_bodega_almacen`.`id_bodega`
                , `seg_bodega_almacen`.`nombre`
                , `seg_ctas_gasto`.`id_cta`
                , `seg_ctas_gasto`.`id_tipo_bn_sv`
                , `seg_tipo_bien_servicio`.`tipo_bn_sv`
                , `seg_ctas_gasto`.`cuenta`
            FROM
                `seg_ctas_gasto`
                INNER JOIN `seg_bodega_almacen` 
                    ON (`seg_ctas_gasto`.`id_bodega` = `seg_bodega_almacen`.`id_bodega`)
                INNER JOIN `seg_tipo_bien_servicio` 
                    ON (`seg_ctas_gasto`.`id_tipo_bn_sv` = `seg_tipo_bien_servicio`.`id_tipo_b_s`)
            WHERE (`seg_bodega_almacen`.`id_bodega` = $id_bodega)
            ORDER BY `seg_tipo_bien_servicio`.`tipo_bn_sv`";
    $rs = $cmd->query($sql);
    $cuentas = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_bodega_almacen`.`id_bodega`
                , `seg_bodega_almacen`.`nombre`
                , '0' AS `id_cta`
                ,`seg_tipo_bien_servicio`.`id_tipo_b_s` AS `id_tipo_bn_sv`
                , `seg_tipo_bien_servicio`.`tipo_bn_sv`
                , '' AS `cuenta`
            FROM
                `seg_tipo_bien_servicio`
                , `seg_bodega_almacen`
            WHERE (`seg_tipo_bien_servicio`.`id_tipo_cotrato` = 17 AND `seg_bodega_almacen`.`id_bodega` = $id_bodega)
            ORDER BY `seg_tipo_bien_servicio`.`tipo_bn_sv`";
    $rs = $cmd->query($sql);
    $bienes = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$cuentas = !empty($cuentas) ?  $cuentas : $bienes;
$bodega = $cuentas[0]['nombre'];
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">GESTIONAR CUENTAS CONTABLES <?php echo $bodega ?></h5>
        </div>
        <div class="px-2">
            <form id="formGesCuentas">
                <input type="hidden" id="id_bodega" name="id_bodega" value="<?php echo $id_bodega ?>">
                <?php
                $control = 0;
                foreach ($cuentas as $c) {
                ?>
                    <div class=" form-row">
                        <div class="form-group col-md-8">
                            <?php if ($control == 0) {
                                echo '<label for="txtTipoBien" class="small">TIPO DE BIEN</label>';
                            }
                            ?>
                            <div class="form-control form-control-sm text-left" style="background-color: #F2F3F4;"><?php echo $c['tipo_bn_sv'] ?></div>
                        </div>
                        <div class="form-group col-md-4">
                            <?php if ($control == 0) {
                                echo '<label for="numCuenta" class="small">CUENTA</label>';
                            }
                            $cta0 = $c['id_cta'] == 0 ? '0|' .  $c['id_tipo_bn_sv'] : $c['id_cta'] . '|' . $c['id_tipo_bn_sv'];
                            ?>
                            <input type="number" class="form-control form-control-sm" name="numCuenta[<?php echo $cta0 ?>]" value="<?php echo $c['cuenta'] ?>">
                        </div>
                    </div>
                <?php
                    $control++;
                }
                ?>
            </form>
        </div>
    </div>
    <div class="text-right pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btnActCtasContables">Actualizar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>