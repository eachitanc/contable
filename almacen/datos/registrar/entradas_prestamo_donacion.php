<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
$id_pd = isset($_POST['id_pd']) ? $_POST['id_pd'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_tipo_entrada`,`id_tercero_api`,`acta_remision`,`fec_entrada`,`estado`, `observacion` FROM `seg_entrada_almacen` WHERE `id_entrada` = '$id_pd'";
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
$id_ter = $entraxpresta['id_tercero_api'];
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
$estado = $entraxpresta['estado'];
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
<?php include '../../../head.php' ?>

<body class="sb-nav-fixed <?php if ($_SESSION['navarlat'] == '1') {
                                echo 'sb-sidenav-toggled';
                            } ?>">
    <?php include '../../../navsuperior.php' ?>
    <div id="layoutSidenav">
        <?php include '../../../navlateral.php' ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid p-2">
                    <div class="card mb-4">
                        <div class="card-header" id="divTituloPag">
                            <div class="row">
                                <div class="col-md-11">
                                    <i class="fas fa-list-alt fa-lg" style="color:#1D80F7"></i>
                                    ENTRADAS POR <?php echo $tipol ?>.
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <?php
                            if ($entraxpresta['estado'] < 3) {
                                echo '<input type="hidden" id="peReg" value="' . $permisos['registrar'] . '">';
                            }
                            ?>
                            <input type="hidden" id="id_prestdonac" value="<?php echo $id_pd . '|' . $entraxpresta['id_tipo_entrada'] ?>">
                            <div class="form-group text-right">
                                <a type="button" class="btn btn-secondary  btn-sm" href="../../lista_entradas.php">Regresar</a>
                                <?php
                                $tipoDentra = $entraxpresta['id_tipo_entrada'] == 2 ? 'Préstamo' : 'Donación';
                                if ($entraxpresta['estado'] < 3) {
                                    echo '<a id="btnCerrarPreDon" type="button" class="btn btn-success btn-sm" value="' . $id_pd . '">Cerrar </a>';
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
                                        <div class="div-cont"><?php echo mb_strtoupper($tipol) ?></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="div-mostrar bor-bottom-left col-md-4">
                                        <label class="lbl-mostrar"># ACTA O REMISIÓN</label>
                                        <div class="div-cont"><?php echo ($entraxpresta['acta_remision']) ?></div>
                                    </div>
                                    <div class="div-mostrar bor-bottom-right col-md-8">
                                        <label class="lbl-mostrar">OBSERVACIONES</label>
                                        <div class="div-cont"><?php echo mb_strtoupper($entraxpresta['observacion']) ?></div>
                                    </div>
                                </div>
                            </div>
                            <table id="tableRegPresDona" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Bien o servicio</th>
                                        <th>Cantidad</th>
                                        <th>Valor</th>
                                        <th>IVA</th>
                                        <th>Subtotal - IVA</th>
                                        <th>Subtotal + IVA</th>
                                        <th>Lote</th>
                                        <th>Fecha Vence</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../../../footer.php' ?>
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
    <?php include '../../../scripts.php' ?>
</body>

</html>