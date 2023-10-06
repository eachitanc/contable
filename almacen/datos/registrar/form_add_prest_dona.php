<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_pde = isset($_POST['id_pd']) ? explode('|', $_POST['id_pd']) : exit('Acción no permitida');
$id_pd = $id_pde[0];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_tipo_entrada`,`id_tercero_api`,`acta_remision`,`fec_entrada`,`estado` FROM `seg_entrada_almacen` WHERE `id_entrada` = '$id_pd'";
    $rs = $cmd->query($sql);
    $entraxpresta = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$tipo = $entraxpresta['id_tipo_entrada'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `descripcion` FROM  `seg_tipo_entrada` WHERE `id_entrada` = $tipo";
    $rs = $cmd->query($sql);
    $tentradas = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$tipol = $tentradas['descripcion'];
if ($entraxpresta['estado'] < 3) {
?>
    <div class="px-0">
        <div class="shadow">
            <div class="card-header mb-3" style="background-color: #16a085 !important;">
                <h5 style="color: white;">REGISTRAR ENTRADA <?php echo mb_strtoupper($tipol) ?></h5>
            </div>
            <div class="px-2">
                <form id="formAddEntraPrestDona">
                    <input type="hidden" id="id_tercero_pd" name="id_tercero_pd" value="<?php echo $entraxpresta['id_tercero_api'] ?>">
                    <input type="hidden" name="id_pre_don" value="<?php echo $id_pd ?>">
                    <input type="hidden" id="tipoEntrada" name="tipoEntrada" value="<?php echo $entraxpresta['id_tipo_entrada'] ?>">
                    <input type="hidden" id="numActaRem" name="numActaRem" value="<?php echo $entraxpresta['acta_remision'] ?>">
                    <div class="form-row text-center">
                        <div class="form-group col-md-6">
                            <label for="buscProd" class="small">Bien y/o producto</label>
                            <input id="buscProd" type="text" class="form-control form-control-sm" placeholder="Buscar">
                            <input type="hidden" id="id_bnsvc" name="id_bnsvc">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="slcSede" class="small">Sede</label>
                            <select type="text" id="slcSede" name="slcSede" class="form-control form-control-sm">
                                <option value="1" selected>Sede principal</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="slcBodega" class="small">Bodega</label>
                            <select id="slcBodega" name="slcBodega" class="form-control form-control-sm">
                                <option value="1" selected>Bodega 1</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row text-center">
                        <div class="form-group col-md-2">
                            <label for="numCantRecb" class="small">cantidad</label>
                            <input type="number" id="numCantRecb" name="numCantRecb" class="form-control form-control-sm">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="numValUnita" class="small">Val. Und</label>
                            <input type="number" id="numValUnita" name="numValUnita" class="form-control form-control-sm" placeholder="Valor sin IVA">
                        </div>
                        <div class="form-group col-md-2">
                            <label for="numIvaProd" class="small">% IVA</label>
                            <select name="numIvaProd" id="numIvaProd" class="form-control form-control-sm">
                                <option value="0">0%</option>
                                <option value="5">5%</option>
                                <option value="19">19%</option>
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="lote" class="small">lote</label>
                            <input type="text" id="lote" name="lote" class="form-control form-control-sm">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="fec_vence" class="small">fecha vencimiento</label>
                            <input type="date" id="fec_vence" name="fec_vence" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="form-row text-center">
                        <div class="form-group col-md-3">
                            <label for="invima" class="small">INVIIMA</label>
                            <input type="text" id="invima" name="invima" class="form-control form-control-sm">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="txtMarcaI" class="small">MARCA</label>
                            <input type="text" id="txtMarcaI" name="txtMarcaI" class="form-control form-control-sm">
                            <input type="hidden" id="idMarcaI" name="idMarcaI" value="0">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php
    $registrar = '<button id="addEntraXPrestDona" type="button" class="btn btn-primary btn-sm">Registrar</button>';
} else {
    echo '<div class="alert alert-info" role="alert">NO SE PUEDE REGISTRAR DESPUES DE HABER CERRADO LOS REGISTROS</div>';
    $registrar = null;
}
?>
<div class="text-center pt-3">
    <?php echo $registrar ?>
    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
</div>