@extends('layout')
@section('Titulo', 'PrecisoGPS - Blog')
@section('ActivarBlog', 'active')
@section('content')
    <main class="main">

        <!-- Page Title -->
        <div class="page-title accent-background">
            <div class="container d-lg-flex justify-content-between align-items-center">
                <h1 class="mb-2 mb-lg-0">Blog</h1>
                <nav class="breadcrumbs">
                    <ol>
                        <li><a href="{{ route('home.inicio') }}">Inicio</a></li>
                        <li class="current">Blog</li>
                    </ol>
                </nav>
            </div>
        </div><!-- End Page Title -->

        <!-- Blog Posts Section -->
        <section id="blog-posts" class="blog-posts section">

            <div class="container">
                <div class="row gy-4">
                    @foreach ($blogs as $blog)
                        <div class="col-lg-4">
                            <article class="position-relative h-100">
                                <div class="post-img position-relative overflow-hidden">
                                    <img src="{{ asset('back/storage/app/public/' . $blog->URL_IMAGEN) }}" class="img-fluid"
                                        alt="Imagen del blog">
                                    <span class="post-date">
                                        {{ \Carbon\Carbon::parse($blog->FECHACREACION)->locale('es')->translatedFormat('d \d\e F') }}
                                    </span>
                                </div>

                                <div class="post-content d-flex flex-column">
                                    <h3 class="post-title">{{ $blog->TITULO }}</h3>

                                    <div class="meta d-flex align-items-center">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-person"></i> <span class="ps-2">{{ $blog->AUTOR }}</span>
                                        </div>
                                        <span class="px-3 text-black-50">/</span>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-folder2"></i> <span class="ps-2">{{ $blog->CATEGORIA }}</span>
                                        </div>
                                    </div>

                                    <p>
                                        {{ Str::limit($blog->CONTENIDO, 150) }}
                                    </p>

                                    <hr>

                                    <a href="{{ route('blog.details', $blog->BLO_ID) }}" class="readmore stretched-link">
                                        <span>Leer más</span><i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>

        </section><!-- /Blog Posts Section -->

        <!-- Blog Pagination Section -->
        <section id="blog-pagination" class="blog-pagination section">
            <div class="container">
                <div class="d-flex justify-content-center">
                    {{ $blogs->appends(request()->query())->links() }}
                </div>
            </div>
        </section><!-- /Blog Pagination Section -->

    </main>
@endsection
