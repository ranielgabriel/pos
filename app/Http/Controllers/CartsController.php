<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Cart;
use App\Product;

class CartsController extends Controller
{

    public function __construct()
    {
        // $this->middleware('auth', ['except' => ['index', 'show']]);
        $this->middleware('auth');
        // ->except(['index', 'show']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $cart = Cart::create([
            'product_id' => $request->input('productId'),
            'user_id' => auth()->user()->id
        ]);

        $product = Product::find($request->input('productId'));

        return redirect('/products')->with('success', '"'. $cart->product->genericNames->description . ' ' . $cart->product->brand_name .'" has been added to your cart.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if(Auth::user()->id != $id){
            $cart = Cart::where('user_id','=', Auth::user()->id)
            ->with('product')
            ->get();
            return redirect('/cart/'. Auth::user()->id);
        }else{
            $cart = Cart::where('user_id','=',$id)
            ->with('product')
            ->get();
            return view('cart.show')->with('cart', $cart);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $cart = Cart::find($id);
        $cart->delete();
        return \App::make('redirect')->back()->with('info', '"'. $cart->product->genericNames->description . ' ' . $cart->product->brand_name . '" has been removed from the cart.');
    }
}
