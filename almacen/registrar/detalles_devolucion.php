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
$id_dev = isset($_POST['id_dev']) ? $_POST['id_dev'] : exit('Accion no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `id_devolucion`,`id_tercero_api`,`id_tipo_salida`, `descripcion`,`acta_remision`,`fec_acta_remision`,`observacion`,`estado`,`seg_tipo_salidas`.`descripcion`
            FROM
                `seg_salida_dpdvo`
            INNER JOIN `seg_tipo_salidas` 
                ON (`seg_salida_dpdvo`.`id_tipo_salida` = `seg_tipo_salidas`.`id_salida`) 
            WHERE `id_devolucion` = '$id_dev'";
    $rs = $cmd->query($sql);
    $devolucion = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$tipo_salida = $devolucion['id_tipo_salida'];
$id_t[] = $devolucion['id_tercero_api'] > 0 ? $devolucion['id_tercero_api'] : 0;
//API URL
$payload = json_encode($id_t);
//API URL
$url = $api . 'terceros/datos/res/lista/terceros';
$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
$dat_ter = json_decode($result, true);
$dat_ter = $dat_ter == 0 ? [] : $dat_ter;
if (!empty($dat_ter)) {
    $ccnit = $dat_ter[0]['cc_nit'];
    $tercer = $dat_ter[0]['apellido1'] . ' ' . $dat_ter[0]['apellido2'] . ' ' . $dat_ter[0]['nombre2'] . ' ' . $dat_ter[0]['nombre1'] . ' ' . $dat_ter[0]['razon_social'];
} else {
    $ccnit = '';
    $tercer = '';
}
$estado = $devolucion['estado'];
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
                                    DETALLES DE <?php echo $devolucion['descripcion'] ?>.
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <form id="formDatosDevolucion">
                                <input type="hidden" id="id_terdev" value="<?php echo $devolucion['id_tercero_api'] ?>">
                                <input type="hidden" id="id_dev_det" name="id_dev_det" value="<?php echo $id_dev ?>">
                            </form>
                            <div class="form-group text-right">
                                <a type="button" class="btn btn-secondary  btn-sm" href="../lista_salidas.php">Regresar</a>
                                <?php
                                $estado_dev = $devolucion['estado'];
                                if ($estado_dev < 3) {
                                    if ($tipo_salida == 3 || $tipo_salida == 4 || $tipo_salida == 5 || $tipo_salida == 9) {
                                        echo '<input type="hidden" id="peReg" value="1">';
                                    }
                                    if ($tipo_salida == 8) {
                                        echo '<a id="btnEntregaPedido" type="button" class="btn btn-success btn-sm" value="FIANZA">Entregar ' . mb_strtolower($devolucion['descripcion']) . ' </a>';
                                    }
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
                                        <div class="div-cont"><?php echo mb_strtoupper($devolucion['descripcion']) ?></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="div-mostrar bor-bottom-left col-md-4">
                                        <label class="lbl-mostrar"># ACTA O REMISIÓN</label>
                                        <div class="div-cont"><?php echo ($devolucion['acta_remision']) ?></div>
                                    </div>
                                    <div class="div-mostrar bor-bottom-right col-md-8">
                                        <label class="lbl-mostrar">OBSERVACIONES</label>
                                        <div class="div-cont"><?php echo mb_strtoupper($devolucion['observacion']) ?></div>
                                    </div>
                                </div>
                            </div>
                            <input id="id_tipo_sal_det" type="hidden" value="<?php echo $tipo_salida ?>">
                            <form id="formDetSalidaFianza">
                                <input type="hidden" id="id_dev_det" name="id_dev_det" value="<?php echo $id_dev ?>">
                                <table id="tableDetallesDevolucion" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                    <thead>
                                        <tr class="text-center">
                                            <th>ID</th>
                                            <th>Descripción</th>
                                            <th>Cantidad</th>
                                            <th>Lote</th>
                                            <th>Vencimiento</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modificarDetalleDev">
                                    </tbody>
                                </table>
                            </form>
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