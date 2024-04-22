<?php

namespace App\Http\Middleware\IU;

use App\Repositories\IU\IuCertificateRepository;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class IuUserOwnsCertificate
{
    /**
     * @var IuCertificateRepository
     */
    private $iuCertificateRepository;

    /**
     * IuUserOwnsCertificate constructor.
     * @param IuCertificateRepository $iuCertificateRepository
     */
    public function __construct(IuCertificateRepository $iuCertificateRepository)
    {
        $this->iuCertificateRepository = $iuCertificateRepository;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $certificate = $this->iuCertificateRepository->getCertificate($request->id);

        if(!$certificate)
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        if($certificate->user_id != $request->user()->id)
            return response()->json(['errors' => Lang::get('auth.forbidden')], 403);

        return $next($request->merge(array("certificate" => $certificate)));
    }
}
