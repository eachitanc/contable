<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../financiero/consultas.php';
$error = "Debe diligenciar este campo";
$id_pto = $_POST['id_pto'];
//Obtener fecha de cierre del módulo
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$fecha_cierre = fechaCierre($_SESSION['vigencia'], 4, $cmd);
$fecha_max = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-12-31'));
// Obtener la fecha de sesión del usuario
$fecha = fechaSesion($_SESSION['vigencia'], $_SESSION['id_user'], $cmd);
try {
    // consulta select tipo de recursos
    $sql = "SELECT id_pto_actos,acto FROM seg_pto_actos_admin ORDER BY  acto";
    $rs = $cmd->query($sql);
    $tipoActo = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$cmd = null;
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">CREAR NUEVA MODIFICACION <?php echo $_SESSION['id_user']; ?></h5>
        </div>
        <form id="formAddModificaPresupuesto">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-4">
                    <label for="fecha" class="small">FECHA MODIFICACION</label>
                    <input type="date" name="fecha" id="fecha" class="form-control form-control-sm" value="<?php echo $fecha; ?>" min="<?php echo $fecha_cierre; ?>" max="<?php echo $fecha_max; ?>">
                    <input type="hidden" name="id_pto" id="id_pto" value="<?php echo $id_pto; ?>">
                </div>
                <input type="hidden" name="datFecVigencia" value="<?php echo $_SESSION['vigencia'] ?>">
                <div class="form-group col-md-4">
                    <label for="numCdp" class="small">TIPO ACTO</label>
                    <select class="custom-select custom-select-sm" id="tipo_acto" name="tipo_acto" required>
                        <option value="">-- Seleccionar --</option>
                        <?php
                        foreach ($tipoActo as $mov) {
                            echo '<option value="' . $mov['id_pto_actos'] . '" >' . $mov['acto'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="numMod" class="small">NUMERO ACTO</label>
                    <input type="text" name="numMod" id="numMod" class="form-control form-control-sm" required>
                </div>

            </div>
            <div class="form-row px-4  ">
                <div class="form-group col-md-12">
                    <label for="Objeto" class="small">OBJETO CDP</label>
                    <textarea id="objeto" type="text" name="objeto" class="form-control form-control-sm py-0 sm" aria-label="Default select example" rows="4"></textarea>
                </div>

            </div>
            <div class="form-row px-2 ">
                <div class="text-center pb-3">
                    <button type="submit" class="btn btn-primary btn-sm" style="width: 5rem;" id="registrarModificaPto">Aceptar</button>
                    <a type="button" class="btn btn-danger btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>