<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_traslado = isset($_POST['id_tras']) ? $_POST['id_tras'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `id_trasl_alm`,`id_tipo_trasl`,`acta_remision`,`observacion`,`id_sede_sale`,`id_bodega_sale`,`id_sede_entra`,`id_bodega_entra`,`fec_traslado`,`estado`
            FROM 
                `seg_traslados_almacen`
            WHERE `id_trasl_alm` = '$id_traslado'";
    $rs = $cmd->query($sql);
    $traslado = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$id_tipo = $traslado['id_tipo_trasl'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_traslado`,`descripcion` FROM `seg_tipo_traslado_almacen` WHERE `id_traslado` = '$id_tipo'";
    $rs = $cmd->query($sql);
    $ttraslado = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_sede`, `nombre` FROM `seg_sedes_empresa`";
    $rs = $cmd->query($sql);
    $sedes = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_bodega`,`id_sede`, `nombre` FROM `seg_bodega_almacen`";
    $rs = $cmd->query($sql);
    $bodegas = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">ACTUALIZAR O MODIFICAR TRASLADO ENTRE <?php echo $ttraslado['descripcion'] ?> </h5>
        </div>
        <div class="px-2">
            <form id="formUpTrasaldoAlmacen">
                <input id="id_Uptraslado" type="hidden" name="id_Uptraslado" value="<?php echo $id_traslado ?>">
                <div class="form-row text-center">
                    <div class="form-group col-md-3">
                        <label for="slcSedeSalida" class="small">SEDE SALIDA</label>
                        <select id="slcSedeSalida" name="slcSedeSalida" class="form-control form-control-sm">
                            <?php
                            foreach ($sedes as $s) {
                                $slc = $s['id_sede'] == $traslado['id_sede_sale'] ? 'selected' : '';
                                echo '<option  ' . $slc . ' value="' . $s['id_sede'] . '">' . $s['nombre'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="slcBodegaSalida" class="small">BODEGA SALIDA</label>
                        <select id="slcBodegaSalida" name="slcBodegaSalida" class="form-control form-control-sm">
                            <?php
                            foreach ($bodegas as $b) {
                                $slc = $b['id_bodega'] == $traslado['id_bodega_sale'] ? 'selected' : '';
                                if ($b['id_sede'] == $traslado['id_sede_sale']) {
                                    echo '<option ' . $slc . ' value="' . $b['id_bodega'] . '">' . $b['nombre'] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="slcSedeEntrada" class="small">SEDE ENTRADA</label>
                        <select id="slcSedeEntrada" name="slcSedeEntrada" class="form-control form-control-sm">
                            <?php
                            foreach ($sedes as $s) {
                                $slc = $s['id_sede'] == $traslado['id_sede_entra'] ? 'selected' : '';
                                echo '<option ' . $slc . ' value="' . $s['id_sede'] . '">' . $s['nombre'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="slcBodegaEntrada" class="small">BODEGA ENTRADA</label>
                        <select id="slcBodegaEntrada" name="slcBodegaEntrada" class="form-control form-control-sm">
                            <?php
                            foreach ($bodegas as $b) {
                                $slc = $b['id_bodega'] == $traslado['id_bodega_entra'] ? 'selected' : '';
                                if ($b['id_sede'] == $traslado['id_sede_entra']) {
                                    echo '<option ' . $slc . ' value="' . $b['id_bodega'] . '">' . $b['nombre'] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-row text-center">
                    <div class="form-group col-md-6">
                        <label for="numActaRemTrasl" class="small"># acta y/o remisión</label>
                        <input type="text" id="numActaRemTrasl" name="numActaRemTrasl" class="form-control form-control-sm" value="<?php echo $traslado['acta_remision'] ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="fecActRemTrasl" class="small">Fecha de acta y/o remisión</label>
                        <input type="date" id="fecActRemTrasl" name="fecActRemTrasl" class="form-control form-control-sm" value="<?php echo $traslado['fec_traslado'] ?>">
                    </div>
                </div>
                <div class="form-row text-center">
                    <div class="form-group col-md-12">
                        <label for="txtaObservacionTrasl" class="small">Observaciones</label>
                        <textarea class="form-control" id="txtaObservacionTrasl" name="txtaObservacionTrasl" rows="3"><?php echo $traslado['observacion'] ?></textarea>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="text-center pt-3">
    <button id="btnUpTraslados" type="button" class="btn btn-primary btn-sm">Actualizar</button>
    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
</div>