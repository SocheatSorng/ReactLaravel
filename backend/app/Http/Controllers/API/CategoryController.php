<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        try {
            $categories = Category::all();
            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Name' => 'required|string|max:50',
            'Description' => 'nullable|string',
            'Image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $categoryData = $request->only(['Name', 'Description']);
            
            // Handle image upload
            if ($request->hasFile('Image')) {
                $image = $request->file('Image');
                $imageName = 'category_' . Str::uuid() . '_' . time() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/uploads/categories', $imageName);
                $categoryData['Image'] = 'storage/uploads/categories/' . $imageName;
            }
            
            $category = Category::create($categoryData);
            
            return response()->json([
                'success' => true,
                'data' => $category,
                'message' => 'Category created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $category = Category::find($id);
            
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'Name' => 'sometimes|required|string|max:50',
            'Description' => 'nullable|string',
            'Image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $category = Category::find($id);
            
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }
            
            // Update fields
            if ($request->has('Name')) {
                $category->Name = $request->Name;
            }
            
            if ($request->has('Description')) {
                $category->Description = $request->Description;
            }
            
            // Handle image upload
            if ($request->hasFile('Image')) {
                // Delete old image if exists
                if ($category->Image && Storage::exists('public/' . str_replace('storage/', '', $category->Image))) {
                    Storage::delete('public/' . str_replace('storage/', '', $category->Image));
                }
                
                $image = $request->file('Image');
                $imageName = 'category_' . Str::uuid() . '_' . time() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/uploads/categories', $imageName);
                $category->Image = 'storage/uploads/categories/' . $imageName;
            }
            
            $category->save();
            
            return response()->json([
                'success' => true,
                'data' => $category,
                'message' => 'Category updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $category = Category::find($id);
            
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }
            
            // Check if category has books
            $bookCount = Book::where('CategoryID', $id)->count();
            if ($bookCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete category with associated books'
                ], 400);
            }
            
            // Delete image if exists
            if ($category->Image && Storage::exists('public/' . str_replace('storage/', '', $category->Image))) {
                Storage::delete('public/' . str_replace('storage/', '', $category->Image));
            }
            
            $category->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Get books by category
    public function books($id)
    {
        try {
            $category = Category::find($id);
            
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }
            
            $books = Book::where('CategoryID', $id)->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'category' => $category,
                    'books' => $books
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}