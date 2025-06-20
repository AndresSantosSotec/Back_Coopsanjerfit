<?php
// app/Http/Controllers/NotificationController.php
namespace App\Http\Controllers\Api\WebAdmin;
use App\Models\User;
use App\Notifications\EjercicioReminder;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function send(Request $req)
    {
        $data = $req->validate([
          'title'=>'required|string',
          'body'=>'required|string',
          'filter'=>'required|string',
        ]);

        $users = match($data['filter']) {
          'all'   => User::all(),
          'koala' => User::where('team','KoalaFit')->get(),
          default => User::where('id',$data['filter'])->get(),
        };

        foreach ($users as $u) {
            $u->notify(new EjercicioReminder($data['title'], $data['body']));
        }

        return response()->json(['message' => 'Notificaciones enviadas']);
    }
}
