<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ScheduledExpenses;
use App\Models\ScheduledIncome;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    public function store_income(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "description" => "required|string|max:255",
            "amount" => "required|numeric",
            "start_date" => "required|date",
            "end_date" => "required|date|after_or_equal:start_date",
            "category_id" => "required|exists:categories,id"
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message" => "Validation error",
                "error" => $validator->errors()
            ], 500);
        }

        try {
            // Authenticate user then create income
            $category = Category::where("id", $request->category_id)->where("user_id", Auth::id())->first();

            if (!$category) {
                return response()->json([
                    "error" => "Error: category does not exist or does not belong to you"
                ], 403);
            }

            // Create a new scheduled income
            $income = ScheduledIncome::create([
                "user_id" => Auth::id(),
                "category_id" => $request->category_id,
                "description" => $request->description,
                "amount" => $request->amount,
                "start_date" => $request->start_date,
                "end_date" => $request->end_date
            ]);

            // Send email notification if successfull

            // Return response
            return response()->json([
                "message" => "Scheduled income created successfully",
                "income" => $income
            ], 200);
        } catch (\Exception $e) {
            Log::error("An error occured creating schedueled income: " . $e->getMessage());

            return response()->json([
                "message" => "An error occured creating schedueled income",
                "error" => $e->getMessage()
            ], 500);
        }
    }


    public function store_expenses(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "item" => "required|string",
            "price" => "required|numeric",
            "description" => "nullable|string",
            "quatity" => "nullable|string",
            "date" => "required|date",
            "category_id" => "required|exists:categories,id"
        ]);

        if ($validator->fails()) {
            return response()->json([
                "message" => "Validation error",
                "error" => $validator->errors()
            ], 500);
        }

        try {
            $category = Category::where("id", $request->category_id)->where("user_id", Auth::id())->first();

            if (!$category) {
                return response()->json([
                    "error" => "Error: category does not exist or does not belong to you"
                ], 403);
            }

            $expense = ScheduledExpenses::create([
                "user_id" => Auth::id(),
                "item" => $request->item,
                "price" => $request->price,
                "quantity" => $request->quantity,
                "description" => $request->description,
                "date" => $request->date,
                "category_id" => $request->category_id
            ]);

            // Send email notification if successfull

            return response()->json([
                "message" => "Scheduled expense added successfully!",
                "expense" => $expense
            ], 200);
        } catch (\Exception $e) {
            Log::error("An error occured creating schedueled expenses: " . $e->getMessage());

            return response()->json([
                "message" => "An error occured creating schedueled expenses",
                "error" => $e->getMessage()
            ], 500);
        }
    }
}
