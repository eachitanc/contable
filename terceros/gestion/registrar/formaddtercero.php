<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
$key = array_search('2', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT * FROM seg_tipo_tercero";
    $rs = $cmd->query($sql);
    $tipoTercero = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT * FROM seg_tipos_documento";
    $rs = $cmd->query($sql);
    $tipodoc = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT * FROM seg_pais";
    $rs = $cmd->query($sql);
    $pais = $rs->fetchAll();
    $sql = "SELECT * FROM seg_departamento ORDER BY nombre_dpto";
    $rs = $cmd->query($sql);
    $dpto = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$error = "Debe diligenciar este campo";
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../../../head.php' ?>

<body class="sb-nav-fixed <?php
                            if ($_SESSION['navarlat'] == '1') {
                                echo 'sb-sidenav-toggled';
                            }
                            ?>">
    <?php include '../../../navsuperior.php' ?>
    <div id="layoutSidenav">
        <?php include '../../../navlateral.php' ?>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid p-2">
                    <div class="card mb-4">
                        <div class="card-header" id="divTituloPag">
                            <i class="fas fa-user-plus fa-lg" style="color: #07CF74;"></i>
                            REGISTRAR TERCERO
                        </div>
                        <div class="card-body" id="divCuerpoPag">
                            <nav>
                                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                    <a class="nav-item nav-link active small" id="nav_regTercro-tab" data-toggle="tab" href="#nav_regTercro" role="tab" aria-controls="nav_regTercro" aria-selected="true">Nuevo Tercero</a>
                                    <a class="nav-item nav-link small" id="nav-agregTipoTercer-tab" data-toggle="tab" href="#nav-agregTipoTercer" role="tab" aria-controls="nav-agregTipoTercer" aria-selected="false">Agregar Tipo de Tercero</a>
                                </div>
                            </nav>
                            <div class="tab-content" id="nav-tabContent">
                                <div class="tab-pane fade show active" id="nav_regTercro" role="tabpanel" aria-labelledby="nav_regTercro-tab">
                                    <div class="card-header p-2" id="divDivisor">
                                        <div class="text-center">DATOS DE TERCERO</div>
                                    </div>
                                    <div class="shadow">
                                        <form id="formNuevoTercero">
                                            <div class="form-row px-4 pt-2">
                                                <div class="form-group col-md-2">
                                                    <label for="slcTipoTercero" class="small">Tipo de tercero</label>
                                                    <select id="slcTipoTercero" name="slcTipoTercero" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                                        <option selected value="0">--Selecionar tipo--</option>
                                                        <?php
                                                        foreach ($tipoTercero as $tT) {
                                                            echo '<option value="' . $tT['id_tipo'] . '">' . $tT['descripcion'] . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                    <div id="eslcTipoTercero" class="invalid-tooltip">
                                                        <?php echo $error ?>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-2">
                                                    <label for="datFecInicio" class="small">Fecha de inicio</label>
                                                    <input type="date" class="form-control form-control-sm" id="datFecInicio" name="datFecInicio">
                                                    <div id="edatFecInicio" class="invalid-tooltip">
                                                        <?php echo $error ?>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-2">
                                                    <label for="slcGenero" class="small">Género</label>
                                                    <select id="slcGenero" name="slcGenero" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                                        <option value="0">--Selecionar--</option>
                                                        <option value="M">MASCULINO</option>
                                                        <option value="F">FEMENINO</option>
                                                        <option value="NA">NO APLICA</option>
                                                    </select>
                                                    <div id="eslcGenero" class="invalid-tooltip">
                                                        <?php echo $error ?>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-2">
                                                    <label for="datFecNacimiento" class="small">Fecha de Nacimiento</label>
                                                    <input type="date" class="form-control form-control-sm" id="datFecNacimiento" name="datFecNacimiento">
                                                    <div id="edatFecNacimiento" class="invalid-tooltip">
                                                        <?php echo $error ?>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-2">
                                                    <label for="slcTipoDocEmp" class="small">Tipo de documento</label>
                                                    <select id="slcTipoDocEmp" name="slcTipoDocEmp" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                                        <option selected value="0">--Selecionar tipo--</option>
                                                        <?php
                                                        foreach ($tipodoc as $td) {
                                                            echo '<option value="' . $td['id_tipodoc'] . '">' . mb_strtoupper($td['descripcion']) . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                    <div id="eslcTipoDocEmp" class="invalid-tooltip">
                                                        <?php echo $error ?>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-2">
                                                    <label for="txtCCempleado" class="small">Identificación</label>
                                                    <input type="number" class="form-control form-control-sm" id="txtCCempleado" name="txtCCempleado" min="1" placeholder="C.C., NIT, etc.">
                                                    <div id="etxtCCempleado" class="invalid-tooltip">
                                                        <?php echo $error ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-row px-4">
                                                <div class="form-group col-md-2">
                                                    <label for="txtNomb1Emp" class="small">Primer nombre</label>
                                                    <input type="text" class="form-control form-control-sm" id="txtNomb1Emp" name="txtNomb1Emp" placeholder="Nombre">
                                                </div>
                                                <div class="form-group col-md-2">
                                                    <label for="txtNomb2Emp" class="small">Segundo nombre</label>
                                                    <input type="text" class="form-control form-control-sm" id="txtNomb2Emp" name="txtNomb2Emp" placeholder="Nombre">
                                                </div>
                                                <div class="form-group col-md-2">
                                                    <label for="txtApe1Emp" class="small">Primer apellido</label>
                                                    <input type="text" class="form-control form-control-sm" id="txtApe1Emp" name="txtApe1Emp" placeholder="Apellido">
                                                </div>
                                                <div class="form-group col-md-2">
                                                    <label for="txtApe2Emp" class="small">Segundo apellido</label>
                                                    <input type="text" class="form-control form-control-sm" id="txtApe2Emp" name="txtApe2Emp" placeholder="Apellido">
                                                </div>
                                                <div class="form-group col-md-4">
                                                    <label for="txtRazonSocial" class="small">Razón Social</label>
                                                    <input type="text" class="form-control form-control-sm" id="txtRazonSocial" name="txtRazonSocial" placeholder="Nombre empresa">
                                                </div>
                                            </div>
                                            <div class="form-row px-4">
                                                <div class="form-group col-md-3">
                                                    <label for="slcPaisEmp" class="small">País</label>
                                                    <select id="slcPaisEmp" name="slcPaisEmp" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                                        <option selected value="0">--Selecionar--</option>
                                                        <?php
                                                        foreach ($pais as $p) {
                                                            echo '<option value="' . $p['id_pais'] . '">' . mb_strtoupper($p['nombre_pais']) . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                    <div id="eslcPaisEmp" class="invalid-tooltip">
                                                        <?php echo $error ?>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label for="slcDptoEmp" class="small">Departamento</label>
                                                    <select id="slcDptoEmp" name="slcDptoEmp" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                                        <option selected value="0">--Selecionar--</option>
                                                        <?php
                                                        foreach ($dpto as $d) {
                                                            echo '<option value="' . $d['id_dpto'] . '">' . $d['nombre_dpto'] . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                    <div id="eslcDptoEmp" class="invalid-tooltip">
                                                        <?php echo $error ?>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label for="slcMunicipioEmp" class="small">Municipio</label>
                                                    <select id="slcMunicipioEmp" name="slcMunicipioEmp" class="form-control form-control-sm py-0 sm" aria-label="Default select example" placeholder="elegir mes">
                                                        <option selected value="0">Debe elegir departamento</option>
                                                    </select>
                                                    <div id="eslcMunicipioEmp" class="invalid-tooltip">
                                                        <?php echo $error ?>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label for="txtDireccion" class="small">Dirección</label>
                                                    <input type="text" class="form-control form-control-sm" id="txtDireccion" name="txtDireccion" placeholder="Residencial">
                                                    <div id="etxtDireccion" class="invalid-tooltip">
                                                        <?php echo $error ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-row px-4">
                                                <div class="form-group col-md-3">
                                                    <label for="mailEmp" class="small">Correo</label>
                                                    <input type="email" class="form-control form-control-sm" id="mailEmp" name="mailEmp" placeholder="Correo electrónico">
                                                    <div id="emailEmp" class="invalid-tooltip">
                                                        <?php echo $error ?>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-2">
                                                    <label for="txtTelEmp" class="small">Contacto</label>
                                                    <input type="text" class="form-control form-control-sm" id="txtTelEmp" name="txtTelEmp" placeholder="Teléfono/celular">
                                                    <div id="etxtTelEmp" class="invalid-tooltip">
                                                        <?php echo $error ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-center pb-3">
                                                <button class="btn btn-primary btn-sm" id="btnNewTercero">Registrar</button>
                                                <a type="button" class="btn btn-secondary  btn-sm" href="../listterceros.php"> Cancelar</a>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="nav-agregTipoTercer" role="tabpanel" aria-labelledby="nav-agregTipoTercer-tab">
                                    <form id="formAddTipoTercero">
                                        <div class="form-row px-4 pt-2">
                                            <div class="form-group col-md-9">
                                                <label for="txtBuscarTercero" class="small">Buscar Tercero</label>
                                                <input type="text" class="form-control form-control-sm" id="txtBuscarTercero">
                                                <input type="hidden" id="txtIdTercero" name="txtIdTercero" value="0">
                                                <div id="etxtBuscarTercero" class="invalid-tooltip">
                                                    <?php echo $error ?>
                                                </div>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label for="slcTipoTerce" class="small">Tipo de tercero</label>
                                                <select id="slcTipoTerce" name="slcTipoTerce" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                                                    <option selected value="0">--Selecionar tipo--</option>
                                                    <?php
                                                    foreach ($tipoTercero as $tT) {
                                                        echo '<option value="' . $tT['id_tipo'] . '">' . $tT['descripcion'] . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                                <div id="eslcTipoTerce" class="invalid-tooltip">
                                                    <?php echo $error ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-center pb-2">
                                            <button class="btn btn-primary btn-sm" id="btnNewTipoTercero">Agregar</button>
                                            <a type="button" class="btn btn-secondary  btn-sm" href="../listterceros.php"> Cancelar</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <?php include '../../../footer.php' ?>
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
                        <a type="button" class="btn btn-primary btn-sm" href="javascript:location.reload()">Aceptar</a>
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
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
    </div>
    <?php include '../../../scripts.php' ?>
</body>

</html>