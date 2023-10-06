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
// Consulta tipo de presupuesto
$id_pto_presupuestos = $_POST['id_pto'];
// consulto id_pto_tipo de la tabla seg_pto_presupuestos cuando id_pto_presupuestos es igual a $id_pto_presupuestos
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_pto_tipo FROM seg_pto_presupuestos WHERE id_pto_presupuestos=$id_pto_presupuestos";
    $rs = $cmd->query($sql);
    $id_pto_tipo = $rs->fetch();
    $id_tipo = $id_pto_tipo['id_pto_tipo'];
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// si el tipo de presupuesto es 1 (ingresos) genera etiqueta para no mostrar ciertas opciones de un select
if ($id_tipo == 1) {
    $buscar = 'WHERE accion = 1';
} else {
    $buscar = 'WHERE accion >= 0';
}

if (isset($_POST['tipo_mod'])) $tipo_dato = $_POST['tipo_mod'];
else $tipo_dato = "";
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT nombre FROM seg_pto_presupuestos WHERE id_pto_presupuestos=$id_pto_presupuestos";
    $rs = $cmd->query($sql);
    $nomPresupuestos = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    // consulta select tipo de recursos
    $sql = "SELECT id_pto_tipomod,cod,tipo FROM seg_pto_tipo_modifica $buscar ORDER BY tipo";
    $rs = $cmd->query($sql);
    $tipoMod = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
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
                                    MODIFICACIONES A <?php echo strtoupper($nomPresupuestos['nombre'])  ?>
                                </div>
                                <input type="hidden" id="id_pto_ppto" value="<?php echo $id_pto_presupuestos ?>">
                                <input type="hidden" id="peReg" value="<?php echo $permisos['registrar']; ?>">

                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div>
                                <div clas="row">
                                    <div class="center-block">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST">

                                                    <select class="custom-select custom-select-sm" id="id_pto_doc" name="id_pto_doc" onchange="cambiaListadoModifica(value)">
                                                        <option value="">-- Seleccionar --</option>
                                                        <?php
                                                        foreach ($tipoMod as $mov) {
                                                            if ($mov['cod'] == $tipo_dato) {
                                                                echo '<option value="' . $mov['cod'] . '" selected>' . $mov['tipo'] . '</option>';
                                                            } else {
                                                                echo '<option value="' . $mov['cod'] . '" >' . $mov['tipo'] . '</option>';
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br>
                                <table id="tableModPresupuesto" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Número</th>
                                            <th>Fecha</th>
                                            <th>Documento</th>
                                            <th>Acto admin</th>
                                            <th>Valor</th>
                                            <th>Acciones</th>

                                        </tr>
                                    </thead>
                                    <tbody id="modificarModPresupuesto">
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Número</th>
                                            <th>Fecha</th>
                                            <th>Documento</th>
                                            <th>Acto admin</th>
                                            <th>Valor</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="text-center pt-4">
                                <a type="button" class="btn btn-danger" style="width: 7rem;" href="lista_presupuestos.php"> VOLVER</a>
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
    </div>
    <?php include '../scripts.php' ?>
</body>

</html>