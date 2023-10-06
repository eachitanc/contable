<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: <?php echo $_SESSION["urlin"] ?>/index.php');
    exit;
}
if ($_SESSION['login'] !== 'admin') {
    header('Location: <?php echo $_SESSION["urlin"] ?>/index.php');
    exit;
}
include '../../conexion.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT * FROM seg_empresas LIMIT 1";
    $rs = $cmd->query($sql);
    $obj = $rs->fetch();
    $sql = "SELECT * FROM seg_departamento ORDER BY nombre_dpto";
    $rs = $cmd->query($sql);
    $dpto = $rs->fetchAll();
    $sql = "SELECT * FROM seg_municipios ORDER BY nom_municipio";
    $rs = $cmd->query($sql);
    $municipio = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
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
                            <i class="fas fa-city fa-lg" style="color: #07cf74"></i>
                            FORMULARIO DE ACTUALIZACION EMPRESA.
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <form id="formUpEmpresa">
                                <input type="number" name="idUpEmpresa" value="<?php echo $obj['id_empresa'] ?>" hidden="true">
                                <div class="form-row">
                                    <div class="form-group col-md-8">
                                        <label for="txtNitEmpresa">NIT</label>
                                        <input type="text" class="form-control" id="txtNitEmpresa" name="txtNitEmpresa" value="<?php echo $obj['nit'] ?>" placeholder="Identificación">
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for="txtUpNomEmpresa">Nombre</label>
                                        <input type="text" class="form-control" id="txtUpNomEmpresa" name="txtUpNomEmpresa" value="<?php echo $obj['nombre'] ?>" placeholder="Empresa">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-3">
                                        <label for="mailUpEmpresa">Correo eléctronico</label>
                                        <input type="email" class="form-control" id="mailUpEmpresa" name="mailUpEmpresa" value="<?php echo $obj['correo'] ?>" placeholder="correo@empresa.com">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="txtUpTel">Teléfono</label>
                                        <input type="text" class="form-control" id="txtUpTel" name="txtUpTel" value="<?php echo $obj['telefono'] ?>" placeholder="Nombre">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="slcDptoEmp">Departamento</label>
                                        <select id="slcDptoEmp" name="slcDptoEmp" class="form-control py-0 sm" aria-label="Default select example">
                                            <?php
                                            foreach ($dpto as $d) {
                                                if ($obj['id_dpto'] === $d['id_dpto']) {
                                                    echo '<option selected value="' . $d['id_dpto'] . '">' . $d['nombre_dpto'] . '</option>';
                                                } else {
                                                    echo '<option value="' . $d['id_dpto'] . '">' . $d['nombre_dpto'] . '</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="slcMunicipioEmp">Municipio</label>
                                        <select id="slcMunicipioEmp" name="slcMunicipioEmp" class="form-control py-0 sm" aria-label="Default select example" placeholder="elegir mes">
                                            <?php
                                            foreach ($municipio as $m) {
                                                if ($obj['id_ciudad'] === $m['id_municipio']) {
                                                    echo '<option selected value="' . $m['id_municipio'] . '">' . $m['nom_municipio'] . '</option>';
                                                } else {
                                                    if ($obj['id_dpto'] === $m['id_departamento']) {
                                                        echo '<option value="' . $m['id_municipio'] . '">' . $m['nom_municipio'] . '</option>';
                                                    }
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-3">
                                        <label for="txtUpDireccion">Dirección</label>
                                        <input type="text" class="form-control" id="txtUpDireccion" name="txtUpDireccion" value="<?php echo $obj['direccion'] ?>" placeholder="Usuario">
                                    </div>
                                </div>
                                <br>
                                <button class="btn btn-primary btn-sm" id="btnUpEmpresa"> Actualizar</button>
                                <a type="button" class="btn btn-secondary  btn-sm" href="<?php echo $_SESSION['urlin'] ?>/inicio.php"> Cancelar</a>
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
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Aceptar</button>
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
                        <a type="button" class="btn btn-primary btn-sm" href="<?php echo $_SESSION['urlin'] ?>/inicio.php">Aceptar</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
    </div>
    <?php include '../../scripts.php' ?>
</body>

</html>