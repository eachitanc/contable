<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
include '../conexion.php';
include '../permisos.php';
// Consulta tipo de presupuesto
$id_pto_presupuestos = $_POST['id_pto'];
$vigencia = $_SESSION['vigencia'];
// consulto id_pto_tipo de la tabla seg_pto_presupuestos cuando id_pto_presupuestos es igual a $id_pto_presupuestos
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_pto_cargue`,`cod_pptal`,`nom_rubro`,`tipo_dato` 
            FROM `seg_pto_cargue` 
            WHERE `vigencia` = '$vigencia' AND `id_pto_presupuestos` = $id_pto_presupuestos";
    $rs = $cmd->query($sql);
    $rubros = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `nombre` 
            FROM `seg_pto_presupuestos` 
            WHERE `id_pto_presupuestos`= $id_pto_presupuestos";
    $rs = $cmd->query($sql);
    $nomPresupuestos = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../head.php'; ?>

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
                                    HOMOLOGACIONES A <?php echo strtoupper($nomPresupuestos['nombre'])  ?>
                                </div>
                                <input type="hidden" id="id_pto_ppto" value="<?php echo $id_pto_presupuestos ?>">
                                <input type="hidden" id="peReg" value="<?php echo $permisos['registrar']; ?>">

                            </div>
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <div class="table-responsive">
                                <table id="tableHomologaPto" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                                    <thead>
                                        <tr class="text-center">
                                            <?php
                                            if ($id_pto_presupuestos == 1) {
                                            ?>
                                                <th>Código</th>
                                                <th>Nombre</th>
                                                <th>Tipo</th>
                                                <th>Presupuesto<br>Definitivo</th>
                                                <th>Código CGR</th>
                                                <th>CPC</th>
                                                <th>Fuente</th>
                                                <th>Terceros</th>
                                                <th>Política<br>Pública</th>
                                                <th>Situación<br>Fondos</th>
                                            <?php
                                            } else if ($id_pto_presupuestos == 2) {
                                            ?>
                                                <th>Código</th>
                                                <th>Nombre</th>
                                                <th>Tipo</th>
                                                <th>Presupuesto<br>Definitivo</th>
                                                <th>Codigo CGR</th>
                                                <th>Vigencia</th>
                                                <th>Sección<br>Presupuesto</th>
                                                <th>Sector</th>
                                                <th>CPC</th>
                                                <th>Fuente</th>
                                                <th>Situación<br>Fondos</th>
                                                <th>Política<br>Pública</th>
                                                <th>Terceros</th>
                                                <th>Código SIA</th>
                                                <th>Clase de<br>pago SIA</th>

                                            <?php
                                            }
                                            ?>
                                        </tr>
                                    </thead>
                                    <tbody id="modificaHomologaPto">
                                        <?php
                                        foreach ($rubros as $rb) {
                                            $tp_cta = $rb['tipo_dato'] == 0 ? 'M' : 'D';
                                            echo "<tr>";
                                            echo "<td>" . $rb['cod_pptal'] . "</td>";
                                            echo "<td>" . $rb['nom_rubro'] . "</td>";
                                            echo "<td class='text-center'>" . $tp_cta . "</td>";
                                            if ($id_pto_presupuestos == 1) {
                                                if ($tp_cta == 'D') {
                                                    echo "<td class='p-0'><input type='text' class='form-control form-control-sm py-0 px-1' name='proDef[" . $rb['id_pto_cargue'] . "]'></td>";
                                                    echo "<td class='p-0'><input type='text' class='form-control form-control-sm py-0 px-1' name='codCgr[" . $rb['id_pto_cargue'] . "]'></td>";
                                                    echo "<td class='p-0'><input type='text' class='form-control form-control-sm py-0 px-1' name='cpc[" . $rb['id_pto_cargue'] . "]'></td>";
                                                    echo "<td class='p-0'><input type='text' class='form-control form-control-sm py-0 px-1' name='fuente[" . $rb['id_pto_cargue'] . "]'></td>";
                                                    echo "<td class='p-0'><input type='text' class='form-control form-control-sm py-0 px-1' name='tercero[" . $rb['id_pto_cargue'] . "]'></td>";
                                                    echo "<td class='p-0'><input type='text' class='form-control form-control-sm py-0 px-1' name='polPub[" . $rb['id_pto_cargue'] . "]'></td>";
                                                    echo "<td class='p-0'><input type='text' class='form-control form-control-sm py-0 px-1' name='sitFondo[" . $rb['id_pto_cargue'] . "]'></td>";
                                                } else {
                                                    echo "<td colspan=7></td>";
                                                }
                                            } else if ($id_pto_presupuestos == 2) {
                                                echo "<td></td>";
                                                echo "<td></td>";
                                                echo "<td></td>";
                                                echo "<td></td>";
                                                echo "<td></td>";
                                                echo "<td></td>";
                                                echo "<td></td>";
                                                echo "<td></td>";
                                                echo "<td></td>";
                                                echo "<td></td>";
                                                echo "<td></td>";
                                                echo "<td></td>";
                                            }
                                            echo "</tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center pt-4">
                                <a type="button" class="btn btn-secondary" style="width: 7rem;" href="lista_presupuestos.php"> Regresar</a>
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