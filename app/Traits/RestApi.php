<?php

namespace App\Traits;

use App\Api\V1\Controllers\Qsc3;
use App\Api\V1\Controllers\Qsc2;
use App\Api\V1\Controllers\Qsc4;
use Illuminate\Http\Response;

use App\Http\Requests;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

if (!defined('LARAVEL_START')) define('LARAVEL_START', microtime(true));

trait RestApi
{
    // Set default isArray false
    protected $isArray = false;
    // Set default pagination false
    protected $paginaton = false;
    /**
     * Generate output data
     * @param  array  $data
     * @return array Response
     */
    function output($data, $success_response = 'Success', $success_code = 200)
    {
        // If responsse should array then return empty array
        // when data not available
        $emptyResponse = $this->isArray ? [] : '';
        $output['meta'] = [
            'code' => 200,
            'message' => 'No data available',
            'response_time' => microtime(true) - LARAVEL_START,
            'response_date' => Carbon::parse(date('Y-m-d H:i:s'))->toISOString()
        ];

        $output['data'] = isset($data['data']) ? $data['data'] : $emptyResponse;
        if (isset($data['pagination'])) {
            $output['pagination'] = $data['pagination'];
        }

        if (is_object($data))
            $data = $data->toArray();

        if (!empty($data)) {
            $output['meta'] = [
                'code' => $success_code,
                'message' => $success_response,
                'response_time' => microtime(true) - LARAVEL_START,
                'response_date' => Carbon::parse(date('Y-m-d H:i:s'))->toISOString()
            ];

            $output['data'] = isset($data['data']) ? $data['data'] : $data;

            // if (isset($data['data']) && $this->pagination)
            //     $output['pagination'] = array_except($data, 'data');

            if (!empty($this->pagination))
                $output['pagination'] = $this->pagination;

            return response()->json($output, $output['meta']['code']);
        }

        return response()->json($output, $output['meta']['code']);
    }

    /**
     * @param  Illuminate\Http\Request $request
     * @param  array $config
     * @param  string $message
     * @return array
     */
    function validateRequest($request, $config, $message = '')
    {
        // if (is_null($request)) {
        //     header('HTTP/1.0 400 Bad Request');
        //     header('Cache-Control: no-cache');
        //     header('Content-Type:  application/json');

        //     exit($this->errorRequest(400, 'Please check all input', [], true));
        // }

        $request = is_array($request) ? $request : (array) $request;
        $validate = Validator::make($request, $config);

        if ($validate->fails()) {
            // exit($this->errorRequest(422, 'User Not Found'));
            return $validate->errors()->toArray();
            // $this->errorValidation($validate->errors()->toArray());
            // die();
        }
    }

    /**
     * @param  integer $code
     * @param  string $message
     * @param  array $message_aray
     * @param  boolean $echo
     * @return JSON string if echo TRUE else JSON
     */
    function errorRequest($code = '', $message = '', $message_array = [], $echo = false)
    {
        switch ($code) {
            case 400:
                $httpMessage = 'Bad Request';
                break;

            case 401:
                $httpMessage = 'Unauthorized';
                break;

            case 403:
                $httpMessage = 'Forbidden';
                break;

            case 404:
                $httpMessage = 'Not Found';
                break;

            case 405:
                $httpMessage = 'Method Not Allowed';
                break;

            case 422:
                $httpMessage = 'Unprocessable Entity';
                break;

            case 500:
                $httpMessage = 'Internal Server Error';
                break;

            default:
                $httpMessage = 'Internal server error';
                break;
        }

        $output['meta'] = [
            'code' => $code,
            'message' => empty($message) ? $httpMessage : $message,
            'message_array' => $message_array,
            'response_time' => microtime(true) - LARAVEL_START,
            'response_date' => date('Y-m-d H:i:s')
        ];

        if ($echo == true) {
            return json_encode($output);
        }

        return response()->json($output, $output['meta']['code']);
    }

    /**
     * @param  array $errors
     * @return JSON
     */
    function errorValidation(array $errors)
    {
        $errorArray = [];
        $errorFlatten = array_flatten($errors);

        foreach ($errors as $key => $value) {
            $errorArray[$key] = array_first($value);
        }

        $output['meta'] = [
            'code' => 422,
            'message' => array_first($errorFlatten),
            'message_array' => $errorArray,
            'response_time' => microtime(true) - LARAVEL_START,
            'response_date' => Carbon::parse(date('Y-m-d H:i:s'))->toISOString()
        ];

        return response()->json($output, 422);
    }

    function hash_filename($filename = '')
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 10; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString . date('Y-m-d-H-i-s') . random_int(10, 99);
    }

    function clean($string)
    {
        // $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens
        return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }
}
