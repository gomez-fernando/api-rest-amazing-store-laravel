<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Category;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }


    public function index()
    {
        $categories = Category::all();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'categories' => $categories,
        ]);
    }

    public function show($id)
    {
        $category = Category::find($id);
        if (is_object($category)) {
            $data = [
                'code' => 200,
                'status' => 'success',
                'category' => $category
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'La categoría no existe'
            ];
        }
        return response()->json($data, $data['code']);
    }

    public function store(Request $request)
    {
        // recoger los datos por post
        $json = $request->input('json', null);
        // pasamos esto a un array de php con el parámetro true
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            // validar los datos
            $validate = \Validator::make($params_array, [
                'name' => 'required'
            ]);

            // guardar la categoría
            if ($validate->fails()) {
                $data = [
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'No se ha guardado la categoría'
                ];
            } else {
                $category = new Category;
                $category->name = $params_array['name'];
                $category->save();

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'category' => $category
                ];
            }
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'No has enviado ninguna categoría'
            ];
        }
        // devolver el resultado
        return response()->json($data, $data['code']);
    }

    public function update($id, Request $request)
    {
        // recoger los datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            // validar los datos
            $validate = \Validator::make($params_array, [
                'name' => 'required'
            ]);
            // quitar lo que no queremos actualizar
            unset($params_array['id']);
            unset($params_array['created_at']);
            // actualizar el registro
            $category = Category::where('id', $id)->update($params_array);
            $data = [
                'code' => 200,
                'status' => 'success',
                'changes' => $params_array
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'No has enviado ninguna categoría'
            ];
        }
        // devolver respuesta
        return response()->json($data, $data['code']);
    }
}