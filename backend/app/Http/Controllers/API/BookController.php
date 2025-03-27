<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BookController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Book::with('category');
            
            // Apply filters
            if ($request->has('category_id')) {
                $query->byCategory($request->category_id);
            }
            
            // Search functionality
            if ($request->has('search')) {
                $query->search($request->search);
            }
            
            // Price range filter
            if ($request->has('min_price')) {
                $query->where('Price', '>=', $request->min_price);
            }
            
            if ($request->has('max_price')) {
                $query->where('Price', '<=', $request->max_price);
            }
            
            // Sorting
            $sortField = $request->get('sort_by', 'CreatedAt');
            $sortDirection = $request->get('sort_direction', 'desc');
            
            // Map API sort fields to database fields if needed
            $sortFieldMap = [
                'title' => 'Title',
                'author' => 'Author',
                'price' => 'Price',
                'created_at' => 'CreatedAt'
            ];
            
            $dbSortField = $sortFieldMap[$sortField] ?? 'CreatedAt';
            
            $query->orderBy($dbSortField, $sortDirection === 'asc' ? 'asc' : 'desc');
            
            // Pagination
            $perPage = $request->get('per_page', 15);
            $books = $query->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $books->items(),
                'meta' => [
                    'total' => $books->total(),
                    'per_page' => $books->perPage(),
                    'current_page' => $books->currentPage(),
                    'last_page' => $books->lastPage(),
                ]
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
            'CategoryID' => 'nullable|exists:tbCategory,CategoryID',
            'Title' => 'required|string|max:255',
            'Author' => 'required|string|max:100',
            'Price' => 'required|numeric|min:0',
            'StockQuantity' => 'nullable|integer|min:0',
            'Image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            // Book detail validation if needed
            'ISBN10' => 'nullable|string|max:10',
            'ISBN13' => 'nullable|string|max:17',
            'Publisher' => 'nullable|string|max:255',
            'PublishYear' => 'nullable|integer|min:1800|max:' . (date('Y') + 1),
            'Edition' => 'nullable|string|max:50',
            'PageCount' => 'nullable|integer|min:1',
            'Language' => 'nullable|string|max:50',
            'Format' => 'nullable|in:Hardcover,Paperback,Ebook,Audiobook',
            'Dimensions' => 'nullable|string|max:100',
            'Weight' => 'nullable|numeric|min:0',
            'Description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Handle image upload
            $imagePath = null;
            if ($request->hasFile('Image')) {
                $image = $request->file('Image');
                $imageName = 'book_' . Str::uuid() . '_' . time() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/uploads/books', $imageName);
                $imagePath = 'storage/uploads/books/' . $imageName;
            }

            // Create the book
            $bookData = $request->only([
                'CategoryID', 'Title', 'Author', 'Price', 'StockQuantity'
            ]);
            
            if ($imagePath) {
                $bookData['Image'] = $imagePath;
            }
            
            $book = Book::create($bookData);
            
            // Create book details if provided and if you have BookDetail model
            $bookDetailFields = [
                'ISBN10', 'ISBN13', 'Publisher', 'PublishYear', 'Edition',
                'PageCount', 'Language', 'Format', 'Dimensions', 'Weight', 'Description'
            ];
            
            if ($request->hasAny($bookDetailFields) && class_exists('App\Models\BookDetail')) {
                $bookDetailData = $request->only($bookDetailFields);
                $bookDetailData['BookID'] = $book->BookID;
                BookDetail::create($bookDetailData);
            }
            
            return response()->json([
                'success' => true,
                'data' => $book->load('category'),
                'message' => 'Book created successfully'
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
            $book = Book::with(['category', 'bookDetail'])->find($id);
            
            if (!$book) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book not found'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $book
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
            'CategoryID' => 'nullable|exists:tbCategory,CategoryID',
            'Title' => 'sometimes|required|string|max:255',
            'Author' => 'sometimes|required|string|max:100',
            'Price' => 'sometimes|required|numeric|min:0',
            'StockQuantity' => 'nullable|integer|min:0',
            'Image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            // Book detail validation
            'ISBN10' => 'nullable|string|max:10',
            'ISBN13' => 'nullable|string|max:17',
            'Publisher' => 'nullable|string|max:255',
            'PublishYear' => 'nullable|integer|min:1800|max:' . (date('Y') + 1),
            'Edition' => 'nullable|string|max:50',
            'PageCount' => 'nullable|integer|min:1',
            'Language' => 'nullable|string|max:50',
            'Format' => 'nullable|in:Hardcover,Paperback,Ebook,Audiobook',
            'Dimensions' => 'nullable|string|max:100',
            'Weight' => 'nullable|numeric|min:0',
            'Description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $book = Book::find($id);
            
            if (!$book) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book not found'
                ], 404);
            }

            // Handle image upload
            if ($request->hasFile('Image')) {
                // Delete old image if it exists
                if ($book->Image && Storage::exists('public/' . str_replace('storage/', '', $book->Image))) {
                    Storage::delete('public/' . str_replace('storage/', '', $book->Image));
                }
                
                $image = $request->file('Image');
                $imageName = 'book_' . Str::uuid() . '_' . time() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/uploads/books', $imageName);
                $book->Image = 'storage/uploads/books/' . $imageName;
            }

            // Update book data
            $bookFields = ['CategoryID', 'Title', 'Author', 'Price', 'StockQuantity'];
            foreach ($bookFields as $field) {
                if ($request->has($field)) {
                    $book->$field = $request->$field;
                }
            }
            $book->save();
            
            // Update book details if provided
            $bookDetailFields = [
                'ISBN10', 'ISBN13', 'Publisher', 'PublishYear', 'Edition',
                'PageCount', 'Language', 'Format', 'Dimensions', 'Weight', 'Description'
            ];
            
            if ($request->hasAny($bookDetailFields) && class_exists('App\Models\BookDetail')) {
                // Find or create book detail
                $bookDetail = BookDetail::firstOrNew(['BookID' => $book->BookID]);
                
                foreach ($bookDetailFields as $field) {
                    if ($request->has($field)) {
                        $bookDetail->$field = $request->$field;
                    }
                }
                
                $bookDetail->save();
            }
            
            return response()->json([
                'success' => true,
                'data' => $book->load('category', 'bookDetail'),
                'message' => 'Book updated successfully'
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
            $book = Book::find($id);
            
            if (!$book) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book not found'
                ], 404);
            }
            
            // Delete associated image
            if ($book->Image && Storage::exists('public/' . str_replace('storage/', '', $book->Image))) {
                Storage::delete('public/' . str_replace('storage/', '', $book->Image));
            }
            
            // Delete associated details if needed
            if (class_exists('App\Models\BookDetail')) {
                BookDetail::where('BookID', $id)->delete();
            }
            
            $book->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Book deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    // Featured books
    public function featured()
    {
        try {
            $featuredBooks = Book::with('category')
                ->orderBy('CreatedAt', 'desc')
                ->take(8)
                ->get();
                
            return response()->json([
                'success' => true,
                'data' => $featuredBooks
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    // Related books
    public function related($id)
    {
        try {
            $book = Book::find($id);
            
            if (!$book) {
                return response()->json([
                    'success' => false,
                    'message' => 'Book not found'
                ], 404);
            }
            
            // Get books in the same category
            $relatedBooks = Book::where('BookID', '!=', $id)
                ->where('CategoryID', $book->CategoryID)
                ->with('category')
                ->take(4)
                ->get();
                
            return response()->json([
                'success' => true,
                'data' => $relatedBooks
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}