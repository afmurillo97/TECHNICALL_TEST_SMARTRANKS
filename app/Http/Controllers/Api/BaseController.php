<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Info(
 *     title="SmartRanks Technical Test API",
 *     version="1.0.0",
 *     description="A comprehensive RESTful API for SmartRanks jobsearch platform. This API provides endpoints for managing products, categories, and user authentication. Built as part of a technical assessment to demonstrate RESTful API development best practices.",
 *     @OA\Contact(
 *         email="afmurillo97@gmail.com",
 *         name="Felipe Murillo",
 *         url="https://github.com/afmurillo97"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     ),
 *     @OA\ExternalDocumentation(
 *         description="Technical Assessment Documentation",
 *         url="https://github.com/afmurillo97/TECHNICALL_TEST_SMARTRANKS"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://api.smartranks.test",
 *     description="Local Development Server"
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Local Development Server"
 * )
 * 
 * @OA\Server(
 *     url="https://www.technical-test.site",
 *     description="Production Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="JWT token authentication. Include the token in the Authorization header as 'Bearer {token}'"
 * )
 * 
 * @OA\Schema(
 *     schema="User",
 *     title="User Model",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="role", type="string", enum={"user","admin"}, example="user"),
 *     @OA\Property(property="email", type="string", format="email", example="user@smartranks.test"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", example="2023-01-01T00:00:00Z"),
 *     @OA\Property(property="password", type="string", description="Hashed password (read-only)", example="$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi", readOnly=true),
 *     @OA\Property(property="remember_token", type="string", description="Token used for persistent session remember-me functionality", example="YjJQM1NoWXBRT0ZzQzVUeWZPZUxodz09", maxLength=100, readOnly=true),
 *     @OA\Property(property="created_at",type="string",format="date-time",example="2023-01-01T00:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-02-01T00:00:00Z")
 * )
 * 
 * @OA\Schema(
 *     schema="Category",
 *     title="Category Model",
 *     description="Category model",
 *     @OA\Property(property="id", type="integer", example=5),
 *     @OA\Property(property="name", type="string", example="Category Name"),
 *     @OA\Property(property="description", type="string", example="Category description"),
 *     @OA\Property(property="featured_image", type="string", example="http://example.com/image.jpg"),
 *     @OA\Property(property="status", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T00:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-02-01T00:00:00Z")
 * )
 * 
 * @OA\Schema(
 *     schema="Product",
 *     title="Product Model",
 *     description="Product model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="category_id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Product Name"),
 *     @OA\Property(property="category", type="string", example="Category Name"),
 *     @OA\Property(property="sku", type="string", example="PRD-001"),
 *     @OA\Property(property="description", type="string", example="Product description"),
 *     @OA\Property(property="purchase_price", type="number", format="float", example=19.99),
 *     @OA\Property(property="sale_price", type="number", format="float", example=59.99),
 *     @OA\Property(property="stock", type="integer", example=100),
 *     @OA\Property(property="featured_image", type="string", example="http://example.com/image.jpg"),
 *     @OA\Property(property="status", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T00:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-02-01T00:00:00Z")
 * )
 * 
 * @OA\Schema(
 *     schema="CategoryResource",
 *     title="Category Resource",
 *     description="Category resource with transformed data",
 *     @OA\Property(property="id", type="integer", example=7),
 *     @OA\Property(property="category_name", type="string", example="Category Name"),
 *     @OA\Property(property="description_excerpt", type="string", example="Category excerpt"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T00:00:00Z"),
 *     @OA\Property(property="url_image", type="string", example="http://example.com/image.jpg"),
 *     @OA\Property(property="products", type="array", nullable=true, @OA\Items(ref="#/components/schemas/ProductResource"))
 * )
 * 
 * @OA\Schema(
 *     schema="ProductResource",
 *     title="Product Resource",
 *     description="Product resource with transformed data",
 *     @OA\Property(property="id", type="integer", example=7),
 *     @OA\Property(property="name", type="string", example="Product Name"),
 *     @OA\Property(property="category_id", type="integer", example=1),
 *     @OA\Property(property="category", type="string", example="Electronics"),
 *     @OA\Property(property="sku", type="string", example="PRD-001"),
 *     @OA\Property(property="description", type="string", example="Product description"),
 *     @OA\Property(property="price", type="number", format="float", example=19.99),
 *     @OA\Property(property="stock", type="integer", example=100),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T00:00:00Z"),
 *     @OA\Property(property="product_images", type="object", @OA\Property( property="featured_image", type="string", example="http://example.com/image.jpg"))
 * )
 * 
 * @OA\Schema(
 *     schema="CategoryCollection",
 *     title="Category Collection",
 *     description="Collection of categories with metadata",
 *     @OA\Property( property="data", type="array", @OA\Items(ref="#/components/schemas/CategoryResource")),
 *     @OA\Property( property="meta", type="object",
 *        @OA\Property( property="organization", type="string", example="afmurillo97 Company"),
 *        @OA\Property( property="final_tester", type="string", example="SMART RANKS TECHNICAL TEST"),
 *        @OA\Property( property="authors", type="array", @OA\Items(type="string",example="Felipe Murillo"))
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="ProductCollection",
 *     title="Product Collection",
 *     description="Collection of products with metadata",
 *     @OA\Property( property="data", type="array", @OA\Items(ref="#/components/schemas/ProductResource")),
 *     @OA\Property( property="meta", type="object",
 *        @OA\Property( property="organization", type="string", example="afmurillo97 Company"),
 *        @OA\Property( property="final_tester", type="string", example="SMART RANKS TECHNICAL TEST"),
 *        @OA\Property( property="authors", type="array", @OA\Items(type="string",example="Felipe Murillo"))
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="StoreCategoryRequest",
 *     title="Request Structure to Store Category",
 *     required={"name", "status"},
 *     @OA\Property(property="name", type="string", example="Electronics"),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="featured_image", type="string", nullable=true),
 *     @OA\Property(property="status", type="boolean", example=false)
 * )
 * 
 * @OA\Schema(
 *     schema="StoreProductRequest",
 *     title="Request Structure to Store Product",
 *     required={"category_id", "name", "purchase_price", "sale_price", "status"},
 *     @OA\Property(property="category_id", type="number", example="5"),
 *     @OA\Property(property="name", type="string", example="Samsung Galaxy S5"),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="purchase_price", type="float", example=10.55),
 *     @OA\Property(property="sale_price", type="float", example=60.00),
 *     @OA\Property(property="stock", type="number", nullable=true),
 *     @OA\Property(property="featured_image", type="string", nullable=true),
 *     @OA\Property(property="status", type="boolean", example=true)
 * )
 * 
 * @OA\Schema(
 *     schema="UpdateCategoryRequest",
 *     title="Request Structure to Update Category",
 *     @OA\Property(property="name", type="string", example="Electronics Updated"),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="featured_image", type="string", nullable=true),
 *     @OA\Property(property="status", type="boolean", example=true)
 * )
 * 
 * @OA\Schema(
 *     schema="UpdateProductRequest",
 *     title="Request Structure to Update Product",
 *     @OA\Property(property="category_id", type="number", example="5"),
 *     @OA\Property(property="name", type="string", example="Samsung Galaxy S5"),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="purchase_price", type="float", example=40.38),
 *     @OA\Property(property="sale_price", type="float", example=66.50),
 *     @OA\Property(property="stock", type="number", nullable=true),
 *     @OA\Property(property="featured_image", type="string", nullable=true),
 *     @OA\Property(property="status", type="boolean", example=false)
 * )
 * 
 * @OA\Tag(
 *     name="Base",
 *     description="Base controller with common response methods"
 * )
 */
class BaseController extends Controller
{
    /**
     * @OA\Schema(
     *     schema="SuccessResponse",
     *     type="object",
     *     @OA\Property(property="message", type="string", example="Operation successful"),
     *     @OA\Property(
     *         property="response",
     *         type="object",
     *         nullable=true,
     *         additionalProperties=true,
     *         description="Response data object"
     *     )
     * )
     * 
     * @param string $message Success message
     * @param int $status HTTP status code
     * @param mixed|null $res Response data
     * @return JsonResponse
     */
    protected function successResponse(string $message, int $status, $res = null): JsonResponse
    {
        $response = ['message' => $message];
        if (!is_null($res)) {
            $response['response'] = $res;
        }
        return response()->json($response, $status);
    }

    /**
     * @OA\Schema(
     *     schema="ErrorResponse",
     *     type="object",
     *     @OA\Property(property="message", type="string", example="Unauthenticated")
     * )
     * 
     * @param string $message Error message
     * @param int $status HTTP status code
     * @return JsonResponse
     */
    protected function errorResponse(string $message, int $status): JsonResponse
    {
        return response()->json(['error' => $message], $status);
    }
}
