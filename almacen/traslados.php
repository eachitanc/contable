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
$tipo = isset($_POST['t_traslado']) ? $_POST['t_traslado'] : '0';
$describe = isset($_POST['describe']) ? $_POST['describe'] : '';
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
$rol = $_SESSION['rol'];
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
                                    <i class="fas fa-sync fa-lg" style="color:#1D80F7"></i>
                                    TRASLADOS DE ALMACÉN.
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div class="m-0 row justify-content-center">
                                <div class="form-group text-center">
                                    <label for="slctipoTraslado" class="small">Tipo de traslado</label>
                                    <select id="slctipoTraslado" name="slctipoTraslado" class="form-control form-control-sm" aria-label="Default select example">
                                        <option value="0">--Seleccionar--</option>
                                        <?php
                                        foreach ($ttraslado as $tt) {
                                            $slc = $tt['id_traslado'] == $tipo ? 'selected' : '';
                                            echo '<option ' . $slc . ' value="' . $tt['id_traslado'] . '">' . $tt['descripcion'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <?php if ($tipo != 0) { ?>
                                <input id="tipo_trasl_alm" type="hidden" value="<?php echo $tipo ?>">
                                <?php if ($rol == 1 || $rol == 3) { ?>
                                    <input type="hidden" id="peReg" value="<?php echo $permisos['registrar'] ?>">
                                <?php } ?>
                                <table id="tableTrasladosAlmacen" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                    <thead>
                                        <tr class="text-center">
                                            <th rowspan="2" class="centro-vertical">ID</th>
                                            <th rowspan="2" class="centro-vertical">Tipo</th>
                                            <th colspan="2">Salida</th>
                                            <th colspan="2">Entrada</th>
                                            <!--<th rowspan="2" class="centro-vertical"># Acta o remisión</th>
                                            <th rowspan="2" class="centro-vertical">Observaciones</th>-->
                                            <th rowspan="2" class="centro-vertical">Fecha</th>
                                            <th rowspan="2" class="centro-vertical">Acciones</th>
                                        </tr>
                                        <tr class="text-center centro-vertical">
                                            <th>Sede</th>
                                            <th>Bodega</th>
                                            <th>Sede</th>
                                            <th>Bodega</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modificarTrasladosAlmacen">
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