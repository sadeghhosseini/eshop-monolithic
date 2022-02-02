<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;


class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        /* $this->reportable(function (Throwable $e) {
            Log::debug('nice');
            Log::debug(get_class($e));
        }); */

        $this->renderable(function (Throwable $e) {
            if (App::environment(['local'])) {
                Log::debug(get_class($e));//to log all the exception for development
            }
        });

        /* $this->renderable(function(Throwable $exception, Request $request) {
            
            if ($request->is('api/*')) {
                if ($exception instanceof ValidationException) {
                    return response()->json([
                        'errors'=> $exception->errors(),
                    ], 400);
                } else if ($exception instanceof HttpException) {
                    return response()->json($exception->getMessage(), $exception->getStatusCode());
                }
            }
        }); */
        
        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Not Found.'
                ], 404);
            }
        });
        
        $this->renderable(function (AccessDeniedHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'This Action Is Unauthorized.',
                ], 403);
            }
        });

        $this->renderable(function (MethodNotAllowedHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Method Not Allowed.'
                ], 405);
            }
        });
        
        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthenticated',
                ], 401);
            }
        });
    }



    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        if ($request->is('api/*')) {
            throw new HttpResponseException(response()->json($e->validator->errors(), 400));
        }
    }
}
