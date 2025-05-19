<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\User;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class SitemapController extends Controller
{
    public function index(){
        $products = Product::all();
        $categories = Category::all();
        $brands = Brand::all();
        $pages = Page::all();

        return response()->view('sitemap.main', compact('pages','products','categories', 'brands'))->header('Content-Type', 'text/xml');
    }

    public function links(){
        $products = Product::all();
        $categories = Category::all();
        $brands = Brand::all();
        $pages = Page::all();

        return response()->view('sitemap.links', compact('pages','products','categories', 'brands'));
    }

    public function products()
    {
        $products = Product::all();
        return response()->view('sitemap.product-details', compact('products'))->header('Content-Type', 'text/xml');
    }
    public function categories()
    {
        $categories = Category::all();
        return response()->view('sitemap.category', compact('categories'))->header('Content-Type', 'text/xml');
    }
    public function brands()
    {
        $brands = Brand::all();
        return response()->view('sitemap.brand', compact('brands'))->header('Content-Type', 'text/xml');
    }
    public function pages()
    {
        $pages = Page::all();
        return response()->view('sitemap.page', compact('pages'))->header('Content-Type', 'text/xml');
    }
}
