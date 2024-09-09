<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Check if user is authenticated and carry out operation

        try {
            $categories = Category::where("user_id", Auth::user()->id)->get();
            // dd($categories);
            $expenses = Expense::whereIn("category_id", $categories->pluck("id"))->get();

            return response()->json([
                "message" => "Request successful!",
                "expenses" => $expenses
            ]);
        } catch (\Exception $e) {
            // Log error and return response
            Log::error("Error retrieving expenses: " . $e->getMessage());

            return response()->json([
                "message" => "An error occured retrieving expenses.",
                "error" => $e->getMessage()
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
        // Validate expense input
        $validator = Validator::make($request->all(), [
            "item" => "required|string|max:255",
            "price" => "required|numeric|min:0",
            "quantity" => "required|integer",
            "description" => "string|max:1000",
            "date" => "nullable|string",
            "category_id" => "required|integer|exists:categories,id"
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
                "message" => "Error: You do not have permission to create expenses for this category."
            ], 403);
        }

        // Create expense
        try {
            $expense = Expense::create([
                "item" => $request->item,
                "price" => $request->price,
                "quantity" => $request->quantity,
                "description" => $request->description,
                "date" => $request->date ?: Carbon::now(),
                "category_id" => $category->id
            ]);

            return response()->json([
                "message" => "Expense created successfully",
                "expense" => $expense
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
        try {
            // Check if user is authenticated and return expense

            $expense = Expense::findOrFail($id);

            if (!$expense->category->user_id === Auth::user()->id) {
                return response()->json([
                    "message" => "Error: you do not have permission to carry out this operation"
                ], 403);
            }


            return response()->json([
                "message" => "Request successfull",
                "expense" => $expense
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                "message" => "Expense not found",
                "error" => $e->getMessage()
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error fetching expense (ID: $id): " . $e->getMessage());

            return response()->json([
                "message" => "An error occurred while fetching expense.",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add expense to category.
     */
    public function add_to_category(string $expense_id, string $category_id)
    {
        try {
            // Check if user is authenticated and carry out operation

            // Retrieve the expense and category
            $expense = Expense::findOrFail($expense_id);
            $category = Category::findOrFail($category_id);

            if (!$expense->category->user_id === Auth::user()->id) {
                return response()->json([
                    "message" => "Error: you do not have permission to carry out this operation"
                ], 403);
            }

            // Associate the expense with the category
            $expense->category_id = $category->id;
            $expense->save();

            return response()->json([
                "message" => "Expense added to category successfully!"
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                "message" => "Expense or category not found."
            ], 404);
        } catch (\Exception $e) {
            Log::error("Error adding expense to category: " . $e->getMessage());

            return response()->json([
                "message" => "An error occurred while adding expense to category.",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Check if user is authenticated and update
        try {
            $expense = Expense::findOrFail($id);

            if (!$expense->category->user_id === Auth::user()->id) {
                return response()->json([
                    "message" => "Error: you do not have permission to update this expense"
                ], 403);
            }

            $expense->update($request->all());

            return response()->json([
                "message" => "Expense updated successfully",
                "expense" => $expense
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error updating expense (ID: $id):" . $e->getMessage());

            return response()->json([
                "message" => "An error occured updating expense.",
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

            $expense = Expense::findOrFail($id);

            if (!$expense->category->user_id === Auth::user()->id) {
                return response()->json([
                    "message" => "Error: you do not have permission to delete this expense"
                ], 403);
            }

            $expense->delete();

            return response()->json([
                "message" => "Expense deleted successfully",
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error deleting expense (ID: $id):" . $e->getMessage());

            return response()->json([
                "message" => "An error occured deleting expense.",
                "error" => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Set up recurring transactions.
     */

     public function recurring_expense(){
        
     }


     public function recurring_income(){

     }
}
