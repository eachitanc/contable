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
$key = array_search('3', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
$vigencia = $_SESSION['vigencia'];
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
                                    <i class="fas fa-copy fa-lg" style="color:#1D80F7"></i>
                                    OPCIONES
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div id="accordion">
                                <!-- parte-->
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="modContrata">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapsemodContrata" aria-expanded="true" aria-controls="collapsemodContrata">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="fas fa-file-contract fa-lg" style="color: #2ECC71;"></span>
                                                    </div>
                                                    <div>
                                                        1. MODALIDAD DE CONTRATACIÓN
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="collapsemodContrata" class="collapse" aria-labelledby="modContrata">
                                        <div class="card-body">
                                            <table id="tableModalidad" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th>Modalidad</th>
                                                        <th>Acción</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="modificarModalidades">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!-- parte-->
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="tipoContrato">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapsetipoContrato" aria-expanded="true" aria-controls="collapsetipoContrato">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="fas fa-file-signature fa-lg" style="color: #E74C3C;"></span>
                                                    </div>
                                                    <div>
                                                        2. TIPO DE CONTRATO
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="collapsetipoContrato" class="collapse" aria-labelledby="tipoContrato">
                                        <div class="card-body">
                                            <table id="tableTipoContrato" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th>Tipo de compra</th>
                                                        <th>Tipo de contrato</th>
                                                        <th>Acción</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="modificarTipoContratos">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!-- parte-->
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="tipoSerBien">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapseTipoSerBien" aria-expanded="true" aria-controls="collapseTipoSerBien">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="fas fa-mail-bulk fa-lg" style="color: #5DADE2;"></span>
                                                    </div>
                                                    <div>
                                                        3. TIPO DE BIEN O SERVICIO
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="collapseTipoSerBien" class="collapse" aria-labelledby="tipoSerBien">
                                        <div class="card-body">
                                            <table id="tableTipoBnSv" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th>Tipo de compra</th>
                                                        <th>Tipo de contrato</th>
                                                        <th>Tipo de Bien y/o servicio</th>
                                                        <th>Acción</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="modificarTipoBnSvs">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!--parte-->
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="servicosBienes">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapeseBnSv" aria-expanded="true" aria-controls="collapeseBnSv">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="fas fa-cart-arrow-down fa-lg" style="color: #E67E22;"></span>
                                                    </div>
                                                    <div>
                                                        4. BIENES Y SERVICIOS
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <input type="hidden" id="peReg" value="<?php echo $permisos['registrar'] ?>">
                                    <div id="collapeseBnSv" class="collapse" aria-labelledby="servicosBienes">
                                        <div class="card-body">
                                            <table id="tableBnSv" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th>Tipo de compra</th>
                                                        <th>Tipo de contrato</th>
                                                        <th>Tipo de Bien y/o servicio</th>
                                                        <th>Bien y/o servicio</th>
                                                        <th>Acción</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="modificarBnSvs">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!--parte-->
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="masOpciones">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapeseMsOp" aria-expanded="true" aria-controls="collapeseMsOp">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="fas fa-bars fa-lg" style="color: #2980B9;"></span>
                                                    </div>
                                                    <div>
                                                        5. MÁS OPCIONES
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="collapeseMsOp" class="collapse" aria-labelledby="masOpciones">
                                        <div class="card-body">
                                            <button type="button" class="btn btn-outline-success" id="btnExcelHomolgBnSv">
                                                <span class="fas fa-file-excel"></span>
                                                Homolagación de bienes y/o servicios
                                            </button>
                                            <button type="button" class="btn btn-outline-warning" id="btnExcelHomolgEscHonor">
                                                <span class="fas fa-file-excel"></span>
                                                Homolagación escala de honorarios
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
    </div>
    <?php include '../../scripts.php' ?>
</body>

</html>