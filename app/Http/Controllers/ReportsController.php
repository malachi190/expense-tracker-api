<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReportsController extends Controller
{
    public function check_net_savings()
    {
        try {
            $categories = Category::where("user_id", Auth::id())->pluck("id");

            if ($categories->isEmpty()) {
                return response()->json([
                    "message" => "Category does not exist or you do not have permission to access it.",
                ], 403);
            }

            $expenses = Expense::whereIn("category_id", $categories)->get();
            $incomes = Income::whereIn("category_id", $categories)->get();

            // Sum total price and incomes
            $total_expense = $expenses->sum("price");
            $total_income = $incomes->sum("income");

            // Find the difference between expense and income
            $net_save = $total_income - $total_expense;

            // Return response with net save
            return response()->json([
                "message" => "Request successfull!",
                "saved" => $net_save
            ]);
        } catch (\Exception $e) {
            Log::error("Error getting net savings" . $e->getMessage(), [
                "line" => $e->getLine(),
                "file" => $e->getFile()
            ]);

            return response()->json([
                "message" => "An error occured getting net savings.",
                "error" => $e->getMessage()
            ]);
        }
    }
}
