<?php
namespace App\Http\Controllers\Api\AppMobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CollaboratorController extends Controller
{
    public function show(Request $request)
    {
        $collab = $request->user()
                         ->colaborator()                    // relación definida en User
                         ->with(['user','fitcoinAccount']) // si la necesitas
                         ->firstOrFail();

        return response()->json([
          'collaborator' => $collab
        ]);
    }
}
