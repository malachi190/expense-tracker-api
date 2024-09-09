<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Income;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class IncomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $category = Category::where("user_id", Auth::user()->id)->get();
            $income = Income::whereIn("category_id", $category->pluck("id"))->get();


            return response()->json([
                "message" => "Request successfull",
                "income" => $income
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error fetching income" . $e->getMessage());

            return response()->json([
                "message" => "An error occured getting income"
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "income" => "required|numeric",
            "description" => "required|string",
            "date" => "nullable|date"
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if user is authenticated
        $user = Auth::user();
        $category = Category::findOrFail($request->category_id);

        if (!$user->categories->contains($category)) {
            return response()->json([
                "message" => "Error: You do not have permission to create income for this category."
            ], 403);
        }

        // Create income
        try {
            $income = Income::create([
                "income" => $request->income,
                "description" => $request->description,
                "date" => $request->date ?: Carbon::now(),
                "category_id" => $category->id
            ]);

            return response()->json([
                "message" => "Income created successfully",
                "income" => $income
            ], 200);
        } catch (\Exception $e) {
            // Log error and return response
            Log::error("Error creating expense: " . $e->getMessage());

            return response()->json([
                "message" => "An error occured creating expense.",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Check if user is authenticated and update
        try {
            $income = Income::findOrFail($id);

            if (!$income->category->user_id === Auth::user()->id) {
                return response()->json([
                    "message" => "Error: you do not have permission to update this income"
                ], 403);
            }

            $income->update($request->all());

            return response()->json([
                "message" => "Income updated successfully",
                "income" => $income
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error updating income (ID: $id):" . $e->getMessage());

            return response()->json([
                "message" => "An error occured updating income.",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            // Check if user is authenticated and carry out deletion

            $income = Income::findOrFail($id);

            if (!$income->category->user_id === Auth::user()->id) {
                return response()->json([
                    "message" => "Error: you do not have permission to delete this income"
                ], 403);
            }

            $income->delete();

            return response()->json([
                "message" => "Income deleted successfully",
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error deleting income (ID: $id):" . $e->getMessage());

            return response()->json([
                "message" => "An error occured deleting income.",
                "error" => $e->getMessage()
            ], 500);
        }
    }
}
