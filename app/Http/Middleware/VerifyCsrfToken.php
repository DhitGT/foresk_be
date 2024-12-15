<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
        "/api/login",
        "/api/register",
        "/api/webProfile/store",
        "/api/dashboard/i/getEskulInstansi",
        "/api/dashboard/i/getUserInstansi",
        "/api/dashboard/i/addUser",
        "/api/dashboard/i/editUser",
        "/api/dashboard/i/updateHakAkses",
        "/api/dashboard/o/webprofile/storeNavbar",
        "/api/dashboard/o/webprofile/storeJumbotron",
        "/api/dashboard/o/webprofile/storeAboutUs",
        "/api/eskul/store",
        "/api/eskul/trash",
        "/api/eskul/restore",
        "/api/webprofile/getProfileInfoWithDomain",
        "/api/webprofile/getEskulInstansiPublic",
    ];
}
