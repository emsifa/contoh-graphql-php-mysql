<?php

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;

class ProductImageType extends ObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'Product Image',
            'description' => 'Data image produk',
            'fields' => function() {
                return [
                    'id' => [
                        'type' => Types::nonNull(Types::int()),
                        // @FIXME: nggak ngerti kenapa 'id' ini mesti di resolve dulu
                        'resolve' => function($value) {
                            return (int) $value->id;
                        }
                    ],
                    'product' => [
                        'type' => Types::product()
                    ],
                    'image' => [
                        'type' => Types::string()
                    ],
                    'url' => [
                        'type' => Types::string()
                    ]
                ];
            },
            'resolveField' => function($value, $args, $context, ResolveInfo $info) {
                if (method_exists($this, $info->fieldName)) {
                    return $this->{$info->fieldName}($value, $args, $context, $info);
                } else {
                    return $value->{$info->fieldName};
                }
            }
        ];
        parent::__construct($config);
    }

    public function product($value, $args, $context)
    {
        $pdo = $context['pdo'];
        $product_id = $value->product_id;
        $result = $pdo->query("select * from products where id = {$product_id}");
        return $result->fetchAll(PDO::FETCH_OBJ);
    }

    public function url($value, $args, $context)
    {
        return BASE_URL.'/product/images/'.$value->image;
    }
}
