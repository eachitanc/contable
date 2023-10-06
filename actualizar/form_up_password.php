<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
include '../conexion.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_permiso, seg_usuarios.id_usuario, nombre1, nombre2, apellido1, apellido2, login, listar, registrar, editar, borrar
            FROM
                seg_permisos_usuario
            INNER JOIN seg_usuarios 
                ON (seg_permisos_usuario.id_usuario = seg_usuarios.id_usuario)
            WHERE estado = '1'";
    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow mb-3">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h6 style="color: white;"><i class="fas fa-lock fa-lg" style="color: #FCF3CF"></i>&nbsp;MODIFICAR CONTRASEÑA</h5>
        </div>
        <div class="pt-3 px-3">
            <div class="form-row">
                <div class="form-group col-md-12">
                    <label for="passAnt" class="small">Contraseña actual</label>
                    <input type="password" class="form-control form-control-sm" id="passAnt" name="passAnt" placeholder="Contraseña" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-12">
                    <label for="passAnt" class="small">Nueva contraseña</label>
                    <input type="password" class="form-control form-control-sm" id="passNew" name="passNew" placeholder="Contraseña nueva" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-12">
                    <label for="passNewConf" class="small">Confirmar contraseña</label>
                    <input type="password" class="form-control form-control-sm" id="passNewConf" name="passNewConf" placeholder="Repetir contraseña" required>
                </div>
            </div>
        </div>
    </div>
    <div class="text-right">
        <button id="btnChangePass" type="button" class="btn btn-primary btn-sm">Modificar</button>
        <a class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</a>
    </div>
</div>