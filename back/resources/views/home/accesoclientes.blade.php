@extends('layout')
@section('Titulo', 'Acceso a clientes')
@section('ActivarAccessC', 'active')
@section('content')
    <br>
    <br>
    <br>
    <br>
    <br>
    <!-- Política de Privacidad -->
    <section class="page-section" id="">
        <div class="container">
            <div class="text-center">
                <h2 class="section-heading text-uppercase">Nuestras plataformas</h2>
                <h3 class="section-subheading text-muted">Encuentra la plataforma de tu preferencia.</h3>
            </div>
            <div class="row text-center">
                <div class="col-md-4">
                    <span class="fa-stack fa-4x">
                        <i class="fas fa-solid fa-square fa-stack-2x text-primary"></i>
                        <i class="fas fa-bus fa-stack-1x fa-inverse"></i>
                    </span>
                    <h4 class="my-3">PRECISO BUS</h4>
                    <a class="btn btn-success" href="https://nimbus.wialon.com/login" target="_blank">Visitar página</a>
                </div>
                <div class="col-md-4">
                    <span class="fa-stack fa-4x">
                        <i class="fas fa-solid fa-square fa-stack-2x text-primary"></i>
                        <i class="fas fa-satellite-dish fa-stack-1x fa-inverse"></i>
                    </span>
                    <h4 class="my-3">RASTREA TU VEHICULO</h4>
                    <a class="btn btn-success" href="http://www.precisogps.online/" target="_blank">Visitar página</a>
                </div>
                <div class="col-md-4">
                    <span class="fa-stack fa-4x">
                        <i class="fas fa-solid fa-square fa-stack-2x text-primary"></i>
                        <i class="fas fa-clock fa-stack-1x fa-inverse"></i>
                    </span>
                    <h4 class="my-3">MINUTOS CAIDOS</h4>
                    <a class="btn btn-success" href="http://157.245.141.38:4020/login" target="_blank">Visitar
                        página</a>
                </div>
            </div>
            <br>
            <br>

            <div class="row text-center">
                <div class="col-md-4">
                    <span class="fa-stack fa-4x">
                        <i class="fas fa-solid fa-square fa-stack-2x text-primary"></i>
                        <i class="fas fa-wrench fa-stack-1x fa-inverse"></i>
                    </span>
                    <h4 class="my-3">MANTENIMIENTO VEHICULAR</h4>
                    <a class="btn btn-success" href="https://fleetrun.wialon.com/login" target="_blank">Visitar
                        página</a>
                </div>
                <div class="col-md-4">
                    <span class="fa-stack fa-4x">
                        <i class="fas fa-solid fa-square fa-stack-2x text-primary"></i>
                        <i class="fas fa-cash-register fa-stack-1x fa-inverse"></i>
                    </span>
                    <h4 class="my-3">CAJA COMÚN</h4>
                    <a class="btn btn-success" href="http://157.230.189.65:5030/login" target="_blank">Visitar
                        página</a>
                </div>

                <div class="col-md-4">
                    <span class="fa-stack fa-4x">
                        <i class="fas fa-solid fa-square fa-stack-2x text-primary"></i>
                        <i class="fas fa-folder-open fa-stack-1x fa-inverse"></i>
                    </span>
                    <h4 class="my-3">SECRETARIA DE MOVILIDAD OPERADORAS</h4>
                    <a class="btn btn-success" data-bs-toggle="modal" href="#modalSecretaria">Encuentra tu CIA</a>
                </div>
            </div>
            <br>
            <br>
            @if (Auth::check())
                @if (Auth::user()->p_e_r_f_i_l->p_e_r_m_i_s_o_s->contains('DESCRIPCION', 'SIMCARDS'))
                    <div class="row text-center">
                        <div class="col-md-4">
                            <span class="fa-stack fa-4x">
                                <i class="fas fa-solid fa-square fa-stack-2x text-primary"></i>
                                <i class="fas fa-sim-card fa-stack-1x fa-inverse"></i>
                            </span>
                            <h4 class="my-3">REPOSICIÓN DE CHIPS</h4>
                            <a class="btn btn-success" href="http://www.miclaro.com.ec/ivrdigital" target="_blank">Visitar
                                página</a>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </section>

    <div class="portfolio-modal modal fade modal-secretaria" id="modalSecretaria" tabindex="-1" role="dialog"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <!-- Botón para cerrar -->
                <div class="close-modal" data-bs-dismiss="modal">
                    <img src="{{ asset('img/close-icon.svg') }}" alt="Close modal" />
                </div>
                <!-- Contenido del modal -->
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-12">
                            <div class="modal-body text-center">
                                <!-- Título -->
                                <h2 class="text-uppercase mb-3">Secretaría de Movilidad</h2>
                                <p class="text-muted mb-4">Lista de operadoras. Usa el filtro para encontrar fácilmente.</p>
                                <!-- Campo de filtro -->
                                <input id="filtroModal" type="text" class="form-control mb-3"
                                    placeholder="Filtrar operadoras...">
                                <!-- Lista -->
                                <ul id="listaOperadoras" class="list-group text-start">
                                    <li class="list-group-item">
                                        <a target="_blank"
                                            href="https://mdmqdireccioninformatica-my.sharepoint.com/:f:/g/personal/dmgm_movilidad_quito_gob_ec/Ev1ZMLYgYF1PnTNw1uuO_rcBsQI-3IL9H0OCxsHaqa9ObQ?email=trans-alfa95%40hotmail.com&e=ECFlAv">
                                            Transalfa S.A.
                                        </a>
                                    </li>
                                    <li class="list-group-item">
                                        <a target="_blank"
                                            href="https://mdmqdireccioninformatica-my.sharepoint.com/:f:/g/personal/dmgm_movilidad_quito_gob_ec/EmdCi57J0bhMrDL7PnAJqQsBHegEX_sInhKFAq-Bf3do3w?email=sirenita_expres2017%40hotmail.com&e=oHybNv">
                                            Trans Sirena Express S.A.
                                        </a>
                                    </li>
                                    <li class="list-group-item">
                                        <a target="_blank"
                                            href="https://mdmqdireccioninformatica-my.sharepoint.com/:f:/g/personal/dmgm_movilidad_quito_gob_ec/El7k5IVxJg9DqLpk4AUkaXkBFBQymvVjYsGhWWI4ofiivA?email=transperifericosb%40gmail.com&e=vBb7M4">
                                            Transperiféricos S.A.
                                        </a>
                                    </li>
                                    <li class="list-group-item">

                                        <a target="_blank"
                                            href="https://mdmqdireccioninformatica-my.sharepoint.com/:f:/g/personal/dmgm_movilidad_quito_gob_ec/EidP6bxW9BJKihOYsxVWWNIBftqzJDn0LYeFxCEo4VMFLg?email=intra31express%40gmail.com&e=ek2IIz">Intraexpress
                                            S.A.</a>
                                    </li>
                                    <li class="list-group-item">

                                        <a target="_blank"
                                            href="https://mdmqdireccioninformatica-my.sharepoint.com/:f:/g/personal/dmgm_movilidad_quito_gob_ec/EllmmQmeM9xBv5R18R044xMBbZV1-WFnYWanpJ0pZyNtvg?email=operadora_quitumbe%40hotmail.com&e=00qvv5">Quitumbe
                                            S.A.</a>
                                    </li>
                                    <li class="list-group-item">
                                        <a target="_blank"
                                            href="https://mdmqdireccioninformatica-my.sharepoint.com/:f:/g/personal/dmgm_movilidad_quito_gob_ec/Ety5AbNLp7xIn0nKQ3G9NOwB0YVG3BXemX3nr6cZeoOsjg?email=tstransporsel%40hotmail.com&e=4jO9Bc">Transporsel
                                            S.A.</a>
                                    </li>
                                    <li class="list-group-item">
                                        <a target="_blank"
                                            href="https://mdmqdireccioninformatica-my.sharepoint.com/:f:/g/personal/dmgm_movilidad_quito_gob_ec/EoQ17-OVtQxFvA8VYFoHWGIB-2jQwQojjh_YIS2XxCPD8A?email=kinaraexpress%40hotmail.com&e=gtKHm5">Kinara
                                            Express S.A.</a>
                                    </li>
                                    <li class="list-group-item">
                                        <a target="_blank"
                                            href="https://mdmqdireccioninformatica-my.sharepoint.com/:f:/g/personal/dmgm_movilidad_quito_gob_ec/EiIwcao6BPlEjsYR0FEBWEABiKZmd0A0A-A9zspBvVt3aw?email=ciaruvitransa2017%40hotmail.com&e=B03UfB">Rutvitransa
                                            S.A</a>
                                    </li>


                                    <li class="list-group-item">
                                        <a target="_blank"
                                            href="https://mdmqdireccioninformatica-my.sharepoint.com/:f:/g/personal/dmgm_movilidad_quito_gob_ec/ErOGhhjM_LBEsdAMGaH0MkQBg0eR0K-CLyi6GxFnsptbLw?email=transfloresta2%40gmail.com&e=r8JfWG">Transfloresta
                                            S.A.</a>
                                    </li>
                                    <li class="list-group-item">
                                        <a target="_blank"
                                            href="https://mdmqdireccioninformatica-my.sharepoint.com/:f:/g/personal/dmgm_movilidad_quito_gob_ec/Eix4q5kNVZ1Nn_zy4onKZFYBoc-Hnj1X535SyS5yBjtR0Q?email=urbanquito2017%40gmail.com&e=n9YNNw">UrbanQuito
                                            S.A.</a>
                                    </li>
                                    <li class="list-group-item">
                                        <a target="_blank"
                                            href="https://mdmqdireccioninformatica-my.sharepoint.com/:f:/g/personal/dmgm_movilidad_quito_gob_ec/Eimynka85wBPqjjUbFp6m14BSdHPe9v72DI1PHeoGo4YIw?email=stalin.yepez%40hotmail.com&e=jpXkzU">Nacional
                                            S.A.</a>
                                    </li>
                                </ul>
                                <!-- Botón de cierre -->
                                <div class="mt-4">
                                    <button class="btn btn-danger btn-xl text-uppercase" data-bs-dismiss="modal"
                                        type="button">
                                        <i class="fas fa-xmark me-1"></i> Cerrar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('jsCode', 'js/scriptNavBar.js')
