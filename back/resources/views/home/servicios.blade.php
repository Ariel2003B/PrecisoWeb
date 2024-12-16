@extends('layout')
@section('Titulo', 'Servicios')
@section('ActivarServicios', 'active')
@section('content')
<br>
<br>
<br>
<br>

<section class="page-section" id="services">
    <div class="container">
        <div class="text-center">
            <h2 class="section-heading text-uppercase">Servicios</h2>
            <h3 class="section-subheading text-muted">Soluciones tecnológicas a la medida del cliente.</h3>
        </div>
        <div class="row text-center">
            <div class="col-md-4">
                <span class="fa-stack fa-4x">
                    <i class="fas fa-circle fa-stack-2x text-primary"></i>
                    <i class="fas fa-satellite-dish fa-stack-1x fa-inverse"></i>
                </span>
                <h4 class="my-3">Rastreo Satelital</h4>
                <p class="text-muted">Rastreo satelital para todo tipo de vehículos, permitiendo localización y
                    monitoreo constante.</p>
            </div>
            <div class="col-md-4">
                <span class="fa-stack fa-4x">
                    <i class="fas fa-circle fa-stack-2x text-primary"></i>
                    <i class="fas fa-video fa-stack-1x fa-inverse"></i>
                </span>
                <h4 class="my-3">Cámaras de Seguridad</h4>
                <p class="text-muted">Instalación de cámaras de seguridad en vehículos para mayor control y
                    vigilancia en tiempo real.</p>
            </div>
            <div class="col-md-4">
                <span class="fa-stack fa-4x">
                    <i class="fas fa-circle fa-stack-2x text-primary"></i>
                    <i class="fas fa-cogs fa-stack-1x fa-inverse"></i>
                </span>
                <h4 class="my-3">Soluciones Tecnológicas</h4>
                <p class="text-muted">Desarrollo de soluciones IoT personalizadas, incluyendo sistemas de seguridad
                    y monitoreo.</p>
            </div>
        </div>
        <div class="row text-center mt-5">
            <div class="col-md-4">
                <span class="fa-stack fa-4x">
                    <i class="fas fa-circle fa-stack-2x text-primary"></i>
                    <i class="fas fa-user-check fa-stack-1x fa-inverse"></i>
                </span>
                <h4 class="my-3">Contadores de Pasajeros</h4>
                <p class="text-muted">Sistema de conteo de pasajeros para transporte público, permitiendo un control
                    preciso y seguro.</p>
            </div>
            <div class="col-md-4">
                <span class="fa-stack fa-4x">
                    <i class="fas fa-circle fa-stack-2x text-primary"></i>
                    <i class="fas fa-solar-panel fa-stack-1x fa-inverse"></i>
                </span>
                <h4 class="my-3">Sistemas Solares</h4>
                <p class="text-muted">Implementación de sistemas solares personalizados para diferentes necesidades
                    energéticas.</p>
            </div>
            <div class="col-md-4">
                <span class="fa-stack fa-4x">
                    <i class="fas fa-circle fa-stack-2x text-primary"></i>
                    <i class="fas fa-shield-alt fa-stack-1x fa-inverse"></i>
                </span>
                <h4 class="my-3">Seguridad Integral</h4>
                <p class="text-muted">Soluciones de seguridad integral, incluyendo desarrollo de hardware y software
                    con monitoreo 24/7.</p>
            </div>
        </div>
    </div>
</section>
@endsection
@section('jsCode', 'js/scriptNavBar.js')