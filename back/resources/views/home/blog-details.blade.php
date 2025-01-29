@extends('layout')

@section('Titulo', 'PrecisoGPS - Detalles Blog')
@section('ActivarBlog', 'active')

@section('content')
    <main class="main">
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">{{ $blog->TITULO }}</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li><a href="{{ route('home.blogs') }}">Blog</a></li>
                        <li><a href="">{{ $blog->TITULO }}</a></li>
                    </ol>
                </nav>
            </div>
        </div><!-- End Page Title -->
        <div class="container">
            <div class="row">

                <!-- Contenido Principal -->
                <div class="col-lg-8">

                    <!-- Sección de Detalle del Blog -->
                    <section id="blog-details" class="blog-details section">
                        <div class="container">
                            <article class="article">

                                <!-- Imagen del Blog -->
                                <div class="post-img">
                                    <img src="{{ asset('back/storage/app/public/' . $blog->URL_IMAGEN) }}" alt=""
                                        class="img-fluid">
                                </div>

                                <!-- Título del Blog -->
                                <h2 class="title">{{ $blog->TITULO }}</h2>

                                <!-- Meta Información -->
                                <div class="meta-top">
                                    <ul>
                                        <li class="d-flex align-items-center">
                                            <i class="bi bi-person"></i>
                                            <span>{{ $blog->AUTOR }}</span>
                                        </li>
                                        <li class="d-flex align-items-center">
                                            <i class="bi bi-clock"></i>
                                            <time datetime="{{ $blog->FECHACREACION }}">
                                                {{ \Carbon\Carbon::parse($blog->FECHACREACION)->locale('es')->translatedFormat('d \d\e F, Y') }}
                                            </time>
                                        </li>
                                        <li class="d-flex align-items-center">
                                            <i class="bi bi-chat-dots"></i>
                                            <span>{{ count($comentarios) }} Comentarios</span>
                                        </li>
                                    </ul>
                                </div>

                                <!-- Contenido del Blog -->
                                <div class="content">
                                    <p>{{ $blog->CONTENIDO }}</p>

                                    @foreach ($blog->s_u_b_t_i_t_u_l_o_s as $subtitulo)
                                        <h3>{{ $subtitulo->TEXTO }}</h3>
                                        <p>{{ $subtitulo->CONTENIDO }}</p>
                                    @endforeach
                                </div>

                            </article>
                        </div>
                    </section>

                    <!-- Sección de Comentarios -->
                    <section id="blog-comments" class="blog-comments section">
                        <div class="container">
                            <h4 class="comments-count">{{ count($comentarios) }} Comentarios</h4>

                            @foreach ($comentarios->where('RES_RES_ID', null) as $comentario)
                                <div class="comment">
                                    <div class="d-flex">
                                        <div>
                                            <h5>
                                                <a href="">{{ $comentario->AUTOR }}</a>
                                                <a href="#" class="reply" data-bs-toggle="modal"
                                                    data-bs-target="#replyModal"
                                                    data-comment-id="{{ $comentario->RES_ID }}">
                                                    <i class="bi bi-reply-fill"></i> Reply
                                                </a>
                                            </h5>
                                            <time datetime="{{ $comentario->FECHACREACION }}">
                                                {{ \Carbon\Carbon::parse($comentario->FECHACREACION)->locale('es')->translatedFormat('d \d\e F, Y') }}
                                            </time>
                                            <p>{{ $comentario->DESCRIPCION }}</p>
                                        </div>
                                    </div>

                                    <!-- Respuestas anidadas -->
                                    @foreach ($comentario->r_e_s_p_u_e_s_t_a as $respuesta)
                                        <div class="comment comment-reply ms-4">
                                            <div class="d-flex">
                                                <div>
                                                    <h5>
                                                        <a href="">{{ $respuesta->AUTOR }}</a>
                                                    </h5>
                                                    <time datetime="{{ $respuesta->FECHACREACION }}">
                                                        {{ \Carbon\Carbon::parse($respuesta->FECHACREACION)->locale('es')->translatedFormat('d \d\e F, Y') }}
                                                    </time>
                                                    <p>{{ $respuesta->DESCRIPCION }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section id="comment-form" class="comment-form section">
                        <div class="container">
                            <h4>Deja tu comentario</h4>
                            <form action="{{ route('comentario.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="BLO_ID" value="{{ $blog->BLO_ID }}">

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <input name="AUTOR" type="text" class="form-control" placeholder="Tu Nombre*"
                                            required>
                                    </div>
                                    <div class="col form-group">
                                        <textarea name="DESCRIPCION" class="form-control" placeholder="Tu Comentario*" rows="4" required></textarea>
                                    </div>
                                </div>

                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary">Publicar Comentario</button>
                                </div>
                            </form>
                        </div>
                    </section>

                </div>

                <!-- Barra Lateral (Solo con Búsqueda) -->
                <div class="col-lg-4 sidebar">
                    <div class="widgets-container">

                        <!-- Widget de Búsqueda -->
                        <!-- Widget de Búsqueda -->
                        <div class="search-widget widget-item">
                            <h3 class="widget-title">Buscar en Publicaciones Recientes</h3>
                            <input type="text" id="search-recent" placeholder="Buscar...">
                        </div>


                        <!-- Publicaciones Recientes -->
                        <div class="recent-posts-widget widget-item">
                            <h3 class="widget-title">Publicaciones Recientes</h3>
                            <div id="recent-posts-container">
                                @foreach ($recientes as $reciente)
                                    <div class="post-item">
                                        <h4>
                                            <a
                                                href="{{ route('blog.details', $reciente->BLO_ID) }}">{{ $reciente->TITULO }}</a>
                                        </h4>
                                        <time datetime="{{ $reciente->FECHACREACION }}">
                                            {{ \Carbon\Carbon::parse($reciente->FECHACREACION)->locale('es')->translatedFormat('d \d\e F, Y') }}
                                        </time>
                                    </div>
                                @endforeach
                            </div>
                        </div>


                    </div>
                </div>

            </div>
        </div>
    </main>




    <!-- Modal de Respuesta -->
    <div class="modal fade" id="replyModal" tabindex="-1" aria-labelledby="replyModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="replyModalLabel">Responder al comentario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="replyForm" action="{{ route('comentario.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="BLO_ID" value="{{ $blog->BLO_ID }}">
                        <input type="hidden" name="RES_RES_ID" id="parentCommentId">

                        <div class="mb-3">
                            <label for="replyAuthor" class="form-label">Tu Nombre</label>
                            <input type="text" name="AUTOR" id="replyAuthor" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="replyContent" class="form-label">Tu Respuesta</label>
                            <textarea name="DESCRIPCION" id="replyContent" class="form-control" rows="4" required></textarea>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">Enviar Respuesta</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let replyButtons = document.querySelectorAll(".reply");

            replyButtons.forEach(button => {
                button.addEventListener("click", function() {
                    let commentId = this.getAttribute("data-comment-id");
                    document.getElementById("parentCommentId").value = commentId;
                });
            });
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let searchInput = document.getElementById("search-recent");
            let recentPosts = document.querySelectorAll("#recent-posts-container .post-item");

            searchInput.addEventListener("input", function() {
                let searchText = this.value.toLowerCase();

                recentPosts.forEach(post => {
                    let title = post.querySelector("h4 a").textContent.toLowerCase();

                    if (title.includes(searchText)) {
                        post.style.display = "block";
                    } else {
                        post.style.display = "none";
                    }
                });
            });
        });
    </script>

@endsection
