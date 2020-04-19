<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\Category;

class PruebasController extends Controller
{
    public function testOrm()
    {
        // $posts = Post::all();
        // // var_dump($posts);
        // foreach ($posts as $post) {
        //     echo '<h2>' . $post->title . '</h2>';
        //     echo "<p>Nombre: {$post->user->name}</p>";
        //     echo "<p>Categoría: {$post->category->name}</p>";
        //     echo '<p>Contenido: ' . $post->content . '</p><hr>';
        // }

        $categories = Category::all();
        // var_dump($posts);
        foreach ($categories as $category) {
            echo '<h2>' . $category->name . '</h2>';

            foreach ($category->posts as $post) {
                echo "<p>Nombre: {$post->title}</p>";
                echo "<p>Categoría: {$post->category->name} - Usuario: {$post->user->name}</p>";
                echo '<p>Contenido: ' . $post->content . '</p><hr>';
            }
        }

        die();
    }
}