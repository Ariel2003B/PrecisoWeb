@extends('layout')
@section('Titulo', 'Nosotros')
@section('ActivarNosotros', 'active')
@section('content')
<br>
<br>
<br>
<br>
<!-- About-->
    <section class="page-section" id="about">
        <div class="container">
            <div class="text-center">
                <h2 class="section-heading text-uppercase">Nosotros</h2>
                <h3 class="section-subheading text-muted">Conoce más sobre nuestra misión, visión y proyectos.</h3>
            </div>
            <ul class="timeline">
                <li>
                    <div class="timeline-image"><img class="rounded-circle img-fluid" src="{{asset('img/about/4.jpg')}}"
                            alt="Misión" /></div>
                    <div class="timeline-panel">
                        <div class="timeline-heading">
                            <h4>Misión</h4>
                            <h4 class="subheading">Compromiso con la tecnología nacional</h4>
                        </div>
                        <div class="timeline-body">
                            <p class="text-muted">Somos una empresa con 23 años en el mercado, brindando servicios de
                                monitoreo en el transporte público y privado con productos tecnológicos desarrollados a
                                la medida, permitiendo a nuestros clientes adquirirlos sin intermediarios y con
                                excelente calidad.</p>
                        </div>
                    </div>
                </li>
                <li class="timeline-inverted">
                    <div class="timeline-image"><img class="rounded-circle img-fluid" src="{{asset('img/about/3.jpg')}}"
                            alt="Visión" /></div>
                    <div class="timeline-panel">
                        <div class="timeline-heading">
                            <h4>Visión</h4>
                            <h4 class="subheading">Liderazgo en desarrollo tecnológico</h4>
                        </div>
                        <div class="timeline-body">
                            <p class="text-muted">Ser pioneros en tecnología que permita satisfacer necesidades en
                                hardware y software nacional, con miras a lograr certificaciones ISO para 2030,
                                impulsando proyectos en empresas públicas y privadas tanto nacionales como
                                internacionales.</p>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="timeline-image"><img class="rounded-circle img-fluid" src="{{asset('img/about/2.jpg')}}"
                            alt="Proyectos de éxito" /></div>
                    <div class="timeline-panel">
                        <div class="timeline-heading">
                            <h4>Proyectos de Éxito</h4>
                            <h4 class="subheading">Innovación aplicada en grandes proyectos</h4>
                        </div>
                        <div class="timeline-body">
                            <p class="text-muted">Hemos participado en proyectos como la instalación de sistemas de
                                monitoreo en el Teleférico de Quito, soluciones de conteo de pasajeros en el transporte
                                público de Quito, y sistemas de control para tanqueros en petroleras.</p>
                        </div>
                    </div>
                </li>
                <li class="timeline-inverted">
                    <div class="timeline-image">
                        <h4>
                            ¡Sé Parte
                            <br />
                            De Nuestra
                            <br />
                            Historia!
                        </h4>
                    </div>
                </li>
            </ul>
        </div>
    </section>
    <section class="page-section bg-light" id="clients-carousel">
        <div class="container">
            <div class="text-center">
                <h2 class="section-heading text-uppercase">Nuestros Clientes</h2>
                <h3 class="section-subheading text-muted">Empresas que confían en nosotros</h3>
            </div>
            <div id="logoCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner text-center">
                    <div class="carousel-item active">
                        <div class="d-flex justify-content-center align-items-center">
                            <img src="{{asset('img/clients/Kinara.jpg')}}" alt="Logo Kinara">
                            <img src="{{asset('img/clients/quitumbe.png')}}" alt="Logo Quitumbe">
                            <img src="{{asset('img/clients/Transporsel.jpg')}}" alt="Logo TransporSel">
                            <img src="{{asset('img/clients/Transalfa.jpg')}}" alt="Logo Transalfa">
                            <img src="{{asset('img/clients/teleferico.png')}}" alt="Logo Teleferico">
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="d-flex justify-content-center align-items-center">
                            <img src="{{asset('img/clients/Sirena.jpg')}}" alt="Logo Sirena">
                            <img src="{{asset('img/clients/Rutvitransa.png')}}" alt="Logo Rutvitransa">
                            <img src="{{asset('img/clients/xtrim.png')}}" alt="Logo Xtrim tv cable">
                        </div>
                    </div>
                    <div class="carousel-item">
                        <div class="d-flex justify-content-center align-items-center">
                            <img src="{{asset('img/clients/Rapitrans.jpg')}}" alt="Logo Rapitrans">
                        </div>
                    </div>
                </div>
                <!-- Controles -->
                <button class="carousel-control-prev" type="button" data-bs-target="#logoCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Anterior</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#logoCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Siguiente</span>
                </button>
            </div>
        </div>
    </section>
    <section class="page-section" id="contact">
        <div class="container">
            <div class="text-center">
                <h2 class="section-heading text-uppercase">Contáctanos</h2>
                <h3 class="section-subheading text-muted">¿Tienes preguntas? Nuestro equipo está listo para asistirte.
                </h3>
            </div>
            <!-- * * * * * * * * * * * * * * *-->
            <!-- * * SB Forms Contact Form * *-->
            <!-- * * * * * * * * * * * * * * *-->
            <!-- This form is pre-integrated with SB Forms.-->
            <!-- To make this form functional, sign up at-->
            <!-- https://startbootstrap.com/solution/contact-forms-->
            <!-- to get an API token!-->
            <form id="contactForm" data-sb-form-api-token="API_TOKEN">
                <div class="row align-items-stretch mb-5">
                    <div class="col-md-6">
                        <div class="form-group">
                            <!-- Name input-->
                            <input class="form-control" id="name" type="text" placeholder="Nombres *"
                                data-sb-validations="required" />
                            <div class="invalid-feedback" data-sb-feedback="name:required">Es requerido su nombre.
                            </div>
                        </div>
                        <div class="form-group">
                            <!-- Email address input-->
                            <input class="form-control" id="email" type="email" placeholder="Correo electronico *"
                                data-sb-validations="required,email" />
                            <div class="invalid-feedback" data-sb-feedback="email:required">Se requiere un correo
                                electronico</div>
                            <div class="invalid-feedback" data-sb-feedback="email:email">Correo electronico no valido.
                            </div>
                        </div>
                        <div class="form-group mb-md-0">
                            <!-- Phone number input-->
                            <input class="form-control" id="phone" type="tel" placeholder="Teléfono *"
                                data-sb-validations="required" />
                            <div class="invalid-feedback" data-sb-feedback="phone:required">Es requerido su telefono.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group form-group-textarea mb-md-0">
                            <!-- Message input-->
                            <textarea class="form-control" id="message" placeholder="Tu mensaje *" data-sb-validations="required"></textarea>
                            <div class="invalid-feedback" data-sb-feedback="message:required">Es requerido su mensaje.
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Submit success message-->
                <!---->
                <!-- This is what your users will see when the form-->
                <!-- has successfully submitted-->
                <div class="d-none" id="submitSuccessMessage">
                    <div class="text-center text-white mb-3">
                        <div class="fw-bolder">Pronto nuestro equipo se comunicara contigo.</div>
                    </div>
                </div>
                <!-- Submit error message-->
                <!---->
                <!-- This is what your users will see when there is-->
                <!-- an error submitting the form-->
                <div class="d-none" id="submitErrorMessage">
                    <div class="text-center text-danger mb-3">Error sending message!</div>
                </div>
                <!-- Submit Button-->
                <div class="text-center"><button class="btn btn-primary btn-xl text-uppercase disabled" id="submitButton"
                        type="submit">Enviar mensaje</button></div>
            </form>
        </div>
    </section>
@endsection
@section('jsCode', 'js/scriptNavBar.js')
