<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\User;


class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Check if user is authenticated and return all categories

        try {
            $categories = Category::where("user_id", Auth::user()->id)->get();

            return response()->json([
                "message" => "Request successful!",
                "categories" => $categories
            ]);
        } catch (\Exception $e) {
            // Log error and return response
            Log::error("Error retrieving categories: " . $e->getMessage());

            return response()->json([
                "message" => "An error occured retrieving categories.",
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
        $validator = Validator::make($request->all(), [
            "name" => "required|string|max:255",
            "budget" => "nullable|numeric",
        ]);


        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if user is authenticated
        // $user = User::findOrFail($request->user_id);

        if (!Auth::user()->id === $request->user_id) {
            return response()->json([
                "massage" => "Error: Not a valid user..."
            ], 401);
        }

        try {
            $category = Category::create([
                "name" => $request->name,
                "budget" => $request->budget,
                "user_id" => $request->user_id
            ]);

            return response()->json([
                "message" => "Category created successfully",
                "category" => $category
            ], 200);
        } catch (\Exception $e) {
            // Log error and return response
            Log::error("Error creating category: " . $e->getMessage());

            return response()->json([
                "message" => "An error occured creating category.",
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
        try {
            $category = Category::findOrFail($id);

            if (!$category->user_id === Auth::user()->id) {
                return response()->json([
                    "message" => "Error: you are not authorized to update a category"
                ]);
            }

            $category->update($request->all());

            return response()->json([
                "message" => "Category updated successfully",
                "category" => $category
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error updating category: " . $e->getMessage());

            return response()->json([
                "message" => "An error occured updating category.",
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
            $category = Category::findOrFail($id);

            if (!$category->user_id === Auth::user()->id) {
                return response()->json([
                    "message" => "Error: you are not authorized to delete a category"
                ]);
            }

            $category->delete();

            return response()->json([
                "message" => "Category deleted succesfully",
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error deleting category: " . $e->getMessage());

            return response()->json([
                "message" => "An error occured deleting category.",
                "error" => $e->getMessage()
            ], 500);
        }
    }
}
