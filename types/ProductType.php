<?php

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;

class ProductType extends ObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'Product',
            'description' => 'Data produk',
            'fields' => function() {
                return [
                    'id' => [
                        'type' => Types::nonNull(Types::int()),
                        // @FIXME: nggak ngerti kenapa 'id' ini mesti di resolve dulu
                        'resolve' => function($value) {
                            return (int) $value->id;
                        }
                    ],
                    'slug' => [
                        'type' => Types::string()
                    ],
                    'name' => [
                        'type' => Types::string()
                    ],
                    'stock' => [
                        'type' => Types::int()
                    ],
                    'price' => [
                        'type' => Types::int(),
                        'description' => 'Harga dalam rupiah'
                    ],
                    'weight' => [
                        'type' => Types::int(),
                        'description' => 'Berat dalam gram'
                    ],
                    'description' => [
                        'type' => Types::string()
                    ],
                    'thumbnail' => [
                        'type' => Types::string()
                    ],
                    'url_thumbnail' => [
                        'type' => Types::string()
                    ],
                    'category' => [
                        'type' => Types::productCategory()
                    ],
                    'images' => [
                        'type' => Types::listOf(Types::productImage()),
                        'description' => 'Daftar image produk'
                    ],
                    'reviews' => [
                        'type' => Types::listOf(Types::productReview()),
                    ]
                ];
            },
            'resolveField' => function($value, $args, $context, ResolveInfo $info) {
                if (method_exists($this, $info->fieldName)) {
                    return $this->{$info->fieldName}($value, $args, $context, $info);
                } else {
                    return is_numeric($value->{$info->fieldName})? (int) $value->{$info->fieldName} : $value->{$info->fieldName};
                }
            }
        ];
        parent::__construct($config);
    }

    public function url_thumbnail($value)
    {
        return BASE_URL.'/products/thumbnail/'.$value->thumbnail;
    }

    public function category($value, $args, $context)
    {
        $pdo = $context['pdo'];
        $category_id = $value->category_id;
        $result = $pdo->query("select * from product_category where id = {$category_id}");
        return $result->fetchObject();
    }

    public function images($value, $args, $context)
    {
        $pdo = $context['pdo'];
        $product_id = $value->id;
        $result = $pdo->query("select * from product_images where product_id = {$product_id}");
        return $result->fetchAll(PDO::FETCH_OBJ);
    }

    public function reviews($value, $args, $context)
    {
        $pdo = $context['pdo'];
        $product_id = $value->id;
        $result = $pdo->query("select * from product_reviews where product_id = {$product_id}");
        return $result->fetchAll(PDO::FETCH_OBJ);
    }
}
