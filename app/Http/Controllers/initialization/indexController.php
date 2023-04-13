<?php

namespace App\Http\Controllers\initialization;

use App\Http\Controllers\BD\getPersonal;
use App\Http\Controllers\Config\Lib\VendorApiController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class indexController extends Controller
{
    public function initialization(Request $request): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {
        $contextKey = $request->contextKey;
        if ($contextKey == null) {
            return view("main.dump");
        }
        $vendorAPI = new VendorApiController();
        $employee = $vendorAPI->context($contextKey);
        $accountId = $employee->accountId;

        $isAdmin = $employee->permissions->admin->view;

        return to_route('main', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function index(Request $request, $accountId): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {

        $isAdmin = $request->isAdmin;
        $getPersonal = new getPersonal($accountId);
        if ($getPersonal->status == "деактивированный" or $getPersonal->status == null){
            $hideOrShow = "show";
        } else  $hideOrShow = "hide";

        return view("main.index" , [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
            'hideOrShow' => $hideOrShow,
        ] );

    }

}
