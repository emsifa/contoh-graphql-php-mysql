<?php

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;

class ProductReviewType extends ObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'Product Review',
            'description' => 'Data review produk',
            'fields' => function() {
                return [
                    'id' => [
                        'type' => Types::nonNull(Types::int()),
                        // @FIXME: nggak ngerti kenapa 'id' ini mesti di resolve dulu
                        'resolve' => function($value) {
                            return (int) $value->id;
                        }
                    ],
                    'user' => [
                        'type' => Types::user()
                    ],
                    'product' => [
                        'type' => Types::product()
                    ],
                    'star' => [
                        'type' => Types::int()
                    ],
                    'message' => [
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

    public function user($value, $args, $context)
    {
        $pdo = $context['pdo'];
        $user_id = $value->user_id;
        $result = $pdo->query("select * from users where id = {$user_id}");
        return $result->fetchObject() ?: null;
    }

    public function product($value, $args, $context)
    {
        $pdo = $context['pdo'];
        $product_id = $value->product_id;
        $result = $pdo->query("select * from products where id = {$product_id}");
        return $result->fetchObject() ?: null;
    }
}
