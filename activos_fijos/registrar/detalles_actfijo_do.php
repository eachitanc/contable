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
$id_af = isset($_POST['id']) ? $_POST['id'] : exit('Accion no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_entrada_activo_fijo`.`id_entra_af`
                , `seg_entrada_activo_fijo`.`id_tercero_api`
                , `seg_entrada_activo_fijo`.`id_tipo_entrada`
                , `seg_tipo_entrada`.`descripcion`
                , `seg_entrada_activo_fijo`.`acta_remision`
                , `seg_entrada_activo_fijo`.`fec_acta_remision`
                , `seg_entrada_activo_fijo`.`observacion`
                , `seg_entrada_activo_fijo`.`estado`
            FROM
                `seg_entrada_activo_fijo`
                INNER JOIN `seg_tipo_entrada` 
                    ON (`seg_entrada_activo_fijo`.`id_tipo_entrada` = `seg_tipo_entrada`.`id_entrada`) 
            WHERE `id_entra_af` = '$id_af'";
    $rs = $cmd->query($sql);
    $activo_fijo = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$tipo_entrada = $activo_fijo['id_tipo_entrada'];
$id_ter = $activo_fijo['id_tercero_api'];
//API URL
$url = $api . 'terceros/datos/res/datos/id/' . $id_ter;
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res_api = curl_exec($ch);
curl_close($ch);
$dat_ter = json_decode($res_api, true);
$ccnit = $dat_ter[0]['cc_nit'];
$tercer = $dat_ter[0]['apellido1'] . ' ' . $dat_ter[0]['apellido2'] . ' ' . $dat_ter[0]['nombre2'] . ' ' . $dat_ter[0]['nombre1'] . ' ' . $dat_ter[0]['razon_social'];
$estado = $activo_fijo['estado'];
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
                                    <i class="fas fa-list-alt fa-lg" style="color:#1D80F7"></i>
                                    DETALLES DE <?php echo $activo_fijo['descripcion'] ?>.
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <form id="formDatosActivoFijoDet">
                                <input type="hidden" id="id_terdev" value="<?php echo $activo_fijo['id_tercero_api'] ?>">
                                <input type="hidden" id="id_acfi_det" name="id_acfi_det" value="<?php echo $id_af ?>">
                            </form>
                            <div class="form-group text-right">
                                <a type="button" class="btn btn-secondary  btn-sm" href="../entradas_activos_fijos.php">Regresar</a>
                                <?php
                                $estado_dev = $activo_fijo['estado'];
                                if ($estado_dev < 3) {
                                    echo '<input type="hidden" id="peReg" value="' . $permisos['registrar'] . '">';
                                    echo '<a id="btnCerrarDOActFijo" type="button" class="btn btn-success btn-sm" value="' . $id_af . '">Cerrar ' . mb_strtolower($activo_fijo['descripcion']) . ' </a>';
                                } else {
                                    echo '<button type="button" class="btn btn-secondary btn-sm" disabled>Cerrado</button>';
                                }
                                ?>
                            </div>
                            <div class="shadow detalles-empleado mb-4">
                                <div class="row">
                                    <div class="div-mostrar bor-top-left col-md-2">
                                        <label class="lbl-mostrar">CC o NIT</label>
                                        <div class="div-cont"><?php echo $ccnit ?></div>
                                    </div>
                                    <div class="div-mostrar col-md-6">
                                        <label class="lbl-mostrar">TERCERO</label>
                                        <div class="div-cont"><?php echo $tercer ?></div>
                                    </div>
                                    <div class="div-mostrar col-md-2">
                                        <label class="lbl-mostrar">ESTADO</label>
                                        <div class="div-cont"><?php echo $estado ?></div>
                                    </div>
                                    <div class="div-mostrar bor-top-right col-md-2">
                                        <label class="lbl-mostrar">TIPO</label>
                                        <div class="div-cont"><?php echo mb_strtoupper($activo_fijo['descripcion']) ?></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="div-mostrar bor-bottom-left col-md-4">
                                        <label class="lbl-mostrar"># ACTA O REMISIÓN</label>
                                        <div class="div-cont"><?php echo ($activo_fijo['acta_remision']) ?></div>
                                    </div>
                                    <div class="div-mostrar bor-bottom-right col-md-8">
                                        <label class="lbl-mostrar">OBSERVACIONES</label>
                                        <div class="div-cont"><?php echo mb_strtoupper($activo_fijo['observacion']) ?></div>
                                    </div>
                                </div>
                            </div>
                            <input id="id_tipo_entra_acfi_det" type="hidden" value="<?php echo $tipo_entrada ?>">
                            <table id="tableDetallesActFijoDO" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                <thead>
                                    <tr class="text-center">
                                        <th>ID</th>
                                        <th>Bien</th>
                                        <th>Mantenimiento</th>
                                        <th>Depreciable</th>
                                        <th>Marca</th>
                                        <th>Modelo</th>
                                        <th>Valor Unitario</th>
                                        <th>Descripción</th>
                                        <th>Cantidad</th>
                                        <th>No. Serial(es)</th>
                                        <th>Tipo de Activo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="modificarDetalleActFijDO">
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