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

    </div>
</section>
@endsection
@section('jsCode', 'js/scriptNavBar.js')