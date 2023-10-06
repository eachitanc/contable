<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: <?php echo $_SESSION["urlin"] ?>/index.php');
    exit;
}
include '../conexion.php';
$usuario = $_SESSION['id_user'];
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$sql = "SELECT * FROM seg_usuarios  WHERE id_usuario='$usuario'";
$rs = $cmd->query($sql);
$obj = $rs->fetch();
$cmd = null;
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
                            <i class="fas fa-user-edit fa-lg" style="color: #07CF74;"></i>
                            EDITAR PERFIL DE USUARIO.
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <form id="formUpUser">
                                <input type="number" id="numIdUsuario" name="numIdUsuario" value="<?php echo $obj['id_usuario'] ?>" hidden>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="txtNombre1">Nombres </label>
                                        <div class="form-row">
                                            <div class="col">
                                                <input type="text" class="form-control" id="txtNombre1" name="txtNombre1" value="<?php echo $obj['nombre1'] ?>" placeholder="Primer nombre">
                                            </div>
                                            <div class="col">
                                                <input type="text" class="form-control" id="txtNombre2" name="txtNombre2" value="<?php echo $obj['nombre2'] ?>" placeholder="Segundo nombre">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="txtApellido1">Apellidos </label>
                                        <div class="form-row">
                                            <div class="col">
                                                <input type="text" class="form-control" id="txtApellido1" name="txtApellido1" value="<?php echo $obj['apellido1'] ?>" placeholder="Primer apellido">
                                            </div>
                                            <div class="col">
                                                <input type="text" class="form-control" id="txtApellido2" name="txtApellido2" value="<?php echo $obj['apellido2'] ?>" placeholder="Segundo apellido">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-3">
                                        <label for="txtUsuario">Usuario</label>
                                        <input type="text" class="form-control" id="txtUsuario" name="txtUsuario" value="<?php echo $obj['login'] ?>" placeholder="Usuario">
                                    </div>
                                    <div class="form-group col-md-9">
                                        <label for="emailUsuario">Correo</label>
                                        <input type="email" class="form-control" id="emailUsuario" name="emailUsuario" value="<?php echo $obj['correo'] ?>" placeholder="Correo electrónico">
                                    </div>
                                </div>
                                <button class="btn btn-success btn-sm" id="btnUpUserPerfil"> Actualizar</button>
                                <a type="button" class="btn btn-secondary  btn-sm" href="<?php echo $_SESSION['urlin'] ?>/inicio.php"> Cancelar</a>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../footer.php' ?>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="divModalDone" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
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
                    <a type="button" class="btn btn-success btn-sm" href="../inicio.php">Aceptar</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
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
    <?php include '../scripts.php' ?>
</body>

</html>