<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}

include '../../conexion.php';
include '../../permisos.php';
$vigencia = $_SESSION['vigencia'];
$id_traslado = isset($_POST['id_traslado']) ? $_POST['id_traslado'] : exit('Accion no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `id_trasl_alm`,`id_tipo_trasl`,`acta_remision`,`observacion`,`id_sede_sale`,`id_bodega_sale`,`id_sede_entra`,`id_bodega_entra`,`fec_traslado`,`estado`
            FROM `seg_traslados_almacen`
            WHERE `id_trasl_alm` = '$id_traslado'";
    $rs = $cmd->query($sql);
    $traslado = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$tipo_trasl = $traslado['id_tipo_trasl'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_traslado`, `descripcion` FROM  `seg_tipo_traslado_almacen` WHERE `id_traslado` = '$tipo_trasl' ";
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
$estado = $traslado['estado'];
switch ($estado) {
    case '1':
        $estado = 'INICIALIZADA';
        break;
    case '2':
        $estado = 'ABIERTA';
        break;
    case '3':
        $estado = 'CERRRADA';
        break;
    default:
        $estado = '';
        break;
}
$key = array_search($traslado['id_sede_sale'], array_column($sedes, 'id_sede'));
$sede_s = false !== $key ? $sedes[$key]['nombre'] : '';
$key = array_search($traslado['id_bodega_sale'], array_column($bodegas, 'id_bodega'));
$bodega_s = false !== $key ? $bodegas[$key]['nombre'] : '';
$key = array_search($traslado['id_sede_entra'], array_column($sedes, 'id_sede'));
$sede_e = false !== $key ? $sedes[$key]['nombre'] : '';
$key = array_search($traslado['id_bodega_entra'], array_column($bodegas, 'id_bodega'));
$bodega_e = false !== $key ? $bodegas[$key]['nombre'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../../head.php' ?>

<body class="sb-nav-fixed <?php if ($_SESSION['navarlat'] == '1') {
                                echo 'sb-sidenav-toggled';
                            } ?>">
    <?php include '../../navsuperior.php' ?>
    <div id="layoutSidenav">
        <?php include '../../navlateral.php' ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid p-2">
                    <div class="card mb-4">
                        <div class="card-header" id="divTituloPag">
                            <div class="row">
                                <div class="col-md-11">
                                    <i class="fas fa-sync-alt fa-lg" style="color:#1D80F7"></i>
                                    DETALLES DE TRASLADOS ENTRE <?php echo $ttraslado['descripcion'] ?>.
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <form id="formDatosTraslado">
                                <input type="hidden" id="id_up_tra_alm" name="id_up_tra_alm" value="<?php echo $id_traslado ?>">
                            </form>
                            <div class="form-group text-right">
                                <a type="button" class="btn btn-secondary  btn-sm" href="../traslados.php">Regresar</a>
                                <?php
                                $estado_trasl = $traslado['estado'];
                                if ($estado_trasl < 3) {
                                    echo '<input type="hidden" id="peReg" value="' . $permisos['registrar'] . '">';
                                    echo '<a id="btnCerrarTraslado" type="button" class="btn btn-success btn-sm" value="' . $id_traslado . '">Cerrar traslado entre ' . mb_strtolower($ttraslado['descripcion']) . ' </a>';
                                } else {
                                    echo '<button type="button" class="btn btn-secondary btn-sm" disabled>Cerrado</button>';
                                }
                                ?>
                            </div>
                            <div class="shadow detalles-empleado mb-4">
                                <div class="row">
                                    <div class="div-mostrar bor-top-left col-md-3">
                                        <label class="lbl-mostrar">SEDE SALIDA</label>
                                        <div class="div-cont"><?php echo $sede_s ?></div>
                                    </div>
                                    <div class="div-mostrar col-md-3">
                                        <label class="lbl-mostrar">BODEGA SALIDA</label>
                                        <div class="div-cont"><?php echo $bodega_s ?></div>
                                    </div>
                                    <div class="div-mostrar col-md-3">
                                        <label class="lbl-mostrar">SEDE ENTRADA</label>
                                        <div class="div-cont"><?php echo $sede_e ?></div>
                                    </div>
                                    <div class="div-mostrar bor-top-right col-md-3">
                                        <label class="lbl-mostrar">BODEGA ENTRADA</label>
                                        <div class="div-cont"><?php echo $bodega_e ?></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="div-mostrar bor-bottom-left col-md-2">
                                        <label class="lbl-mostrar">ESTADO</label>
                                        <div class="div-cont"><?php echo $estado ?></div>
                                    </div>
                                    <div class="div-mostrar col-md-2">
                                        <label class="lbl-mostrar">TIPO</label>
                                        <div class="div-cont"><?php echo 'TRASLADO ENTRE ' . $ttraslado['descripcion'] ?></div>
                                    </div>
                                    <div class="div-mostrar col-md-2">
                                        <label class="lbl-mostrar"># ACTA o REMISIÓN</label>
                                        <div class="div-cont"><?php echo $traslado['acta_remision'] ?></div>
                                    </div>
                                    <div class="div-mostrar bor-bottom-right col-md-6">
                                        <label class="lbl-mostrar">OBSERVACIONES</label>
                                        <div class="div-cont"><?php echo mb_strtoupper($traslado['observacion']) ?></div>
                                    </div>
                                </div>
                            </div>
                            <table id="tableDetallesTraslado" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                <thead>
                                    <tr class="text-center">
                                        <th>ID</th>
                                        <th>Descripción</th>
                                        <th>Cantidad</th>
                                        <th>Lote</th>
                                        <th>Observacion</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="modificarDetalleTraslados">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../../footer.php' ?>
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
    <?php include '../../scripts.php' ?>
</body>

</html>