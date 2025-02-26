<?php 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RelayStatus;

class RelayController extends Controller
{
    public function toggleRelay(Request $request)
    {
        $relay = RelayStatus::first(); 
        
        if (!$relay) {
            $relay = RelayStatus::create(['status' => $request->status]);
        } else {
            $relay->update(['status' => $request->status]);
        }

        return response()->json(["message" => "Relay updated", "status" => $relay->status]);
    }

    public function getRelayStatus()
    {
        $relay = RelayStatus::first();
        if ($relay) {
            return response()->json(["status" => $relay->status]);
        } else {
            return response()->json(["status" => 0]); // Default mati
        }
    }
}