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
        // "/api/login",
        // "/api/logout",
        // "/api/register",
        // "/api/kas/getEskulKas",
        // "/api/kas/storeKas",
        // "/api/absent/getEskulAbsenByCode",
        // "/api/absent/storeAbsen",
        // "/api/absent/getUserByName",
        // "/api/absent/editAbsen",
        // "/api/absent/deleteAbsen",
        // "/api/auth/googleSignIn",
        // "/api/webProfile/store",
        // "/api/dashboard/i/getEskulInstansi",
        // "/api/dashboard/i/getUserInstansi",
        // "/api/dashboard/i/addUser",
        // "/api/dashboard/i/editUser",
        // "/api/dashboard/i/updateHakAkses",
        // "/api/dashboard/o/storeEskulMember",
        // "/api/dashboard/o/eskul-report-activities",
        // "/api/dashboard/o/webprofile/storeNavbar",
        // "/api/dashboard/o/webprofile/storeGallery",
        // "/api/dashboard/o/webprofile/storeJumbotron",
        // "/api/dashboard/o/webprofile/storeAboutUs",
        // "/api/dashboard/o/webprofile/getEskulWebPage",
        // "/api/getEskulWebPageUrl",
        // "/api/dashboard/o/webprofile/storeActivitiesEskulItem",
        // "/api/dashboard/o/webprofile/storeActivitiesDesc",
        // "/api/eskul/store",
        // "/api/eskul/trash",
        // "/api/eskul/restore",
        // "/api/webprofile/getProfileInfoWithDomain",
        // "/api/webprofile/getEskulInstansiPublic",
        "*"
    ];
}
