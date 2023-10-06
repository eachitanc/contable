<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}

include '../conexion.php';
include '../permisos.php';
$key = array_search('7', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
$data = isset($_POST['datos']) ? explode('|', $_POST['datos']) : array('0', '', '0', '');
$id_enlote =  $data[0];
$lote = $data[1];
$id_product =  $data[2];
$describe = $data[3];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_traslado`, `descripcion` FROM  `seg_tipo_traslado_almacen`";
    $rs = $cmd->query($sql);
    $ttraslado = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../head.php' ?>

<body class="sb-nav-fixed <?php if ($_SESSION['navarlat'] == '1') {
                                echo 'sb-sidenav-toggled';
                            } ?>">
    <?php include '../navsuperior.php' ?>
    <div id="layoutSidenav">
        <?php include '../navlateral.php' ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid p-2">
                    <div class="card mb-4">
                        <div class="card-header" id="divTituloPag">
                            <div class="row">
                                <div class="col-md-11">
                                    <i class="fas fa-balance-scale-left fa-lg" style="color:#1D80F7"></i>
                                    AJUSTE DE INVENTARIO.
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <form id="formAjustarCantidad">
                                <div class="form-row text-center">
                                    <div class="form-group col-md-6">
                                        <label for="buscarArticuloAjIn" class="small">Buscar Artículo</label>
                                        <input id="buscarArticuloAjIn" class="form-control form-control-sm" value="<?php echo $describe ?>">
                                        <input id="desc_prod_ajuste" type="hidden" name="desc_prod_ajuste" value="<?php echo $describe ?>">
                                        <input id="id_prod_ajuste" type="hidden" name="id_prod_ajuste" value="<?php echo $id_product ?>">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="buscarLoteAjIn" class="small">Buscar lote</label>
                                        <input id="buscarLoteAjIn" class="form-control form-control-sm" value="<?php echo $lote ?>">
                                        <input id="desc_lote" type="hidden" name="desc_lote" value="<?php echo $lote ?>">
                                        <input id="id_Enlote_ajuste" type="hidden" name="id_Enlote_ajuste" value="<?php echo $id_enlote ?>">
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="existencia_lote" class="small">Cantidad Disponible</label>
                                        <input id="existencia_lote" name="existencia_lote" class="form-control form-control-sm">
                                    </div>
                                    <div class="form-group col-md-1">
                                        <label for="btnAjustarCantidad" class="small" style="color: #ffffff00;">botón</label>
                                        <button type="button" id="btnAjustarCantidad" class="btn btn-success btn-sm w-100">Ajustar</button>
                                    </div>
                                </div>
                            </form>
                            <?php if ($id_enlote != 0) { ?>
                                <input id="tipo_trasl_alm" type="hidden" value="<?php echo $id_enlote ?>">
                                <input type="hidden" id="peReg" value="<?php echo $permisos['registrar'] ?>">
                                <table id="tableAjusteInventario" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                    <thead>
                                        <tr class="text-center">
                                            <th class="centro-vertical">Fecha Registro</th>
                                            <th class="centro-vertical">Sede</th>
                                            <th class="centro-vertical">Bodega</th>
                                            <th class="centro-vertical"># Acta o remisión</th>
                                            <th class="centro-vertical">Tercero</th>
                                            <th class="centro-vertical">Entrada</th>
                                            <th class="centro-vertical">Salida</th>
                                            <th class="centro-vertical">Cantidad</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modificarAjustesInventario">
                                    </tbody>
                                </table>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../footer.php' ?>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="divModalError" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header" id="divModalHeader">
                        <h5 class="modal-title" id="exampleModalLongTitle">
                            <i class="fas fa-exclamation-circle fa-lg" style="color:red"></i>
                            ¡Error!
                        </h5>
                    </div>
                    <div class="modal-body text-center" id="divMsgError">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success btn-sm" data-dismiss="modal">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
        <!-- Modal -->
        <div class="modal fade" id="divModalConfDel" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header" id="divModalHeaderConfir">
                        <h5 class="modal-title" id="exampleModalLongTitle">
                            <i class="fas fa-exclamation-triangle fa-lg" style="color: #E67E22;"></i>
                            ¡Confirmar!
                        </h5>
                    </div>
                    <div class="modal-body" id="divMsgConfdel">

                    </div>
                    <div class="modal-footer" id="divBtnsModalDel">
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="divModalDone" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header" id="divDone">
                        <h5 class="modal-title" id="exampleModalLongTitle">
                            <i class="fas fa-check-circle fa-lg" style="color:#2FDA49"></i>
                            ¡Correcto!
                        </h5>
                    </div>
                    <div class="modal-body text-center" id="divMsgDone">

                    </div>
                    <div class="modal-footer">
                        <a type="button" class="btn btn-primary btn-sm" data-dismiss="modal"> Aceptar</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
        <!-- Modal -->
        <div class="modal fade" id="divModalForms" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div id="divTamModalForms" class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body text-center" id="divForms">

                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
        <!-- Modal -->
        <div class="modal fade" id="divModalReg" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div id="divTamModalReg" class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body text-center" id="divFormsReg">

                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
    </div>
    <?php include '../scripts.php' ?>
</body>

</html>