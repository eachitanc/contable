<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_tipo = isset($_POST['t_traslado']) ? $_POST['t_traslado'] : exit('Acción no permitida');
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
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR TRASLADO ENTRE <?php echo $ttraslado['descripcion'] ?> </h5>
        </div>
        <div class="px-2">
            <form id="formAddTrasaldoAlmacen">
                <input id="id_tipo_traslado" type="hidden" name="id_tipo_traslado" value="<?php echo $id_tipo ?>">
                <div class="form-row text-center">
                    <div class="form-group col-md-3">
                        <label for="slcSedeSalida" class="small">SEDE SALIDA</label>
                        <select id="slcSedeSalida" name="slcSedeSalida" class="form-control form-control-sm">
                            <option value="0">--Seleccionar--</option>
                            <?php
                            foreach ($sedes as $s) {
                                echo '<option value="' . $s['id_sede'] . '">' . $s['nombre'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="slcBodegaSalida" class="small">BODEGA SALIDA</label>
                        <select id="slcBodegaSalida" name="slcBodegaSalida" class="form-control form-control-sm">
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="slcSedeEntrada" class="small">SEDE ENTRADA</label>
                        <select id="slcSedeEntrada" name="slcSedeEntrada" class="form-control form-control-sm">
                            <option value="0">--Seleccionar--</option>
                            <?php
                            foreach ($sedes as $s) {
                                echo '<option value="' . $s['id_sede'] . '">' . $s['nombre'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="slcBodegaEntrada" class="small">BODEGA ENTRADA</label>
                        <select id="slcBodegaEntrada" name="slcBodegaEntrada" class="form-control form-control-sm">
                        </select>
                    </div>
                </div>
                <div class="form-row text-center">
                    <div class="form-group col-md-6">
                        <label for="numActaRemTrasl" class="small"># acta y/o remisión</label>
                        <input type="text" id="numActaRemTrasl" name="numActaRemTrasl" class="form-control form-control-sm">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="fecActRemTrasl" class="small">Fecha de acta y/o remisión</label>
                        <input type="date" id="fecActRemTrasl" name="fecActRemTrasl" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="form-row text-center">
                    <div class="form-group col-md-12">
                        <label for="txtaObservacionTrasl" class="small">Observaciones</label>
                        <textarea class="form-control" id="txtaObservacionTrasl" name="txtaObservacionTrasl" rows="3"></textarea>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="text-center pt-3">
    <button id="btnTraslados" type="button" class="btn btn-primary btn-sm">Registrar</button>
    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
</div>