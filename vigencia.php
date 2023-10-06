<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
?>
<?php
include 'conexion.php';
$res = array();
$idUsuer = $_SESSION['id_user'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT
	`id_vigencia`,`anio`,`seg_empresas`.`nombre`, `nit`, `seg_empresas`.`caracter`
            FROM
                `con_vigencias`
            INNER JOIN `seg_empresas` 
                ON (`con_vigencias`.`id_empresa` = `seg_empresas`.`id_empresa`)
            INNER JOIN `seg_usuarios` 
                ON (`seg_usuarios`.`id_empresa` = `seg_empresas`.`id_empresa`)
            WHERE `id_usuario` = '$idUsuer'
            GROUP BY `id_vigencia`";
    $rs = $cmd->query($sql);
    $obj = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$_SESSION['caracter'] = $obj[0]['caracter'];
?>
<!DOCTYPE html>
<html lang="es">
<?php include 'head.php';
?>

<body class="sb-nav-fixed">
    <div id="divFondo" class="container-fluid">
        <div class="row justify-content-center align-items-center minh-100">
            <div class="center-block">
                <div class="card shadow-lg border-0 rounded-lg mt-5" style="width: 23rem;">
                    <div class="card div-gris">
                        <img src="<?php echo $_SESSION['urlin'] ?>/images/logoFinanciero.png" class="card-img-top" alt="Logo">
                    </div>
                    <div class="card-body">
                        <form id="formVigencia">
                            <label class="mb-1 lbl-mostrar px-1" for="slcEmpresa">EMPRESA</label>
                            <div class="input-group">
                                <select id="slcEmpresa" name="slcEmpresa" class="form-control py-2" aria-label="Default select example">
                                    <option selected value="0">--Elegir Empresa--</option>
                                    <?php
                                    $_SESSION['nit_emp'] = $obj[0]['nit'];
                                    echo '<option value="1" selected>' . $obj[0]['nombre'] . '</option>';
                                    ?>
                                </select>
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <span class="fas fa-landmark fa-lg" style="color: #16A085;"></span>
                                    </div>
                                </div>
                            </div>
                            <label class="mb-1 pt-4 lbl-mostrar px-1" for="slcVigencia">VIGENCIA</label>
                            <div class="input-group">
                                <select id="slcVigencia" name="slcVigencia" class="form-control py-2" aria-label="Default select example">
                                    <option selected value="0">--Elegir Vigencia--</option>
                                    <?php
                                    foreach ($obj as $o) {
                                        if ($o["anio"] >= '2023') {
                                            echo '<option value="' . $o["anio"] . '">' . $o["anio"] . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <span class="far fa-calendar-alt fa-lg" style="color: #D35400;"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="justify-content-between mt-4 mb-0">
                                <center><button class="btn btn-primary" id="btnEntrar">Entrar</button></center>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center div-gris">
                        <div class="small">Bienvenid@</div>
                    </div>
                </div>
            </div>
        </div>
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
    <div class="modal fade" id="divModalXSesion" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header" id="divModalHeaderConfir">
                    <h5 class="modal-title" id="exampleModalLongTitle">
                        <i class="fas fa-exclamation-triangle fa-lg" style="color: #E67E22;"></i>
                        ¡Atención!
                    </h5>
                </div>
                <div class="modal-body text-center">
                    <p>Por su seguridad, se ha cerrado la sesión.</p>
                </div>
                <div class="modal-footer">
                    <a class="btn btn-primary btn-sm" href="<?php echo $_SESSION['urlin'] . '/index.php' ?>">Aceptar</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <?php include 'scripts.php'; ?>
</body>

</html>