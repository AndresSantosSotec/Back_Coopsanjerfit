<?php

namespace App\Http\Controllers\Api\WebAdmin;

use App\Http\Controllers\Controller;
use App\Models\FitcoinAccount;
use App\Models\FitcoinTransaction;
use App\Models\Colaborator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FitcoinAccountController extends Controller
{
    /**
     * Listar todas las cuentas de CoinFits con su colaborador.
     */
    public function index()
    {
        $accounts = FitcoinAccount::with('colaborator')->get();
        return response()->json($accounts);
    }

    /**
     * Mostrar la cuenta (y balance) de un colaborador en particular,
     * junto con sus transacciones.
     */
    public function show($colaboratorId)
    {
        $account = FitcoinAccount::with('transactions')
            ->where('colaborator_id', $colaboratorId)
            ->firstOrFail();

        return response()->json($account);
    }
}

class FitcoinTransactionController extends Controller
{
    /**
     * Listar las transacciones de CoinFits para un colaborador.
     */
    public function index($colaboratorId)
    {
        $account = FitcoinAccount::where('colaborator_id', $colaboratorId)
            ->firstOrFail();

        return response()->json($account->transactions);
    }

    /**
     * Registrar una nueva transacción (crédito o débito).
     * - type: credit|debit
     * - amount: entero positivo
     * - description: texto opcional
     */
    public function store(Request $request, $colaboratorId)
    {
        $account = FitcoinAccount::firstOrCreate(['colaborator_id' => $colaboratorId]);

        $data = $request->validate([
            'type'        => ['required', Rule::in(['credit', 'debit'])],
            'amount'      => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        // Ajuste del monto según el tipo
        $signedAmount = $data['type'] === 'debit'
            ? -abs($data['amount'])
            : abs($data['amount']);

        // Crear la transacción
        $tx = $account->transactions()->create([
            'type'        => $data['type'],
            'amount'      => $signedAmount,
            'description' => $data['description'] ?? null,
        ]);

        // Actualizar el balance
        $account->balance += $signedAmount;
        $account->save();

        return response()->json($tx, 201);
    }
}
