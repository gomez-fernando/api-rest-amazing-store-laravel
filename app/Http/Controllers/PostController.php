<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use Illuminate\Http\Response;
use App\Helpers\JwtAuth;

class PostController extends Controller
{
    // protección con autenticación
    public function __construct()
    {
        $this->middleware('api.auth', ['except' => [
            'index',
            'show',
            'getImage',
            'getPostsByCategory',
            'getPostsByUser',
            ]]);
    }

    private function getIdentity(Request $request)
    {
        //conseguir el usuario identificado
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);

        return $user;
    }

    public function index()
    {
        $posts = Post::all()->load('category');

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'posts' => $posts
        ], 200);
    }

    public function show($id)
    {
        $post = Post::find($id)->load('category')
                                ->load('user');

        if (is_object($post)) {
            $data = [
                'code' => 200,
                'status' => 'success',
                'post' => $post
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'El post no existe'
            ];
        }
        return response()->json($data, $data['code']);
    }

    public function store(Request $request)
    {
        // revoger datos por post
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);
        if (!empty($params_array)) {
            //conseguir el usuario identificado
            $user = $this->getIdentity($request);
            // validar los datos
            $validate = \Validator::make($params_array, [
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required',
                // 'image' => 'required|image', para las pruebas se queda solo en required
                'image' => 'required',

            ]);
            if ($validate->fails()) {
                $data = [
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'No se ha guardado el post, faltan datos'
                ];
            } else {
                // guardar el post
                $post = new Post();
                $post->user_id = $user->sub;
                $post->category_id = $params->category_id;
                $post->title = $params->title;
                $post->content = $params->content;
                $post->image = $params->image;
                $post->save();

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'post' => $post
                ];
            }
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'No se ha guardado el post, faltan datos'
            ];
        }

        //devolver la respuesta
        return response()->json($data, $data['code']);
    }

    public function update($id, Request $request)
    {
        // recoger los datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        // datos para devolver por defecto
        $data = array(
            'code' => 400,
            'status' => 'error',
            'message' => 'Datos enviados incorrectamente'
        );


        if (!empty($params_array)) {

            // validar los datos
            $validate = \Validator::make($params_array, [
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required',
            ]);
            if ($validate->fails()) {
                $data['errors'] = $validate->errors();
                return response()->json($data, $data['code']);
            }
            // eliminar lo que no queremos actualizar
            unset($params_array['id']);
            unset($params_array['user_id']);
            unset($params_array['created_at']);
            unset($params_array['user']);

            // actulizar el registro
            // $post = Post::where('id', $id)->updateOrCreate($params_array);   // con ->get() obtenemos los objetos actualizados ahora, pero sólo funciona con un query builder

            // conseguir usuario identificado
            $user = $this->getIdentity($request);

            // buscar el registro
            $post = Post::where('id', $id)
                ->where('user_id', $user->sub)
                ->first();

            if (!empty($post) && is_object($post)) {
                // actualizar el registro
                $where = [
                    'id' => $id,
                    'user_id' => $user->sub,
                ];
                $post = Post::updateOrCreate($where, $params_array);


                // devolver respuesta
                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'post' => $post,
                    'changes' => $params_array
                );
            }
            // $where = [
            //     'id' => $id,
            //     'user_id' => $user->sub,
            // ];

            // $post = Post::where('id', $id)
            //             ->where('user_id', $user->sub)
            //             ->update($params_array);
            // $post = Post::find($id);
            // $post = Post::updateOrCreate($where, $params_array);
            // // devolver respuesta
            // $data = array(
            //     'code' => 200,
            //     'status' => 'success',
            //     'post' => $post,
            //     'changes' => $params_array
            // );
        }
        return response()->json($data, $data['code']);
    }

    public function destroy($id, Request $request)
    {
        //conseguir el usuario identificado
        $user = $this->getIdentity($request);

        // conseguir el registro
        // $post = Post::find($id);
        $post = Post::where('id', $id)
            ->where('user_id', $user->sub)
            ->first();

        if (!empty($post)) {
            // eliminar el objeto
            $post->delete();
            // devolver respuesta
            $data = [
                'code' => 200,
                'status' => 'success',
                'post' => $post,
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'El post no existe'
            ];
        }
        return response()->json($data, $data['code']);
    }

    public function upload(Request $request)
    {
        {
            // recoger la imagen de la peticion
            $image = $request->file('file0');
            // validar la imagen
            $validate = \Validator::make($request->all(), [
                'file0' => 'required|image|mimes:jpg,png,jpeg,gif'
            ]);
            // guardar la imagen
            if (!$image || $validate->fails()) {
                $data = [
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Error al subir la imagen'
                ];
            } else {
                $image_name = time() . $image->getClientOriginalName();

                \Storage::disk('images')->put($image_name, \File::get($image));

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'image' => $image_name
                ];
            }
            // devolver la respuesta
            return response()->json($data, $data['code']);
        }
    }

    public function getImage($filename)
    {
        // comprobar si existe el fichero
        $isset = \Storage::disk('images')->exists($filename);
        // var_dump($isset);
        // die();

        if ($isset) {
            // conseguir la imagen
            $file = \Storage::disk('images')->get($filename);
            // devolver la imagen
            return new Response($file, 200);
        } else {
            // mostrar error
            $data = [
                'code' => 404,
                'status' => 'error',
                'message' => 'La imagen no existe'
            ];
        }
        return response()->json($data, $data['code']);
    }

    public function getPostsByCategory($id)
    {
        $posts = Post::where('category_id', $id)->get();

        return response()->json([
            'status' => 'success',
            'posts' => $posts
        ], 200);
    }

    public function getPostsByUser($id)
    {
        $posts = Post::where('user_id', $id)->get();

        return response()->json([
            'status' => 'success',
            'posts' => $posts
        ], 200);
    }
}