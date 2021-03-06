<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use JWTAuth;
use App\Services\UserService;
use App\Http\Requests\UserRegistrationRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use Mail;

class UserController extends Controller
{
    /**
     * @var UserService
     */
    protected $userService;

    /**
     * PurchaseController constructor.
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @param LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function authenticate(LoginRequest $request)
    {
        return $this->userService->authenticate($request);
    }

    /**
     * @param \App\Http\Requests\UserRegistrationRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(UserRegistrationRequest $request)
    {
        return $this->userService->register($request);

    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAuthenticatedUser()
    {
        return $this->userService->getAuthenticatedUser();
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserProfile(Request $request)
    {
        return $this->userService->getUserProfile($request);
    }    

    public function getUserInfo(Request $request)
    {        
        return $this->userService->findUserInfo($request);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTokenRefresh()
    {
        return $this->userService->getTokenRefresh();
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function emailVerify(Request $request)
    {
        return $this->userService->emailVerify($request);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function switchAccount(Request $request)
    {

        return $this->userService->switchAccount($request);

    }

    /**
     * @param ChangePasswordRequest $request
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        return $this->userService->changePassword($request->all());
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $token = $request->header('Authorization');
        return $this->userService->logout($token);
    }

    public function deleteAccount()
    {
        return $this->userService->deleteUserAccount();
    }

    public function sendEmail()
    {
        $to_name = 'Matrimonial assist';
        $to_email = 'ahabib@bs-23.net';

        $details = [
            'title' => 'Mail from matrimonial-assist.com',
            'body' => 'This is for testing email using smtp'
        ];

        Mail::to($to_email)->send(new \App\Mail\MyTestMail($details));

        dd("Email is Sent.");
    }
}
