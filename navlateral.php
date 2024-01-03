<?php
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
include 'conexion.php';
include 'permisos.php';
$rol = $_SESSION['rol'];
?>
<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu ">
            <div class="nav">
                <div class="sb-sidenav-menu-heading">MÓDULOS</div>
                <?php
                $key = array_search('1', array_column($perm_modulos, 'id_modulo'));
                if (false !== $key) {
                ?>
                    <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                        <div class="form-row">
                            <div class="div-icono">
                                <span class="fas fa-calculator fa-lg" style="color: #2ECC71CC;"></span>
                            </div>
                            <div>
                                Nómina
                            </div>
                        </div>
                        <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                    </a>
                    <div class="collapse" id="collapseLayouts" aria-labelledby="headingOne" data-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav shadow-nav-lat">
                            <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/empleados/listempleados.php">
                                <div class="form-row">
                                    <div class="div-icono">
                                        <i class="fas fa-users fa-sm" style="color: #85C1E9;"></i>
                                    </div>
                                    <div>
                                        Empleados
                                    </div>
                                </div>
                            </a>
                            <?php if ($_SESSION['caracter'] == '1') { ?>
                                <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/empleados/contratacion/list_contratos.php">
                                    <div class="form-row">
                                        <div class="div-icono">
                                            <i class="fas fa-file-signature fa-sm" style="color: #2ECC71;"></i>
                                        </div>
                                        <div>
                                            Contratación
                                        </div>
                                    </div>
                                </a>
                            <?php } ?>
                            <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#pagesCollapseAuth2" aria-expanded="false" aria-controls="pagesCollapseAuth2">
                                <div class="form-row">
                                    <div class="div-icono">
                                        <i class="fas fa-donate fa-sm" style="color: #FFC300CC;"></i>
                                    </div>
                                    <div>
                                        Devengados
                                    </div>
                                </div>
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                            </a>
                            <div class="collapse" id="pagesCollapseAuth2" aria-labelledby="headingOne">
                                <nav class="sb-sidenav-menu-nested nav shadow-nav-lat">
                                    <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/extras/horas/listhoraextra.php">
                                        <div class="form-row">
                                            <div class="div-icono">
                                                <i class="fas fa-history fa-xs" style="color: #F9E79F;"></i>
                                            </div>
                                            <div>
                                                Horas extra
                                            </div>
                                        </div>
                                    </a>
                                    <?php if ($_SESSION['caracter'] == '1') { ?>
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/extras/viaticos/listviaticos.php">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="fas fa-suitcase-rolling fa-xs" style="color: #73C6B6;"></i>
                                                </div>
                                                <div>
                                                    Viáticos
                                                </div>
                                            </div>
                                        </a>
                                    <?php }
                                    if ($_SESSION['caracter'] == '2') { ?>
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/extras/viaticos/lista_resoluciones_viaticos.php" title="Generar resoluciones de viáticos">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="fas fa-file-contract fa-xs" style="color: #27AE60;"></i>
                                                </div>
                                                <div>
                                                    Res. Viáticos
                                                </div>
                                            </div>
                                        </a>
                                    <?php } ?>
                                </nav>
                            </div>
                            <?php if (false) { ?>
                                <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#pagesCollapseAuth3" aria-expanded="false" aria-controls="pagesCollapseAuth3">
                                    <div class="form-row">
                                        <div class="div-icono">
                                            <i class="fas fa-medkit fa-sm" style="color: #A569BD;"></i>
                                        </div>
                                        <div>
                                            Seg. social</div>
                                    </div>
                                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                                </a>
                                <div class="collapse" id="pagesCollapseAuth3" aria-labelledby="headingOne">
                                    <nav class="sb-sidenav-menu-nested nav shadow-nav-lat">
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/seguridad_social/eps/listeps.php">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="fas fa-hospital fa-xs" style="color: #EC7063;"></i>
                                                </div>
                                                <div>
                                                    EPS
                                                </div>
                                            </div>
                                        </a>
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/seguridad_social/arl/listarl.php">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="far fa-hospital fa-xs" style="color: #F8C471;"></i>
                                                </div>
                                                <div>
                                                    ARL
                                                </div>
                                            </div>
                                        </a>
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/seguridad_social/afp/listafp.php">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="fas fa-gopuram fa-xs" style="color: #E59866;"></i>
                                                </div>
                                                <div>
                                                    AFP
                                                </div>
                                            </div>
                                        </a>
                                    </nav>
                                </div>
                            <?php } ?>
                            <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#liqnomina" aria-expanded="false" aria-controls="liqnomina">
                                <div class="form-row">
                                    <div class="div-icono">
                                        <i class="fas fa-money-check-alt fa-sm" style="color: #fc6404;"></i>
                                    </div>
                                    <div>
                                        Liquidar
                                    </div>
                                </div>
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                            </a>
                            <div class="collapse" id="liqnomina" aria-labelledby="headingOne">
                                <nav class="sb-sidenav-menu-nested nav shadow-nav-lat">
                                    <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/liquidar_nomina/listempliquidar.php">
                                        <div class="form-row">
                                            <div class="div-icono">
                                                <i class="fas fa-file-invoice-dollar fa-xs" style="color: #2ECC71;"></i>
                                            </div>
                                            <div>
                                                Mensual
                                            </div>
                                        </div>
                                    </a>
                                    <?php if ($_SESSION['caracter'] == '2') { ?>
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/liquidar_nomina/retroactivo/lista_retroactivos.php">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="fas fa-expand fa-xs" style="color: #5D6D7E;"></i>
                                                </div>
                                                <div>
                                                    Retroactivo
                                                </div>
                                            </div>
                                        </a>
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/liquidar_nomina/listempliquidar_vacaciones.php">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="fas fa-sun fa-xs" style="color: #F4D03F;"></i>
                                                </div>
                                                <div>
                                                    Vacaciones
                                                </div>
                                            </div>
                                        </a>
                                    <?php }
                                    if (false) {
                                    ?>
                                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/liquidar_nomina/liqxempleado.php">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="fas fa-funnel-dollar fa-xs" style="color: #2874A6;"></i>
                                                </div>
                                                <div>
                                                    Por Empleado
                                                </div>
                                            </div>
                                        </a>
                                    <?php }
                                    if ($_SESSION['caracter'] == '2') { ?>
                                        <a class="nav-link collapsed sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/liquidar_nomina/liquidar_pres_soc.php">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="fas fa-user-friends fa-sm" style="color: #2ECC71;"></i>
                                                </div>
                                                <div>
                                                    Prestaciones
                                                </div>
                                            </div>
                                        </a>
                                        <a class="nav-link collapsed sombra btnListLiqPrima" href="javascript:void(0)" value="1">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="fas fa-money-check-alt fa-sm" style="color: #0000FF;"></i>
                                                </div>
                                                <div>
                                                    Prima Servicios
                                                </div>
                                            </div>
                                        </a>
                                        <a class="nav-link collapsed sombra btnListLiqPrima" href="javascript:void(0)" value="2">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="fas fa-holly-berry fa-sm" style="color: #FF0000;"></i>
                                                </div>
                                                <div>
                                                    Prima Navidad
                                                </div>
                                            </div>
                                        </a>
                                        <a class="nav-link collapsed sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/liquidar_nomina/listempliquidar_cesantias.php">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="fas fa-piggy-bank fa-sm" style="color: #BB8FCE;"></i>
                                                </div>
                                                <div>
                                                    Cesantías
                                                </div>
                                            </div>
                                        </a>
                                    <?php }
                                    if ($_SESSION['caracter'] == '1') { ?>
                                        <a class="nav-link collapsed sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/liquidar_nomina/liquidar_contrato.php">
                                            <div class="form-row">
                                                <div class="div-icono">
                                                    <i class="fas fa-file-signature fa-sm" style="color: #EC7063;"></i>
                                                </div>
                                                <div>
                                                    Contrato
                                                </div>
                                            </div>
                                        </a>
                                    <?php } ?>
                                    <a class="nav-link collapsed sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/liquidar_nomina/mostrar/liqxmes.php">
                                        <div class="form-row">
                                            <div class="div-icono">
                                                <i class="far fa-calendar-check fa-sm" style="color: #2471A3;"></i>
                                            </div>
                                            <div>
                                                Liquidado
                                            </div>
                                        </div>
                                    </a>
                                </nav>
                            </div>
                            <?php if ($_SESSION['caracter'] == '2') { ?>
                                <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/soportes/nom_electronica.php">
                                    <div class="form-row">
                                        <div class="div-icono">
                                            <i class="fas fa-ticket-alt fa-sm" style="color: #FF1B1B;"></i>
                                        </div>
                                        <div>
                                            Soportes NE
                                        </div>
                                    </div>
                                </a>
                            <?php }
                            if ($_SESSION['caracter'] == '2') { ?>
                                <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/certificaciones/certificaciones.php">
                                    <div class="form-row">
                                        <div class="div-icono">
                                            <i class="fas fa-certificate fa-sm" style="color: #2E86C1;"></i>
                                        </div>
                                        <div>
                                            Certificaciones
                                        </div>
                                    </div>
                                </a>
                            <?php } ?>
                            <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/configuracion.php">
                                <div class="form-row">
                                    <div class="div-icono">
                                        <i class="fas fa-cogs" style="color: #839192;"></i>
                                    </div>
                                    <div>
                                        Configuración
                                    </div>
                                </div>
                            </a>
                            <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/nomina/informes/listado.php">
                                <div class="form-row">
                                    <div class="div-icono">
                                        <i class="fas fa-info-circle fa-sm" style="color: #FF5733;"></i>
                                    </div>
                                    <div>
                                        Informes
                                    </div>
                                </div>
                            </a>
                        </nav>
                    </div>
                <?php
                }
                $key = array_search('2', array_column($perm_modulos, 'id_modulo'));
                if (false !== $key) {
                ?>
                    <!--MODULO-->
                    <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#collapseTerceros" aria-expanded="false" aria-controls="collapseTerceros">
                        <div class="form-row">
                            <div class="div-icono">
                                <span class="fas fa-people-arrows fa-lg" style="color: #2874A6"></span>
                            </div>
                            <div>
                                Terceros
                            </div>
                        </div>
                        <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                    </a>
                    <div class="collapse" id="collapseTerceros" aria-labelledby="headingTerceros" data-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav accordion shadow-nav-lat" id="sidenavAccordionPages">
                            <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/terceros/gestion/listterceros.php">
                                <div class="form-row">
                                    <div class="div-icono">
                                        <i class="fas fa-users fa-sm" style="color: #85C1E9;"></i>
                                    </div>
                                    <div>
                                        Gestión
                                    </div>
                                </div>
                            </a>
                        </nav>
                    </div>
                <?php
                }
                $key = array_search('3', array_column($perm_modulos, 'id_modulo'));
                if (false !== $key) {
                ?>
                    <!--MODULO-->
                    <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#collapseContratacion" aria-expanded="false" aria-controls="collapseContratacion">
                        <div class="form-row">
                            <div class="div-icono">
                                <span class="fas fa-file-signature fa-lg" style="color: #A569BD"></span>
                            </div>
                            <div>
                                Contratación
                            </div>
                        </div>
                        <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                    </a>
                    <div class="collapse" id="collapseContratacion" aria-labelledby="headingContratacion" data-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav accordion shadow-nav-lat" id="sidenavAccordionPages">
                            <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/contratacion/gestion/lista_tipos.php">
                                <div class="form-row">
                                    <div class="div-icono">
                                        <i class="fas fa-cogs fa-sm" style="color: #85C1E9;"></i>
                                    </div>
                                    <div>
                                        Adquisiciones
                                    </div>
                                </div>
                            </a>
                            <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/contratacion/adquisiciones/lista_adquisiciones.php">
                                <div class="form-row">
                                    <div class="div-icono">
                                        <i class="fas fa-store fa-sm" style="color: #FFC300CC;"></i>
                                    </div>
                                    <div>
                                        Compras
                                    </div>
                                </div>
                            </a>
                            <?php if ($_SESSION['caracter'] == '1') { ?>
                                <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/contratacion/no_obligados/listar_facturas.php">
                                    <div class="form-row">
                                        <div class="div-icono">
                                            <i class="fas fa-ticket-alt fa-sm" style="color: #F8C471;"></i>
                                        </div>
                                        <div>
                                            No obligados
                                        </div>
                                    </div>
                                </a>
                            <?php } ?>
                        </nav>
                    </div>
                    <!--MODULO-->
                <?php
                }
                $key = array_search('4', array_column($perm_modulos, 'id_modulo'));
                if (false !== $key) {
                ?>
                    <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#collapsePages2" aria-expanded="false" aria-controls="collapsePages2">
                        <div class="form-row">
                            <div class="div-icono">
                                <i class="fas fa-chart-pie fa-lg" style="color: #FF5733"></i>
                            </div>
                            <div>
                                Presupuesto
                            </div>
                        </div>
                        <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                    </a>
                    <div class="collapse" id="collapsePages2" aria-labelledby="headingTwo" data-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav accordion shadow-nav-lat" id="sidenavAccordionPages">
                            <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/presupuesto/lista_presupuestos.php">
                                <div class="form-row">
                                    <div class="div-icono">
                                        <i class="fas fa-file-invoice-dollar fa-sm" style="color: #85C1E9;"></i>
                                    </div>
                                    <div>
                                        Gestión
                                    </div>
                                </div>
                            </a>
                            <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/presupuesto/lista_informes_presupuesto.php">
                                <div class="form-row">
                                    <div class="div-icono">
                                        <i class="far fa-file fa-sm" style="color: #FF5733;"></i>
                                    </div>
                                    <div>
                                        Informes
                                    </div>
                                </div>
                            </a>
                        </nav>
                    </div>
                <?php
                }
                $key = array_search('5', array_column($perm_modulos, 'id_modulo'));
                if (false !== $key) {
                ?>
                    <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#collapseConta" aria-expanded="false" aria-controls="collapsePages2">
                        <div class="sb-nav-link-icon"><i class="fas fa-file-invoice-dollar fa-lg" style="color: #45B39D"></i></div>
                        Contabilidad
                        <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                    </a>
                    <div class="collapse" id="collapseConta" aria-labelledby="headingTwo" data-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav accordion shadow-nav-lat" id="sidenavAccordionPages">
                            <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/contabilidad/lista_documentos_mov.php">
                                <div class="div-icono">
                                    <i class="fas fa-sort-amount-down-alt fa-sm" style="color: #85C1E9;"></i>
                                </div>
                                <div>
                                    Movimientos
                                </div>
                            </a>
                            <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/contabilidad/lista_documentos_mov.php">
                                <div class="div-icono">
                                    <i class="fas fa-credit-card fa-sm" style="color: #FFC300CC;"></i>
                                </div>
                                <div>
                                    Cuentas por pagar
                                </div>
                            </a>
                            <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/contabilidad/lista_informes_contabilidad.php">
                                <div class="div-icono">
                                    <i class="far fa-file fa-sm" style="color: #FF5733;"></i>
                                </div>
                                <div>
                                    Informes
                                </div>
                            </a>
                        </nav>
                    </div>
                <?php
                }
                $key = array_search('6', array_column($perm_modulos, 'id_modulo'));
                if (false !== $key) {
                ?>
                    <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#collapseTeso" aria-expanded="false" aria-controls="collapsePages2">
                        <div class="form-row">
                            <div class="div-icono">
                                <span class="fas fa-coins fa-lg" style="color: #3498DB"></span>
                            </div>
                            <div>
                                Tesorería
                            </div>
                        </div>
                        <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                    </a>
                    <div class="collapse" id="collapseTeso" aria-labelledby="headingTwo" data-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav accordion shadow-nav-lat" id="sidenavAccordionPages">
                            <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/tesoreria/lista_documentos_com.php?var=1">
                                <div class="div-icono">
                                    <i class="fas fa-sort-amount-down-alt fa-sm" style="color: #85C1E9;"></i>
                                </div>
                                <div>
                                    Pagos
                                </div>
                            </a>
                            <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/tesoreria/lista_documentos_com.php?var=2">
                                <div class="div-icono">
                                    <i class="fas fa-sort-amount-down-alt fa-sm" style="color: #85C1E9;"></i>
                                </div>
                                <div>
                                    Recaudos
                                </div>
                            </a>
                            <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/tesoreria/lista_documentos_com.php?var=3">
                                <div class="div-icono">
                                    <i class="fas fa-sort-amount-down-alt fa-sm" style="color: #85C1E9;"></i>
                                </div>
                                <div>
                                    Traslados
                                </div>
                            </a>
                            <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/tesoreria/lista_informes_tesoreria.php">
                                <div class="div-icono">
                                    <i class="far fa-file fa-sm" style="color: #FF5733;"></i>
                                </div>
                                <div>
                                    Informes
                                </div>
                            </a>
                            <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#pagesCollapseError" aria-expanded="false" aria-controls="pagesCollapseError">
                                Mas Opciones
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                            </a>
                            <div class="collapse" id="pagesCollapseError" aria-labelledby="headingOne" data-parent="#sidenavAccordionPages">
                                <nav class="sb-sidenav-menu-nested nav shadow-nav-lat">
                                    <a class="nav-link sombra" href="#">Conciliaciones</a>
                                    <a class="nav-link sombra" href="#">Certificados</a>
                                </nav>
                            </div>
                        </nav>
                    </div>
                <?php
                }
                ?>
                <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#collapseAlmacen" aria-expanded="false" aria-controls="collapsePages2">
                    <div class="form-row">
                        <div class="div-icono">
                            <span class="fas fa-store fa-lg" style="color: #82E0AA"></span>
                        </div>
                        <div>
                            Almacén
                        </div>
                    </div>
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                </a>
                <div class="collapse" id="collapseAlmacen" aria-labelledby="headingTwo" data-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav accordion shadow-nav-lat" id="sidenavAccordionPages">
                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/almacen/lista_pedidos.php">
                            <div class="form-row">
                                <div class="div-icono">
                                    <i class="fas fa-shopping-bag" style="color: #E74C3C;"></i>
                                </div>
                                <div>
                                    Pedidos
                                </div>
                            </div>
                        </a>
                        <?php
                        if ($rol == 3 || $rol == 1) {
                        ?>
                            <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/almacen/lista_entradas.php">
                                <div class="form-row">
                                    <div class="div-icono">
                                        <i class="fas fa-door-open" style="color: #85C1E9;"></i>
                                    </div>
                                    <div>
                                        Entradas
                                    </div>
                                </div>
                            </a>
                        <?php } ?>
                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/almacen/lista_salidas.php">
                            <div class="form-row">
                                <div class="div-icono">
                                    <i class="fas fa-sign-out-alt" style="color: #F1C40F;"></i>
                                </div>
                                <div>
                                    Salidas
                                </div>
                            </div>
                        </a>
                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/almacen/kardex.php">
                            <div class="form-row">
                                <div class="div-icono">
                                    <i class="fas fa-table" style="color: #FF5733;"></i>
                                </div>
                                <div>
                                    Kardex
                                </div>
                            </div>
                        </a>
                        <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/almacen/traslados.php">
                            <div class="form-row">
                                <div class="div-icono">
                                    <i class="fas fa-exchange-alt" style="color: #2ECC71;"></i>
                                </div>
                                <div>
                                    Traslados
                                </div>
                            </div>
                        </a>
                        <?php if (false) { ?>
                            <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/almacen/ajuste_inventario.php">
                                <div class="form-row">
                                    <div class="div-icono">
                                        <i class="fas fa-balance-scale-left" style="color: #4D305A"></i>
                                    </div>
                                    <div>
                                        Ajustar inventario
                                    </div>
                                </div>
                            </a>
                        <?php } ?>
                        <a class="nav-link sombra" href="#" id="listInfAlmacen">
                            <div class="form-row">
                                <div class="div-icono">
                                    <i class="fas fa-info-circle" style="color: #8E44AD;"></i>
                                </div>
                                <div>
                                    Informes
                                </div>
                            </div>
                        </a>
                        <?php if ($rol == 3 || $rol == 1) { ?>
                            <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/almacen/configuracion.php">
                                <div class="form-row">
                                    <div class="div-icono">
                                        <i class="fas fa-cogs" style="color: #839192;"></i>
                                    </div>
                                    <div>
                                        Configuración
                                    </div>
                                </div>
                            </a>

                        <?php
                        }
                        ?>
                    </nav>
                </div>
                <?php
                $key = array_search('8', array_column($perm_modulos, 'id_modulo'));
                if (false !== $key) {
                ?>
                    <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#collapseActFijos" aria-expanded="false" aria-controls="collapsePages2">
                        <div class="form-row">
                            <div class="div-icono">
                                <span class="fas fa-laptop-house fa-lg" style="color: #D2B4DE"></span>
                            </div>
                            <div>
                                Activos Fijos
                            </div>
                        </div>
                        <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                    </a>
                    <div class="collapse" id="collapseActFijos" aria-labelledby="headingTwo" data-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav accordion shadow-nav-lat" id="sidenavAccordionPages">
                            <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/activos_fijos/entradas_activos_fijos.php">
                                <div class="form-row">
                                    <div class="div-icono">
                                        <i class="fas fa-people-carry fa-sm" style="color: #85C1E9;"></i>
                                    </div>
                                    <div>
                                        Entradas
                                    </div>
                                </div>
                            </a>
                            <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/activos_fijos/componentes_acfijos.php">
                                <div class="form-row">
                                    <div class="div-icono">
                                        <span class="fas fa-pencil-ruler fa-sm" style="color: #F1C40F;"></span>
                                    </div>
                                    <div>
                                        Gestión
                                    </div>
                                </div>
                            </a>
                            <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/activos_fijos/mantenimiento_acfijos.php">
                                <div class="form-row">
                                    <div class="div-icono">
                                        <span class="fas fa-tools fa-sm" style="color: #EB984E;"></span>
                                    </div>
                                    <div>
                                        Mantenimiento
                                    </div>
                                </div>
                            </a>
                        </nav>
                    </div>
                <?php
                }
                $key = array_search('9', array_column($perm_modulos, 'id_modulo'));
                if (false !== $key) {
                ?>
                    <a class="nav-link collapsed sombra" href="#" data-toggle="collapse" data-target="#collapseCostos" aria-expanded="false" aria-controls="collapsePages2">
                        <div class="form-row">
                            <div class="div-icono">
                                <span class="fas fa-laptop-house fa-lg" style="color: #D2B4DE"></span>
                            </div>
                            <div>
                                Costos
                            </div>
                        </div>
                        <div class="sb-sidenav-collapse-arrow"><i class="fas fa-caret-down"></i></div>
                    </a>
                    <div class="collapse" id="collapseCostos" aria-labelledby="headingTwo" data-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav accordion shadow-nav-lat" id="sidenavAccordionPages">
                            <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/activos_fijos/entradas_activos_fijos.php">
                                <div class="form-row">
                                    <div class="div-icono">
                                        <i class="fas fa-people-carry fa-sm" style="color: #85C1E9;"></i>
                                    </div>
                                    <div>
                                        Entradas
                                    </div>
                                </div>
                            </a>
                            <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/activos_fijos/componentes_acfijos.php">
                                <div class="form-row">
                                    <div class="div-icono">
                                        <span class="fas fa-pencil-ruler fa-sm" style="color: #F1C40F;"></span>
                                    </div>
                                    <div>
                                        Gestión
                                    </div>
                                </div>
                            </a>
                        </nav>
                    </div>
                <?php
                }
                $key = array_search('10', array_column($perm_modulos, 'id_modulo'));
                if (false !== $key) {
                ?>
                    <a class="nav-link sombra" href="<?php echo $_SESSION['urlin'] ?>/consultas/listado.php">
                        <div class="form-row">
                            <div class="div-icono">
                                <i class="fas fa-user-secret fa-lg" style="color: #1ABC9C;"></i>
                            </div>
                            <div>
                                Consultas
                            </div>
                        </div>
                    </a>
                <?php
                }
                ?>
            </div>
        </div>
        <div class="sb-sidenav-footer py-0">
            <style>
                #btnRegVigencia,
                #btnRegVigencia:hover {
                    color: whitesmoke;
                    text-decoration: none;
                }
            </style>
            <div class="small">Actualmente:</div>
            <?php
            if ($_SESSION['id_user'] == 1) {
                $valida = '<div><a type="button" id="btnRegVigencia" href="javascript:void(0)" title="Agregar Vigencia">Vigencia:</a> ' . $_SESSION['vigencia'] . '</div>';
            } else {
                $valida = '<div>Vigencia: ' . $_SESSION['vigencia'] . '</div>';
            }
            ?>
            <div class="small">
                <?php echo $valida ?>
                <div>Usuario: <?php echo mb_strtoupper($_SESSION['user']) ?></div>
            </div>
        </div>
    </nav>
</div>