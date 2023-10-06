<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
include '../conexion.php';
include '../permisos.php';
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../head.php';
// Consulta la lista de chequeras creadas en el sistema
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
?>

<body class="sb-nav-fixed <?php if ($_SESSION['navarlat'] === '1') {
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
                                    <i class="fas fa-users fa-lg" style="color:#1D80F7"></i>
                                    LISTA DE CHEQUERAS REGISTRADAS EN EL SISTEMA
                                </div>
                                <input type="hidden" id="peReg" value="<?php echo $permisos['registrar']; ?>">

                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div>
                                <div clas="row">
                                    <div class="center-block">
                                        <div class="input-group">
                                            <div class="input-group-prepend px-1">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br>
                                <table id="tableFinChequeras" class="table table-striped table-bordered table-sm table-hover shadow" style="table-layout: fixed;width: 98%;">
                                    <thead>
                                        <tr>
                                            <th style="width: 8%;">Fecha</th>
                                            <th style="width: 14%;">Banco</th>
                                            <th style="width: 35%;">Cuenta</th>
                                            <th style="width: 15%;">Num chequera</th>
                                            <th style="width: 8%;">Inicial</th>
                                            <th style="width: 8%;">En uso</th>
                                            <th style="width: 10%;">Acciones</th>

                                        </tr>
                                    </thead>
                                    <tbody id="modificartableFinChequeras">
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Banco</th>
                                            <th>Cuenta</th>
                                            <th>Num chequera</th>
                                            <th>Inicial</th>
                                            <th>En uso</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="text-center pt-4">
                            </div>
                        </div>

                    </div>
                </div>
            </main>
            <?php include '../footer.php' ?>
        </div>
        <!-- Modal formulario-->
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
    <?php include '../scripts.php' ?>

</body>

</html>