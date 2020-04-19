<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;
// si usamos el alias no hace falta importar el provider aquí
use App\Helpers\JwtAuth;

class UserController extends Controller
{
    public function register(Request $request)
    {
        // Recoger los datos por post
        $json = $request->input('json', null);
        $params = json_decode($json); // devuelve objeto
        $params_array = json_decode($json, true); //devuelve array

        if (!empty($params) && !empty($params_array)) {
            // Limpiar datos
            $params_array = array_map('trim', $params_array);

            // var_dump($json);
            // var_dump($params);
            // var_dump($params_array);
            // die();
            // Validar datos
            $validate = \Validator::make($params_array, [
                'name'   => 'required|alpha',
                'surname'   => 'required|alpha',
                'nick'   => 'required|alpha',
                'email'   => 'required|email|unique:users', //Comprobar que el usuario no existe ya (duplicado)
                'password'   => 'required'
            ]);
            if ($validate->fails()) {
                // La validación ha fallado

                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'El usuario no se ha creado',
                    'errors' => $validate->errors()
                );
            } else {
                // La validación ha sido correcta

                // Cifrar la contraseña
                // $pwd = password_hash($params->password, PASSWORD_BCRYPT, ['cost' => 4]);

                $pwd = hash('sha256', $params->password);

                // crear el usuario
                $user = new User();
                $user->name = $params_array['name'];
                $user->surname = $params_array['surname'];
                $user->nick = $params_array['nick'];
                $user->email = $params_array['email'];
                $user->password = $pwd;
                $user->role = 'ROLE_USER';

                // var_dump($user);
                // die();

                // Guardar el usuario
                $user->save();

                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El usuario se ha creado correctamente',
                    'user' => $user,
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Los datos enviados no son correctos',
            );
        }


        // devolver los datos en json
        return response()->json($data, $data['code']);
    }

    public function login(Request $request)
    {
        $jwtAuth = new \JwtAuth();  // usamos el alias para no tener que importar el provider

        // recibir datos por post
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);
        // Validar esos datos
        $validate = \Validator::make($params_array, [
            'email'   => 'required|email', //Comprobar que el usuario no existe ya (duplicado)
            'password'   => 'required'
        ]);
        if ($validate->fails()) {
            // La validación ha fallado

            $signUp = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'El usuario no se ha podido identificar',
                'errors' => $validate->errors()
            );
        } else {
            // Cifrar la password
            $pwd = hash('sha256', $params->password);
            // Devolver token o datos
            $signUp = $jwtAuth->signUp($params->email, $pwd);
            if (!empty($params->getToken)) {
                $signUp = $jwtAuth->signUp($params->email, $pwd, true);
            }
        }

        // $email = 'admin@admin.com';
        // $password = 'pass12PASS';

        // password_hash crea diferentes cifrados cada vez
        // $pwd = password_hash($password, PASSWORD_BCRYPT, ['cost' => 4]);

        // hash en cambio genera siempre el mismo codigo
        // $pwd = hash('sha256', $password);

        // var_dump($pwd);
        // die();

        return response()->json($signUp, 200);
    }

    public function update(Request $request)
    {
        // comprobar si el usuario está identificado
        // usando el middleware que hemos creado
        $token = $request->header('Authorization');
        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        //recoger los datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if ($checkToken && !empty($params_array)) {
            // echo '<h1>Login correcto</h1>';
            // actualizar usuario

            // sacar usuario identificado
            $user = $jwtAuth->checkToken($token, true);
            // var_dump($user);
            // die();

            // validar los datos
            $validate = \Validator::make($params_array, [
                'name'   => 'required|alpha',
                'surname'   => 'required|alpha',
                'nick'   => 'required|alpha',
                'email'   => 'required|email|unique:users, ' .$user->sub, //Comprobar que el usuario no existe, excepto el del usuario que se está identificando
                // 'password'   => 'required'
            ]);

            // quitar los campos que no quiero actualizar
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['remember_token']);

            // actualizar el usuario
            $user_update = User::where('id', $user->sub)->update($params_array);

            // devolver un array con el resultado
            $data = array(
                'code' => 200,
                'status' => 'success',
                'user' => $user,
                'changes' => $params_array
            );
        } else {
            // echo '<h1>Login incorrecto</h1>';
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'El usuario no está identificado',
            );
        }

        // die();
        return response()->json($data, $data['code']);
    }

    public function upload(Request $request)
    {
        // recoger los datos de la petición
        $image = $request->file('file0');
        // var_dump($image);
        // die();

        // validación de la imagen
        $validate = \Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        // Guardar imagen
        if (!$image || $validate->fails()) {
            // var_dump($image);
            // die();
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'La imagen no se ha subido correctamente',
            ];
        } else {
            $image_name = time().$image->getClientOriginalName();
            \Storage::disk('users')->put($image_name, \File::get($image));

            $data = array(
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            );
        }


        return response()->json($data, $data['code']);
    }

    public function getImage($filename)
    {
        $isset = \Storage::disk('users')->exists($filename);
        if ($isset) {
            $file = \Storage::disk('users')->get($filename);
            return new Response($file, 200);
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'La imagen no existe'
            );
            return response()->json($data, $data['code']);
        }
    }

    public function detail($id)
    {
        $user = User::find($id);

        if (is_object($user)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'user' => $user
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'El usuario no existe',
            );
        }
        return response()->json($data, $data['code']);
    }
}