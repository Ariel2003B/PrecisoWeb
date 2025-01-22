<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CarritoController extends Controller
{
    public function index()
    {
        $carrito = session('carrito', []);
        return view('carrito.index', compact('carrito'));
    }

    public function addToCart(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $price = $request->input('price');
        $time = $request->input('time');

        $carrito = session('carrito', []);
        $carrito[$id] = [
            'id' => $id,
            'name' => $name,
            'price' => $price,
            'time' => $time,
            'quantity' => isset($carrito[$id]) ? $carrito[$id]['quantity'] + 1 : 1,
        ];

        session(['carrito' => $carrito]);

        return response()->json(['message' => 'Plan añadido al carrito', 'cartCount' => count($carrito)]);
    }

    public function removeFromCart($id)
    {
        $carrito = session('carrito', []);
        if (isset($carrito[$id])) {
            unset($carrito[$id]);
            session(['carrito' => $carrito]);
        }

        return redirect()->route('carrito.index')->with('success', 'Plan eliminado del carrito.');
    }

    public function clearCart()
    {
        session()->forget('carrito');
        return redirect()->route('carrito.index')->with('success', 'Carrito vaciado.');
    }

    public function updateQuantity(Request $request)
    {
        $id = $request->input('id');
        $quantity = $request->input('quantity');

        $carrito = session('carrito', []);
        if (isset($carrito[$id])) {
            $carrito[$id]['quantity'] = max(1, $quantity); // Evita cantidades menores a 1
            session(['carrito' => $carrito]);
        }

        return response()->json(['message' => 'Cantidad actualizada.', 'total' => $this->calculateTotal()]);
    }

    private function calculateTotal()
    {
        $carrito = session('carrito', []);
        $total = 0;
        foreach ($carrito as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }



}
