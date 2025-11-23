<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    // Register new user
    public function register(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email'=> $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Generate JWT token
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60
            ], 201);

        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate token',
                'error' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Login user
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not create token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get authenticated user
    public function me()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            return response()->json([
                'success' => true,
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid or expired',
                'error' => $e->getMessage()
            ], 401);
        }
    }

    public function products()
    {
        try {
            // Attempt to fetch products
            $products = DB::table('products')->get(); // Using query builder is safer than raw SQL

            return response()->json([
                'success' => true,
                'message' => 'Products retrieved successfully',
                'products' => $products
            ], 200);

        } catch (\Exception $e) {
            // Catch any database or unexpected error
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products. Database might be down.',
                'error' => $e->getMessage() // Optional: include exception message for debugging
            ], 500);
        }
    }

    public function product($id)
    {
        try {
            // Find the product by ID
            $product = \App\Models\Product::find($id);

            if (!$product) {
                return response()->json([
                    "success" => false,
                    "message" => "Product not found"
                ], 404);
            }

            return response()->json([
                "success" => true,
                "message" => "Product retrieved successfully",
                "product" => $product
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Database error: " . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $user = $request->user();
            $total = 0;

            // Check stock
            foreach ($request->products as $item) {
                $product = Product::findOrFail($item['id']);
                if ($product->stock < $item['quantity']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for product: {$product->name}"
                    ], 400);
                }
                $total += $product->price * $item['quantity'];
            }

            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'address' => $request->address,
                'phone' => $request->phone,
                'total' => $total,
            ]);

            // Create order items & decrease stock
            foreach ($request->products as $item) {
                $product = Product::findOrFail($item['id']);
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);

                $product->stock -= $item['quantity'];
                if ($product->stock <= 0) {
                    $product->out_of_stock = 1;
                }
                $product->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'order_number' => $order->id,
                'total' => $total,
                'items' => $order->items()->with('product')->get()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to place order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function order($id)
    {
        /*
            select users.name as username , o.address , o.phone , p.name as product_name , p.price as product_price , oi.quantity , o.total as total_price
            from orders o , users , order_items oi , products p
            where users.id = o.user_id and o.id = oi.order_id and p.id = oi.product_id and users.id = (auth_user_id) and o.id = $id
        */

        try {
            // Authenticate user
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Fetch the order header (validate ownership)
            $order = DB::table('orders')
                ->where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found or unauthorized'
                ], 404);
            }

            // Fetch order items (products within the order)
            $items = DB::table('order_items as oi')
                ->join('products as p', 'p.id', '=', 'oi.product_id')
                ->select('p.name as product_name', 'p.price as product_price', 'oi.quantity')
                ->where('oi.order_id', $id)
                ->get();

            return response()->json([
                'success' => true,
                'order' => [
                    'order_id' => $order->id,
                    'username' => $user->name,
                    'address' => $order->address,
                    'phone' => $order->phone,
                    'total_price' => $order->total,
                    'items' => $items
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching order details',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    public function my_orders()
    {
        try {
            // Get the authenticated user
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Fetch orders for this user
            $orders = DB::table('orders')
                ->where('user_id', $user->id)
                ->get();

            return response()->json([
                'success' => true,
                'orders' => $orders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not retrieve orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update_order(Request $request, $id)
    {
        /*
        update orders set address = (address input field in Vue) and phone = (phone number input field in Vue)
        where id = $id and user_id = (auth_user_id)
        */

        $request->validate([
            'address' => 'required|string|max:255',
            'phone'   => 'required|string|max:20',
        ]);

        try {
            // Get authenticated user
            $user = JWTAuth::parseToken()->authenticate();

            // Find the order & ensure it belongs to user
            $order = Order::where('id', $id)
                        ->where('user_id', $user->id)
                        ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found or unauthorized'
                ], 404);
            }

            // Update order info
            $order->address = $request->address;
            $order->phone   = $request->phone;
            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully',
                'order' => $order
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order',
                'error' => $e->getMessage()
            ], 500);
        }


    }

    public function delete_order($id)
    {
        /*
        delete from orders where id = $id and user_id = (auth_user_id)
        */

        try {
            // Authenticate user
            $user = JWTAuth::parseToken()->authenticate();

            // Check if order exists & belongs to user
            $order = Order::where('id', $id)
                        ->where('user_id', $user->id)
                        ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found or unauthorized'
                ], 404);
            }

            // Delete order items first (FK constraint)
            OrderItem::where('order_id', $id)->delete();

            // Delete order header
            $order->delete();

            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete order',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // Logout user
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json([
                'success' => true,
                'message' => 'Successfully logged out'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to logout',
                'error' => $e->getMessage()
            ], 401);
        }
    }

    // Refresh token
    public function refresh()
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());
            return response()->json([
                'success' => true,
                'access_token' => $newToken,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token refresh failed',
                'error' => $e->getMessage()
            ], 401);
        }
    }
}



/*

select users.name as username , o.address , o.phone , p.name as product_name , p.price as product_price , oi.quantity , o.total as total_price
from orders o , users , order_items oi , products p
where users.id = o.user_id and o.id = oi.order_id and p.id = oi.product_id and users.id = 9 and o.id = 3

*/