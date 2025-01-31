<?php

namespace App\Http\Controllers;

use App\Models\BLOG;
use App\Models\SUBTITULO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = BLOG::with('s_u_b_t_i_t_u_l_o_s')->orderBy('FECHACREACION', 'desc')->get();
        return view('blog.index', compact('blogs'));
    }
    public function create()
    {
        return view('blog.create');
    }


    public function store(Request $request)
    {
        try {
            // Validar entrada
            $request->validate([
                'TITULO' => 'required|max:255',
                'AUTOR' => 'nullable|max:500',
                'CATEGORIA' => 'nullable|max:100',
                'URL_IMAGEN' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'CONTENIDO' => 'required',
                'subtitulos' => 'nullable|array',
                'subtitulos.*' => 'nullable|max:255',
                'textosubtitulos' => 'nullable|array',
                'textosubtitulos.*' => 'nullable'
            ]);
            // Manejo de imagen
            $imagenPath = null;
            if ($request->hasFile('URL_IMAGEN')) {
                $imagenPath = $request->file('URL_IMAGEN')->store('imagenes_blog', 'public');
            }

            // Crear el blog
            $blog = BLOG::create([
                'TITULO' => $request->TITULO,
                'AUTOR' => $request->AUTOR,
                'CATEGORIA' => $request->CATEGORIA,
                'FECHACREACION' => now(),
                'URL_IMAGEN' => $imagenPath,
                'CONTENIDO' => $request->CONTENIDO,
                'NUMEROCOMENTARIOS' => 0
            ]);

            // Guardar los subtítulos si existen
            if ($request->has('subtitulos') && $request->has('textosubtitulos')) {
                foreach ($request->subtitulos as $index => $titulo) {
                    if (!empty($titulo) && !empty($request->textosubtitulos[$index])) {
                        SUBTITULO::create([
                            'BLO_ID' => $blog->BLO_ID,
                            'NUMERO' => $index + 1,
                            'TEXTO' => $titulo,
                            'CONTENIDO' => $request->textosubtitulos[$index]
                        ]);
                    }
                }
            }

            return redirect()->route('blog.index')->with('success', 'Blog creado correctamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ocurrió un error inesperado. ' . $e->getMessage())->withInput();
        }
    }



    public function show($id)
    {
        $blog = BLOG::with('s_u_b_t_i_t_u_l_o_s')->findOrFail($id);
        return view('blog.show', compact('blog'));
    }
    // Mostrar formulario de edición
    public function edit($id)
    {
        $blog = BLOG::with('s_u_b_t_i_t_u_l_o_s')->findOrFail($id);
        return view('blog.edit', compact('blog'));
    }

    // Actualizar un blog
    public function update(Request $request, $id)
    {
        $blog = BLOG::findOrFail($id);

        // Validar entrada
        $request->validate([
            'TITULO' => 'required|max:255',
            'AUTOR' => 'nullable|max:500',
            'CATEGORIA' => 'nullable|max:100',
            'URL_IMAGEN' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'CONTENIDO' => 'required',
            'subtitulos' => 'nullable|array',
            'textosubtitulos' => 'nullable|array',
            'deleted_subtitulos' => 'nullable|string'
        ]);

        // Actualizar imagen si se proporciona una nueva
        if ($request->hasFile('URL_IMAGEN')) {
            if ($blog->URL_IMAGEN) {
                Storage::delete('public/' . $blog->URL_IMAGEN);
            }
            $blog->URL_IMAGEN = $request->file('URL_IMAGEN')->store('imagenes_blog', 'public');
        }

        $blog->update([
            'TITULO' => $request->TITULO,
            'AUTOR' => $request->AUTOR,
            'CATEGORIA' => $request->CATEGORIA,
            'CONTENIDO' => $request->CONTENIDO
        ]);

        // Eliminar subtítulos que fueron marcados para eliminación
        if (!empty($request->deleted_subtitulos)) {
            $subtitulosEliminados = explode(',', $request->deleted_subtitulos);
            SUBTITULO::whereIn('SUB_ID', $subtitulosEliminados)->delete();
        }

        // Actualizar subtítulos existentes o agregar nuevos
        if ($request->has('subtitulos')) {
            $numero = 1;
            foreach ($request->subtitulos as $sub_id => $titulo) {
                if (!empty($titulo) && !empty($request->textosubtitulos[$sub_id])) {
                    SUBTITULO::updateOrCreate(
                        ['SUB_ID' => $sub_id, 'BLO_ID' => $blog->BLO_ID],
                        ['NUMERO' => $numero++, 'TEXTO' => $titulo, 'CONTENIDO' => $request->textosubtitulos[$sub_id]]
                    );
                }
            }
        }

        return redirect()->route('blog.index')->with('success', 'Blog actualizado correctamente.');
    }

    // Eliminar un blog
    public function destroy($id)
    {
        $blog = BLOG::findOrFail($id);

        if ($blog->URL_IMAGEN) {
            Storage::delete('public/' . $blog->URL_IMAGEN);
        }

        $blog->delete();

        return redirect()->route('blog.index')->with('success', 'Blog eliminado correctamente.');
    }
}
