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
$key = array_search('1', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
$contador = 1;
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
                                    <i class="fas fa-cogs fa-lg" style="color:#1D80F7"></i>
                                    CONFIGURACIONES NÓMINA.
                                </div>
                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div id="accordion">
                                <?php
                                if (false) {

                                ?>
                                    <div class="card">
                                        <div class="card-header card-header-detalles py-0 headings" id="headingTwo">
                                            <h5 class="mb-0">
                                                <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#configtwo" aria-expanded="true" aria-controls="collapseOne">
                                                    <div class="form-row">
                                                        <div class="div-icono">
                                                            <span class="far fa-calendar-plus fa-lg" style="color: #E74C3C;"></span>
                                                        </div>
                                                        <div>
                                                            <?php echo $contador;
                                                            $contador++ ?>. VIGENCIA.
                                                        </div>
                                                    </div>
                                                </a>
                                            </h5>
                                        </div>
                                        <div id="configtwo" class="collapse" aria-labelledby="headingTwo">
                                            <div class="card-body">
                                                <table id="tableVigencia" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                    <thead class="text-center">
                                                        <tr>
                                                            <th>VIGENCIA</th>
                                                            <th>ACCIONES</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="modificaVigencia">
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                }
                                ?>
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="headingOne">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#configone" aria-expanded="true" aria-controls="collapseOne">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="far fa-list-alt fa-lg" style="color: #3498DB;"></span>
                                                    </div>
                                                    <div>
                                                        <?php echo $contador;
                                                        $contador++ ?>. PARAMETROS DE LIQUIDACIÓN.
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="configone" class="collapse" aria-labelledby="headingOne">
                                        <input type="hidden" id="peReg" value="<?php echo $permisos['registrar'] ?>">
                                        <div class="card-body">
                                            <table id="tableParamLiq" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                <thead class="text-center">
                                                    <tr>
                                                        <th>ID_CONCEPTO</th>
                                                        <th>CONCEPTO</th>
                                                        <th>VALOR</th>
                                                        <th>ACCIONES</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="modificaParamLiq">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="headingtres">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#configtres" aria-expanded="true" aria-controls="collapsetres">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="fas fa-sort-amount-up fa-lg" style="color: #F9E79F;"></span>
                                                    </div>
                                                    <div>
                                                        <?php echo $contador;
                                                        $contador++ ?>. INCREMENTO SALARIAL.
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="configtres" class="collapse" aria-labelledby="headingtres">
                                        <div class="card-body">
                                            <table id="tableIncremento" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                <thead class="text-center">
                                                    <tr>
                                                        <th>PORCENTAJE</th>
                                                        <th>FECHA INICIO</th>
                                                        <th>ESTADO</th>
                                                        <th>ACCIONES</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="modificaIncremento">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header card-header-detalles py-0 headings" id="headingcuatro">
                                        <h5 class="mb-0">
                                            <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#configcuatro" aria-expanded="true" aria-controls="collapsecuatro">
                                                <div class="form-row">
                                                    <div class="div-icono">
                                                        <span class="fas fa-users fa-lg" style="color: #9B59B6;"></span>
                                                    </div>
                                                    <div>
                                                        <?php echo $contador;
                                                        $contador++ ?>. TERCEROS.
                                                    </div>
                                                </div>
                                            </a>
                                        </h5>
                                    </div>
                                    <div id="configcuatro" class="collapse" aria-labelledby="headingcuatro">
                                        <div class="card-body">
                                            <table id="tableTerceroNomina" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                                <thead class="text-center">
                                                    <tr>
                                                        <th>CÓD.</th>
                                                        <th>CATEGORIA</th>
                                                        <th>NOMBRE</th>
                                                        <th>NIT</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="modificaTerceroNomina">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
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